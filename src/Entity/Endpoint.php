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
 *   config_prefix = "socrata",
 *   admin_permission = "administer socrata",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   links = {
 *     "edit-form" = "/admin/structure/socrata/{endpoint}",
 *     "delete-form" = "/admin/structure/socrata/{endpoint}/delete",
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

  // Your specific configuration property get/set methods go here,
  // implementing the interface.
}
