<?php

/**
 * @file
 * Definition of socrata_catalog_search_handler_filter_domain.
 */

/**
 * @ingroup views_filter_handlers
 */
class socrata_catalog_search_handler_filter_domain extends views_handler_filter_string {
  function operators() {
    $operators = array(
      '=' => array(
        'title' => t('Is equal to'),
        'short' => t('='),
        'method' => 'op_equal',
        'values' => 1,
      ),
    );
    return $operators;
  }

  function query() {
    $this->ensure_my_table();

    // Override the field name per http://labs.socrata.com/docs/search.html#-full-text-search.
    $field = 'domains';

    $info = $this->operators();
    if (!empty($info[$this->operator]['method'])) {
      $this->{$info[$this->operator]['method']}($field);
    }
  }

  function value_form(&$form, &$form_state) {
    parent::value_form($form, $form_state);

    // Add help text.
    $form['value']['#description'] = t('You may enter multiple values separated by a comma as "OR" query parameters.');
  }
}
