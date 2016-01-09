<?php

/**
 * @file
 * Definition of socrata_catalog_search_handler_filter_tags.
 */

/**
 * @ingroup views_filter_handlers
 */
class socrata_catalog_search_handler_filter_tags extends socrata_catalog_search_handler_filter_categories {
  function get_value_options() {
    // @todo Make this a look up from http://api.us.socrata.com/api/catalog/v1/tags.
    $options = array(
      'blds' => t("blds"),
      'business' => t("business"),
      'construction' => t("construction"),
      'crime' => t("crime"),
      'education' => t("education"),
      'finance' => t("finance"),
      'health' => t("health"),
      'infrastructure' => t("infrastructure"),
      'lives' => t("lives"),
      'politics' => t("politics"),
      'transportation' => t("transportation"),
    );
    $this->value_options = $options;
  }
}
