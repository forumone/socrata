<?php

namespace Drupal\socrata_views\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\ResultRow;

/**
 * Field handler to provide renderer for Socrata Location field.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("location")
 */
class Location extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['display'] = ['default' => 'text'];
    $options['link_text'] = ['default' => 'Map', 'translatable' => TRUE];

    return $options;
  }

  /**
   * Provide link to the page being visited.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['display'] = [
      '#type' => 'select',
      '#title' => $this->t('Display mode'),
      '#options' => [
        'text' => $this->t('Text, no link'),
        'link' => $this->t('Dispay as link'),
        'wkt' => $this->t('Render as WKT'),
      ],
      '#default_value' => $this->options['display'],
    ];

    $form['link_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link text'),
      '#description' => $this->t('Text to use for link.'),
      '#default_value' => $this->options['link_text'],
    ];

    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $text = '';
    $value = $this->getValue($values);
    if (!empty($value)) {
      // Decode the nested address data if provided.
      if (!empty($value['human_address'])) {
        $value['human_address'] = json_decode($value['human_address'], TRUE);
        $human_address = $this->sanitizeValue(implode(', ', $value['human_address']), 'xss');
      }

      // Suss out default display text - TODO make themeable.
      if (!empty($this->options['link_text'])) {
        $text = $this->sanitizeValue($this->options['link_text'], 'xss');
      }
      elseif (!empty($value['human_address'])) {
        $text = $human_address;
      }
      elseif (isset($value['latitude']) && isset($value['longitude'])) {
        $longitude = $this->sanitizeValue($value['longitude'], 'xss');
        $latitude = $this->sanitizeValue($value['latitude'], 'xss');
        // There is a method to the madness; This is the correct ordering for
        // embedding into a WKT POINT definition.
        $text = "{$longitude}, {$latitude}";
      }
      else {
        $text = $this->t('Map');
      }

      if ('link' == $this->options['display']) {
        $this->options['alter']['make_link'] = TRUE;

        if (!empty($value['human_address'])) {
          $this->options['alter']['path'] = "http://maps.google.com/maps?q={$human_address}";
        }
        elseif (isset($value['latitude']) && isset($value['longitude'])) {
          $this->options['alter']['path'] = "http://maps.google.com/maps?q={$value['latitude']},{$value['longitude']}";
        }
      }
      elseif ('wkt' == $this->options['display']) {
        $longitude = $this->sanitizeValue($value['longitude'], 'xss');
        $latitude = $this->sanitizeValue($value['latitude'], 'xss');
        $text = "POINT ({$longitude} {$latitude})";
      }
    }

    return $text;
  }

}
