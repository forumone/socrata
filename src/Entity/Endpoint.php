<?php

namespace Drupal\socrata\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Url;
use Drupal\socrata\EndpointInterface;

/**
 * Defines the Socrata endpoint entity.
 *
 * @ConfigEntityType(
 *   id = "endpoint",
 *   label = @Translation("Socrata endpoint"),
 *   handlers = {
 *     "list_builder" = "Drupal\socrata\Controller\EndpointListBuilder",
 *     "form" = {
 *       "add" = "Drupal\socrata\Form\EndpointForm",
 *       "edit" = "Drupal\socrata\Form\EndpointForm",
 *       "delete" = "Drupal\socrata\Form\EndpointDeleteForm",
 *     }
 *   },
 *   config_prefix = "endpoint",
 *   admin_permission = "administer socrata",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   links = {
 *     "edit-form" = "/admin/structure/socrata/{endpoint}",
 *     "delete-form" = "/admin/structure/socrata/{endpoint}/delete"
 *   }
 * )
 */
class Endpoint extends ConfigEntityBase implements EndpointInterface {

  /**
   * The Endpoint ID.
   *
   * @var string
   */
  public $id;

  /**
   * The Endpoint label.
   *
   * @var string
   */
  public $label;


  /**
   * The Endpoint URL.
   *
   * @var string
   */
  public $url;

  /**
   * The Endpoint Application token.
   *
   * @var string
   */
  public $app_token;

  /**
   * {@inheritdoc}
   */
  public function getUrl() {
    return $this->url;
  }

  /**
   * {@inheritdoc}
   */
  public function getAppToken() {
    return $this->app_token;
  }

  /**
   * Return Socrata SODA URL with endpoint and parameters.
   *
   * @param array $params
   *   Array of query parameters.
   *
   * @return string
   *   Formatted URL
   */
  public function getSodaUrl(array $params = []) {
    // Add app token if available.
    if ($this->getAppToken()) {
      $params['$$app_token'] = $this->getAppToken();
    }

    return Url::fromUri($this->getUrl(), ['query' => $params, 'absolute' => TRUE])->toString();
  }

  /**
   * Return Unencoded Socrata SODA URL with endpoint and parameters.
   *
   * @param array $params
   *   Array of query parameters.
   *
   * @return string
   *   Formatted URL
   */
  public function getUnencodedSodaUrl(array $params = []) {
    // We might not want to encode the URL in cases where we just want it to be
    // output for humans to read, most notably in the query displayed in the
    // views preview.
    // In this case, assemble our URL with the query parameters directly,
    // rather than passing them in as query arguments to the URL function, where
    // they'll be URL-encoded.
    // Add app token if available.
    if ($this->getAppToken()) {
      $params['$$app_token'] = $this->getAppToken();
    }

    $url_with_params = $this->getUrl();
    if (!empty($params)) {
      $url_with_params .= '?';
      $params_query = [];
      foreach ($params as $key => $value) {
        $params_query[] = $key . '=' . $value;
      }
      $url_with_params .= implode('&', $params_query);
    }

    return Url::fromUri($url_with_params, ['absolute' => TRUE])->toString();
  }

  /**
   * Returns an embed URL.
   *
   * @return string
   *   The embed URL.
   */
  public function getEmbedUrl() {
    $components = $this->getComponents();
    return "https://{$components['host']}/w/{$components['dataset_id']}";
  }

  /**
   * Return download Socrata URL.
   *
   * @param string $format
   *   The download format.
   *
   * @return string
   *   The download URL.
   */
  public function getDownloadUrl($format = 'csv') {
    $components = $this->getComponents();

    // Note that this is the old Socrata API style URL.
    return "{$components['scheme']}://{$components['host']}/api/views/{$components['dataset_id']}/rows.{$format}?accessType=DOWNLOAD";
  }

  /**
   * Return Socrata metadata URL.
   *
   * @return string
   *   The metadata URL
   */
  public function getMetaDataUrl() {
    $components = $this->getComponents();

    return "{$components['scheme']}://{$components['host']}/api/views/{$components['dataset_id']}.json";
  }

  /**
   * Parse out the components of endpoint URL.
   *
   * @return array
   *   Array of components.
   */
  public function getComponents() {
    $components = parse_url($this->url);
    $dataset_id = '';
    $path = $components['path'];
    if ($path) {
      $parts = explode('/', $path);
      if (!empty($parts)) {
        $tmp = explode('.', array_pop($parts));
        if (!empty($tmp)) {
          $dataset_id = $tmp[0];
        }
      }
    }
    $components['dataset_id'] = $dataset_id;

    return $components;
  }

}
