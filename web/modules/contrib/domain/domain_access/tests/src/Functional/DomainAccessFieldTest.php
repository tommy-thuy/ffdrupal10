<?php

namespace Drupal\Tests\domain_access\Functional;

use Drupal\node\Entity\NodeType;
use Drupal\Tests\domain\Functional\DomainTestBase;
use Drupal\domain_access\DomainAccessManagerInterface;

/**
 * Tests the domain access entity reference field type.
 *
 * @group domain_access
 */
class DomainAccessFieldTest extends DomainTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'domain',
    'domain_access',
    'field',
    'field_ui',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create 5 domains.
    $this->domainCreateTestDomains(5);
  }

  /**
   * Tests that the fields are accessed properly.
   */
  public function testDomainAccessFields() {
    $label = 'Send to all affiliates';

    // Test a user who can access all domain settings.
    $perms = ['create article content', 'publish to any domain'];
    $user1 = $this->drupalCreateUser($perms);
    $this->drupalLogin($user1);

    // Visit the article creation page.
    $this->drupalGet('node/add/article');
    $this->assertSession()->statusCodeEquals(200);

    // Check for the form options.
    $domains = \Drupal::entityTypeManager()->getStorage('domain')->loadMultiple();
    foreach ($domains as $domain) {
      $this->assertSession()->responseContains('>' . $domain->label() . '</label>');
    }
    $this->assertSession()->pageTextContains($label);

    // Test a user who can access some domain settings.
    $perms = ['create article content', 'publish to any assigned domain'];
    $user2 = $this->drupalCreateUser($perms);
    $active_domain = array_rand($domains, 1);
    $this->addDomainsToEntity('user', $user2->id(), $active_domain, DomainAccessManagerInterface::DOMAIN_ACCESS_FIELD);
    $this->drupalLogin($user2);

    // Visit the article creation page.
    $this->drupalGet('node/add/article');
    $this->assertSession()->statusCodeEquals(200);

    // Check for the form options.
    foreach ($domains as $domain) {
      if ($domain->id() === $active_domain) {
        $this->assertSession()->responseContains('>' . $domain->label() . '</label>');
      }
      else {
        $this->assertSession()->responseNotContains('>' . $domain->label() . '</label>');
      }
    }
    $this->assertSession()->pageTextNotContains($label);

    // Test a user who can access no domain settings.
    $user3 = $this->drupalCreateUser(['create article content']);
    $this->drupalLogin($user3);

    // Visit the article creation page.
    $this->drupalGet('node/add/article');
    $this->assertSession()->statusCodeEquals(200);

    // Check for the form options.
    foreach ($domains as $domain) {
      $this->assertSession()->responseNotContains('>' . $domain->label() . '</label>');
    }
    $this->assertSession()->pageTextNotContains($label);

    // Attempt saving the node.
    // The domain/domain affiliates fields are not accessible to this user.
    // The save will fail with an EntityStorageException until
    // https://www.drupal.org/node/2609252 is fixed.
    $edit = [];
    $edit['title[0][value]'] = $this->randomMachineName(8);
    $edit['body[0][value]'] = $this->randomMachineName(16);
    $this->drupalGet('node/add/article');
    $this->submitForm($edit, 'Save');

    // Check that the node exists in the database.
    $node = $this->drupalGetNodeByTitle($edit['title[0][value]']);
    $this->assertNotNull($node, 'Node found in database.');

    // Test a user who can assign users to domains.
    $perms = ['administer users', 'assign editors to any domain'];
    $user4 = $this->drupalCreateUser($perms);
    $this->drupalLogin($user4);

    // Visit the account creation page.
    $this->drupalGet('admin/people/create');
    $this->assertSession()->statusCodeEquals(200);

    // Check for the form options.
    foreach ($domains as $domain) {
      $this->assertSession()->responseContains('>' . $domain->label() . '</label>');
    }

    // Test a user who can assign users to some domains.
    $perms = ['administer users', 'assign domain editors'];
    $user5 = $this->drupalCreateUser($perms);
    $active_domain = array_rand($domains, 1);
    $this->addDomainsToEntity('user', $user5->id(), $active_domain, DomainAccessManagerInterface::DOMAIN_ACCESS_FIELD);
    $this->drupalLogin($user5);

    // Visit the account creation page.
    $this->drupalGet('admin/people/create');
    $this->assertSession()->statusCodeEquals(200);

    // Check for the form options.
    foreach ($domains as $domain) {
      if ($domain->id() === $active_domain) {
        $this->assertSession()->responseContains('>' . $domain->label() . '</label>');
      }
      else {
        $this->assertSession()->responseNotContains('>' . $domain->label() . '</label>');
      }
    }

    // Test a user who can access no domain settings.
    $user6 = $this->drupalCreateUser(['administer users']);
    $this->drupalLogin($user6);

    // Visit the account creation page.
    $this->drupalGet('admin/people/create');
    $this->assertSession()->statusCodeEquals(200);

    // Check for the form options.
    foreach ($domains as $domain) {
      $this->assertSession()->pageTextNotContains($domain->label());
    }

    // Test a user who can access all domain settings.
    $perms = ['bypass node access', 'publish to any domain'];
    $user7 = $this->drupalCreateUser($perms);
    $this->drupalLogin($user7);

    // Create a new content type and test that the fields are created.
    // Create a content type programmatically.
    $type = $this->drupalCreateContentType();
    $type_exists = (bool) NodeType::load($type->id());
    $this->assertTrue($type_exists, 'The new content type has been created in the database.');

    // The test is not passing to domain_access_node_type_insert() properly.
    domain_access_confirm_fields('node', $type->id());

    // Visit the article creation page.
    $this->drupalGet('node/add/' . $type->id());
    $this->assertSession()->statusCodeEquals(200);

    // Check for the form options.
    $domains = \Drupal::entityTypeManager()->getStorage('domain')->loadMultiple();
    foreach ($domains as $domain) {
      $this->assertSession()->responseContains('>' . $domain->label() . '</label>');
    }
    $this->assertSession()->pageTextContains($label);

    // Test user without access to affiliates field editing their user page.
    $user8 = $this->drupalCreateUser(['change own username']);
    $this->drupalLogin($user8);

    $user_edit_page = 'user/' . $user8->id() . '/edit';
    $this->drupalGet($user_edit_page);
    // Check for the form options.
    $domains = \Drupal::entityTypeManager()->getStorage('domain')->loadMultiple();
    foreach ($domains as $domain) {
      $this->assertSession()->responseNotContains('>' . $domain->label() . '</label>');
    }

    $this->assertSession()->pageTextNotContains($label);

    // Change own username.
    $edit = [];
    $edit['name'] = $this->randomMachineName();

    $this->drupalGet($user_edit_page);
    $this->submitForm($edit, 'Save');
  }

}
