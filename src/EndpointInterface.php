<?php
/**
 * @file
 * Contains \Drupal\socrata\EndpointInterface.
 */

namespace Drupal\socrata;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining a Socrata endpoint entity.
 */
interface EndpointInterface extends ConfigEntityInterface {
  public function getUrl();
  public function getAppToken();
}
