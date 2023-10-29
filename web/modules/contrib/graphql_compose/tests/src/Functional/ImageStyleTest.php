<?php

declare(strict_types=1);

namespace Drupal\Tests\graphql_compose\Functional;

use Drupal\Core\File\FileSystemInterface;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\node\NodeInterface;
use Drupal\Tests\image\Kernel\ImageFieldCreationTrait;

/**
 * Test image style fields and derivatives.
 *
 * @group graphql_compose
 */
class ImageStyleTest extends GraphQLComposeBrowserTestBase {

  use ImageFieldCreationTrait;

  /**
   * The test node.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected NodeInterface $node;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'graphql_compose_image_style',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create image style.
    $style = ImageStyle::create([
      'name' => 'test',
      'label' => 'Test',
      'effects' => [
        [
          'id' => 'image_resize',
          'data' => [
            'width' => 200,
            'height' => 200,
          ],
        ],
      ],
    ]);

    $style->save();

    $this->setEntityConfig('image_style', 'test', [
      'enabled' => TRUE,
    ]);

    /** @var \Drupal\Core\File\FileSystemInterface $file_system */
    $file_system = \Drupal::service('file_system');

    // Create a file.
    $file_uri = $file_system->copy(
      'core/misc/druplicon.png',
      'public://druplicon.png',
      FileSystemInterface::EXISTS_REPLACE
    );

    $file = File::create();
    $file->setFileUri($file_uri);
    $file->setFilename($file_system->basename($file->getFileUri()));
    $file->setPermanent();
    $file->save();

    // Warm up the image style.
    $derivative_uri = $style->buildUri($file_uri);
    $style->createDerivative($file_uri, $derivative_uri);

    // Create the node.
    $this->createContentType([
      'type' => 'test',
      'name' => 'Test node type',
    ]);

    $this->node = $this->createNode([
      'type' => 'test',
      'title' => 'Test',
      'status' => 1,
    ]);

    $this->createImageField('field_image', 'test');

    $this->node = $this->node->load($this->node->id());

    $this->node->set('field_image', [
      'target_id' => $file->id(),
      'alt' => 'Test',
      'title' => 'Test',
    ])->save();

    $this->setEntityConfig('node', 'test', [
      'enabled' => TRUE,
      'query_load_enabled' => TRUE,
    ]);

    $this->setFieldConfig('node', 'test', 'field_image', [
      'enabled' => TRUE,
    ]);
  }

  /**
   * Test load image style variations.
   *
   * Note: This can fail the first run.
   *
   * Maybe image style gen is slow.
   * Note sure, don't mind.
   * Works on CI.
   */
  public function testImageStyleVariations(): void {

    $query = <<<GQL
      query {
        nodeTest(id: "{$this->node->uuid()}") {
          id
          image {
            url
            variations(styles: [TEST]) {
              url
              width
              height
            }
          }
        }
      }
    GQL;

    $content = $this->executeQuery($query);

    $image = $content['data']['nodeTest']['image'];

    $this->assertStringContainsString('styles/test/public', $image['variations'][0]['url']);

    $this->assertEquals(200, $image['variations'][0]['width']);
    $this->assertEquals(200, $image['variations'][0]['height']);
  }

}
