<?php

namespace Drupal\socrata_views\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\Date;

/**
 * Extend the default Date to work with Socrata fields.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("socrata_date")
 */
class SocrataDate extends Date {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->ensureMyTable();
    // Use the realField name instead of the table.field default.
    $field = "$this->realField";

    $info = $this->operators();
    if (!empty($info[$this->operator]['method'])) {
      $this->{$info[$this->operator]['method']}($field);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function opBetween($field) {
    $a = intval(strtotime($this->value['min'], 0));
    $b = intval(strtotime($this->value['max'], 0));

    if ($this->value['type'] == 'offset') {
      // Keep sign.
      $a = time() + $a;
      // Keep sign.
      $b = time() + $b;
    }

    $a = date('Y-m-d\TH:i:s', $a);
    $b = date('Y-m-d\TH:i:s', $b);
    // This is safe because we are manually scrubbing the values.
    // It is necessary to do it this way because $a and $b are formulas when
    // using an offset.
    $operator = strtoupper($this->operator);
    $this->query->addWhereExpression($this->options['group'], "$field $operator '$a' AND '$b'");
  }

  /**
   * {@inheritdoc}
   */
  public function opSimple($field) {
    $value = intval(strtotime($this->value['value'], 0));
    if (!empty($this->value['type']) && $this->value['type'] == 'offset') {
      // Keep sign.
      $value = time() + $value;
    }
    // This is safe because we are manually scrubbing the value.
    // It is necessary to do it this way because $value is a formula when using
    // an offset. Format as ISO8601 Time with no timezone offset per
    // http://dev.socrata.com/docs/datatypes/floating_timestamp.html.
    $value = date('Y-m-d\TH:i:s', $value);
    $this->query->addWhere($this->options['group'], $field, "{$value}", $this->operator);
  }

}
