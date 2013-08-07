<?php
/**
 * @file
 *
 * Contains the exportable UI for the Socrata module's sources
 */

/**
 * Define this Export UI plugin.
 */
$plugin = array(
  'schema' => 'socrata_sources',  // As defined in hook_schema().
  'access' => 'administer socrata',  // Define a permission users must have to access these pages.

  // Define the menu item.
  'menu' => array(
    'menu item' => 'socrata',
    'menu title' => 'Socrata',
    'menu description' => 'Administer Socrata sources',
  ),

  // Define user interface texts.
  'title singular' => t('source'),
  'title plural' => t('sources'),
  'title singular proper' => t('Socrata source'),
  'title plural proper' => t('Socrata sources'),

  // Define the names of the functions that provide the add/edit forms.
  'form' => array(
    'settings' => 'socrata_ctools_export_ui_form',
    // 'submit' and 'validate' are also valid callbacks.
  ),
);

/**
 * Define the preset add/edit form.
 */
function socrata_ctools_export_ui_form(&$form, &$form_state) {
  $source = $form_state['item'];

  $form['description'] = array(
    '#type' => 'textfield',
    '#title' => t('Description'),
    '#description' => t('The human readable name or description of this source'),
    '#default_value' => $source->description,
    '#required' => TRUE,
  );

  $form['endpoint'] = array(
    '#type' => 'textfield',
    '#title' => t('Endpoint URL'),
    '#description' => t('The endpoint URL for the dataset'),
    '#default_value' => $source->endpoint,
    '#required' => TRUE,
  );

  $form['app_token'] = array(
    '#type' => 'textfield',
    '#title' => t('Application token'),
    '#description' => t('The application token for the dataset (optional, but recommended if you have it)'),
    '#default_value' => $source->app_token,
    '#required' => FALSE,
  );

}
