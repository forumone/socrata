<?php

namespace Drupal\socrata_catalog_search;

use Drupal\socrata\Entity\Endpoint;
use Drupal\socrata\SocrataSelectQuery;

/**
 * @file
 * Decorator class for a Socrata catalog search query.
 */
class SocrataCatalogSearchSelectQuery extends SocrataSelectQuery {

  /**
   * Socrata Catalog Search filter lists.
   */

  /**
   * Get the Socrata Catalog Search source object.
   */
  public function setEndpoint(Endpoint $endpoint = NULL) {
    $values = [
      'id' => 'catalog_search',
      'label' => 'Catalog Search API',
      'url' => 'http://api.us.socrata.com/api/catalog/v1',
      'app_token' => '',
    ];
    $this->endpoint = new Endpoint($values, 'endpoint');
  }

}
