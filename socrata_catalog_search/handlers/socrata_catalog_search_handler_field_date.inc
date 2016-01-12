<?php

/**
 * @file
 * Definition of socrata_catalog_search_handler_field_date.
 */

/**
 * Field handler to provide date.
 *
 *
 * @ingroup views_field_handlers
 */
class socrata_catalog_search_handler_field_date extends views_handler_field_date {
  /**
   * Get the value that's supposed to be rendered.
   *
   * @param $values
   *   An object containing all retrieved values.
   * @param $field
   *   Optional name of the field where the value is stored.
   */
  function get_value($values, $field = NULL) {
    $values = parent::get_value($values, $field);

    // Convert the date string to a timestamp.
    return strtotime($values);
  }
}
