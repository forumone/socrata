<?php

namespace Drupal\socrata;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining a Socrata endpoint entity.
 */
interface EndpointInterface extends ConfigEntityInterface {

  /**
   * Returns the Endpoint URL.
   *
   * @return string
   *   URL.
   */
  public function getUrl();

  /**
   * Returns the Endpoint Application Token.
   *
   * @return string
   *   Application token.
   */
  public function getAppToken();

}
