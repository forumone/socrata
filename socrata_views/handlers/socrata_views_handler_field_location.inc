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
    return $options;
  }

  /**
   * Provide link to the page being visited.
   */
  function options_form(&$form, &$form_state) {
    parent::options_form($form, $form_state);
  }

  function render($values) {
    $value = $this->get_value($values);
    // Decode the nested address data if provided
    if (!empty($value['human_address'])) {
      $value['human_address'] = json_decode($value['human_address'], TRUE);
    }
    //dpm($this->options);
    if (isset($value['latitude']) && isset($value['longitude'])) {
      $this->options['alter']['make_link'] = TRUE;
      $this->options['alter']['target'] = '_blank';
      $this->options['alter']['path'] = "http://maps.google.com/maps?q={$value['latitude']},{$value['longitude']}";
    }
    return t('Map');
  }
}
