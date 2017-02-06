<?php

namespace Drupal\Tests\socrata\Functional;

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;
use Drupal\socrata\Entity\Endpoint;
use Symfony\Component\Yaml\Yaml;

/**
 * Tests the Endpoint config entity form.
 *
 * @group socrata
 * @group endpoint
 * @see \Drupal\socrata\Form\EndpointForm
 */
class EndpointFormTest extends BrowserTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = ['socrata'];

  /**
   * Endpoint data.
   *
   * @var array
   */
  protected $data;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->data = Yaml::parse(file_get_contents(__DIR__ . '/../../config/data.yml'));
  }

  /**
   * Tests accessing Socrata form as a authenticated user without permission.
   */
  public function testAccessEndpointConfig() {
    // Create authenticated user.
    $this->drupalLogin($this->drupalCreateUser());
    $this->drupalGet(Url::fromRoute('entity.endpoint.collection'));
    $this->assertSession()->statusCodeEquals(403);

    // Create administrative user.
    $this->drupalLogin($this->drupalCreateUser(['administer socrata']));
    $this->drupalGet(Url::fromRoute('entity.endpoint.collection'));
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Tests accessing Socrata form as a authenticated user without permission.
   */
  public function testAddEndpoint() {
    $this->drupalLogin($this->drupalCreateUser(['administer socrata']));
    $this->drupalGet(Url::fromRoute('entity.endpoint.collection'));

    $edit = [];
    $this->drupalPostForm(Url::fromRoute('entity.endpoint.add_form'), $edit, t('Save'));
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Machine-readable name field is required.');
    $this->assertSession()->pageTextContains('Name field is required');
    $this->assertSession()->pageTextContains('URL field is required');

    $edit['label'] = $this->data['endpoints']['valid']['label'];
    $edit['id'] = $this->data['endpoints']['valid']['id'];
    $edit['url'] = str_replace('resource/', '', $this->data['endpoints']['valid']['url']);
    $this->drupalPostForm(Url::fromRoute('entity.endpoint.add_form'), $edit, t('Save'));
    $this->assertSession()->pageTextContains("does not point to a valid SODA2 resource");

    $edit['url'] = $this->data['endpoints']['valid']['url'];
    $this->drupalPostForm(Url::fromRoute('entity.endpoint.add_form'), $edit, t('Save'));
    $this->assertSession()->pageTextNotContains('does not point to a valid SODA2 resource');
    $this->assertSession()->pageTextNotContains('did not return a valid response');
    $this->assertSession()->pageTextContains("Saved the {$edit['label']} endpoint");
    $endpoint = Endpoint::load($edit['id']);
    $this->assertNotNull($endpoint);

    $this->drupalPostForm(Url::fromRoute('entity.endpoint.add_form'), $edit, t('Save'));
    $this->assertSession()->pageTextContains("The machine-readable name is already in use");

    $this->drupalGet(Url::fromRoute('entity.endpoint.edit_form', ['endpoint' => $edit['id']]));
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->elementAttributeContains('css', 'input#edit-id', 'disabled', 'disabled');

    $this->drupalPostForm(Url::fromRoute('entity.endpoint.delete_form', ['endpoint' => $edit['id']]), [], t('Delete'));
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains("{$edit['label']} has been deleted");
    $endpoint = Endpoint::load($edit['id']);
    $this->assertNull($endpoint);

  }

}
