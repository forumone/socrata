<?php
/**
 * @file
 * Contains \Drupal\socrata\Entity\Endpoint.
 */

namespace Drupal\socrata\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
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
   * Returns an embed URL.
   *
   * @return string
   */
  public function getEmbedURL() {
    $components = $this->getComponents();
    return "https://{$components['host']}/w/{$components['dataset_id']}";
  }

  /**
   * Parse out the components of endpoint URL.
   *
   * @return string
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
