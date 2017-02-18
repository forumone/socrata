<?php

/**
 * @file
 * Views data for socrata_catalog_search.
 */

/**
 * Implements hook_views_data().
 */
function socrata_catalog_search_views_data() {
  $tables = [];

  $tables['socrata_catalog_search'] = [
    // Meta data.
    'table' => [
      'base' => [
        'field' => 'id',
        'title' => t('Socrata Catalog Search'),
        'help' => t('The local table of responses imported from SDRG SDRG.'),
        'query_id' => 'socrata_catalog_search_query',
      ],
      'group' => t('Socrata Catalog Search'),
    ],
    'id' => [
      'title' => t('4x4 ID'),
      'help' => t('The unique identifier for the endpoint.'),
    ],
    'description' => [
      'title' => t('Description'),
      'help' => t('The description for the endpoint, if one has been provided.'),
    ],
    'updatedAt' => [
      'title' => t('Last Updated'),
      'help' => t('The timestamp at which the endpoint was last updated.'),
      'field' => [
        'id' => 'calendar_date',
        'click sortable' => TRUE,
      ],
      'sort' => [
        'id' => 'socrata_catalog_sort',
      ],
    ],
    'name' => [
      'title' => t('Name'),
      'help' => t('The title of the endpoint.'),
    ],
    'type' => [
      'title' => t('Type'),
      'help' => t('The type of the endpoint.'),
      'field' => [
        'id' => 'standard',
        'click sortable' => TRUE,
      ],
      'sort' => [
        'id' => 'socrata_catalog_sort',
      ],
      'filter' => [
        'id' => 'socrata_type',
      ],
      'argument' => [
        'id' => 'string',
      ],
    ],
    'domain_category' => [
      'title' => t('Category'),
      'help' => t('The singular category given to the endpoint by the owning domain.'),
      'field' => [
        'id' => 'standard',
        'click sortable' => TRUE,
      ],
      'sort' => [
        'id' => 'socrata_catalog_sort',
      ],
      'argument' => [
        'id' => 'string',
      ],
    ],
    'domain_tags' => [
      'title' => t('Tags'),
      'help' => t('A list of tags given to the endpoint by the owning domain.'),
      'field' => [
        'id' => 'socrata_list',
        'click sortable' => TRUE,
      ],
      'sort' => [
        'id' => 'socrata_catalog_sort',
      ],
      'argument' => [
        'id' => 'string',
      ],
    ],
    'domain' => [
      'title' => t('Domain'),
      'help' => t('The endpoint base domain.'),
      'field' => [
        'id' => 'standard',
        'click sortable' => TRUE,
      ],
      'sort' => [
        'id' => 'socrata_catalog_sort',
      ],
      'filter' => [
        'id' => 'socrata_domain',
      ],
      'argument' => [
        'id' => 'string',
      ],
    ],
    'permalink' => [
      'title' => t('Permalink'),
      'help' => t('The Permalink to the resource.'),
    ],
    'link' => [
      'title' => t('Link'),
      'help' => t('The endpoint API link.'),
    ],
    // Full text search filter.
    'fulltext' => [
      'group' => t('Socrata Catalog Search'),
      'title' => t('Fulltext search'),
      'help' => t('Search all Endpoint fields.'),
      'filter' => [
        'id' => 'socrata_full_text',
      ],
      'argument' => [
        'id' => 'socrata_full_text',
      ],
    ],
  ];

  // Add field boiler plate settings.
  $field_boiler_plate = [
    'field' => [
      'id' => 'standard',
      'click sortable' => TRUE,
    ],
    'sort' => [
      'id' => 'socrata_catalog_sort',
    ],
  ];

  foreach ($tables['socrata_catalog_search'] as $field => $value) {
    if (in_array($field, [
      'table',
      'updatedAt',
      'categories',
      'tags',
      'domain',
      'type',
      'fulltext',
    ])) {
      continue;
    }
    $tables['socrata_catalog_search'][$field] = array_merge($value, $field_boiler_plate);
  }

  return $tables;
}
