<?php

namespace Drupal\socrata_views\Plugin\views\style;

/**
 * Default style plugin to render an RSS feed.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "json",
 *   title = @Translation("JSON File"),
 *   help = @Translation("Export the Socrata endpoint data to a JSON file."),
 *   display_types = {"socrata_export"}
 * )
 */
class JSON extends ExportStyleBase {

  /**
   * {@inheritdoc}
   */
  public function getDownloadFormat() {
    return 'json';
  }

}
