<?php

namespace Drupal\Tests\socrata_filter\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\socrata\Entity\Endpoint;
use Drupal\filter\FilterPluginCollection;
use Drupal\Core\Render\RenderContext;
use Symfony\Component\Yaml\Yaml;

/**
 * Endpoint units tests.
 *
 * @group socrata_filter
 */
class FilterSocrataTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['socrata_filter', 'socrata', 'filter'];

  /**
   * Filter interface.
   *
   * @var \Drupal\filter\Plugin\FilterInterface[]
   */
  protected $filter;

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

    $data = Yaml::parse(file_get_contents(__DIR__ . '/../../../../tests/config/data.yml'));

    $this->endpoint = new Endpoint($data['endpoints']['valid'], 'endpoint');
    $this->endpoint->save();

    $this->filters = $manager = $this->container->get('plugin.manager.filter');
    $bag = new FilterPluginCollection($manager, []);
    $this->filter = $bag->get('filter_socrata');
  }

  /**
   * Test the process method of the FilterSocrata class.
   */
  public function testProcessFilterSocrata() {

    $filter = $this->filter;

    $text = 'Donec rutrum congue leo eget malesuada';
    $filtered_text = $this->filter->process($text, NULL);
    $this->assertEquals($filtered_text, $text);

    $text = 'Donec rutrum congue [socrata source=fake_endpoint] leo eget malesuada';
    $filtered_text = $this->filter->process($text, NULL);
    $this->assertEquals($filtered_text->getProcessedText(), str_replace('[socrata source=fake_endpoint]', '', $text));

    $text = 'Donec rutrum congue [socrata source=endpoint] leo eget malesuada';
    /** @var \Drupal\Core\Render\RendererInterface $renderer */
    $renderer = \Drupal::service('renderer');
    $filtered_text = function ($input) use ($filter, $renderer) {
      return $renderer->executeInRenderContext(new RenderContext(), function () use ($input, $filter) {
        return $filter->process($input, NULL);
      });
    };
    $this->assertContains('socrata-embed', $filtered_text($text)->getProcessedText());
  }

}
