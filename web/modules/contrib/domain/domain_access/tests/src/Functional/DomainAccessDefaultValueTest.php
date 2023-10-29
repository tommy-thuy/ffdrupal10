<?php

namespace Drupal\Tests\domain_access\Functional;

use Drupal\Tests\domain\Functional\DomainTestBase;

/**
 * Tests the domain access handling of default field values.
 *
 * @see https://www.drupal.org/node/2779133
 *
 * @group domain_access
 */
class DomainAccessDefaultValueTest extends DomainTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['domain', 'domain_access', 'field', 'field_ui'];

  /**
   * Test the usage of DomainAccessManager::getDefaultValue().
   */
  public function testDomainAccessDefaultValue() {
    $admin_user = $this->drupalCreateUser([
      'bypass node access',
      'administer content types',
      'administer node fields',
      'administer node display',
      'administer domains',
      'publish to any domain',
    ]);
    $this->drupalLogin($admin_user);

    // Create 5 domains.
    $this->domainCreateTestDomains(5);

    // Visit the article field display administration page.
    $this->drupalGet('node/add/article');
    $this->assertSession()->statusCodeEquals(200);

    // Check the new field exists on the page.
    $this->assertSession()->pageTextContains('Domain Access');
    $this->assertSession()->responseContains('name="field_domain_access[example_com]" value="example_com" checked="checked"');
    // Check the all affiliates field.
    $this->assertSession()->pageTextContains('Send to all affiliates');
    $this->assertSession()->responseNotContains('name="field_domain_all_affiliates[value]" value="1" checked="checked"');

    // Now save the node with the values set.
    $edit = [
      'title[0][value]' => 'Test node',
      'field_domain_access[example_com]' => 'example_com',
    ];
    $this->drupalGet('node/add/article');
    $this->submitForm($edit, 'Save');

    // Load the node.
    $node = \Drupal::entityTypeManager()->getStorage('node')->load(1);
    $this->assertNotNull($node, 'Article node created.');
    // Check that the values are set.
    $values = \Drupal::service('domain_access.manager')->getAccessValues($node);
    $this->assertCount(1, $values, 'Node saved with one domain record.');
    $allValue = \Drupal::service('domain_access.manager')->getAllValue($node);
    $this->assertEmpty($allValue, 'Not sent to all affiliates.');

    // Logout the admin user.
    $this->drupalLogout();

    // Create a limited value user.
    $test_user = $this->drupalCreateUser([
      'create article content',
      'edit any article content',
    ]);

    // Login and try to edit the created node.
    $this->drupalLogin($test_user);

    $this->drupalGet('node/1/edit');
    $this->assertSession()->statusCodeEquals(200);

    // Now save the node with the values set.
    $edit = [
      'title[0][value]' => 'Test node update',
    ];
    $this->drupalGet('node/1/edit');
    $this->submitForm($edit, 'Save');

    // Load the node.
    $node = \Drupal::entityTypeManager()->getStorage('node')->load(1);
    $this->assertNotNull($node, 'Article node created.');
    // Check that the values are set.
    $values = \Drupal::service('domain_access.manager')->getAccessValues($node);
    $this->assertCount(1, $values, 'Node saved with one domain record.');
    $allValue = \Drupal::service('domain_access.manager')->getAllValue($node);
    $this->assertEmpty($allValue, 'Not sent to all affiliates.');

  }

}
