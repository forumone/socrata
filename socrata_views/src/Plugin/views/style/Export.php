<?php

/**
 * @file
 * Contains \Drupal\socrata_views\Plugin\views\style\Export.
 */

namespace Drupal\socrata_views\Plugin\views\style;

use Drupal\views\Plugin\views\style\StylePluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Default style plugin to render an RSS feed.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "socrata_export",
 *   title = @Translation("Socrata Data Export"),
 *   help = @Translation("Export the Socrata endpoint data to a file."),
 *   theme = "views_view_rss",
 *   display_types = {"socrata_export"}
 * )
 */
class Export extends StylePluginBase {

  /**
   * Does the style plugin for itself support to add fields to it's output.
   *
   * @var bool
   */
  protected $usesRowPlugin = FALSE;

  public function attachTo(array &$build, $display_id, Url $feed_url, $title) {
    $download_format = $this->definition['export feed type'];
    $endpoint = $this->view->build_info['query'];
    dpm($endpoint);
    // $url = $endpoint->getDownloadUrl($download_format);
    // $this->view->feed_icon = l(
    //   $this->options['attach_text'],
    //   $url
    // );
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['attach_text'] = array(
      'default' => $this->definition['export feed text'],
      'translatable' => TRUE,
    );
    return $options;

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
      '#default_value' => $this->options['attach_text'],
      '#description' => t('This text is used in building the export link. By default it is the data format.'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    if (empty($this->view->rowPlugin)) {
      debug('Drupal\views\Plugin\views\style\Rss: Missing row plugin');
      return array();
    }
    return 'blarg';
  }

}
