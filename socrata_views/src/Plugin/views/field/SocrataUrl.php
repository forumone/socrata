<?php

namespace Drupal\socrata_views\Plugin\views\field;

use Drupal\views\Plugin\views\field\Url;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\ResultRow;

/**
 * Field plugin to provide renderer that turns a URL into a clickable link.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("socrata_url")
 */
class SocrataUrl extends Url {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['display_as_link'] = array('default' => TRUE);
    $options['link_text_option'] = array('default' => 'socrata_description');
    $options['link_text'] = array('default' => '', 'translatable' => TRUE);

    return $options;
  }

  /**
   * Provide link to the page being visited.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['display_as_link'] = array(
      '#title' => $this->t('Display as link'),
      '#type' => 'checkbox',
      '#default_value' => !empty($this->options['display_as_link']),
    );
    $form['link_text_option'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Select what to use as the link text'),
      '#options' => array(
        'url' => $this->t('URL'),
        'socrata_description' => $this->t('Description from Socrata, otherwise provided text.'),
        'user_link_text' => $this->t('Provided link text'),
      ),
      '#default_value' => $this->options['link_text_option'],
      '#states' => array(
        // Hide the settings when the display_as_link checkbox is disabled.
        'invisible' => array(
          ':input[name="options[display_as_link]"]' => array('checked' => FALSE),
        ),
      ),
    );
    $form['link_text'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Link text'),
      '#description' => $this->t('Text to use for link based on selection above.'),
      '#default_value' => $this->options['link_text'],
      '#states' => array(
        // Show only when link_text option selected & when the display_as_link
        // checkbox is enabled.
        'invisible' => array(
          ':input[name="options[link_text_option]"]' => array('value' => 'url'),
        ),
        'visible' => array(
          ':input[name="options[display_as_link]"]' => array('checked' => TRUE),
        ),
      ),
    );
    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $text = '';
    $value = $this->getValue($values);
    if (!empty($value) && !empty($value['url'])) {
      $text = $this->sanitizeValue($value['url'], 'url');
    }
    if (!empty($this->options['display_as_link'])) {
      switch ($this->options['link_text_option']) {
        case 'socrata_description':
          if (!empty($value['description'])) {
            $text = $this->sanitizeValue($value['description'], 'xss');
          }
          elseif (!empty($value['link_text'])) {
            $text = $this->sanitizeValue($this->options['link_text'], 'xss');
          }
          break;

        case 'user_link_text':
          if (!empty($this->options['link_text'])) {
            $text = $this->sanitizeValue($this->options['link_text'], 'xss');
          }
          break;
      }
      $this->options['alter']['make_link'] = TRUE;
      $this->options['alter']['path'] = $value['url'];
    }
    return $text;
  }

}
