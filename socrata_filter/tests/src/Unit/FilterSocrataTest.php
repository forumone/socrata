<?php

namespace Drupal\Tests\socrata_filter\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\socrata\Entity\Endpoint;
use Drupal\socrata_filter\Plugin\Filter\FilterSocrata;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Utility\UnroutedUrlAssembler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Endpoint units tests.
 *
 * @group socrata_filter
 */
class FilterSocrataTest extends UnitTestCase {

  /**
   * Endpoint object.
   *
   * @var object
   */
  protected $endpoint;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $data = [
      'url' => 'https://data.seattle.gov/resource/rn6u-vkuv.json',
      'id' => 'endpoint',
      'label' => 'Endpoint',
      'app_token' => '',
    ];
    $this->endpoint = new Endpoint($data, 'endpoint');
  }

  /**
   * Test the process method of the FilterSocrata class.
   */
  public function testProcessFilterSocrata() {
    $text = 'Donec rutrum congue leo eget malesuada';
    $filter = $this->getMockBuilder('Drupal\socrata_filter\Plugin\Filter\FilterSocrata')
      ->disableOriginalConstructor()
      ->setMethods(NULL)
      ->getMock();
    $filtered_text = $filter->process($text, NULL);
    $this->assertEquals($filtered_text, $text);
  }

}
