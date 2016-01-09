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
      'construction' => t("construction"),
      'finance' => t("finance"),
      'politics' => t("politics"),
      'education' => t("education"),
      'business' => t("business"),
      'crime' => t("crime"),
      'transportation' => t("transportation"),
      'blds' => t("blds"),
      'health' => t("health"),
      'infrastructure' => t("infrastructure"),
      'lives' => t("lives"),
    );
    $this->value_options = $options;
  }
}
