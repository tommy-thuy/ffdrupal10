<?php

namespace Drupal\Tests\lupus_ce_renderer\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test Lupus Custom Elements renderer features.
 *
 * @group lupus_ce_renderer
 */
class LupusCeRendererAccessTest extends BrowserTestBase {

  /**
   * The node to use for testing.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * Path to created node.
   *
   * @var string
   */
  protected $nodePath;

  /**
   * A user with administrative permissions.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'custom_elements',
    'lupus_ce_renderer',
    'layout_builder',
  ];

  /**
   * {@inheritdoc}
   */
  protected $strictConfigSchema = FALSE;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create Basic page node type.
    $this->drupalCreateContentType(['type' => 'page', 'name' => 'Basic page']);
    $this->node = $this->drupalCreateNode();
    $this->nodePath = 'node/' . $this->node->id();

    // Create admin user.
    $this->adminUser = $this->drupalCreateUser([
      'access administration pages',
      'access content',
    ]);
  }

  /**
   * Tests if node page is accessible.
   */
  public function testNodePage() {

    $this->drupalLogin($this->adminUser);
    $this->drupalGet('node', ['query' => ['_format' => 'custom_elements']]);
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Tests if created node is accessible.
   */
  public function testExistingPage() {
    $this->drupalGet($this->nodePath, ['query' => ['_format' => 'custom_elements']]);
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Tests un-existing page access.
   */
  public function test404Page() {
    $this->drupalGet('i-dont-exist', ['query' => ['_format' => 'custom_elements']]);
    $this->assertSession()->statusCodeEquals(404);
  }

  /**
   * Tests forbidden page access.
   */
  public function test403Page() {
    $unpublishedNode = $this->drupalCreateNode(['status' => FALSE]);
    $this->drupalGet('node/' . $unpublishedNode->id(), ['query' => ['_format' => 'custom_elements']]);
    $this->assertSession()->statusCodeEquals(403);
  }

}
