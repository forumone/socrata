<?php

namespace Drupal\Tests\socrata_views\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * SocrataDisplayExtender units tests.
 *
 * @group socrata_views
 */
class SocrataDisplayExtenderTest extends KernelTestBase {

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
  public static $modules = ['views', 'socrata_views'];

  /**
   * The module handler used in this test.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig(['views']);
    $this->moduleHandler = $this->container->get('module_handler');
    $this->moduleHandler->loadInclude('socrata_views', 'install');
    $this->moduleHandler->invoke('socrata_views', 'install');
  }

  /**
   * Test enabling the display extender on install.
   */
  public function testDisplayExtenderEnabled() {
    $config = $this->config('views.settings');
    $display_extenders = $config->get('display_extenders') ?: [];
    $this->assertContains('socrata_display_extender', $display_extenders);
  }

  /**
   * Test enabling the display extender on install.
   */
  public function testDisplayExtenderDisabled() {
    $this->moduleHandler->invoke('socrata_views', 'uninstall');
    $config = $this->config('views.settings');
    $display_extenders = $config->get('display_extenders') ?: [];
    $this->assertNotContains('socrata_display_extender', $display_extenders);
  }

}
