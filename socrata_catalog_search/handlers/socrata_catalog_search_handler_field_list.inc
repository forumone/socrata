<?php

/**
 * @file
 * Definition of socrata_catalog_search_handler_field_list.
 */

/**
 * Field handler to provide a list of categories.
 *
 * The items are expected to be loaded by a child object during pre_render,
 * and 'my field' is expected to be the pointer to the items in the list.
 *
 * Items to render should be in a list in $this->items
 *
 * @ingroup views_field_handlers
 */
class socrata_catalog_search_handler_field_list extends views_handler_field_prerender_list {
  /**
   * Stores all items which are used to render the items.
   * It should be keyed first by the id of the base table, for example nid.
   * The second key is the id of the thing which is displayed multiple times
   * per row, for example the tid.
   */

  /**
   * Render the field.
   *
   * This function is deprecated, but left in for older systems that have not
   * yet or won't update their prerender list fields. If a render_item method
   * exists, this will not get used by advanced_render.
   */
  function render($values) {
    $field = $this->field;
    $items = explode(',', $values->$field);

    if (!empty($items)) {
      if ($this->options['type'] == 'separator') {
        return implode($this->sanitize_value($this->options['separator']), $items);
      }
      else {
        return theme('item_list',
          array(
            'items' => $items,
            'title' => NULL,
            'type' => $this->options['type']
          ));
      }
    }
  }
}
