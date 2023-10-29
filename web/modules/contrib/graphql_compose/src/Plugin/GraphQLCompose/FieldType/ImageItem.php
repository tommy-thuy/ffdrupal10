<?php

declare(strict_types=1);

namespace Drupal\graphql_compose\Plugin\GraphQLCompose\FieldType;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Image\ImageFactory;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\file\FileInterface;
use Drupal\graphql_compose\Plugin\GraphQL\DataProducer\FieldProducerItemInterface;
use Drupal\graphql_compose\Plugin\GraphQL\DataProducer\FieldProducerTrait;
use Drupal\graphql_compose\Plugin\GraphQLCompose\GraphQLComposeFieldTypeBase;
use enshrined\svgSanitize\Sanitizer;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * {@inheritdoc}
 *
 * @GraphQLComposeFieldType(
 *   id = "image",
 *   type_sdl = "Image",
 * )
 */
class ImageItem extends GraphQLComposeFieldTypeBase implements FieldProducerItemInterface, ContainerFactoryPluginInterface {

  use FieldProducerTrait;

  /**
   * File URL generator service.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected FileUrlGeneratorInterface $fileUrlGenerator;

  /**
   * Drupal image factory.
   *
   * @var \Drupal\Core\Image\ImageFactory
   */
  protected ImageFactory $imageFactory;

  /**
   * Drupal renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected RendererInterface $renderer;

  /**
   * SVG sanitizer, provided by svg_image module.
   *
   * @var \enshrined\svgSanitize\Sanitizer
   */
  protected Sanitizer $sanitizer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create(
      $container,
      $configuration,
      $plugin_id,
      $plugin_definition
    );

    $instance->fileUrlGenerator = $container->get('file_url_generator');
    $instance->imageFactory = $container->get('image.factory');
    $instance->renderer = $container->get('renderer');

    if (class_exists('\enshrined\svgSanitize\Sanitizer')) {
      $instance->sanitizer = new Sanitizer();
    }

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveFieldItem(FieldItemInterface $item, array $context, RefinableCacheableDependencyInterface $metadata) {
    if (!$item->entity) {
      return NULL;
    }

    $access = $item->entity->access('view', NULL, TRUE);
    $metadata->addCacheableDependency($access);

    if (!$access->isAllowed()) {
      return NULL;
    }

    /** @var \Drupal\file\FileInterface $file */
    $file = $item->entity;

    $context = new RenderContext();
    $url = $this->renderer->executeInRenderContext($context, function () use ($file) {
      return $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
    });

    if (!$context->isEmpty()) {
      $metadata->addCacheableDependency($context->pop());
    }

    $metadata->addCacheableDependency($file);

    $width = $item->width ?? NULL;
    $height = $item->height ?? NULL;

    if (is_null($width) || is_null($height)) {
      $image = $this->imageFactory->get($file->getFileUri());
      if ($image->isValid()) {
        $width = $image->getWidth();
        $height = $image->getHeight();
      }
    }

    $fields = [
      'url' => $url,
      'width' => $width ?: 0,
      'height' => $height ?: 0,
      'alt' => $item->alt ?: NULL,
      'title' => $item->title ?: NULL,
      'size' => (int) $file->getSize(),
      'mime' => $file->getMimeType(),
    ];

    $config = $this->configFactory->get('graphql_compose.settings');
    if ($config->get('settings.svg_image')) {
      $fields['svg'] = $this->getSvgContent($file);
    }

    return $fields;
  }

  /**
   * Get SVG content.
   *
   * @param \Drupal\Core\File\FileInterface $file
   *   The file object.
   *
   * @return \Drupal\Component\Render\MarkupInterface|null
   *   The SVG content or NULL.
   */
  protected function getSvgContent(FileInterface $file): ?MarkupInterface {
    if (!isset($this->sanitizer)) {
      return NULL;
    }

    if ($file->getMimeType() !== 'image/svg+xml') {
      return NULL;
    }

    // Apply max filesize limit.
    $file_size = $file->getSize();
    $config = $this->configFactory->get('graphql_compose.settings');
    $svg_max = $config->get('settings.svg_filesize') ?: 100;
    if (!$file_size || $file_size > $svg_max * 1024) {
      return NULL;
    }

    if (!file_exists($file->getFileUri())) {
      return NULL;
    }

    $raw = file_get_contents($file->getFileUri()) ?: NULL;

    // SVG content cant be trusted,
    // Sanitize SVG content to prevent XSS attacks.
    $content = $this->sanitizer->sanitize($raw);
    if (!$content) {
      return NULL;
    }

    // Strip XML declaration and doctype.
    $content = preg_replace(['/<\?xml.*\?>/i', '/<!DOCTYPE((.|\n|\r)*?)">/i'], '', $content);
    $content = trim($content);

    return Markup::create($content);
  }

}
