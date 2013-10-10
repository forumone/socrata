<?php

/**
 * @file
 * Definition of socrata_views_handler_field_location.
 */

/**
 * Field handler to provide renderer capable of displaying Socrata location items
 *
 * @ingroup views_field_handlers
 */
class socrata_views_handler_field_location extends views_handler_field {
  function option_definition() {
    $options = parent::option_definition();

    $options['display_as_link'] = array('default' => TRUE, 'bool' => TRUE);
    $options['link_text'] = array('default' => 'Map', 'translatable' => TRUE);

    return $options;
  }

  /**
   * Generate output of location.
   */
  function options_form(&$form, &$form_state) {
    $form['display_as_link'] = array(
      '#type' => 'checkbox',
      '#title' => t('Display as link'),
      '#default_value' => $this->options['display_as_link'],
    );
    $form['link_text'] = array(
      '#type' => 'textfield',
      '#title' => t('Link text'),
      '#description' => t('Text to use for link'),
      '#default_value' => $this->options['link_text'],
    );

    parent::options_form($form, $form_state);
  }

  function render($values) {
    $text = '';
    $value = $this->get_value($values);
    if (!empty($value)) {
      // Decode the nested address data if provided
      if (!empty($value['human_address'])) {
        $value['human_address'] = json_decode($value['human_address'], TRUE);
        $human_address = $this->sanitize_value(implode(', ', $value['human_address']), 'xss');
      }

      // Suss out display text - TODO make themeable
      if (!empty($this->options['link_text'])) {
        $text = $this->sanitize_value($this->options['link_text'], 'xss');
      }
      elseif (!empty($value['human_address'])) {
        $text = $human_address;
      }
      elseif (isset($value['latitude']) && isset($value['longitude'])) {
        $text = "{$value['latitude']}, {$value['longitude']}";
      }
      else {
        $text = t('Map');
      }

      if ($this->options['display_as_link']) {
        $this->options['alter']['make_link'] = TRUE;

        if (!empty($value['human_address'])) {
          $this->options['alter']['path'] = "http://maps.google.com/maps?q={$human_address}";
        }
        elseif (isset($value['latitude']) && isset($value['longitude'])) {
          $this->options['alter']['path'] = "http://maps.google.com/maps?q={$value['latitude']},{$value['longitude']}";
        }
      }
    }

    return $text;
  }
}
