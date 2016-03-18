<?php
/**
 * @file
 * Contains Drupal\socrata_filter\Plugin\Filter\FilterSocrata
 */

namespace Drupal\socrata_filter\Plugin\Filter;

use Drupal\Component\Utility\Xss;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a filter to insert Socrata endpoint.
 *
 * @Filter(
 *   id = "filter_socrata",
 *   title = @Translation("Embed Socrata dataset views"),
 *   description = @Translation("Allow users to embed a Socrata dataset\'s default view in an editable content area"),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_MARKUP_LANGUAGE,
 *   settings = {
 *     "socrata_filter_width" = 500,
 *     "socrata_filter_height" = 425,
 *   },
 * )
 */
class FilterSocrata extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
     // dpm($this->settings);
    $new_text = preg_replace_callback(
      '/\[socrata((?:\s).*)]/i',
      function ($matches) {
        $retval = '';
        if (isset($matches[1])) {
          $attrs = explode(' ', trim($matches[1]));
          foreach ($attrs as $attr) {
            list($name, $val) = explode('=', trim($attr), 2);
            $vars['#' . Xss::filter($name)] = Xss::filter($val);
          }
          $vars['#theme'] = 'socrata_filter';
          $vars['#embed_url'] = 'https://data.seattle.gov/w/i5jq-ms7b';
        }
        return render($vars);
      },
      $text
    );

    return new FilterProcessResult($new_text);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form['socrata_filter_width'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Default width of embed'),
      '#description' => $this->t('The default width of the embedded Socrata view (in pixels) to use if not specified in the embed tag'),
      '#default_value' => $this->settings['socrata_filter_width'],
    );

    $form['socrata_filter_height'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Default height of embed'),
      '#description' => $this->t('The default height of the embedded Socrata view (in pixels) to use if not specified in the embed tag'),
      '#default_value' => $this->settings['socrata_filter_height'],
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function tips($long = FALSE) {
    if ($long) {
      return $this->t(
        'Embed Socrata views using @embed. Values for width and height are optional, if left off the default values configured on the %filter input filter will be used',
        [
          '@embed' => '[socrata source=<source_name> width=<width> height=<height>]',
          '%filter' => 'Embed Socrata dataset views',
        ]
      );
    }
    else {
      return $this->t('Embed Socrata views using @embed', ['@embed' => '[socrata source=<source_name> width=<width> height=<height>]']);
    }
  }

}
