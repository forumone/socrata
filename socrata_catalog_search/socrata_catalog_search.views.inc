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

  $tables['socrata_catalog_search'] = array(
    // Meta data.
    'table' => array(
      'base' => array(
        'field' => 'id',
        'title' => t('Socrata Catalog Search'),
        'help' => t('The local table of responses imported from SDRG SDRG.'),
        'query_id' => 'socrata_catalog_search_query',
      ),
      'group' => t('Socrata Catalog Search'),
    ),
    'id' => array(
      'title' => t('4x4 ID'),
      'help' => t('The unique identifier for the endpoint.'),
    ),
    'description' => array(
      'title' => t('Description'),
      'help' => t('The description for the endpoint, if one has been provided.'),
    ),
    'updatedAt' => array(
      'title' => t('Last Updated'),
      'help' => t('The timestamp at which the endpoint was last updated.'),
      'field' => array(
        'id' => 'calendar_date',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'id' => 'socrata_catalog_search_handler_sort',
      ),
    ),
    'name' => array(
      'title' => t('Name'),
      'help' => t('The title of the endpoint.'),
    ),
    'type' => array(
      'title' => t('Type'),
      'help' => t('The type of the endpoint.'),
      'field' => array(
        'id' => 'standard',
        'click sortable' => TRUE,
       ),
      'sort' => array(
        'id' => 'socrata_catalog_search_handler_sort',
      ),
      'filter' => array(
        'id' => 'socrata_catalog_search_handler_filter_type',
      ),
      'argument' => array(
        'id' => 'string',
      ),
    ),
    'categories' => array(
      'title' => t('Categories'),
      'help' => t('A list of categories that have been automatically assigned by stastically derived models.'),
      'field' => array(
        'id' => 'socrata_list',
        'click sortable' => TRUE,
       ),
      'sort' => array(
        'id' => 'socrata_catalog_search_handler_sort',
      ),
      'filter' => array(
        'id' => 'socrata_catalog_search_handler_filter_list',
      ),
      'argument' => array(
        'id' => 'string',
      ),
    ),
    'tags' => array(
      'title' => t('Tags'),
      'help' => t('A list of tags that have been automatically assigned by stastically derived models.'),
      'field' => array(
        'id' => 'socrata_list',
        'click sortable' => TRUE,
       ),
      'sort' => array(
        'id' => 'socrata_catalog_search_handler_sort',
      ),
      'filter' => array(
        'id' => 'socrata_catalog_search_handler_filter_list',
      ),
      'argument' => array(
        'id' => 'string',
      ),
    ),
    'domain' => array(
      'title' => t('Domain'),
      'help' => t('The endpoint base domain.'),
      'field' => array(
        'id' => 'standard',
        'click sortable' => TRUE,
       ),
      'sort' => array(
        'id' => 'socrata_catalog_search_handler_sort',
      ),
      'filter' => array(
        'id' => 'socrata_catalog_search_handler_filter_domain',
      ),
      'argument' => array(
        'id' => 'string',
      ),
    ),
    'permalink' => array(
      'title' => t('Permalink'),
      'help' => t('The Permalink to the resource.'),
    ),
    'link' => array(
      'title' => t('Link'),
      'help' => t('The endpoint API link.'),
    ),
    // Full text search filter.
    'fulltext' => array(
      'group' => t('Socrata Catalog Search'),
      'title' => t('Fulltext search'),
      'help' => t('Search all Endpoint fields.'),
      'filter' => array(
        'id' => 'socrata_catalog_search_handler_filter_fulltext',
      ),
      'argument' => array(
        'id' => 'socrata_catalog_search_handler_argument_fulltext',
      ),
    ),
  );

  // Add field boiler plate settings.
  $field_boiler_plate = array(
    'field' => array(
      'id' => 'standard',
      'click sortable' => TRUE,
    ),
    'sort' => array(
      'id' => 'socrata_catalog_search_handler_sort',
    ),
  );

  foreach ($tables['socrata_catalog_search'] as $field => $value) {
    if (in_array($field, array('table', 'updatedAt', 'categories', 'tags', 'domain', 'type', 'fulltext'))) {
      continue;
    }
    $tables['socrata_catalog_search'][$field] = array_merge($value, $field_boiler_plate);
  }

  return $tables;
}
