<?php

/**
 * @file
 * Definition of socrata_catalog_search_handler_filter_type.
 */

/**
 * @ingroup views_filter_handlers
 */
class socrata_catalog_search_handler_filter_type extends socrata_catalog_search_handler_filter_list {
  // Change the value form element to a select.
  var $value_form_type = 'select';

  function get_value_options() {
    $options = array(
      'charts' => t("Charts"),
      'datalenses' => t("Datalenses"),
      'datasets' => t("Datasets"),
      'files' => t("Files"),
      'hrefs' => t("Hrefs"),
      'maps' => t("Maps"),
      'stories' => t("Stories"),
    );
    $this->value_options = $options;
  }

  function operators() {
    $operators = array(
      '=' => array(
        'title' => t('Is equal to'),
        'short' => t('is equal to'),
        'short_single' => t('is equal to'),
        'method' => 'op_simple',
        'values' => 1,
      ),
    );
    return $operators;
  }

  /**
   * Alter the value field to be a single-value select.
   */
  function value_form(&$form, &$form_state) {
    parent::value_form($form, $form_state);

    unset($form['value']['#options']['all']);
    $form['value']['#multiple'] = FALSE;
    $form['value']['#size'] = 0;
  }

  /**
   * Turn the value back into an array for later use by parent methods.
   */
  function value_submit($form, &$form_state) {
    $form_state['values']['options']['value'] = drupal_map_assoc(array($form_state['values']['options']['value']));
  }

  /**
   * Override the field name.
   */
  function op_simple() {
    if (empty($this->value)) {
      return;
    }
    $this->ensure_my_table();

    // We use array_values() because the checkboxes keep keys and that can cause
    // array addition problems.
    $this->query->add_where($this->options['group'], "only", array_values($this->value), $this->operator);
  }
}
