<?php
/**
 * @file
 *
 * Contains the page callbacks for the Socrata Views module
 */

/**
 * Page callback for "socrata/views/test"
 */
function _socrata_views_page_test() {
  ctools_include('export');
  $sources = ctools_export_crud_load_all('socrata_sources');
  foreach ($sources as $source) {
    $resp = socrata_query($source->endpoint, array('$limit' => 1));
    dpm($resp);
  }
  dpm($sources);
  return '';
}
