<?php

/**
 * @file
 * Contains \Drupal\socrata_views\Plugin\views\field\CalendarDate.
 * Based heavily on date with the only major difference
 * being that Socrata stores this field type in ISO 8601 format.
 */

namespace Drupal\socrata_views\Plugin\views\field;

use Drupal\views\ResultRow;
use Drupal\views\Plugin\views\field\Date;

/**
 * A handler to provide proper displays for calendar dates.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("calendar_date")
 */
class CalendarDate extends Date {

  /**
   * {@inheritdoc}
   */
  function getValue(ResultRow $values, $field = NULL) {
    $values = parent::getValue($values, $field);

    // Convert the date string to a timestamp.
    return strtotime($values);
  }

}
