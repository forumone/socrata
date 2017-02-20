<?php

namespace Drupal\socrata_filter\Plugin\Filter;

use Drupal\Component\Utility\Xss;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\socrata\Entity\Endpoint;

/**
 * Provides a filter to insert Socrata endpoint.
 *
 * @Filter(
 *   id = "filter_socrata",
 *   title = @Translation("Embed Socrata dataset views"),
 *   description = @Translation("Allow users to embed a Socrata dataset's default view in an editable content area."),
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
    $new_text = preg_replace_callback(
      '/\[socrata((?:\s).*)]/i',
      function ($matches) {
        // @codingStandardsIgnoreStart
        // Suppressing the unused variable warning because it really is
        // used below.
        $retval = '';
        // @codingStandardsIgnoreEnd
        if (isset($matches[1])) {
          $attrs = explode(' ', trim($matches[1]));
          $vars = [];
          foreach ($attrs as $attr) {
            list($name, $val) = explode('=', trim($attr), 2);
            $vars[Xss::filter($name)] = Xss::filter($val);
          }

          // Check if the source was set.
          if (!isset($vars['source'])) {
            return $retval;
          }

          $id = $vars['source'];
          $endpoint = Endpoint::load($id);
          if (!is_object($endpoint)) {
            return $retval;
          }

          $render_array['#theme'] = 'socrata_filter';
          $render_array['#embed_url'] = $endpoint->getEmbedUrl();
          $render_array['#source'] = $vars['source'];
          $render_array['#width'] = $this->getWidth($vars);
          $render_array['#height'] = $this->getHeight($vars);;

          $retval = render($render_array);
        }
        return $retval;
      },
      $text
    );

    return new FilterProcessResult($new_text);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form['socrata_filter_width'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default width of embed'),
      '#description' => $this->t('The default width of the embedded Socrata view (in pixels) to use if not specified in the embed tag.'),
      '#default_value' => $this->settings['socrata_filter_width'],
    ];

    $form['socrata_filter_height'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default height of embed'),
      '#description' => $this->t('The default height of the embedded Socrata view (in pixels) to use if not specified in the embed tag.'),
      '#default_value' => $this->settings['socrata_filter_height'],
    ];
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

  /**
   * Returns the set width or the default.
   *
   * @param array $vars
   *   An array of filter arguments.
   *
   * @return int
   *   The width of the iframe.
   */
  protected function getWidth(array $vars) {
    if (isset($vars['width']) && is_numeric($vars['width'])) {
      return $vars['width'];
    }

    return $this->settings['socrata_filter_width'];
  }

  /**
   * Returns the set height or the default.
   *
   * @param array $vars
   *   An array of filter arguments.
   *
   * @return int
   *   The height of the iframe.
   */
  protected function getHeight(array $vars) {
    if (isset($vars['height']) && is_numeric($vars['height'])) {
      return $vars['height'];
    }

    return $this->settings['socrata_filter_height'];
  }

}
