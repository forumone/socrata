<?php

/**
 * @file
 * Contains \Drupal\socrata_views\Plugin\views\argument\SocrataNumericArgument.
 */

namespace Drupal\socrata_views\Plugin\views\argument;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\argument\NumericArgument;

/**
 * Simple filter to handle greater than/less than filters
 *
 * @ingroup views_argument_handlers
 *
 * @ViewsArgument("socrata_numeric")
 */
class SocrataNumericArgument extends NumericArgument {
  protected function defineOptions() {
    $options = parent::defineOptions();
    // Remove the irrelevant min & max options.
    unset($options['value']['contains']['min']);
    unset($options['value']['contains']['max']);

    return $options;
  }

  function operators() {
    $operators = parent::operators();

    // Remove irrelevant operators for SoQL.
    unset($operators['between']);
    unset($operators['not between']);
    unset($operators['regular_expression']);

    return $operators;
  }

  protected function valueForm(&$form, FormStateInterface $form_state) {
   parent::valueForm($form, $form_state);

    // Remove the irrelevant min & max options.
   unset($form['value']['min']);
   unset($form['value']['max']);
  }

  protected function opSimple($field) {
    // Have to override method because for numeric values parent $field is in
    // the form of table.field for some reason.
    $this->query->addWhere($this->options['group'], $this->field, $this->value, $this->operator);
  }

}
