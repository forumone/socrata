<?php

/**
 * @file
 * Definition of SocrataPoint.
 */

namespace Drupal\socrata_views\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\ResultRow;

/**
 * Field handler to provide simple renderer that turns a URL into a clickable link.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("socrata_point")
 */
class SocrataPoint extends FieldPluginBase {
  /**
   * Collect options for field display
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['display'] = array('default' => 'text');
    $options['link_text'] = array('default' => 'Map', 'translatable' => TRUE);

    return $options;
  }

  /**
   * Option form.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['display'] = array(
      '#type' => 'select',
      '#title' => t('Display mode'),
      '#options' => array(
        'text' => t('Text, no link'),
        'link' => t('Dispay as link'),
        'wkt' => t('Render as WKT'),
      ),
      '#default_value' => $this->options['display'],
    );

    $form['link_text'] = array(
      '#type' => 'textfield',
      '#title' => t('Link text'),
      '#description' => t('Text to use for link'),
      '#default_value' => $this->options['link_text'],
    );

    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * Generate output of point.
   */
  public function render(ResultRow $values) {
    $text = '';
    $value = $this->getValue($values);
    if (!empty($value)) {
      // Get the lat/long in the order as specified
      // at http://dev.socrata.com/docs/datatypes/point.html.
      $longitude = $this->sanitizeValue($value['coordinates'][0], 'xss');
      $latitude = $this->sanitizeValue($value['coordinates'][1], 'xss');
      // Suss out default display text - TODO make themeable
      if (!empty($this->options['link_text'])) {
        $text = $this->sanitizeValue($this->options['link_text'], 'xss');
      }
      elseif ($longitude && $latitude) {
        // There is a method to the madness;this is the correct ordering
        // for embedding into a WKT POINT definition.
        $text = "{$longitude}, {$latitude}";
      }
      else {
        $text = t('Map');
      }

      if ('link' == $this->options['display']) {
        $this->options['alter']['make_link'] = TRUE;

        if ($longitude && $latitude) {
          $this->options['alter']['path'] = "http://maps.google.com/maps?q={$latitude},{$longitude}";
        }
      }
      elseif ('wkt' == $this->options['display']) {
        $text = "POINT ({$longitude} {$latitude})";
      }
    }

    return $text;
  }
}
