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
   * Generate output.
   */
  function render(ResultRow $values) {
    // @todo Refactor this to just alter the value and pass to the parent
    // render().
    $value = strtotime($this->getValue($values));
    $format = $this->options['date_format'];
    if (in_array($format, array('custom', 'raw time ago', 'time ago', 'raw time hence', 'time hence', 'raw time span', 'time span', 'raw time span', 'inverse time span', 'time span'))) {
      $custom_format = $this->options['custom_date_format'];
    }

    if ($value) {
      $timezone = !empty($this->options['timezone']) ? $this->options['timezone'] : NULL;
      $time_diff = REQUEST_TIME - $value; // will be positive for a datetime in the past (ago), and negative for a datetime in the future (hence)
      switch ($format) {
        case 'raw time ago':
          return $this->dateFormatter->formatTimeDiffSince($value, array('granularity' => is_numeric($custom_format) ? $custom_format : 2));

        case 'time ago':
          return $this->t('%time ago', array('%time' => $this->dateFormatter->formatTimeDiffSince($value, array('granularity' => is_numeric($custom_format) ? $custom_format : 2))));

        case 'raw time hence':
          return $this->dateFormatter->formatTimeDiffUntil($value, array('granularity' => is_numeric($custom_format) ? $custom_format : 2));

        case 'time hence':
          return $this->t('%time hence', array('%time' => $this->dateFormatter->formatTimeDiffUntil($value, array('granularity' => is_numeric($custom_format) ? $custom_format : 2))));

        case 'raw time span':
          return ($time_diff < 0 ? '-' : '') . $this->dateFormatter->formatTimeDiffSince($value, array('strict' => FALSE, 'granularity' => is_numeric($custom_format) ? $custom_format : 2));

        case 'inverse time span':
          return ($time_diff > 0 ? '-' : '') . $this->dateFormatter->formatTimeDiffSince($value, array('strict' => FALSE, 'granularity' => is_numeric($custom_format) ? $custom_format : 2));

        case 'time span':
          $time = $this->dateFormatter->formatTimeDiffSince($value, array('strict' => FALSE, 'granularity' => is_numeric($custom_format) ? $custom_format : 2));
          return ($time_diff < 0) ? $this->t('%time hence', array('%time' => $time)) : $this->t('%time ago', array('%time' => $time));

        case 'custom':
          if ($custom_format == 'r') {
            return format_date($value, $format, $custom_format, $timezone, 'en');
          }
          return format_date($value, $format, $custom_format, $timezone);

        default:
          return format_date($value, $format, '', $timezone);
      }
    }
  }
}
