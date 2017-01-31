<?php

namespace Drupal\Tests\socrata\Unit;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Core\Database\Database;
use Drupal\socrata\SocrataSelectQuery;
use Drupal\socrata\Entity\Endpoint;

/**
 * SocrataSelectQuery units tests.
 *
 * @group socrata
 */
class SocrataSelectQueryTest extends KernelTestBase {

  /**
   * Endpoint URL.
   *
   * @var string
   */
  protected $url;

  /**
   * Modules to enable.
   *
   * The test runner will merge the $modules lists from this class, the class
   * it extends, and so on up the class hierarchy. It is not necessary to
   * include modules in your list that a parent class has already declared.
   *
   * @var array
   *
   * @see \Drupal\Tests\KernelTestBase::enableModules()
   * @see \Drupal\Tests\KernelTestBase::bootKernel()
   */
  public static $modules = ['socrata'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->url = 'https://data.seattle.gov/resource/rn6u-vkuv.json';
  }

  /**
   * Test having an non-existant endpoint.
   */
  public function testInvalidTable() {
    $connection = Database::getConnection();
    $query = $connection->select(NULL)->extend('\Drupal\socrata\SocrataSelectQuery');
    $query->setEndpoint();

    $this->assertNull($query->getEndpoint());
  }

  /**
   * Test the class setting an endpoint on query initialization.
   */
  public function testQueryWithEndpoint() {
    $connection = Database::getConnection();
    $data = [
      'url' => $this->url,
      'id' => 'endpoint',
      'label' => 'Endpoint',
      'app_token' => '',
    ];
    $endpoint = new Endpoint($data, 'endpoint');
    $endpoint->save();
    $query = $connection->select('endpoint')->extend('\Drupal\socrata\SocrataSelectQuery');

    $this->assertTrue(is_a($query->getEndpoint(), '\Drupal\socrata\Entity\Endpoint'));
  }

  /**
   * Test setting an endpoint manually with the setEndpoint method.
   */
  public function testSetEndpoint() {
    $connection = Database::getConnection();
    $query = $connection->select($this->url)->extend('\Drupal\socrata\SocrataSelectQuery');
    $endpoint = new Endpoint(['url' => $this->url], 'endpoint');
    $query->setEndpoint($endpoint);

    $this->assertTrue(is_a($query->getEndpoint(), '\Drupal\socrata\Entity\Endpoint'));
  }

  /**
   * Test execution of a SocrataSelectQuery.
   */
  public function testExecuteQuery() {
    $this->installConfig(['socrata']);
    $connection = Database::getConnection();
    $query = $connection->select($this->url)->extend('\Drupal\socrata\SocrataSelectQuery');
    $endpoint = new Endpoint(['url' => $this->url], 'endpoint');
    $query->setEndpoint($endpoint);
    $ret = $query->execute();

    $this->assertTrue(is_array($ret));
    $this->assertArrayHasKey('data', $ret);
  }

  /**
   * Test execution of a SocrataSelectQuery using an invalid URL.
   */
  public function testExecuteQueryFailure() {
    $this->installConfig(['socrata']);
    $connection = Database::getConnection();
    $query = $connection->select(NULL)->extend('\Drupal\socrata\SocrataSelectQuery');
    $endpoint = new Endpoint(['url' => 'https://data.seattle.gov/resource'], 'endpoint');
    $query->setEndpoint($endpoint);
    $ret = $query->execute();

    $this->assertFalse($ret);
  }

  /**
   * Test execution of a SocrataSelectQuery of type "metadata".
   */
  public function testExecuteQueryMetadata() {
    $this->installConfig(['socrata']);
    $connection = Database::getConnection();
    $query = $connection->select(NULL)->extend('\Drupal\socrata\SocrataSelectQuery');
    $endpoint = new Endpoint(['url' => $this->url], 'endpoint');
    $query->setEndpoint($endpoint);
    $ret = $query->execute('metadata');

    $this->assertTrue(is_array($ret));
    $this->assertArrayHasKey('data', $ret);
    $this->assertArrayHasKey('metadata', $ret['data']);
  }

  /**
   * Test conversion of SocrataSelectQuery to a string.
   */
  public function testQueryToString() {
    $connection = Database::getConnection();
    $query = $connection->select(NULL)->extend('\Drupal\socrata\SocrataSelectQuery');
    $string = $query->__toString();

    $this->assertContains('SELECT', $string);
    $this->assertContains('FROM', $string);
  }

  /**
   * Test conversion of SocrataSelectQuery with an Endpoint to a string.
   */
  public function testQueryToStringWithEndpoint() {
    $connection = Database::getConnection();
    $query = $connection->select(NULL)->extend('\Drupal\socrata\SocrataSelectQuery');
    $endpoint = new Endpoint(['url' => $this->url], 'endpoint');
    $query->setEndpoint($endpoint);
    $string = $query->__toString();

    $this->assertContains($this->url, $string);
  }

}
