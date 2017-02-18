<?php

namespace Drupal\socrata_catalog_search\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\InOperator;

/**
 * Socrata Catalog Search Type filter.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("socrata_type")
 */
class SocrataType extends InOperator {

  /**
   * {@inheritdoc}
   */
  public function getValueOptions() {
    $this->valueOptions = [
      'charts' => $this->t("Charts"),
      'datalenses' => $this->t("Datalenses"),
      'datasets' => $this->t("Datasets"),
      'files' => $this->t("Files"),
      'hrefs' => $this->t("Hrefs"),
      'maps' => $this->t("Maps"),
      'stories' => $this->t("Stories"),
    ];
    return $this->valueOptions;
  }

  /**
   * {@inheritdoc}
   */
  public function operators() {
    $operators = parent::operators();

    // Disable unsupported operator.
    unset($operators['not in']);

    return $operators;
  }

  /**
   * {@inheritdoc}
   */
  protected function opSimple() {
    if (empty($this->value)) {
      return;
    }
    $this->ensureMyTable();

    // We use array_values() because the checkboxes keep keys and that can cause
    // array addition problems.
    // Override field.
    $this->query->addWhere($this->options['group'], "only", array_values($this->value), $this->operator);
  }

}
