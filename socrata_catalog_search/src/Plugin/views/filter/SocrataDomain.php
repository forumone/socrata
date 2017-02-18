<?php

namespace Drupal\socrata_catalog_search\Plugin\views\filter;

/**
 * Socrata Catalog Search Domain filter.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("socrata_domain")
 */
class SocrataDomain extends SocrataFilter {

  public $searchField = 'domains';

}
