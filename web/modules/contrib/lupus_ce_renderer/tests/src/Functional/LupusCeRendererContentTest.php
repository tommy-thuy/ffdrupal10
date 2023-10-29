<?php

namespace Drupal\Tests\lupus_ce_renderer\Functional;

use Drupal\lupus_ce_renderer\CustomElementsRenderer;
use Drupal\Tests\BrowserTestBase;

/**
 * Test Lupus Custom Elements renderer features.
 *
 * @group lupus_ce_renderer
 */
class LupusCeRendererContentTest extends BrowserTestBase {

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
   * Tests content format passed by GET parameter.
   */
  public function testPassedContentFormat() {
    $this->drupalGet($this->nodePath, [
      'query' => [
        '_format' => 'custom_elements',
        '_content_format' => CustomElementsRenderer::CONTENT_FORMAT_MARKUP,
      ],
    ]);
    $this->assertSession()->responseContains('"content_format":"markup"');

    $this->drupalGet($this->nodePath, [
      'query' => [
        '_format' => 'custom_elements',
        '_content_format' => CustomElementsRenderer::CONTENT_FORMAT_JSON,
      ],
    ]);
    $this->assertSession()->responseContains('"content_format":"json"');
  }

  /**
   * Testing lupus_ce_renderer_enable settings parameter.
   */
  public function testRendererEnable() {
    // Setting lupus_ce_renderer_enable settings parameter.
    $settings['settings']['lupus_ce_renderer_enable'] = (object) [
      'value' => TRUE,
      'required' => TRUE,
    ];
    $this->writeSettings($settings);
    // Getting the node without passing _format parameter.
    $this->drupalGet($this->nodePath);
    $this->assertSession()->responseHeaderContains('Content-Type', 'application/json');
  }

  /**
   * Testing lupus_ce_renderer_enable settings parameter with _format=html.
   */
  public function testRendererEnableOverrideAttempt() {
    // Setting lupus_ce_renderer_enable settings parameter.
    $settings['settings']['lupus_ce_renderer_enable'] = (object) [
      'value' => TRUE,
      'required' => TRUE,
    ];
    $this->writeSettings($settings);

    // Overriding settings parameter is not allowed.
    $this->drupalGet($this->nodePath, ['query' => ['_format' => 'html']]);
    $this->assertSession()->statusCodeEquals(406);
  }

  /**
   * Testing default content format.
   */
  public function testDefaultContentFormat() {
    // Changing default content output format to markup.
    $settings['settings']['lupus_ce_renderer_default_format'] = (object) [
      'value' => CustomElementsRenderer::CONTENT_FORMAT_MARKUP,
      'required' => TRUE,
    ];
    $this->writeSettings($settings);
    $this->rebuildAll();
    $this->drupalGet($this->nodePath, ['query' => ['_format' => 'custom_elements']]);
    $this->assertSession()->responseContains('"content_format":"markup"');

    // Changing default content output format to json.
    $settings['settings']['lupus_ce_renderer_default_format'] = (object) [
      'value' => CustomElementsRenderer::CONTENT_FORMAT_JSON,
      'required' => TRUE,
    ];
    $this->writeSettings($settings);
    $this->rebuildAll();

    $this->drupalGet($this->nodePath, ['query' => ['_format' => 'custom_elements']]);
    $this->assertSession()->responseContains('"content_format":"json"');

    // Parameter _content_format must override lupus_ce_renderer_default_format.
    $this->drupalGet($this->nodePath, [
      'query' => [
        '_format' => 'custom_elements',
        '_content_format' => CustomElementsRenderer::CONTENT_FORMAT_MARKUP,
      ],
    ]);
    $this->assertSession()->responseContains('"content_format":"markup"');
  }

}
