<?php

/**
 * @file
 * Contains \Drupal\socrata\socrata_views\Plugin\views\display\Export.
 */

namespace Drupal\socrata_views\Plugin\views\display;

use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\display\ResponseDisplayPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\ViewExecutable;
use Drupal\views\Views;

/**
 * The plugin that handles export of Socrata data.
 *
 * @ingroup views_display_plugins
 *
 * @ViewsDisplay(
 *   id = "socrata_export",
 *   title = @Translation("Socrata Data Export"),
 *   help = @Translation("Export the Socrata endpoint data to a file."),
 *   uses_route = TRUE,
 *   admin = @Translation("Export"),
 *   returns_response = TRUE
 * )
 */
class Export extends DisplayPluginBase {

  /**
   * Whether the display allows the use of AJAX or not.
   *
   * @var bool
   */
  protected $ajaxEnabled = FALSE;

  /**
   * Whether the display allows the use of a pager or not.
   *
   * @var bool
   */
  protected $usesPager = FALSE;

  /**
   * Whether the display allows the use of a 'more' link or not.
   *
   * @var bool
   */
  protected $usesMore = FALSE;

  /**
   * Whether the display allows attachments.
   *
   * @var bool
   *   TRUE if the display can use attachments, or FALSE otherwise.
   */
  protected $usesAttachments = FALSE;

  /**
   * Whether the display allows area plugins.
   *
   * @var bool
   */
  protected $usesAreas = FALSE;


  /**
   * {@inheritdoc}
   */
  public function getType() {
    return 'socrata_export';
  }

  /**
   * {@inheritdoc}
   */
  public function preview() {
    if ($plugin = $this->view->display_handler->getPlugin('style')) {
      return $plugin->attachTo();
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    // Disable unused sections
    unset($options['row']);
    unset($options['pager']);
    unset($options['cache']);

    $options['displays'] = array('default' => array());
    $options['attachment_position'] = array('default' => 'before');

    // Overrides for standard stuff.
    $options['style']['contains']['type']['default'] = 'csv';
    $options['style']['contains']['options']['default']  = array('description' => '');
    $options['row']['contains']['type']['default'] = 'socrata_export';
    $options['defaults']['default']['style'] = FALSE;
    $options['defaults']['default']['row'] = FALSE;

    // Make sure the query is not cached.
    $options['defaults']['default']['cache'] = FALSE;

    // Set the display title to an empty string (not used in this display type).
    $options['title']['default'] = '';
    $options['defaults']['default']['title'] = FALSE;

    return $options;
  }

  public function attachmentPositions($position = NULL) {
    $positions = array(
      'before' => $this->t('Before'),
      'after' => $this->t('After'),
      'both' => $this->t('Both'),
    );

    if ($position) {
      return $positions[$position];
    }

    return $positions;
  }

  /**
   * {@inheritdoc}
   */
  public function optionsSummary(&$categories, &$options) {
    parent::optionsSummary($categories, $options);

    // Disable unused sections
    unset($options['title']);
    unset($options['pager']);
    unset($options['cache']);
    unset($options['query']);
    unset($options['use_ajax']);
    unset($options['group_by']);
    unset($options['link_display']);
    unset($options['exposed_form']);

    $categories['attachment'] = array(
      'title' => $this->t('Attachment settings'),
      'column' => 'second',
      'build' => array(
        '#weight' => -10,
      ),
    );

    $displays = array_filter($this->getOption('displays'));
    if (count($displays) > 1) {
      $attach_to = $this->t('Multiple displays');
    }
    elseif (count($displays) == 1) {
      $display = array_shift($displays);
      $displays = $this->view->storage->get('display');
      if (!empty($displays[$display])) {
        $attach_to = $displays[$display]['display_title'];
      }
    }

    if (!isset($attach_to)) {
      $attach_to = $this->t('None');
    }

    $options['displays'] = array(
      'category' => 'attachment',
      'title' => $this->t('Attach to'),
      'value' => $attach_to,
    );

    $options['attachment_position'] = array(
      'category' => 'attachment',
      'title' => $this->t('Attachment position'),
      'value' => $this->attachmentPositions($this->getOption('attachment_position')),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    // It is very important to call the parent function here.
    parent::buildOptionsForm($form, $form_state);

    switch ($form_state->get('section')) {
      case 'displays':
        $form['#title'] .= $this->t('Attach to');
        $displays = array();
        foreach ($this->view->storage->get('display') as $display_id => $display) {
          // @todo The display plugin should have display_title and id as well.
          if ($this->view->displayHandlers->has($display_id) && $this->view->displayHandlers->get($display_id)->acceptAttachments()) {
            $displays[$display_id] = $display['display_title'];
          }
        }
        $form['displays'] = array(
          '#title' => $this->t('Displays'),
          '#type' => 'checkboxes',
          '#description' => $this->t('The feed icon will be available only to the selected displays.'),
          '#options' => array_map('\Drupal\Component\Utility\Html::escape', $displays),
          '#default_value' => $this->getOption('displays'),
        );
        break;
      case 'attachment_position':
        $form['#title'] .= $this->t('Position');
        $form['attachment_position'] = array(
          '#title' => $this->t('Position'),
          '#type' => 'radios',
          '#description' => $this->t('Attach before or after the parent display?'),
          '#options' => $this->attachmentPositions(),
          '#default_value' => $this->getOption('attachment_position'),
        );
        break;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitOptionsForm(&$form, FormStateInterface $form_state) {
    parent::submitOptionsForm($form, $form_state);
    $section = $form_state->get('section');
    switch ($section) {
      case 'displays':
      case 'attachment_position':
        $this->setOption($section, $form_state->getValue($section));
        break;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function attachTo(ViewExecutable $view, $display_id, array &$build) {
    $displays = $this->getOption('displays');

    if (empty($displays[$display_id])) {
      return;
    }

    if (!$this->access()) {
      return;
    }

    $view->setDisplay($this->display['id']);

    if ($plugin = $view->display_handler->getPlugin('style')) {
      $attachment = $plugin->attachTo();
      switch ($this->getOption('attachment_position')) {
        case 'before':
          $this->view->attachment_before[] = $attachment;
          break;
        case 'after':
          $this->view->attachment_after[] = $attachment;
          break;
        case 'both':
          $this->view->attachment_before[] = $attachment;
          $this->view->attachment_after[] = $attachment;
          break;
      }
    }
  }
}
