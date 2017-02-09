<?php

/**
 * @file
 * Contains \Drupal\socrata_views\Plugin\views\filter\SocrataBooleanOperator.
 */

namespace Drupal\socrata_views\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\BooleanOperator;

/**
 * Simple filter to handle matching of boolean values
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("socrata_boolean")
 */
class SocrataBooleanOperator extends BooleanOperator {
  /**
   * This kind of construct makes it relatively easy for a child class
   * to add or remove functionality by overriding this function and
   * adding/removing items from this array.
   */

  /**
   * Add this filter to the query.
   *
   * Due to the nature of fapi, the value and the operator have an unintended
   * level of indirection. You will find them in $this->operator
   * and $this->value respectively.
   */
  function query() {
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
