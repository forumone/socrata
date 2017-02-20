<?php

namespace Drupal\socrata_views\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\BooleanOperator;

/**
 * Extend the default BooleanOperator to work with Socrata fields.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("socrata_boolean")
 */
class SocrataBooleanOperator extends BooleanOperator {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $field = $this->realField;

    if (!is_string($this->value)) {
      $this->query->addWhereExpression($this->options['group'], "({$field} is null or {$field}=false)");
    }
    else {
      $value = $this->value ? 'true' : 'false';
      $this->query->addWhereExpression($this->options['group'], "({$field}{$this->operator}{$value})");
    }
  }

}
