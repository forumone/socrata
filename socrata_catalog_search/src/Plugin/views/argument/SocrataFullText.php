<?php

namespace Drupal\socrata_catalog_search\Plugin\views\argument;

use Drupal\views\Plugin\views\argument\StringArgument;

/**
 * Basic textfield argument to handle string arguments.
 *
 * @ingroup views_argument_handlers
 *
 * @ViewsArgument("socrata_full_text")
 */
class SocrataFullText extends StringArgument {

  /**
   * Add this argument to the query.
   *
   * Due to the nature of fapi, the value and the operator have an unintended
   * level of indirection. You will find them in $this->operator
   * and $this->value respectively.
   */
  public function query($group_by = FALSE) {
    $this->query->addWhere(0, 'q', $this->argument, '=');
  }

}
