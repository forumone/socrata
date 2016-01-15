<?php

/**
 * @file
 * Definition of socrata_catalog_search_handler_sort.
 */

/**
 * @defgroup views_sort_handlers Views sort handlers
 * @{
 * Handlers to tell Views how to sort queries.
 */
class socrata_catalog_search_handler_sort extends views_handler_sort {
  function options_form(&$form, &$form_state) {
    parent::options_form($form, $form_state);
    $form['order']['#description'] = t('Note that because of limitations in the Socrata Catalog Search API, sorting only works on the visible paged results and not the entire data set.');
  }
}
