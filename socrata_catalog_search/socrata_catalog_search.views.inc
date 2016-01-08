<?php
/**
 * @file
 *
 * Views data for socrata_catalog_search.
 */

/**
 * Implements hook_views_data().
 */
function socrata_catalog_search_views_data() {
  $tables = array();

  /*$source_name = 'catalog_search';
  $source = new stdClass();
  $source->description = 'Catalog Search API';
  $source->endpoint = 'http://api.us.socrata.com/api/catalog/v1';

  $group = $source->description;

  // Retrieve table schema
  $components = socrata_components_from_endpoint($source->endpoint);
  $dataset = $components['dataset_id'];

  $query = db_select($source_name)->extend('SocrataCatalogSearchSelectQuery');
  $query->addTag('socrata');
  //@todo: fix and add tag:
  //$query->addTag('socrata_data_' . [4x4 w/ dashes converted to underscores]);
  // Limit to 1 row
  $query->params['limit'] = 1; // Catalog params
  $query->range(0, 1); // SelectQuery

  $resp = $query->execute();
  dpm($resp);*/

  $tables['socrata_catalog_search'] = array(
    // Meta data.
    'table' => array(
      'base' => array(
        'field' => 'id',
        'title' => t('Socrata Catalog Search'),
        'help' => t('The local table of responses imported from SDRG SDRG'),
        'query class' => 'socrata_catalog_search_query',
      ),
      'group' => t('Socrata Catalog Search'),
    ),
    'id' => array(
      'title' => t('4x4 ID'),
      'help' => t('Endpoint Identifier'),
      'mapping' => array('resource' => array('id')),
    ),
    'description' => array(
      'title' => t('Description'),
      'help' => t('Endpoint Description'),
    ),
    'updatedAt' => array(
      'title' => t('Last Updated'),
      'help' => t('Endpoint Last Updated Timestamp'),
      'field' => array(
        'handler' => 'views_handler_field_date',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort_date',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_date',
      ),
    ),
    'name' => array(
      'title' => t('Name'),
      'help' => t('Endpoint Name'),
    ),
    'type' => array(
      'title' => t('Type'),
      'help' => t('Endpoint Type'),
    ),
    'categories' => array(
      'title' => t('Categories'),
      'help' => t('Endpoint Categories'),
      'field' => array(
        'handler' => 'views_handler_field_prerender_list',
        'click sortable' => TRUE,
       ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'socrata_catalog_search_handler_filter_categories',
      ),
    ),
    'tags' => array(
      'title' => t('Tags'),
      'help' => t('Endpoint Tags'),
    ),
    'domain' => array(
      'title' => t('Domain'),
      'help' => t('Endpoint Domain'),
    ),
    'permalink' => array(
      'title' => t('Permalink'),
      'help' => t('Endpoint Permalink'),
    ),
    'link' => array(
      'title' => t('Link'),
      'help' => t('Endpoint Link'),
    ),
  );

  // Add field boiler plate settings.
  $field_boiler_plate = array(
    'field' => array(
      'handler' => 'views_handler_field',
      'click sortable' => TRUE,
    ),
    'sort' => array(
      'handler' => 'views_handler_sort',
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_string',
    ),
    'argument' => array(
      'handler' => 'views_handler_argument_string',
    ),
  );

  foreach ($tables['socrata_catalog_search'] as $field => $value) {
    if (in_array($field, array('table', 'updatedAt', 'categories'))) {
      continue;
    }
    $tables['socrata_catalog_search'][$field] = array_merge($value, $field_boiler_plate);
  }

  return $tables;
}
