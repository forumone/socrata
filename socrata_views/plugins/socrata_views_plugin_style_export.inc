<?php
/**
 * @file
 * Plugin include file for export style plugin.
 */

/**
 * Generalized style plugin for export plugins.
 *
 * @ingroup views_style_plugins
 */
class socrata_views_plugin_style_export extends views_plugin_style {

  /**
   * Set options fields and default values.
   */
  function option_definition() {
    $options = parent::option_definition();

    $options['attach_text'] = array(
      'default' => $this->definition['export feed text'],
      'translatable' => TRUE,
    );
    /*$options['filename'] = array(
      'default' => $this->definition['export feed file'],
      'translatable' => FALSE,
    );*/
    return $options;
  }

  /**
   * Options form mini callback.
   */
  function options_form(&$form, &$form_state) {
    $form['attach_text'] = array(
      '#type' => 'textfield',
      '#title' => t('Attach text'),
      '#default_value' => $this->options['attach_text'],
      '#description' => t('This text is used in building the feed link. By default it is the "alt" text for the feed image.'),
    );
  }

  /**
   * Attach this view to another display as a feed.
   *
   * Provide basic functionality for all export style views like attaching a
   * feed image link.
   */
  function attach_to($display_id, $path, $title) {
    if ($this->display->handler->access()) {
      // Temporary link until I figure out theme call above
      $download_format = $this->definition['export feed type'];
      $socrata_select_query = $this->view->build_info['query'];
      $url = $socrata_select_query->getDownloadUrl($download_format);
      $this->view->feed_icon = l(
        $this->options['attach_text'],
        $url
      );
    }
  }

}
