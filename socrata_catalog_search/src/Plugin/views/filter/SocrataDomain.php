<?php

/**
 * @file
 * Contains \Drupal\socrata_catalog_search\Plugin\views\filter\SocrataDomain.
 */

namespace Drupal\socrata_catalog_search\Plugin\views\filter;

use Drupal\socrata_catalog_search\Plugin\views\filter\SocrataFilter;

/**
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("socrata_domain")
 */
class SocrataDomain extends SocrataFilter {

  public $search_field = 'domains';

}
