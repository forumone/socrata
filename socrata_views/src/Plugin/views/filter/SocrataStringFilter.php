<?php

/**
 * @file
 * Contains \Drupal\socrata_views\Plugin\views\filter\StringFilter.
 */

namespace Drupal\socrata_views\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\filter\StringFilter;

/**
 * Basic textfield filter to handle string filtering commands
 * including equality, like, not like, etc.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("socrata_string")
 */
class SocrataStringFilter extends StringFilter {
  /**
   * This kind of construct makes it relatively easy for a child class
   * to add or remove functionality by overriding this function and
   * adding/removing items from this array.
   */
  function operators() {
    $operators = array(
      '=' => array(
        'title' => $this->t('Is equal to'),
        'method' => 'opEqual',
        'short' => $this->t('='),
        'values' => 1,
      ),
      '!=' => array(
        'title' => $this->t('Is not equal to'),
        'method' => 'opEqual',
        'short' => $this->t('!='),
        'values' => 1,
      ),
      'starts_with' => array(
        'title' => $this->t('Starts with'),
        'short' => $this->t('starts_with'),
        'method' => 'opStartsWith',
        'values' => 1,
      ),
      'is null' => array(
        'title' => $this->t('Is empty (NULL)'),
        'method' => 'opEmpty',
        'short' => $this->t('empty'),
        'values' => 0,
      ),
      'is not null' => array(
        'title' => $this->t('Is not empty (NOT NULL)'),
        'method' => 'opEmpty',
        'short' => $this->t('not empty'),
        'values' => 0,
      ),
    );

    return $operators;
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
    $field = "$this->realField";
    if (!empty($info[$this->operator]['method'])) {
      $this->{$info[$this->operator]['method']}($field);
    }

  }

  public function opEqual($field) {
    $this->query->addWhere($this->options['group'], $field, $this->value, $this->operator);
  }

  protected function opStartsWith($field) {
    $this->query->addWhereExpression($this->options['group'], "{$this->operator}({$field}, '{$this->value}')");
  }

  protected function opEmpty($field) {
    $this->query->addWhereExpression($this->options['group'], "{$field} {$this->operator}");
  }

}
