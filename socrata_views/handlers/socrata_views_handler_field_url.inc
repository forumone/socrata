<?php

/**
 * @file
 * Definition of socrata_views_handler_field_url.  Borrows heavily from views_handler_field_url.
 */

/**
 * Field handler to provide simple renderer that turns a URL into a clickable link.
 *
 * @ingroup views_field_handlers
 */
class socrata_views_handler_field_url extends views_handler_field {
  function option_definition() {
    $options = parent::option_definition();

    $options['display_as_link'] = array('default' => TRUE, 'bool' => TRUE);
    $options['display_description'] = array('default' => TRUE, 'bool' => TRUE);

    return $options;
  }

  /**
   * Provide link to the page being visited.
   */
  function options_form(&$form, &$form_state) {
    $form['display_as_link'] = array(
      '#title' => t('Display as link'),
      '#type' => 'checkbox',
      '#default_value' => !empty($this->options['display_as_link']),
    );
    $form['display_description'] = array(
      '#title' => t('Display description from Socrata instead of URL'),
      '#type' => 'checkbox',
      '#default_value' => !empty($this->options['display_description']),
    );
    parent::options_form($form, $form_state);
  }

  function render($values) {
    $value = $this->get_value($values);
    dpm($this->options);
    dpm($value);
    $text = (!empty($this->options['display_description']) && !empty($this->value['description'])) ? $this->sanitize_value($value, 'description') : $this->sanitize_value($value, 'url');
    if (!empty($this->options['display_as_link'])) {
      $this->options['alter']['make_link'] = TRUE;
      $this->options['alter']['path'] = $value;
    }
    return $text;
  }
}
