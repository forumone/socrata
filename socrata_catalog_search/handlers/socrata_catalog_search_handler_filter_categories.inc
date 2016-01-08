<?php

/**
 * @file
 * Definition of views_handler_filter_many_to_one.
 */

/**
 * Complex filter to handle filtering for many to one relationships,
 * such as terms (many terms per node) or roles (many roles per user).
 *
 * The construct method needs to be overridden to provide a list of options;
 * alternately, the value_form and admin_summary methods need to be overriden
 * to provide something that isn't just a select list.
 *
 * @ingroup views_filter_handlers
 */
class socrata_catalog_search_handler_filter_categories extends views_handler_filter_in_operator {
  function get_value_options() {
    // @todo Make this a look up from http://api.us.socrata.com/api/catalog/v1/categories.
    $options = array(
      'education' => t("education"),
      'finance' => t("finance"),
      'health' => t("health"),
      'housing & development' => t("housing & development"),
      'politics' => t("politics"),
      'public safety' => t("public safety"),
      'transportation' => t("transportation"),
    );
    $this->value_options = $options;
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
        'title' => t('Is one of'),
        'short' => t('in'),
        'short_single' => t('='),
        'method' => 'op_simple',
        'values' => 1,
      ),
    );
    return $operators;
  }
}
