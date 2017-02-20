<?php

namespace Drupal\socrata_catalog_search\Plugin\views\field;

use Drupal\views\ResultRow;
use Drupal\views\Plugin\views\field\PrerenderList;

/**
 * A handler to provide proper displays for calendar dates.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("socrata_list")
 */
class SocrataList extends PrerenderList {

  /**
   * {@inheritdoc}
   *
   * Items should be stored in the result array, if possible, as an array
   * with 'value' as the actual displayable value of the item, plus
   * any items that might be found in the 'alter' options array for
   * creating links, such as 'path', 'fragment', 'query' etc, such a thing
   * is to be made. Additionally, items that might be turned into tokens
   * should also be in this array.
   */
  public function getItems(ResultRow $values) {
    $field = $this->getValue($values);

    $items = explode(',', $field);
    if (!empty($items)) {
      $this->items = [];
      foreach ($items as $count => $value) {
        // Array needs to have a themeable keyed value.
        $this->items[$count] = ['#plain_text' => $value];
      }

      return $this->items;
    }

    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function render_item($count, $item) {
    // Return the bare value so that the string isn't double-escaped.
    return $item['#plain_text'];
  }

}
