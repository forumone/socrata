<?php

namespace Drupal\socrata_views\Plugin\views\display_extender;

use Drupal\views\Plugin\views\display_extender\DisplayExtenderPluginBase;

/**
 * Socrata display extender plugin.
 *
 * @ingroup views_display_extender_plugins
 *
 * @ViewsDisplayExtender(
 *   id = "socrata_display_extender",
 *   title = @Translation("Socrata display extender"),
 *   help = @Translation("Hide unused settings from Socrata based views."),
 *   no_ui = TRUE
 * )
 */
class SocrataDisplayExtender extends DisplayExtenderPluginBase {

  /**
   * {@inheritdoc}
   */
  public function optionsSummary(&$categories, &$options) {
    if ($this->view->query->getPluginId() === 'socrata_views_query') {
      unset($options['cache']);
    }
  }

}
