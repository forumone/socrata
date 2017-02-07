<?php
/**
 * @file
 *
 * Views data for socrata_catalog_search.
 */

/**
 * Implements hook_views_data
 */
function socrata_views_data() {
  $tables = array();

  // Load our available sources
  $endpoints = \Drupal::entityManager()->getStorage('endpoint')->loadMultiple();

  // Iterate over all available tables, provide definitions to Views
  foreach ($endpoints as $endpoint_id => $endpoint) {
    $group = $endpoint->label;

    // Retrieve table schema
    $components = $endpoint->getComponents();
    $dataset = $components['dataset_id'];

    $query = db_select($endpoint_id)->extend('Drupal\socrata\SocrataSelectQuery');
    $query->addTag('socrata');
    //@todo: fix and add tag:
    //$query->addTag('socrata_data_' . [4x4 w/ dashes converted to underscores]);
    // Limit to 1 row
    $query->params['$limit'] = 1; // SoQL params
    $query->range(0, 1); // SelectQuery

    $resp = $query->execute();

    if ($resp !== FALSE && !empty($resp['fields'])) {
      // Also get the metadata.
      $resp_metadata = $query->execute('metadata');
      $metadata_field_list = $resp_metadata['data']['columns'];

      // Define basic table info
      $tables[$endpoint_id] = array();
      $table = &$tables[$endpoint_id];
      $table['table'] = array(
        'group' => $group,
        'title' => $endpoint_id,
        'help' => "Field in {$endpoint_id}",
      );

      // Stub out the base table information (key field will be extracted below)
      $table['table']['base'] = array(
        'title' => "Socrata: {$group}",
        'help' => $group,
        'query_id' => 'socrata_views_query',
      );

      // Add fields to table
      foreach ($resp['fields'] as $field_machine_name => $field_type) {
        $field_options = array(
          'dataset' => $dataset,
          'soda2_type' => $field_type,
        );
        $handlers = _socrata_views_handlers($field_machine_name, $field_type, $field_options);

        // Get a human-readable field name.
        $field_name = $field_machine_name;
        // For most fields, we should be able to get a human-readable name from
        // the metadata query.
        foreach ($metadata_field_list as $metadata_field) {
          if ($field_machine_name == $metadata_field['fieldName']) {
            $field_name = $metadata_field['name'];
            continue;
          }
        }

        // If we still don't have a human-readable name...
        // Hard-code values for a few special cases from the old API.
        // @todo Add version number for "old".
        if ($field_name == $field_machine_name) {
          switch ($field_machine_name) {
            case ':created_at':
              $field_name = 'Created at';
              break;
            case ':updated_at':
              $field_name = 'Updated at';
              break;
            case ':id':
              $field_name = 'ID';
              break;
          }
        }

        $field_def = array_merge_recursive(
          $handlers,
          array(
            'title' => $field_name,
            'help' => t('Socrata data field of type %field_type.', array('%field_type' => $field_type)),
            'field' => array(
              'title' => $field_name,
            ),
          )
        );

        $table[$field_machine_name] = $field_def;
      }

      // Add result id field since we have to define at least one initial field
      if (isset($table[':id'])) {
        $table['table']['base']['field'] = ':id';
        $table['table']['base']['defaults'] = array(
          'field' => ':id',
        );
      }
    }
  }

  return $tables;
}

/**
 * Util function to map field handlers to Socrata field types
 *
 * http://dev.socrata.com/docs/datatypes
 */
function _socrata_views_handlers($name, $type, $options) {
  // Initialize field handlers array
  $handlers = array();

  // Catch the special "meta_data" fields and override their types based on field id so they make sense
  // These are only returned with the old API.
  // @todo Add version number for "old".
  if ('meta_data' == $type) {
    switch ($name) {
      case ':created_at':
      case ':updated_at':
        $type = 'date';
        break;

      case ':id':
        $type = 'text';
        break;

      default:
        $type = 'text';
    }
  }

  // Now override defaults based on field type:
  switch ($type) {
    case 'money':
    case 'percent':
    case 'stars':
      $handlers['field']['id'] = 'numeric';
      $handlers['field']['click sortable'] = TRUE;
      $handlers['sort']['id'] = 'standard';
      $handlers['filter']['id'] = 'socrata_numeric';
      $handlers['argument']['id'] = 'socrata_views_handler_argument_numeric_quoted';
      break;

    case 'number':
    case 'double':
      $handlers['field']['id'] = 'numeric';
      $handlers['field']['click sortable'] = TRUE;
      $handlers['sort']['id'] = 'standard';
      $handlers['filter']['id'] = 'socrata_numeric';
      $handlers['argument']['id'] = 'socrata_numeric';
      break;

    case 'checkbox':
    case 'boolean':
      $handlers['field']['id'] = 'boolean';
      $handlers['field']['click sortable'] = TRUE;
      $handlers['sort']['id'] = 'standard';
      $handlers['filter']['id'] = 'socrata_boolean';
      $handlers['argument']['id'] = 'socrata_boolean';
      break;

    case 'url': // Compound type: description, url
      $handlers['field']['id'] = 'socrata_url';
      break;

    case 'location': // Compound type: needs_recoding, longitude, latitude, human_address (JSON)
      $handlers['field']['id'] = 'location';
      break;

    case 'point': // Longitude, latitude geo data
      $handlers['field']['id'] = 'socrata_point';
      break;

    case 'email':
      $handlers['field']['id'] = 'socrata_views_handler_field_email';
      $handlers['field']['click sortable'] = TRUE;
      $handlers['sort']['id'] = 'standard';
      break;

    case 'document': // Compound type: file_id, filename
      $handlers['field']['id'] = 'socrata_views_handler_field_document';
      break;

    case 'photo':
      $handlers['field']['id'] = 'socrata_views_handler_field_photo';
      break;

    case 'phone': // Compound type: phone_number, phone_type
      $handlers['field']['id'] = 'socrata_views_handler_field_phone';
      break;

    case 'html':
      $handlers['field']['id'] = 'socrata_views_handler_field_html';
      break;

    case 'date':
      $handlers['field']['id'] = 'date';
      $handlers['field']['click sortable'] = TRUE;
      $handlers['sort']['id'] = 'standard';
      $handlers['filter']['id'] = 'socrata_views_handler_filter_date';
      break;

    case 'calendar_date':
    case 'floating_timestamp':
      $handlers['field']['id'] = 'calendar_date';
      $handlers['field']['click sortable'] = TRUE;
      $handlers['sort']['id'] = 'standard';
      $handlers['filter']['id'] = 'socrata_views_handler_filter_date';
      break;

    case 'flag':
      $handlers['field']['id'] = 'standard';
      $handlers['field']['click sortable'] = TRUE;
      $handlers['sort']['id'] = 'standard';
      break;

    case 'text':
      $handlers['field']['id'] = 'standard';
      $handlers['field']['click sortable'] = TRUE;
      $handlers['sort']['id'] = 'standard';
      $handlers['filter']['id'] = 'socrata_string';
      $handlers['argument']['id'] = 'socrata_string';
      break;

    // Unsupported field types
    case 'drop_down_list':
    default:
      break;
  }

  // Add field options to each handler group that has been defined
  foreach ($handlers as $group => &$info) {
    $info += $options;
  }

  return $handlers;
}
