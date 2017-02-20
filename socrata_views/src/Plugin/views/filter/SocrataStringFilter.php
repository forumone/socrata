<?php

namespace Drupal\socrata_views\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\StringFilter;

/**
 * Extend the default StringFilter to work with Socrata fields.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("socrata_string")
 */
class SocrataStringFilter extends StringFilter {

  /**
   * {@inheritdoc}
   */
  public function operators() {
    $operators = [
      '=' => [
        'title' => $this->t('Is equal to'),
        'method' => 'opEqual',
        'short' => $this->t('='),
        'values' => 1,
      ],
      '!=' => [
        'title' => $this->t('Is not equal to'),
        'method' => 'opEqual',
        'short' => $this->t('!='),
        'values' => 1,
      ],
      'starts_with' => [
        'title' => $this->t('Starts with'),
        'short' => $this->t('starts_with'),
        'method' => 'opStartsWith',
        'values' => 1,
      ],
      'is null' => [
        'title' => $this->t('Is empty (NULL)'),
        'method' => 'opEmpty',
        'short' => $this->t('empty'),
        'values' => 0,
      ],
      'is not null' => [
        'title' => $this->t('Is not empty (NOT NULL)'),
        'method' => 'opEmpty',
        'short' => $this->t('not empty'),
        'values' => 0,
      ],
    ];

    return $operators;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $info = $this->operators();
    $field = "$this->realField";
    if (!empty($info[$this->operator]['method'])) {
      $this->{$info[$this->operator]['method']}($field);
    }

  }

  /**
   * {@inheritdoc}
   */
  public function opEqual($field) {
    $this->query->addWhere($this->options['group'], $field, $this->value, $this->operator);
  }

  /**
   * {@inheritdoc}
   */
  protected function opStartsWith($field) {
    $this->query->addWhereExpression($this->options['group'], "{$this->operator}({$field}, '{$this->value}')");
  }

  /**
   * {@inheritdoc}
   */
  protected function opEmpty($field) {
    $this->query->addWhereExpression($this->options['group'], "{$field} {$this->operator}");
  }

}
