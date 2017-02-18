<?php

namespace Drupal\socrata_catalog_search\Plugin\views\sort;

use Drupal\views\Plugin\views\sort\SortPluginBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Default implementation of the base sort plugin.
 *
 * @ingroup views_sort_handlers
 *
 * @ViewsSort("socrata_catalog_sort")
 */
class SocrataCatalogSort extends SortPluginBase {

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    $form['order']['#description'] = $this->t('Note that because of limitations in the Socrata Catalog Search API, sorting only works on the visible paged results and not the entire data set.');
  }

}
