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
        'handler' => 'socrata_catalog_search_handler_field_date',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'socrata_catalog_search_handler_sort',
      ),
    ),
    'name' => array(
      'title' => t('Name'),
      'help' => t('Endpoint Name'),
    ),
    'type' => array(
      'title' => t('Type'),
      'help' => t('Endpoint Type'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
       ),
      'sort' => array(
        'handler' => 'socrata_catalog_search_handler_sort',
      ),
      'filter' => array(
        'handler' => 'socrata_catalog_search_handler_filter_type',
      ),
      'argument' => array(
        'handler' => 'views_handler_argument_string',
      ),
    ),
    'categories' => array(
      'title' => t('Categories'),
      'help' => t('Endpoint Categories'),
      'field' => array(
        'handler' => 'socrata_catalog_search_handler_field_list',
        'click sortable' => TRUE,
       ),
      'sort' => array(
        'handler' => 'socrata_catalog_search_handler_sort',
      ),
      'filter' => array(
        'handler' => 'socrata_catalog_search_handler_filter_list',
      ),
      'argument' => array(
        'handler' => 'views_handler_argument_string',
      ),
    ),
    'tags' => array(
      'title' => t('Tags'),
      'help' => t('Endpoint Tags'),
      'field' => array(
        'handler' => 'socrata_catalog_search_handler_field_list',
        'click sortable' => TRUE,
       ),
      'sort' => array(
        'handler' => 'socrata_catalog_search_handler_sort',
      ),
      'filter' => array(
        'handler' => 'socrata_catalog_search_handler_filter_list',
      ),
      'argument' => array(
        'handler' => 'views_handler_argument_string',
      ),
    ),
    'domain' => array(
      'title' => t('Domain'),
      'help' => t('Endpoint Domain'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
       ),
      'sort' => array(
        'handler' => 'socrata_catalog_search_handler_sort',
      ),
      'filter' => array(
        'handler' => 'socrata_catalog_search_handler_filter_domain',
      ),
      'argument' => array(
        'handler' => 'views_handler_argument_string',
      ),
    ),
    'permalink' => array(
      'title' => t('Permalink'),
      'help' => t('Endpoint Permalink'),
    ),
    'link' => array(
      'title' => t('Link'),
      'help' => t('Endpoint Link'),
    ),
    // Full text search filter.
    'fulltext' => array(
      'group' => t('Socrata Catalog Search'),
      'title' => t('Fulltext search'),
      'help' => t('Search all Endpoint fields'),
      'filter' => array(
        'handler' => 'socrata_catalog_search_handler_filter_fulltext',
      ),
      'argument' => array(
        'handler' => 'socrata_catalog_search_handler_argument_fulltext',
      ),
    ),
  );

  // Add field boiler plate settings.
  $field_boiler_plate = array(
    'field' => array(
      'handler' => 'views_handler_field',
      'click sortable' => TRUE,
    ),
    'sort' => array(
      'handler' => 'socrata_catalog_search_handler_sort',
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
