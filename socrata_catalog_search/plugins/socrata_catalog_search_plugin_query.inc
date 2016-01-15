<?php
/**
 * @file
 * A Views query plugin for executing queries against a Socrata dataset
 */

class socrata_catalog_search_plugin_query extends views_plugin_query {
  // Properties
  var $where = array();
  var $orderby = array();
  var $has_aggregate = FALSE;

  /**
   * Constructor; Create the basic query object and fill with default values.
   */
  function init($base_table, $base_field, $options) {
    parent::init($base_table, $base_field, $options);
  }

  /**
   * Get aggregation info for group by queries.
   */
  function get_aggregation_info() {
    return array();
  }

  /**
   * Generate a query and a countquery from all of the information supplied
   * to the object.
   *
   * @param bool $get_count
   *   Provide a countquery if this is true, otherwise provide a normal query.
   */
  function query($get_count = FALSE) {
    // Build the query.
    $query = db_select($this->base_table)->extend('SocrataCatalogSearchSelectQuery');
    $query->addTag('socrata');
    $query->addTag('socrata_' . $this->view->name);

    // Construct where clause from Views filter grouping.
    foreach ($this->where as $where) {
      $queries = array();
      foreach ($where['conditions'] as $cond) {
        // Multiple values for condition, suss out.
        if (is_array($cond['value']) && !is_string($cond['value']) && !empty($cond['value'])) {
          $values = implode(',', $cond['value']);
        }
        // Otherwise simple field-value comparison.
        else {
          $values = $cond['value'];
        }
        $query->params[$cond['field']] = $values;
      }
    }

    // If this is a full query build vs a counter query, add on options.
    if (!$get_count) {
      // Suss out offset-limit options.
      if (!empty($this->limit)) {
        $query->params['limit'] = $this->limit;
        $query->range(0, $this->limit);
      }
      if (!empty($this->offset)) {
        $query->params['offset'] = $this->offset;
        $query->range($this->offset, $this->limit);
      }

      // Suss out sort fields.
      if (!empty($this->orderby)) {
        $sort_fields = array();
        foreach ($this->orderby as $orderby) {
          $query->orderBy[$orderby['field']] = $orderby['direction'];
        }
      }
    }
    else {
      // @todo: Remove this section because it doesn't actually do anything
      // although we do still need a count query.
      /*if (!$this->has_aggregate) {
        $query->params['$select'] = 'count(*)';
        $query->addExpression('count(*)');
      }*/
    }

    return $query;
  }

  /**
   * Let modules modify the query just prior to finalizing it.
   */
  function alter(&$view) {
    foreach (module_implements('views_query_alter') as $module) {
      $function = $module . '_views_query_alter';
      $function($view, $this);
    }
  }

  /**
   * Builds the necessary info to execute the query.
   *
   * @param view $view
   *   The view which is executed.
   */
  function build(&$view) {
    // Store the view in the object to be able to use it later.
    $this->view = $view;

    $view->init_pager();

    // Let the pager modify the query to add limits.
    $this->pager->query();

    $view->build_info['query'] = $this->query();
    $view->build_info['count_query'] = $this->query(TRUE);
  }

