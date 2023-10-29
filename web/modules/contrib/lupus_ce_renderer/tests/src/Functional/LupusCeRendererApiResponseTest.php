<?php

namespace Drupal\Tests\lupus_ce_renderer\Functional;

use Drupal\lupus_ce_renderer\CustomElementsRenderer;
use Drupal\Tests\BrowserTestBase;
use PHPUnit\Framework\Assert;

/**
 * Test Lupus Custom Elements renderer features.
 *
 * @group lupus_ce_renderer
 */
class LupusCeRendererApiResponseTest extends BrowserTestBase {

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
  protected string $nodePath;

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
   * Testing default content format.
   */
  public function testApiResponseData() {
    // Changing default content output format to markup.
    $settings['settings']['lupus_ce_renderer_default_format'] = (object) [
      'value' => CustomElementsRenderer::CONTENT_FORMAT_MARKUP,
      'required' => TRUE,
    ];
    $this->writeSettings($settings);
    $this->rebuildAll();
    $this->drupalGet($this->nodePath, ['query' => ['_format' => 'custom_elements']]);
    $page_content = json_decode($this->getTextContent(), TRUE);

    // Make sure meta is set.
    Assert::assertNotEmpty($page_content['metatags']['meta'], 'The metatags are not present in the response of the current page.');

    // Make sure there are breadcrumbs.
    Assert::assertNotEmpty($page_content['breadcrumbs'], 'The breadcrumbs are not present in the response of the current page.');

    // Make sure page_layout is set.
    Assert::assertNotEmpty($page_content['page_layout'], 'The layout_page is not present in the response of the current page.');
  }

}
