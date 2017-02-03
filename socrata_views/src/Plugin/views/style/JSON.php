<?php

/**
 * @file
 * Contains \Drupal\socrata_views\Plugin\views\style\JSON.
 */

namespace Drupal\socrata_views\Plugin\views\style;

use Drupal\socrata_views\Plugin\views\style\SocrataExport;

/**
 * Default style plugin to render an RSS feed.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "json",
 *   title = @Translation("JSON File"),
 *   help = @Translation("Export the Socrata endpoint data to a JSON file."),
 *   theme = "views_view_rss",
 *   display_types = {"socrata_export"}
 * )
 */
class JSON extends ExportStyleBase {

  public function getDownloadFormat() {
    return 'json';
  }
}
