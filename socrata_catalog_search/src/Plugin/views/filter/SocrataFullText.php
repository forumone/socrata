<?php

/**
 * @file
 * Contains \Drupal\socrata_catalog_search\Plugin\views\filter\SocrataFullText.
 */

namespace Drupal\socrata_catalog_search\Plugin\views\filter;

use Drupal\socrata_catalog_search\Plugin\views\filter\SocrataFilter;

/**
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("socrata_full_text")
 */
class SocrataFullText extends SocrataFilter {

  public $search_field = 'q';

}
