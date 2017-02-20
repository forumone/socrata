<?php

namespace Drupal\socrata_views\Plugin\views\style;

/**
 * Default style plugin to render an RSS feed.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "csv",
 *   title = @Translation("CSV File"),
 *   help = @Translation("Export the Socrata endpoint data to a CSV file."),
 *   display_types = {"socrata_export"}
 * )
 */
class CSV extends ExportStyleBase {

  /**
   * {@inheritdoc}
   */
  public function getDownloadFormat() {
    return 'csv';
  }

}
