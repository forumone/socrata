<?php

/**
 * @file
 * Contains \Drupal\socrata\socrata_views\Plugin\views\query\Soql.
 */

namespace Drupal\socrata_views\Plugin\views\query;

use Drupal\views\Plugin\views\query\QueryPluginBase;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\Core\Database\DatabaseExceptionWrapper;
use Drupal\views\Plugin\views\join\JoinPluginBase;
use Drupal\views\Plugin\views\HandlerBase;
use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;
use Drupal\views\Views;

/**
 * Views query plugin for an SoQL query.
 *
 * @ingroup views_query_plugins
 *
 * @ViewsQuery(
 *   id = "socrata_views_query",
 *   title = @Translation("SoQL Query"),
 *   help = @Translation("Query will be generated and run using the Socrata database API.")
 * )
 */
class Soql extends QueryPluginBase {

  /**
   * A list of tables in the order they should be added, keyed by alias.
   */
  protected $tableQueue = array();

  /**
   * Holds an array of tables and counts added so that we can create aliases
   */
  var $tables = array();

  /**
   * An array of sections of the WHERE query. Each section is in itself
   * an array of pieces and a flag as to whether or not it should be AND
   * or OR.
   */
  var $where = array();
  /**
   * An array of sections of the HAVING query. Each section is in itself
   * an array of pieces and a flag as to whether or not it should be AND
   * or OR.
   */
  var $having = array();
  /**
   * The default operator to use when connecting the WHERE groups. May be
   * AND or OR.
   */
  protected $groupOperator = 'AND';

  /**
   * A simple array of order by clauses.
   */
  var $orderby = array();

  /**
   * A simple array of group by clauses.
   */
  var $groupby = array();


  /**
   * An array of fields.
   */
  var $fields = array();

  /**
   * A flag as to whether or not to make the primary field distinct.
   */
  var $distinct = FALSE;

  protected $hasAggregate = FALSE;

  /**
   * Should this query be optimized for counts, for example no sorts.
   */
  protected $getCountOptimized = NULL;

  /**
   * An array mapping table aliases and field names to field aliases.
   */
  // protected $fieldAliases = array();

  /**
   * Query tags which will be passed over to the dbtng query object.
   */
  var $tags = array();

  /**
   * Socrata endpoint machine name.
   */
  var $base_table = '';

