<?php

namespace Drupal\Tests\socrata\Functional;

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;
use Drupal\socrata\Entity\Endpoint;

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

    $endpoint_label = 'Endpoint';
    $url = 'https://data.seattle.gov/resource/rn6u-vkuv.json';

    $edit = [];
    $this->drupalPostForm(Url::fromRoute('entity.endpoint.add_form'), $edit, t('Save'));
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Machine-readable name field is required.');
    $this->assertSession()->pageTextContains('Name field is required');
    $this->assertSession()->pageTextContains('URL field is required');

    $edit['label'] = $endpoint_label;
    $edit['id'] = strtolower($endpoint_label);
    $edit['url'] = str_replace('resource/', '', $url);
    $this->drupalPostForm(Url::fromRoute('entity.endpoint.add_form'), $edit, t('Save'));
    $this->assertSession()->pageTextContains("does not point to a valid SODA2 resource");

    $edit['url'] = $url;
    $this->drupalPostForm(Url::fromRoute('entity.endpoint.add_form'), $edit, t('Save'));
    $this->assertSession()->pageTextNotContains('does not point to a valid SODA2 resource');
    $this->assertSession()->pageTextNotContains('did not return a valid response');
    $this->assertSession()->pageTextContains("Saved the $endpoint_label endpoint");
    $endpoint = Endpoint::load($edit['id']);
    $this->assertNotNull($endpoint);

    $this->drupalPostForm(Url::fromRoute('entity.endpoint.add_form'), $edit, t('Save'));
    $this->assertSession()->pageTextContains("The machine-readable name is already in use");

    $this->drupalGet(Url::fromRoute('entity.endpoint.edit_form', ['endpoint' => $edit['id']]));
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->elementAttributeContains('css', 'input#edit-id', 'disabled', 'disabled');

    $this->drupalPostForm(Url::fromRoute('entity.endpoint.delete_form', ['endpoint' => $edit['id']]), [], t('Delete'));
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains("$endpoint_label has been deleted");
    $endpoint = Endpoint::load($edit['id']);
    $this->assertNull($endpoint);

  }

}
