<?php

/**
 * @file
 * Definition of socrata_catalog_search_handler_argument_fulltext.
 */

/**
 * @ingroup views_argument_handlers
 */
class socrata_catalog_search_handler_argument_fulltext extends views_handler_argument_string {
  /**
   * Very simplified override because API only handles basic querying.
   */
  function query($group_by = FALSE) {
    $argument = $this->argument;

    $this->query->add_where(0, 'q', $argument, '=');
  }
}
