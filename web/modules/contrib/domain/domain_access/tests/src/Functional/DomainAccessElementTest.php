<?php

namespace Drupal\Tests\domain_access\Functional;

use Drupal\Tests\domain\Functional\DomainTestBase;
use Drupal\domain_access\DomainAccessManagerInterface;

/**
 * Tests behavior for the domain access field element.
 *
 * @group domain_access
 */
class DomainAccessElementTest extends DomainTestBase {

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
    // Create a new content type.
    $this->createContentType(['type' => 'test']);
  }

  /**
   * Test runner.
   */
  public function testDomainAccessElement() {
    foreach (['article', 'test'] as $type) {
      // TODO: Something new is forcing this line for the 'test' type.
      domain_access_confirm_fields('node', $type);
      $this->runInstalledTest($type);
    }
  }

  /**
   * Basic test setup.
   */
  public function runInstalledTest($node_type) {
    $admin = $this->drupalCreateUser([
      'bypass node access',
      'administer content types',
      'administer node fields',
      'administer node display',
      'administer domains',
      'publish to any domain',
    ]);
    $this->drupalLogin($admin);

    $this->drupalGet('node/add/' . $node_type);
    $this->assertSession()->statusCodeEquals(200);

    // Set the title, so the node can be saved.
    $this->fillField('title[0][value]', 'Test node');

    // We expect to find 5 domain options. We set two as selected.
    $domains = \Drupal::entityTypeManager()->getStorage('domain')->loadMultiple();
    $ids = ['example_com', 'one_example_com', 'two_example_com'];
    foreach ($domains as $domain) {
      $locator = DomainAccessManagerInterface::DOMAIN_ACCESS_FIELD . '[' . $domain->id() . ']';
      $this->findField($locator);
      if (in_array($domain->id(), $ids)) {
        $this->checkField($locator);
      }
    }
    // Find the all affiliates field.
    $locator = DomainAccessManagerInterface::DOMAIN_ACCESS_ALL_FIELD . '[value]';
    $this->findField($locator);

    // Set all affiliates to TRUE.
    $this->checkField($locator);

    // Save the form.
    $this->pressButton('Save');
    $this->assertSession()->statusCodeEquals(200);

    // Get node data. Note that we create one new node for each test case.
    $storage = \Drupal::entityTypeManager()->getStorage('node');
    $nid = $node_type === 'article' ? 1 : 2;
    $node = $storage->load($nid);
    // Check that two values are set.
    $manager = \Drupal::service('domain_access.manager');
    $values = $manager->getAccessValues($node);
    $this->assertTrue(count($values) === 3, 'Node saved with three domain records.');
    $value = $manager->getAllValue($node);
    $this->assertTrue(intval($value) === 1, 'Node saved to all affiliates.');

    // Now login as a user with limited rights.
    $account = $this->drupalCreateUser([
      'create ' . $node_type . ' content',
      'edit any ' . $node_type . ' content',
      'publish to any assigned domain',
    ]);
    $ids = ['example_com', 'one_example_com'];
    $this->addDomainsToEntity('user', $account->id(), $ids, DomainAccessManagerInterface::DOMAIN_ACCESS_FIELD);
    $user_storage = \Drupal::entityTypeManager()->getStorage('user');
    $user = $user_storage->load($account->id());
    $values = $manager->getAccessValues($user);
    $this->assertTrue(count($values) === 2, 'User saved with two domain records.');
    $value = $manager->getAllValue($user);
    $this->assertTrue(intval($value) === 0, 'User not saved to all affiliates.');

    $this->drupalLogin($account);

    $this->drupalGet('node/' . $node->id() . '/edit');
    $this->assertSession()->statusCodeEquals(200);

    foreach ($domains as $domain) {
      $locator = DomainAccessManagerInterface::DOMAIN_ACCESS_FIELD . '[' . $domain->id() . ']';
      $this->findField($locator);
      if ($domain->id() === 'example_com') {
        $this->checkField($locator);
      }
      elseif ($domain->id() === 'one_example_com') {
        $this->uncheckField($locator);
      }

      else {
        $this->assertSession()->fieldNotExists($locator);
      }
    }

    $locator = DomainAccessManagerInterface::DOMAIN_ACCESS_ALL_FIELD . '[value]';
    $this->assertSession()->fieldNotExists($locator);

    // Save the form.
    $this->pressButton('Save');
    $this->assertSession()->statusCodeEquals(200);

    // Now, check the node.
    $storage->resetCache([$node->id()]);
    $node = $storage->load($node->id());
    // Check that two values are set.
    $values = $manager->getAccessValues($node);
    $this->assertTrue(count($values) === 2, 'Node saved with two domain records.');
    $value = $manager->getAllValue($node);
    $this->assertTrue(intval($value) === 1, 'Node saved to all affiliates.');
  }

}
