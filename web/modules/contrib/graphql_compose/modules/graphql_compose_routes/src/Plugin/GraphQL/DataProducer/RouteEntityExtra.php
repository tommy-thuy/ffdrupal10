<?php

declare(strict_types=1);

namespace Drupal\graphql_compose_routes\Plugin\GraphQL\DataProducer;

use Drupal\Core\Entity\TranslatableInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\graphql\GraphQL\Buffers\EntityRevisionBuffer;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\Plugin\GraphQL\DataProducer\Routing\RouteEntity;
use Drupal\graphql_compose_routes\GraphQL\Buffers\EntityPreviewBuffer;
use GraphQL\Deferred;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Loads the entity associated with the current URL.
 *
 * @DataProducer(
 *   id = "route_entity_extra",
 *   name = @Translation("Load entity, preview or revision by url"),
 *   description = @Translation("The entity belonging to the current url."),
 *   produces = @ContextDefinition("entity",
 *     label = @Translation("Entity")
 *   ),
 *   consumes = {
 *     "url" = @ContextDefinition("any",
 *       label = @Translation("The URL")
 *     ),
 *     "language" = @ContextDefinition("string",
 *       label = @Translation("Language"),
 *       required = FALSE
 *     )
 *   }
 * )
 */
class RouteEntityExtra extends RouteEntity implements ContainerFactoryPluginInterface {

  /**
   * The entity revision buffer service.
   *
   * @var \Drupal\graphql\GraphQL\Buffers\EntityRevisionBuffer
   */
  protected EntityRevisionBuffer $entityRevisionBuffer;

  /**
   * The entity preview buffer service.
   *
   * @var \Drupal\graphql_compose_routes\GraphQL\Buffers\EntityPreviewBuffer
   */
  protected EntityPreviewBuffer $entityPreviewBuffer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create(
      $container,
      $configuration,
      $plugin_id,
      $plugin_definition,
    );

    $instance->entityRevisionBuffer = $container->get('graphql.buffer.entity_revision');
    $instance->entityPreviewBuffer = $container->get('graphql_compose_routes.buffer.entity_preview');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($url, ?string $language, FieldContext $context): ?Deferred {
    if ($url instanceof Url) {
      [, $type] = explode('.', $url->getRouteName());
      $parameters = $url->getRouteParameters();

      // Previews.
      if (array_key_exists($type . '_preview', $parameters)) {
        return $this->resolvePreview($type, $parameters, $language, $context);
      }

      // Revisions.
      if (array_key_exists($type . '_revision', $parameters)) {
        return $this->resolveRevision($type, $parameters, $language, $context);
      }
    }

    // Default GraphQL behavior.
    return parent::resolve($url, $language, $context);
  }

  /**
   * Resolve a preview entity.
   *
   * @param string $type
   *   The entity type.
   * @param array $parameters
   *   The URL parameters.
   * @param string|null $language
   *   The language code to get a translation of the entity.
   * @param \Drupal\graphql\GraphQL\Execution\FieldContext $context
   *   The GraphQL field context.
   *
   * @return \GraphQL\Deferred
   *   The deferred entity.
   */
  protected function resolvePreview(string $type, array $parameters, ?string $language, FieldContext $context): Deferred {
    $preview_id = $parameters[$type . '_preview'];
    $resolver = $this->entityPreviewBuffer->add($type, $preview_id);

    return new Deferred(function () use ($type, $resolver, $context, $language) {
      if (!$entity = $resolver()) {
        // If there is no entity with this id, add the list cache tags so that
        // the cache entry is purged whenever a new entity of this type is
        // saved.
        $type = $this->entityTypeManager->getDefinition($type);
        /** @var \Drupal\Core\Entity\EntityTypeInterface $type */
        $tags = $type->getListCacheTags();
        $context->addCacheTags($tags)->addCacheTags(['4xx-response']);
        return NULL;
      }

      // Get the correct translation.
      if (isset($language) && $language != $entity->language()->getId() && $entity instanceof TranslatableInterface) {
        $entity = $entity->getTranslation($language);
        $entity->addCacheContexts(["static:language:{$language}"]);
      }

      $access = $entity->access('view', NULL, TRUE);
      $context->addCacheableDependency($access);
      if ($access->isAllowed()) {
        $context->mergeCacheMaxAge(0);
        return $entity;
      }
      return NULL;
    });
  }

  /**
   * Resolve a preview revision.
   *
   * @param string $type
   *   The entity type.
   * @param array $parameters
   *   The URL parameters.
   * @param string|null $language
   *   The language code to get a translation of the entity.
   * @param \Drupal\graphql\GraphQL\Execution\FieldContext $context
   *   The GraphQL field context.
   *
   * @return \GraphQL\Deferred
   *   The deferred entity.
   */
  protected function resolveRevision(string $type, array $parameters, ?string $language, FieldContext $context): Deferred {
    $revision_id = $parameters[$type . '_revision'];
    $resolver = $this->entityRevisionBuffer->add($type, $revision_id);

    return new Deferred(function () use ($type, $resolver, $context, $language) {
      if (!$entity = $resolver()) {
        // If there is no entity with this id, add the list cache tags so that
        // the cache entry is purged whenever a new entity of this type is
        // saved.
        $type = $this->entityTypeManager->getDefinition($type);
        /** @var \Drupal\Core\Entity\EntityTypeInterface $type */
        $tags = $type->getListCacheTags();
        $context->addCacheTags($tags)->addCacheTags(['4xx-response']);
        return NULL;
      }

      // Get the correct translation.
      if (isset($language) && $language != $entity->language()->getId() && $entity instanceof TranslatableInterface) {
        $entity = $entity->getTranslation($language);
        $entity->addCacheContexts(["static:language:{$language}"]);
      }

      $access = $entity->access('view', NULL, TRUE);
      $context->addCacheableDependency($access);
      if ($access->isAllowed()) {
        return $entity;
      }
      return NULL;
    });
  }

}
