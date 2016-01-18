<?php

/**
 * @file
 * Definition of socrata_catalog_search_handler_filter_list.
 */

/**
 * @ingroup views_filter_handlers
 */
class socrata_catalog_search_handler_filter_list extends views_handler_filter_in_operator {
  function get_value_options() {
    $field = $this->real_field;
    $lists = &drupal_static(__FUNCTION__);
    if (!isset($lists[$field])) {
      $options = array();
      $query = db_select($this->view->base_table)->extend('SocrataCatalogSearchSelectQuery');
      $query->suffix = $field;
      $resp = $query->execute();
      if ($resp && isset($resp['data']['results'])) {
        $results = $resp['data']['results'];
        foreach ($results as $result) {
          // @todo Decide if showing counts is worthwhile.
          // Would need to override admin_summary() too.
          /*if (isset($result['count'])) {
            $suffix = '&nbsp;' . t('(@count)', array('@count' => $result['count']));
          } else {*/
            $suffix =  '';
          // }
          $value = array_shift($result);
          $options[$value] = $value . $suffix;
        }
        asort($options);
      }

      $lists[$field] = $options;
    }

    $this->value_options = $lists[$field];
    return $lists;
  }

  function option_definition() {
    $options = parent::option_definition();

    $options['operator']['default'] = '=';
    $options['value']['default'] = array();
    $options['expose']['contains']['reduce'] = array('default' => FALSE, 'bool' => TRUE);

    return $options;
  }

  /**
   * This kind of construct makes it relatively easy for a child class
   * to add or remove functionality by overriding this function and
   * adding/removing items from this array.
   */
  function operators() {
    $operators = array(
      '=' => array(
        'title' => t('Is all of'),
        'short' => t('is all of'),
        'short_single' => t('is all of'),
        'method' => 'op_simple',
        'values' => 1,
      ),
    );
    return $operators;
  }
}
