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
  // @todo: switch to getSource (or a new getSources wrapper)
  ctools_include('export');
  $sources = ctools_export_crud_load_all('socrata_sources');

  // Iterate over all available tables, provide definitions to Views
  foreach ($sources as $source_name => $source) {
    $group = $source->description;

    // Retrieve table schema
    $components = socrata_components_from_endpoint($source->endpoint);
    $dataset = $components['dataset_id'];

    $query = db_select($source_name)->extend('SocrataSelectQuery');
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
      $tables[$source_name] = array();
      $table = &$tables[$source_name];
      $table['table'] = array(
        'group' => $group,
        'title' => $source_name,
        'help' => "Field in {$source_name}",
      );

      // Stub out the base table information (key field will be extracted below)
      $table['table']['base'] = array(
        'title' => "Socrata: {$group}",
        'help' => $group,
        'query class' => 'socrata_query',
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
      $handlers['field']['handler'] = 'views_handler_field_numeric';
      $handlers['field']['click sortable'] = TRUE;
      $handlers['sort']['handler'] = 'views_handler_sort';
      $handlers['filter']['handler'] = 'socrata_views_handler_filter_numeric_quoted';
      $handlers['argument']['handler'] = 'socrata_views_handler_argument_numeric_quoted';
      break;

    case 'number':
    case 'double':
      $handlers['field']['handler'] = 'views_handler_field_numeric';
      $handlers['field']['click sortable'] = TRUE;
      $handlers['sort']['handler'] = 'views_handler_sort';
      $handlers['filter']['handler'] = 'socrata_views_handler_filter_numeric';
      $handlers['argument']['handler'] = 'socrata_views_handler_argument_numeric';
      break;

    case 'checkbox':
      $handlers['field']['handler'] = 'views_handler_field_boolean';
      $handlers['field']['click sortable'] = TRUE;
      $handlers['sort']['handler'] = 'views_handler_sort';
      $handlers['filter']['handler'] = 'socrata_views_handler_filter_checkbox';
      $handlers['argument']['handler'] = 'socrata_views_handler_argument_checkbox';
      break;

    case 'url': // Compound type: description, url
      $handlers['field']['handler'] = 'socrata_views_handler_field_url';
      break;

    case 'location': // Compound type: needs_recoding, longitude, latitude, human_address (JSON)
      $handlers['field']['handler'] = 'socrata_views_handler_field_location';
      break;

    case 'point': // Longitude, latitude geo data
      $handlers['field']['handler'] = 'socrata_views_handler_field_point';
      break;

    case 'email':
      $handlers['field']['handler'] = 'socrata_views_handler_field_email';
      $handlers['field']['click sortable'] = TRUE;
      $handlers['sort']['handler'] = 'views_handler_sort';
      break;

    case 'document': // Compound type: file_id, filename
      $handlers['field']['handler'] = 'socrata_views_handler_field_document';
      break;

    case 'photo':
      $handlers['field']['handler'] = 'socrata_views_handler_field_photo';
      break;

    case 'phone': // Compound type: phone_number, phone_type
      $handlers['field']['handler'] = 'socrata_views_handler_field_phone';
      break;

    case 'html':
      $handlers['field']['handler'] = 'socrata_views_handler_field_html';
      break;

    case 'date':
      $handlers['field']['handler'] = 'views_handler_field_date';
      $handlers['field']['click sortable'] = TRUE;
      $handlers['sort']['handler'] = 'views_handler_sort';
      $handlers['filter']['handler'] = 'socrata_views_handler_filter_date';
      break;

    case 'calendar_date':
    case 'floating_timestamp':
      $handlers['field']['handler'] = 'socrata_views_handler_field_calendar_date';
      $handlers['field']['click sortable'] = TRUE;
      $handlers['sort']['handler'] = 'views_handler_sort';
      $handlers['filter']['handler'] = 'socrata_views_handler_filter_date';
      break;

    case 'flag':
      $handlers['field']['handler'] = 'views_handler_field';
      $handlers['field']['click sortable'] = TRUE;
      $handlers['sort']['handler'] = 'views_handler_sort';
      break;

    case 'text':
      $handlers['field']['handler'] = 'views_handler_field';
      $handlers['field']['click sortable'] = TRUE;
      $handlers['sort']['handler'] = 'views_handler_sort';
      $handlers['filter']['handler'] = 'socrata_views_handler_filter_text';
      $handlers['argument']['handler'] = 'socrata_views_handler_argument_text';
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
