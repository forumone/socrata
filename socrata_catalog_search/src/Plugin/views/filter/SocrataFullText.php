<?php

namespace Drupal\socrata_catalog_search\Plugin\views\filter;

/**
 * Socrata Catalog Search Full Search filter.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("socrata_full_text")
 */
class SocrataFullText extends SocrataFilter {

  public $searchField = 'q';

}
