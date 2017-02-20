<?php

namespace Drupal\socrata_catalog_search\Plugin\views\query;

use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\ViewExecutable;
use Drupal\views\ResultRow;
use Drupal\socrata_views\Plugin\views\query\Soql;

/**
 * Views query plugin for a Socrata Catalog Search query.
 *
 * @ingroup views_query_plugins
 *
 * @ViewsQuery(
 *   id = "socrata_catalog_search_query",
 *   title = @Translation("Socrata Catalog Search Query"),
 *   help = @Translation("Query will be generated and run using the Socrata Catalog Search API.")
 * )
 */
class SocrataCatalogSearch extends Soql {
  /**
   * Filter parameters.
   *
   * @var string
   */
  public $where = [];

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
  }

  /**
   * Get aggregation info for group by queries.
   */
  public function getAggregationInfo() {
    return [];
  }

  /**
   * {@inheritdoc}
   *
   * @param bool $get_count
   *   Provide a countquery if this is true, otherwise provide a normal query.
   */
  public function query($get_count = FALSE) {
    // Build the query.
    $query = \Drupal::database()->select($this->base_table)->extend('Drupal\socrata_catalog_search\SocrataCatalogSearchSelectQuery');
    $query->addTag('socrata');
    $query->addTag('socrata_' . $this->view->storage->id());

    // Construct where clause from Views filter grouping.
    foreach ($this->where as $where) {
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
        foreach ($this->orderby as $orderby) {
          $query->orderBy[$orderby['field']] = $orderby['direction'];
        }
      }
    }

    return $query;
  }

  /**
   * {@inheritdoc}
   *
   * @param ViewExecutable $view
   *   The view which is executed.
   */
  public function execute(ViewExecutable $view) {
    _socrata_dbg($view->build_info);
    $query = $view->build_info['query'];
    $count_query = $view->build_info['count_query'];

    $start = microtime(TRUE);
    $result = [];

    // Get total count of items and force initial limit if not set.
    $num_dataset_rows = 0;
    $resp = $count_query->execute();
    if ($resp !== FALSE && !empty($resp['data']) && isset($resp['data']['resultSetSize'])) {
      $num_dataset_rows = $resp['data']['resultSetSize'];
    }

    // Let the pager modify the query to add limits.
    $view->pager->preExecute($query);

    // Execute main query, looping if we need to get more than 100 rows.
    do {
      $resp = $query->execute();
      if ($resp !== FALSE) {
        foreach ($resp['data']['results'] as $row) {
          // Put each row into an array instead of an object
          // in case we need to sort on one or more fields.
          $new_row = new ResultRow();

          // Hard-coded mapping.
          $new_row->id = $row['resource']['id'];
          $new_row->name = $row['resource']['name'];
          $new_row->description = $row['resource']['description'];
          $new_row->updatedAt = $row['resource']['updatedAt'];
          $new_row->type = $row['resource']['type'];
          $new_row->domain_category = $row['classification']['domain_category'];
          $new_row->domain_tags = implode(',', $row['classification']['domain_tags']);
          $new_row->domain = $row['metadata']['domain'];
          $new_row->permalink = $row['permalink'];
          $new_row->link = $row['link'];
          $result[] = $new_row;
        }

        // If an "all items" query, bump offset, go again.
        if (empty($query->params['limit'])) {
          $query->params['offset'] = count($result);
        }
      }
    } while (empty($query->params['limit']) && !empty($resp['data']));

    // Sort the result here because the API doesn't support it. Adapted from
    // https://secure.php.net/manual/en/function.array-multisort.php#100534.
    if (isset($query->orderBy) && $result) {
      $order_by_fields = $query->orderBy;

      // Build the arguments from the order by fields.
      $args = [];
      foreach ($order_by_fields as $order_by_field => $order) {
        $tmp = [];
        foreach ($result as $key => $row) {
          $tmp[$key] = $row->{$order_by_field};
        }
        $args[] = $tmp;
        if ($order == 'DESC') {
          $args[] = SORT_DESC;
        }
        else {
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

    // Store off values from query in View.
    $view->result = $result;
    $view->total_rows = count($result);
    // $view->pager->post_execute($view->result);.
    // Execute count query for pager if necessary.
    // if ($this->pager->use_count_query()) {.
    $view->pager->total_items = $num_dataset_rows;
    $view->total_rows = $view->pager->getTotalItems();
    $view->pager->updatePageInfo();
    // }.
    // Wrap up query.
    $view->execute_time = microtime(TRUE) - $start;
  }

  /**
   * Add a simple GROUP BY clause to the query.
   *
   * The caller is responsible
   * for ensuring that the fields are fully qualified and the table is properly
   * added.
   */
  public function addGroupBy($clause) {
    $this->groupby = [];
  }

}
