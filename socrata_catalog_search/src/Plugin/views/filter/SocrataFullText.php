<?php

/**
 * @file
 * Contains \Drupal\socrata_catalog_search\Plugin\views\filter\SocrataFullText.
 */

namespace Drupal\socrata_catalog_search\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\StringFilter;
use Drupal\Core\Form\FormStateInterface;

/**
 * Basic textfield filter to handle string filtering commands
 * including equality, like, not like, etc.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("socrata_full_text")
 */
class SocrataFullText extends StringFilter {

  /**
   * {@inheritdoc}
   */
  function operators() {
    $operators = array(
      '=' => array(
        'title' => t('Contains'),
        'method' => 'opEqual',
        'short' => t('='),
        'values' => 1,
      ),
    );

    return $operators;
  }

  /**
   * {@inheritdoc}
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    parent::valueForm($form, $form_state);

    // Add help text.
    $form['value']['#description'] = t('You may enter multiple values separated by a comma as "OR" query parameters.');
  }

  /**
   * Add this filter to the query.
   *
   * Due to the nature of fapi, the value and the operator have an unintended
   * level of indirection. You will find them in $this->operator
   * and $this->value respectively.
   */
  public function query() {
    $info = $this->operators();

    // Override the field name per http://labs.socrata.com/docs/search.html#-full-text-search.
    $field = 'q';

    if (!empty($info[$this->operator]['method'])) {
      $this->{$info[$this->operator]['method']}($field);
    }
  }

}
