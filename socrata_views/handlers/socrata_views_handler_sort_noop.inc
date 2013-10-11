<?php
/**
 * @file
 *
 * Contains a noop sort handler to apply to fields that will cause a SoQL
 * error if sorting is applied to them, since there doesn't appear to be a
 * way to remove those fields from the "add sort criteria" dialog.
 *
 * Based on views_handler_sort_broken
 */

class socrata_views_handler_sort_noop extends views_handler_sort {
  function ui_name($short = FALSE) {
    return parent::ui_name($short) . t(' (@noop)', array('@noop' => 'No-op handler'));
  }

  function ensure_my_table() { /* No table to ensure! */ }
  function query($group_by = FALSE) { /* No query to run */ }
  function options_form(&$form, &$form_state) {
    $form['markup'] = array(
      '#prefix' => '<div class="form-item description">',
      '#markup' => t('This Socrata field type does not support sorting, no-op handler being used.'),
      '#suffix' => '</div>',
    );
  }
}