  /**
   * Is the view marked as not distinct.
   *
   * @var bool
   */
  protected $noDistinct;

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);

    $base_table = $this->view->storage->get('base_table');
    $this->base_table = $base_table;
    $base_field = $this->view->storage->get('base_field');

    // init the table queue with our primary table.
    $this->tableQueue[$base_table] = array(
      'alias' => $base_table,
      'table' => $base_table,
      'relationship' => $base_table,
      'join' => NULL,
    );

    // init the tables with our primary table
    $this->tables[$base_table][$base_table] = array(
      'count' => 1,
      'alias' => $base_table,
    );

    $this->count_field = array(
      'table' => $base_table,
      'field' => $base_field,
      'alias' => $base_field,
      'count' => TRUE,
    );
  }

  /**
   * Set what field the query will count() on for paging.
   */
  public function setCountField($table, $field, $alias = NULL) {
    if (empty($alias)) {
      $alias = $table . '_' . $field;
    }
    $this->count_field = array(
      'table' => $table,
      'field' => $field,
      'alias' => $alias,
      'count' => TRUE,
    );
  }

  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['query_comment'] = array(
      'default' => '',
    );
    $options['query_tags'] = array(
      'default' => array(),
    );

    return $options;
  }

  /**
   * Add settings for the ui.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['query_comment'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Query Comment'),
      '#description' => $this->t('If set, this comment will be embedded in the query and passed to the SQL server. This can be helpful for logging or debugging.'),
      '#default_value' => $this->options['query_comment'],
    );
    $form['query_tags'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Query Tags'),
      '#description' => $this->t('If set, these tags will be appended to the query and can be used to identify the query in a module. This can be helpful for altering queries.'),
      '#default_value' => implode(', ', $this->options['query_tags']),
      '#element_validate' => array('views_element_validate_tags'),
    );
  }

  /**
   * Special submit handling.
   */
  public function submitOptionsForm(&$form, FormStateInterface $form_state) {
    $element = array('#parents' => array('query', 'options', 'query_tags'));
    $value = explode(',', NestedArray::getValue($form_state->getValues(), $element['#parents']));
    $value = array_filter(array_map('trim', $value));
    $form_state->setValueForElement($element, $value);
  }

  /**
   * Ensure a table exists in the queue; if it already exists it won't
   * do anything, but if it doesn't it will add the table queue. It will ensure
   * a path leads back to the relationship table.
   *
   * @param $table
   *   The unaliased name of the table to ensure.
   * @param $relationship
   *   The relationship to ensure the table links to. Each relationship will
   *   get a unique instance of the table being added. If not specified,
   *   will be the primary table.
   * @param \Drupal\views\Plugin\views\join\JoinPluginBase $join
   *   A Join object (or derived object) to join the alias in.
   *
   * @return
   *   The alias used to refer to this specific table, or NULL if the table
   *   cannot be ensured.
   */
  public function ensureTable($table, $relationship = NULL, JoinPluginBase $join = NULL) {
    return $table;
  }

  /**
   * Add a field to the query table, possibly with an alias. This will
   * automatically call ensureTable to make sure the required table
   * exists, *unless* $table is unset.
   *
   * @param $table
   *   The table this field is attached to. If NULL, it is assumed this will
   *   be a formula; otherwise, ensureTable is used to make sure the
   *   table exists.
   * @param $field
   *   The name of the field to add. This may be a real field or a formula.
   * @param $alias
   *   The alias to create. If not specified, the alias will be $table_$field
   *   unless $table is NULL. When adding formulae, it is recommended that an
   *   alias be used.
   * @param $params
   *   An array of parameters additional to the field that will control items
   *   such as aggregation functions and DISTINCT. Some values that are
   *   recognized:
   *   - function: An aggregation function to apply, such as SUM.
   *   - aggregate: Set to TRUE to indicate that this value should be
   *     aggregated in a GROUP BY.
   *
   * @return $name
   *   The name that this field can be referred to as. Usually this is the alias.
   */
  public function addField($table, $field, $alias = '', $params = array()) {
    // Fields will alwasy be unique in Socrata endpoints.  Creating a custom
    // array makes it much easier to craft the query URL.
    if (isset($params['function'])) {
      $alias = $params['function'] . '_' . $field;
    } elseif (empty($alias)) {
      $alias = $table . '_' . $field;
    }
    // Create a field info array.
    $field_info = array(
      'field' => $field,
      'table' => $table,
      'alias' => $alias,
    ) + $params;

    $this->fields[$field] = $field_info;

    return $field;

  }

  /**
   * Remove all fields that may've been added; primarily used for summary
   * mode where we're changing the query because we didn't get data we needed.
   */
  public function clearFields() {
    $this->fields = array();
  }

  /**
   * Add a simple WHERE clause to the query. The caller is responsible for
   * ensuring that all fields are fully qualified (TABLE.FIELD) and that
   * the table already exists in the query.
   *
   * @param $group
   *   The WHERE group to add these to; groups are used to create AND/OR
   *   sections. Groups cannot be nested. Use 0 as the default group.
   *   If the group does not yet exist it will be created as an AND group.
   * @param $field
   *   The name of the field to check.
   * @param $value
   *   The value to test the field against. In most cases, this is a scalar. For more
   *   complex options, it is an array. The meaning of each element in the array is
   *   dependent on the $operator.
   * @param $operator
   *   The comparison operator, such as =, <, or >=. It also accepts more
   *   complex options such as IN, LIKE, LIKE BINARY, or BETWEEN. Defaults to =.
   *   If $field is a string you have to use 'formula' here.
   *
   * The $field, $value and $operator arguments can also be passed in with a
   * single DatabaseCondition object, like this:
   * @code
   *   $this->query->addWhere(
   *     $this->options['group'],
   *     db_or()
   *       ->condition($field, $value, 'NOT IN')
   *       ->condition($field, $value, 'IS NULL')
   *   );
   * @endcode
   *
   * @see \Drupal\Core\Database\Query\ConditionInterface::condition()
   * @see \Drupal\Core\Database\Query\Condition
   */
  public function addWhere($group, $field, $value = NULL, $operator = NULL) {
    // Ensure all variants of 0 are actually 0. Thus '', 0 and NULL are all
    // the default group.
    if (empty($group)) {
      $group = 0;
    }

    // Check for a group.
    if (!isset($this->where[$group])) {
      $this->setWhereGroup('AND', $group);
    }

    $this->where[$group]['conditions'][] = array(
      'field' => $field,
      'value' => $value,
      'operator' => $operator,
    );
  }

  /**
   * Add a complex WHERE clause to the query.
   *
   * The caller is responsible for ensuring that all fields are fully qualified
   * (TABLE.FIELD) and that the table already exists in the query.
   * Internally the dbtng method "where" is used.
   *
   * @param $group
   *   The WHERE group to add these to; groups are used to create AND/OR
   *   sections. Groups cannot be nested. Use 0 as the default group.
   *   If the group does not yet exist it will be created as an AND group.
   * @param $snippet
   *   The snippet to check. This can be either a column or
   *   a complex expression like "UPPER(table.field) = 'value'"
   * @param $args
   *   An associative array of arguments.
   *
   * @see QueryConditionInterface::where()
   */
  public function addWhereExpression($group, $snippet, $args = array()) {
    // Ensure all variants of 0 are actually 0. Thus '', 0 and NULL are all
    // the default group.
    if (empty($group)) {
      $group = 0;
    }

    // Check for a group.
    if (!isset($this->where[$group])) {
      $this->setWhereGroup('AND', $group);
    }

    $this->where[$group]['conditions'][] = array(
      'field' => $snippet,
      'value' => $args,
      'operator' => 'formula',
    );
  }

  /**
   * Add a complex HAVING clause to the query.
   * The caller is responsible for ensuring that all fields are fully qualified
   * (TABLE.FIELD) and that the table and an appropriate GROUP BY already exist in the query.
   * Internally the dbtng method "having" is used.
   *
   * @param $group
   *   The HAVING group to add these to; groups are used to create AND/OR
   *   sections. Groups cannot be nested. Use 0 as the default group.
   *   If the group does not yet exist it will be created as an AND group.
   * @param $snippet
   *   The snippet to check. This can be either a column or
   *   a complex expression like "COUNT(table.field) > 3"
   * @param $args
   *   An associative array of arguments.
   *
   * @see QueryConditionInterface::having()
   */
  public function addHavingExpression($group, $snippet, $args = array()) {
    // Ensure all variants of 0 are actually 0. Thus '', 0 and NULL are all
    // the default group.
    if (empty($group)) {
      $group = 0;
    }

    // Check for a group.
    if (!isset($this->having[$group])) {
      $this->setWhereGroup('AND', $group, 'having');
    }

    // Add the clause and the args.
    $this->having[$group]['conditions'][] = array(
      'field' => $snippet,
      'value' => $args,
      'operator' => 'formula',
    );
  }

  /**
   * Add an ORDER BY clause to the query.
   *
   * @param $table
   *   The table this field is part of. If a formula, enter NULL.
   *   If you want to orderby random use "rand" as table and nothing else.
   * @param $field
   *   The field or formula to sort on. If already a field, enter NULL
   *   and put in the alias.
   * @param $order
   *   Either ASC or DESC.
   * @param $alias
   *   The alias to add the field as. In SQL, all fields in the order by
   *   must also be in the SELECT portion. If an $alias isn't specified
   *   one will be generated for from the $field; however, if the
   *   $field is a formula, this alias will likely fail.
   * @param $params
   *   Any params that should be passed through to the addField.
   */
  public function addOrderBy($table, $field = NULL, $order = 'ASC', $alias = '', $params = array()) {
    // Only ensure the table if it's not the special random key.
    // @todo: Maybe it would make sense to just add an addOrderByRand or something similar.
    if ($table && $table != 'rand') {
      $this->ensureTable($table);
    }

    // Only fill out this aliasing if there is a table;
    // otherwise we assume it is a formula.
    if (!$alias && $table) {
      $as = $table . '_' . $field;
    }
    else {
      $as = $alias;
    }

    if ($field) {
      $as = $this->addField($table, $field, $as, $params);
    }

    $this->orderby[] = array(
      'field' => $as,
      'direction' => strtoupper($order)
    );
  }

  /**
   * Add a simple GROUP BY clause to the query. The caller is responsible
   * for ensuring that the fields are fully qualified and the table is properly
   * added.
   */
  public function addGroupBy($clause) {
    // Only add it if it's not already in there.
    if (!in_array($clause, $this->groupby)) {
      $this->groupby[] = $clause;
    }
  }

  /**
   * Returns the alias for the given field added to $table.
   *
   * @access protected
   *
   * @see \Drupal\views\Plugin\views\query\Sql::addField
   */
  /*protected function getFieldAlias($table_alias, $field) {
    return isset($this->fieldAliases[$table_alias][$field]) ? $this->fieldAliases[$table_alias][$field] : FALSE;
  }*/

  /**
   * Adds a query tag to the sql object.
   *
   * @see SelectQuery::addTag()
   */
  public function addTag($tag) {
    $this->tags[] = $tag;
  }

  /**
   * Generates a unique placeholder used in the db query.
   */
  function placeholder($base = 'views') {
    static $placeholders = array();
    if (!isset($placeholders[$base])) {
      $placeholders[$base] = 0;
      return ':' . $base;
    }
    else {
      return ':' . $base . ++$placeholders[$base];
    }
  }

  /**
   * Construct the "WHERE" or "HAVING" part of the query.
   *
   * As views has to wrap the conditions from arguments with AND, a special
   * group is wrapped around all conditions. This special group has the ID 0.
   * There is other code in filters which makes sure that the group IDs are
   * higher than zero.
   *
   * @param $where
   *   'where' or 'having'.
   */
  protected function buildCondition($where = 'where') {
    $has_condition = FALSE;
    $has_arguments = FALSE;
    $has_filter = FALSE;

    $main_group = db_and();
    $filter_group = $this->groupOperator == 'OR' ? db_or() : db_and();

    foreach ($this->$where as $group => $info) {

      if (!empty($info['conditions'])) {
        $sub_group = $info['type'] == 'OR' ? db_or() : db_and();
        foreach ($info['conditions'] as $clause) {
          if ($clause['operator'] == 'formula') {
            $has_condition = TRUE;
            $sub_group->where($clause['field'], $clause['value']);
          }
          else {
            $has_condition = TRUE;
            $sub_group->condition($clause['field'], $clause['value'], $clause['operator']);
          }
        }

        // Add the item to the filter group.
        if ($group != 0) {
          $has_filter = TRUE;
          $filter_group->condition($sub_group);
        }
        else {
          $has_arguments = TRUE;
          $main_group->condition($sub_group);
        }
      }
    }

    if ($has_filter) {
      $main_group->condition($filter_group);
    }

    if (!$has_arguments && $has_condition) {
      return $filter_group;
    }
    if ($has_arguments && $has_condition) {
      return $main_group;
    }
  }

  /**
   * Returns a list of non-aggregates to be added to the "group by" clause.
   *
   * Non-aggregates are fields that have no aggregation function (count, sum,
   * etc) applied. Since the SQL standard requires all fields to either have
   * an aggregation function applied, or to be in the GROUP BY clause, Views
   * gathers those fields and adds them to the GROUP BY clause.
   *
   * @return array
   *   An array of the fieldnames which are non-aggregates.
   */
  protected function getNonAggregates() {
    $non_aggregates = array();
    foreach ($this->fields as $field) {
      $string = '';
      if (!empty($field['table'])) {
        $string .= $field['table'] . '.';
      }
      $string .= $field['field'];
      $fieldname = (!empty($field['alias']) ? $field['alias'] : $string);

      if (!empty($field['count'])) {
        // Retained for compatibility.
        $field['function'] = 'count';
      }

      if (!empty($field['function'])) {
        $this->hasAggregate = TRUE;
      }
      // This is a formula, using no tables.
      elseif (empty($field['table'])) {
        $non_aggregates[] = $fieldname;
      }
      elseif (empty($field['aggregate'])) {
        $non_aggregates[] = $fieldname;
      }

      if ($this->getCountOptimized) {
        // We only want the first field in this case.
        break;
      }
    }

    return $non_aggregates;
  }

  /**
  /**
   * Generate a query and a countquery from all of the information supplied
   * to the object.
   *
   * @param $get_count
   *   Provide a countquery if this is true, otherwise provide a normal query.
   */
  public function query($get_count = FALSE) {
    $query = \Drupal::database()->select($this->base_table)->extend('Drupal\socrata\SocrataSelectQuery');
    $query->addTag('socrata');
    $query->addTag('socrata_' . $this->view->storage->id());

    // Construct where clause from Views filter grouping.
    $groups = array();
    foreach ($this->where as $where) {
      $queries = array();
      foreach ($where['conditions'] as $cond) {
        // Multiple values for condition, suss out.
        if (is_array($cond['value']) && !is_string($cond['value']) && !empty($cond['value'])) {
          $in_queries = array();
          foreach ($cond['value'] as $in_val) {
            $in_queries[] = $this->constructQueryComponent($cond['field'], $in_val, $cond['operator']);
          }
          if (!empty($in_queries)) {
            $queries[] = '(' . implode(' AND ', $in_queries) . ')';
          }
        }
        // Otherwise simple field-value comparison.
        else {
          $queries[] = $this->constructQueryComponent($cond['field'], $cond['value'], $cond['operator']);
        }
      }
      if (!empty($queries)) {
        $groups[] = '(' . implode(" {$where['type']} ", $queries) . ')';
        $query->where(implode(" {$this->groupOperator} ", $groups));
        $query->params['$where'] = implode(" {$this->groupOperator} ", $groups);
      }
    }

    // Store off requested fields.
    $this->hasAggregate = $this->view->display_handler->getOption('group_by');

    if (!empty($this->fields)) {
      $fields_list = $non_aggregates = array();
      foreach ($this->fields as $field => $field_info) {
        // If an aggregate function is specified, wrap it around the field.
        if (isset($field_info['function']) && $this->hasAggregate) {
          $fields_list[] = $field_info['function'] . '(' . $field . ')';
        }
        else {
          $fields_list[] = $field;
          $non_aggregates[] = $field;
        }
      }
    }
    if ($this->hasAggregate && (!empty($this->groupby) || !empty($non_aggregates))) {
      $groupby = array_unique(array_merge($this->groupby, $non_aggregates));
      $query->params['$group'] = implode(',', $groupby);
    }
    else {
      $fields_list = $non_aggregates;
    }
    $query->params['$select'] = implode(',', $fields_list);
    $query->fields($this->base_table, $fields_list);

    // If this is a full query build vs a counter query, add on options.
    if (!$get_count) {
      // Suss out offset-limit options.
      if (empty($this->limit)) {
        $this->limit = 1000;
      }
      $query->params['$limit'] = $this->limit;
      $query->range(0, $this->limit);
      if (!empty($this->offset)) {
        $query->params['$offset'] = $this->offset;
        $query->range($this->offset, $this->limit);
      }

      // Suss out sort fields.
      if (!empty($this->orderby)) {
        $sort_fields = array();
        foreach ($this->orderby as $orderby) {
          $sort_fields[] = "{$orderby['field']} {$orderby['direction']}";
          $query->orderBy($orderby['field'], $orderby['direction']);
        }
        $query->params['$order'] = implode(',', $sort_fields);
      }
    }
    else {
      if (!$this->hasAggregate) {
        $query->params['$select'] = 'count(*)';
        $query->addExpression('count(*)');
      }
    }

    return $query;
  }

