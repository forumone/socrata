<?php
/**
 * @file
 * Decorator class for a Socrata dataset query
 */

class SocrataCatalogSearchSelectQuery extends SocrataSelectQuery {

  /**
   * Get the Socrata Catalog Search source object.
   */
  public function getSource() {
    $source = new stdClass();
    $source->name = 'catalog_search';
    $source->description = 'Catalog Search API';
    $source->endpoint = 'http://api.us.socrata.com/api/catalog/v1';
    $source->app_token = '';

    return $source;
  }
}
