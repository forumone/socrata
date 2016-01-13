<?php

/**
 * @file
 * Definition of socrata_catalog_search_handler_argument_list.
 */

/**
 * @ingroup views_argument_handlers
 */
class socrata_catalog_search_handler_argument_list extends views_handler_argument_string {
  /**
   * Very simplified override because API only handles basic querying.
   */
  function query($group_by = FALSE) {
    $argument = $this->argument;
    $field = "$this->table_alias.$this->real_field";

    $this->query->add_where(0, $field, $argument, '=');
  }
}