/**
 * Utility method for constructing components for predicate.
 *
 * @param string $field
 * @param string $value
 * @param string $operator
 *
 * @return string
 */
private function constructQueryComponent($field, $value, $operator) {
  $component = '';

  // Check to see if the predicate component is a prebuilt formula.
  if ($operator == 'formula') {
    $component = $field;
  }
  // Otherwise, build a "normal" comparison predicate component.
  else {
    $component = "{$field}{$operator}'{$value}'";
  }

  return $component;
}

  /**
   * Get the arguments attached to the WHERE and HAVING clauses of this query.
   */
  public function getWhereArgs() {
    $args = array();
    foreach ($this->where as $where) {
      $args = array_merge($args, $where['args']);
    }
    foreach ($this->having as $having) {
      $args = array_merge($args, $having['args']);
    }
    return $args;
  }

  /**
   * Let modules modify the query just prior to finalizing it.
   */
  function alter(ViewExecutable $view) {
    \Drupal::moduleHandler()->invokeAll('views_query_alter', array($view, $this));
  }

  /**
   * Builds the necessary info to execute the query.
   */
  function build(ViewExecutable $view) {
    // Store the view in the object to be able to use it later.
    $this->view = $view;

    $view->initPager();

    // Let the pager modify the query to add limits.
    $view->pager->query();

    // Have to override limit set by pager->query() above
    // because Mini pager adds 1 for some odd reason.
    // See line 61 of core/modules/views/src/Plugin/views/pager/Mini.php.
    $this->limit = $view->pager->getItemsPerPage();

    $view->build_info['query'] = $this->query();
    $view->build_info['count_query'] = $this->query(TRUE);
  }

  /**
   * Executes the query and fills the associated view object with according
   * values.
   *
   * Values to set: $view->result, $view->total_rows, $view->execute_time,
   * $view->current_page.
   */
  function execute(ViewExecutable $view) {
    $query = $view->build_info['query'];
    $count_query = $view->build_info['count_query'];

    if ($query) {
      $result = array();
      // Count queries must be run through the preExecute() method.
      $count_query->preExecute();

      $start = microtime(TRUE);
      // Get total count of items and force initial limit if not set.
      $total_items = $index = 0;
      $resp = $count_query->execute();
      if ($resp !== FALSE && !empty($resp['data'][0]['count'])) {
        $total_items = intval($resp['data'][0]['count']);
      }

      // Just set total rows on pager instead of calling pager->executeCountQuery()
      // because method calls fetchField(), which breaks for non-SQL queries.
      $view->pager->total_items = $total_items;

      // Let the pager modify the query to add limits.
      $view->pager->preExecute($query);

      // Execute main query, looping if we need to get more than 1000 rows.
      do {
        $resp = $query->execute();
        if ($resp !== FALSE && isset($resp['data'])) {
          // Have to map Socrata result field labels back onto what Views knows them as.
          $original_field_names = $view->query->fields;
          $field_name_map = array();
          foreach ($original_field_names as $field => $attributes) {
            if (isset($attributes['function']) && $this->hasAggregate) {
              $field_alias = $attributes['alias'];
            }
            else {
              $field_alias = $field;
            }
            $field_name_map[$field_alias] = $field;
          }

          foreach ($resp['data'] as $row) {
            $new_row = new ResultRow();
            // Map the results with the correct field alias in field order.
            foreach ($field_name_map as $alias => $field) {
              // If Socrata returns a row with empty data, put in empty string.
              if (isset($row[$alias])) {
                $value = $row[$alias];
              }
              else {
                $value = '';
              }
              $new_row->{$field} = $value;
            }
            $new_row->index = $index++;
            $result[] = $new_row;
          }

          // If an "all items" query, bump offset, go again.
          if (empty($query->params['$limit'])) {
            $query->params['$offset'] = count($result);
          }
        }
      } while (empty($query->params['$limit']) && !empty($resp['data']));

      // Store off values from query in View.
      $view->result = $result;

      // The mini pager changes its total_items property in its postExecute() method
      // which breaks the mini pager so we'll just disable calling that method.
      // $view->pager->postExecute($view->result);

      $view->pager->updatePageInfo();
      $view->total_rows = $view->pager->getTotalItems();
    }
    else {
      $start = microtime(TRUE);
    }
    $view->execute_time = microtime(TRUE) - $start;
  }

  public function addSignature(ViewExecutable $view) {
    $view->query->addField(NULL, "'" . $view->storage->id() . ':' . $view->current_display . "'", 'view_name');
  }

  public function getAggregationInfo() {
    return array(
      'group' => array(
        'title' => $this->t('Group results together'),
        'is aggregate' => FALSE,
      ),
      'count' => array(
        'title' => $this->t('Count'),
        'method' => 'aggregationMethodSimple',
        'handler' => array(
          'argument' => 'groupby_numeric',
          'field' => 'numeric',
          'filter' => 'groupby_numeric',
          'sort' => 'groupby_numeric',
        ),
      ),
      'sum' => array(
        'title' => $this->t('Sum'),
        'method' => 'aggregationMethodSimple',
        'handler' => array(
          'argument' => 'groupby_numeric',
          'field' => 'numeric',
          'filter' => 'groupby_numeric',
          'sort' => 'groupby_numeric',
        ),
      ),
      'avg' => array(
        'title' => $this->t('Average'),
        'method' => 'aggregationMethodSimple',
        'handler' => array(
          'argument' => 'groupby_numeric',
          'field' => 'numeric',
          'filter' => 'groupby_numeric',
          'sort' => 'groupby_numeric',
        ),
      ),
      'min' => array(
        'title' => $this->t('Minimum'),
        'method' => 'aggregationMethodSimple',
        'handler' => array(
          'argument' => 'groupby_numeric',
          'field' => 'numeric',
          'filter' => 'groupby_numeric',
          'sort' => 'groupby_numeric',
        ),
      ),
      'max' => array(
        'title' => $this->t('Maximum'),
        'method' => 'aggregationMethodSimple',
        'handler' => array(
          'argument' => 'groupby_numeric',
          'field' => 'numeric',
          'filter' => 'groupby_numeric',
          'sort' => 'groupby_numeric',
        ),
      ),
    );
  }

  public function aggregationMethodSimple($group_type, $field) {
    return strtoupper($group_type) . '(' . $field . ')';
  }

}
