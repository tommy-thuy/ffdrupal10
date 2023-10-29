<?php

namespace Drupal\Tests\domain_access\Functional;

use Drupal\Tests\domain\Functional\DomainTestBase;
use Drupal\domain_access\DomainAccessManagerInterface;

/**
 * Tests the domain access entity reference field type.
 *
 * @group domain_access
 */
class DomainAccessAllAffiliatesTest extends DomainTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['domain', 'domain_access', 'field', 'field_ui'];

  /**
   * Tests that the module installed its field correctly.
   */
  public function testDomainAccessAllField() {
    $label = 'Send to all affiliates';
    $admin_user = $this->drupalCreateUser([
      'administer content types',
      'administer node fields',
      'administer node display',
      'administer domains',
    ]);
    $this->drupalLogin($admin_user);

    // Visit the article field administration page.
    $this->drupalGet('admin/structure/types/manage/article/fields');
    $this->assertSession()->statusCodeEquals(200);

    // Check for the field.
    $this->assertSession()->pageTextContains($label);

    // Visit the article field display administration page.
    $this->drupalGet('admin/structure/types/manage/article/display');
    $this->assertSession()->statusCodeEquals(200);

    // Check for the field.
    $this->assertSession()->pageTextContains($label);
  }

  /**
   * Tests the storage of the domain access field.
   */
  public function testDomainAccessAllFieldStorage() {
    $label = 'Send to all affiliates';
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
    $this->assertSession()->pageTextContains($label);

    // We expect to find 5 domain options.
    $one = $two = NULL;
    $domains = \Drupal::entityTypeManager()->getStorage('domain')->loadMultiple();
    foreach ($domains as $domain) {
      $string = 'value="' . $domain->id() . '"';
      $this->assertSession()->responseContains($string);
      if (!isset($one)) {
        $one = $domain->id();
        continue;
      }
      if (!isset($two)) {
        $two = $domain->id();
      }
    }

    // Try to post a node, assigned to the first two domains.
    $edit['title[0][value]'] = 'Test node';
    $edit["field_domain_access[{$one}]"] = TRUE;
    $edit["field_domain_access[{$two}]"] = TRUE;
    $edit["field_domain_all_affiliates[value]"] = 1;
    $this->drupalGet('node/add/article');
    $this->submitForm($edit, 'Save');
    $this->assertSession()->statusCodeEquals(200);
    $node = \Drupal::entityTypeManager()->getStorage('node')->load(1);
    // Check that two values are set.
    $values = \Drupal::service('domain_access.manager')->getAccessValues($node);
    $this->assertCount(2, $values, 'Node saved with two domain records.');
    // Check that all affiliates is set.
    $this->assertNotEmpty($node->get(DomainAccessManagerInterface::DOMAIN_ACCESS_ALL_FIELD)->value, 'Node assigned to all affiliates.');
  }

}
