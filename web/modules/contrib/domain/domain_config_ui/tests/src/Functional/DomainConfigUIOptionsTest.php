<?php

namespace Drupal\Tests\domain_config_ui\Functional;

use Drupal\domain\DomainInterface;
use Drupal\Tests\domain_config\Functional\DomainConfigTestBase;
use Drupal\Tests\domain_config_ui\Traits\DomainConfigUITestTrait;

/**
 * Tests granular permissions for the domain config user interface.
 *
 * @group domain_config_ui
 */
class DomainConfigUIOptionsTest extends DomainConfigTestBase {

  use DomainConfigUITestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'domain_config_ui',
  ];

  /**
   * {@inheritDoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->createAdminUser();
    $this->createLimitedUser();
    $this->createLanguageUser();

    $this->domainCreateTestDomains(5);
    // Assign the adminUser and languageUser to some domains.
    $domains = ['example_com', 'one_example_com'];
    $this->addDomainsToEntity('user', $this->limitedUser->id(),
      $domains, DomainInterface::DOMAIN_ADMIN_FIELD);
    $domains = ['two_example_com', 'three_example_com'];
    $this->addDomainsToEntity('user', $this->languageUser->id(),
      $domains, DomainInterface::DOMAIN_ADMIN_FIELD);
  }

  /**
   * Tests access the the settings form.
   */
  public function testFormOptions() {
    $this->drupalLogin($this->adminUser);
    $path = '/admin/config/domain/config-ui';
    $path2 = '/admin/config/system/site-information';

    // Visit the domain config ui administration page.
    $this->drupalGet($path);
    $this->assertSession()->statusCodeEquals(200);

    // Visit the site information page.
    $this->drupalGet($path2);
    $this->assertSession()->statusCodeEquals(200);
    $this->findField('domain');
    $this->findField('language');

    // We expect to find five domain options.
    $domains = \Drupal::entityTypeManager()->getStorage('domain')->loadMultiple();
    foreach ($domains as $domain) {
      $string = 'value="' . $domain->id() . '"';
      $this->assertSession()->responseContains($string);
    }
    // We expect to find 'All Domains'.
    $this->assertSession()->responseContains('All Domains</option>');

    // We expect to find two language options.
    $languages = ['en', 'es'];
    foreach ($languages as $langcode) {
      $string = 'value="' . $langcode . '"';
      $this->assertSession()->responseContains($string);
    }

    // Now test the editorUser.
    $this->drupalLogin($this->limitedUser);

    // Visit the domain config ui administration page.
    $this->drupalGet($path);
    $this->assertSession()->statusCodeEquals(403);

    // Visit the site information page.
    $this->drupalGet($path2);
    $this->assertSession()->statusCodeEquals(200);
    $this->findField('domain');
    $this->findNoField('language');

    // We expect to find two domain options.
    foreach ($domains as $domain) {
      $string = 'value="' . $domain->id() . '"';
      if (in_array($domain->id(), ['example_com', 'one_example_com'], TRUE)) {
        $this->assertSession()->responseContains($string);
      }
      else {
        $this->assertSession()->responseNotContains($string);
      }
    }

    // We expect to find 'All Domains'.
    $this->assertSession()->responseContains('All Domains</option>');

    // Now test the languageUser.
    $this->drupalLogin($this->languageUser);

    // Visit the domain config ui administration page.
    $this->drupalGet($path);
    $this->assertSession()->statusCodeEquals(403);

    // Visit the site information page.
    $this->drupalGet($path2);
    $this->assertSession()->statusCodeEquals(200);
    $this->findField('domain');
    $this->findField('language');

    // We expect to find two domain options.
    foreach ($domains as $domain) {
      $string = 'value="' . $domain->id() . '"';
      if (in_array($domain->id(), ['two_example_com', 'three_example_com'], TRUE)) {
        $this->assertSession()->responseContains($string);
      }
      else {
        $this->assertSession()->responseNotContains($string);
      }
    }

    // We do not expect to find 'All Domains'.
    $this->assertSession()->responseNotContains('All Domains</option>');

    // We expect to find two language options.
    $languages = ['en', 'es'];
    foreach ($languages as $langcode) {
      $string = 'value="' . $langcode . '"';
      $this->assertSession()->responseContains($string);
    }
  }

}
