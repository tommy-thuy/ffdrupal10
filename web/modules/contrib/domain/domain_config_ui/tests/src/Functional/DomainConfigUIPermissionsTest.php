<?php

namespace Drupal\Tests\domain_config_ui\Functional;

use Drupal\Tests\domain_config\Functional\DomainConfigTestBase;
use Drupal\Tests\domain_config_ui\Traits\DomainConfigUITestTrait;

/**
 * Tests the domain config user interface.
 *
 * @group domain_config_ui
 */
class DomainConfigUIPermissionsTest extends DomainConfigTestBase {

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
    $this->createEditorUser();

    $this->domainCreateTestDomains(5);
  }

  /**
   * Tests access the the settings form.
   */
  public function testSettingsAccess() {
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

    $this->drupalLogin($this->editorUser);

    // Visit the domain config ui administration page.
    $this->drupalGet($path);
    $this->assertSession()->statusCodeEquals(403);

    // Visit the site information page.
    $this->drupalGet($path2);
    $this->assertSession()->statusCodeEquals(200);
    $this->findNoField('domain');
  }

}
