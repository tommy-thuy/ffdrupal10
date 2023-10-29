<?php

declare(strict_types=1);

namespace Drupal\graphql_compose\Plugin\GraphQLCompose\SchemaType;

use Drupal\graphql_compose\Plugin\GraphQLCompose\GraphQLComposeSchemaTypeBase;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * {@inheritdoc}
 *
 * @GraphQLComposeSchemaType(
 *   id = "Image",
 * )
 */
class ImageType extends GraphQLComposeSchemaTypeBase {

  /**
   * {@inheritdoc}
   */
  public function getTypes(): array {
    $types = [];

    $types[] = new ObjectType([
      'name' => $this->getPluginId(),
      'description' => (string) $this->t('A image object to represent an managed file.'),
      'fields' => function () {
        $fields = [
          'url' => [
            'type' => Type::nonNull(Type::string()),
            'description' => (string) $this->t('The URL of the image.'),
          ],
          'width' => [
            'type' => Type::nonNull(Type::int()),
            'description' => (string) $this->t('The width of the image.'),
          ],
          'height' => [
            'type' => Type::nonNull(Type::int()),
            'description' => (string) $this->t('The height of the image.'),
          ],
          'alt' => [
            'type' => Type::string(),
            'description' => (string) $this->t('The alt text of the image.'),
          ],
          'title' => [
            'type' => Type::string(),
            'description' => (string) $this->t('The title text of the image.'),
          ],
          'size' => [
            'type' => Type::nonNull(Type::int()),
            'description' => (string) $this->t('The size of the image in bytes.'),
          ],
          'mime' => [
            'type' => Type::string(),
            'description' => (string) $this->t('The mime type of the image.'),
          ],
        ];

        // Add SVG support via the SVG Image module.
        $config = $this->configFactory->get('graphql_compose.settings');
        if ($config->get('settings.svg_image')) {
          $svg_max = (int) $config->get('settings.svg_filesize') ?: 100;

          $fields['svg'] = [
            'type' => Type::string(),
            'description' => (string) $this->t('Contents of the image, if the mime is `image/svg+xml` and size <= `@size`.', [
              '@size' => format_size($svg_max * 1024),
            ]),
          ];
        }

        return $fields;
      },
    ]);

    return $types;
  }

}