  /**
   * Executes the query and fills the associated view object with according
   * values.
   *
   * Values to set: $view->result, $view->total_rows, $view->execute_time,
   * $view->pager['current_page'].
   *
   * $view->result should contain an array of objects. The array must use a
   * numeric index starting at 0.
   *
   * @param view $view
   *   The view which is executed.
   */
  function execute(&$view) {
    _socrata_dbg($view->build_info);
    $query = $view->build_info['query'];
    $count_query = $view->build_info['count_query'];

    $start = microtime(TRUE);
    $result = array();
    $table = $this->base_table;

    // Load source info.
    $source = $query->getSource();
    if ($source) {
      // Get total count of items and force initial limit if not set.
      $num_dataset_rows = 0;
      $resp = $count_query->execute();
      if ($resp !== FALSE && !empty($resp['data']) && isset($resp['data']['resultSetSize'])) {
        $num_dataset_rows = $resp['data']['resultSetSize'];
      }

      // Let the pager modify the query to add limits.
      $this->pager->pre_execute($query);

      // Execute main query, looping if we need to get more than 100 rows.
      do {
        $resp = $query->execute();
        if ($resp !== FALSE) {
          foreach ($resp['data']['results'] as $row) {
            // Put each row into an array instead of an object
            // in case we need to sort on one or more fields.
            $new_row = array();

            // Hard-coded mapping
            $new_row['id'] = $row['resource']['id'];
            $new_row['name'] = $row['resource']['name'];
            $new_row['description'] = $row['resource']['description'];
            $new_row['updatedAt'] = $row['resource']['updatedAt'];
            $new_row['type'] = $row['resource']['type'];
            $new_row['categories'] = implode(',', $row['classification']['categories']);
            $new_row['tags'] = implode(',', $row['classification']['tags']);
            $new_row['domain'] = $row['metadata']['domain'];
            $new_row['permalink'] = $row['permalink'];
            $new_row['link'] = $row['link'];
            $result[] = $new_row;
          }

          // If an "all items" query, bump offset, go again.
          if (empty($query->params['limit'])) {
            $query->params['offset'] = count($result);
          }
        }
      } while (empty($query->params['limit']) && !empty($resp['data']));

      // Sort the result in Drupal because the API doesn't support it.
      // Adapted from https://secure.php.net/manual/en/function.array-multisort.php#100534.
      if (isset($query->orderBy) && $result) {
        $order_by_fields = $query->orderBy;

        // Build the arguments from the order by fields.
        $args = array();
        foreach ($order_by_fields as $order_by_field => $order) {
          $tmp = array();
          foreach ($result as $key => $row) {
            $tmp[$key] = $row[$order_by_field];
          }
          $args[] = $tmp;
          if ($order == 'DESC') {
            $args[] = SORT_DESC;
          } else {
            $args[] = SORT_ASC;
          }
        }
        // Add the data to the args.
        $args[] = &$result;

        call_user_func_array('array_multisort', $args);
        $result = array_pop($args);
      }

      // Convert the row arrays to objects for use by Views.
      foreach ($result as $key => $row) {
        $result[$key] = (object) $row;
      }
    }

    // Store off values from query in View.
    $view->result = $result;
    $view->total_rows = count($result);
    $this->pager->post_execute($view->result);

    // Execute count query for pager if necessary.
    if ($this->pager->use_count_query()) {
      $this->pager->total_items = $num_dataset_rows;
      $view->total_rows = $this->pager->get_total_items();
      $this->pager->update_page_info();
    }

    // Wrap up query.
    $view->execute_time = microtime(TRUE) - $start;
  }

  /***************************************************************************
   * REQUIRED methods for a Views query plugin leveraging base handlers
   **************************************************************************/

  /**
   * Add field to the query
   */
  function add_field($table, $field, $alias = '', $params = array()) {
    if (isset($params['function'])) {
      $alias = $params['function'] . '_' . $field;
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
   * Copied from views_plugin_query_default, called by filter handlers.
   */
  function add_where($group, $field, $value = NULL, $operator = NULL) {
    // Ensure all variants of 0 are actually 0. Thus '', 0 and NULL are all
    // the default group.
    if (empty($group)) {
      $group = 0;
    }

    // Check for a group.
    if (!isset($this->where[$group])) {
      $this->set_where_group('AND', $group);
    }

    $exploded_field = explode('.', $field);
    $this->where[$group]['conditions'][] = array(
      'field' => array_pop($exploded_field),
      'value' => $value,
      'operator' => $operator,
    );
  }

  /**
   * Ripped straight out of views_plugin_query_default.
   */
  function add_where_expression($group, $snippet, $args = array()) {
    // Ensure all variants of 0 are actually 0. Thus '', 0 and NULL are all
    // the default group.
    if (empty($group)) {
      $group = 0;
    }

    // Check for a group.
    if (!isset($this->where[$group])) {
      $this->set_where_group('AND', $group);
    }

    $this->where[$group]['conditions'][] = array(
      'field' => $snippet,
      'value' => $args,
      'operator' => 'formula',
    );
  }

  /**
   * Copied from views_plugin_query_default, 
   * modified for our simplified case, called by sort handlers.
   */
  function add_orderby($table, $field, $order = 'ASC', $alias = '', $params = array()) {
    // Only fill out this aliasing if there is a table;
    // otherwise we assume it is a formula.
    if (!$alias && $table) {
      $as = $field;
    }
    else {
      $as = $alias;
    }

    if ($field) {
      $as = $this->add_field($table, $field, $as, $params);
    }

    // If using an aggregate function, wrap the field in the function.
    if ($params && isset($params['function'])) {
      $as = $params['function'] . '(' . $as . ')';
    }

    $this->orderby[] = array(
      'field' => $as,
      'direction' => strtoupper($order),
    );
  }

  /**
   * Remove all fields that may have been added; primarily used for summary
   * mode where we're changing the query because we didn't get data we needed.
   */
  function clear_fields() {
    $this->fields = array();
  }

  /**
   * Dummy placeholder method to satisfy Views core handlers.
   */
  function ensure_table($table) {
    return $table;
  }
}
