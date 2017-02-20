<?php

namespace Drupal\Tests\socrata\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\socrata\Entity\Endpoint;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Utility\UnroutedUrlAssembler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Yaml\Yaml;

/**
 * Endpoint units tests.
 *
 * @group socrata
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

    $this->data = Yaml::parse(file_get_contents(__DIR__ . '/../../config/data.yml'));
    $this->endpoint = new Endpoint($this->data['endpoints']['valid'], 'endpoint');

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
    $this->assertEquals($this->endpoint->getUrl(), $this->data['endpoints']['valid']['url']);
  }

  /**
   * Test getting the app token.
   */
  public function testGetAppToken() {
    $this->assertEquals($this->endpoint->getAppToken(), $this->data['endpoints']['valid']['app_token']);
  }

  /**
   * Test getting the SODA URL.
   */
  public function testGetSodaUrlWithoutParams() {
    $this->assertEquals($this->data['endpoints']['valid']['url'], $this->endpoint->getSodaUrl());
  }

  /**
   * Test getting the SODA URL with token and parameters.
   *
   * @dataProvider getUrlOptions
   */
  public function testGetSodaUrlWithParams($token, $params) {
    $this->endpoint->app_token = $token;
    $query_params = isset($token) ? $params + ['$$app_token' => $token] : $params;
    $url = $this->data['endpoints']['valid']['url'] . '?' . UrlHelper::buildQuery($query_params);

    $this->assertEquals($url, $this->endpoint->getSodaUrl($params));
  }

  /**
   * Test getting the SODA URL.
   */
  public function testGetUnencodedSodaUrlWithoutParams() {
    $this->assertEquals($this->data['endpoints']['valid']['url'], $this->endpoint->getUnencodedSodaUrl());
  }

  /**
   * Test getting the SODA URL with token and parameters.
   *
   * @dataProvider getUrlOptions
   */
  public function testGetUnencodedSodaUrlWithParams($token, $params) {
    $this->endpoint->app_token = $token;
    $query_params = isset($token) ? $params + ['$$app_token' => $token] : $params;
    $url = $this->data['endpoints']['valid']['url'] . '?' . urldecode(http_build_query($query_params));

    $this->assertEquals($url, $this->endpoint->getUnencodedSodaUrl($params));
  }

  /**
   * Test getting the URL components.
   *
   * The dataset is the string of characters followed by the .json file
   * extension.
   */
  public function testGetComponents() {
    $components = $this->endpoint->getComponents();
    $parsed_url = parse_url($this->data['endpoints']['valid']['url']);
    preg_match('/([^\/]+)?\.\w+$/', $parsed_url['path'], $matches);
    $this->assertEquals($matches[1], $components['dataset_id']);
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
