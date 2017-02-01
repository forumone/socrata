<?php

namespace Drupal\Tests\socrata\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\socrata\Entity\Endpoint;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Utility\UnroutedUrlAssembler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

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

    $container = new ContainerBuilder();

    $requestStack = new RequestStack();
    $requestStack->push(new Request());
    $pathProcessor = $this->getMock('Drupal\Core\PathProcessor\OutboundPathProcessorInterface');
    $unroutedUrlAssembler = new UnroutedUrlAssembler($requestStack, $pathProcessor);

    $container->set('unrouted_url_assembler', $unroutedUrlAssembler);
    \Drupal::setContainer($container);
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

  /**
   * Test getting the SODA URL.
   */
  public function testGetSodaUrlWithoutParams() {
    $this->assertEquals($this->data['url'], $this->endpoint->getSodaURL());
  }

  /**
   * Test getting the SODA URL with token and parameters.
   *
   * @dataProvider getUrlOptions
   */
  public function testGetSodaUrlWithParams($token, $params) {
    $this->endpoint->app_token = $token;
    $query_params = isset($token) ? $params + ['$$app_token' => $token] : $params;
    $url = $this->data['url'] . '?' . UrlHelper::buildQuery($query_params);

    $this->assertEquals($url, $this->endpoint->getSodaURL($params));
  }

  /**
   * Data provider for ::testGetSodaUrlWithParams().
   *
   * @return array
   *   Test data.
   */
  public function getUrlOptions() {
    return [
      [NULL, ['param1' => 'value1']],
      ['token', []],
      ['token', ['param1' => 'value1']],
    ];
  }

}
