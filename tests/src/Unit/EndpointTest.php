<?php

namespace Drupal\Tests\socrata\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\socrata\Entity\Endpoint;

/**
 * Endpoint units tests.
 *
 * @group endpoint
 * @group endpoint
 */
class EndpointTest extends UnitTestCase {

  /**
   * Endpoint data.
   *
   * @var array
   */
  protected $data;

  /**
   * Endpoint object.
   *
   * @var object
   */
  protected $endpoint;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->data = [
      'url' => 'https://data.seattle.gov/resource/rn6u-vkuv.json',
      'id' => 'endpoint',
      'label' => 'Endpoint',
      'app_token' => '',
    ];
    $this->endpoint = new Endpoint($this->data, 'endpoint');
  }

  /**
   * Test getting the URL.
   */
  public function testGetUrl() {
    $this->assertEquals($this->endpoint->getUrl(), $this->data['url']);
  }

  /**
   * Test getting the app token.
   */
  public function testGetAppToken() {
    $this->assertEquals($this->endpoint->getAppToken(), $this->data['app_token']);
  }

}
