<?php

/**
 * @file
 * Contains \Drupal\socrata_views\Plugin\views\style\CSV.
 */

namespace Drupal\socrata_views\Plugin\views\style;

use Drupal\views\Plugin\views\style\StylePluginBase;
use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\socrata\Entity\Endpoint;

/**
 * Default style plugin to render an RSS feed.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "csv",
 *   title = @Translation("CSV File"),
 *   help = @Translation("Export the Socrata endpoint data to a CSV file."),
 *   theme = "views_view_rss",
 *   display_types = {"socrata_export"}
 * )
 */
class CSV extends StylePluginBase {

  /**
   * Does the style plugin for itself support to add fields to it's output.
   *
   * @var bool
   */
  protected $usesRowPlugin = FALSE;

  public function getDownloadFormat() {
    return 'csv';
  }

  public function attachTo() {
    $base_table = $this->view->storage->get('base_table');
    $download_format = $this->getDownloadFormat();
    $endpoint = Endpoint::load($base_table);
    $url = $endpoint->getDownloadUrl($download_format);

    $title = Html::escape($this->options['attach_text']);
    $link = Link::fromTextAndUrl($title, Url::fromUri($url));
    $link = $link->toRenderable();

    return $link;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    // Disable irrelevant options.
    unset($form['uses_fields']);

    $form['attach_text'] = array(
      '#type' => 'textfield',
      '#title' => t('Attach text'),
      '#default_value' => $this->options['attach_text'] ?: $this->t('Download'),
      '#description' => $this->t('This text is used for building the export link.'),
    );
  }

}
