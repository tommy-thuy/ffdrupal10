<?php

declare(strict_types=1);

namespace Drupal\graphql_compose_edges\Plugin\GraphQL\DataProducer;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\graphql_compose_edges\EntityConnection;
use Drupal\graphql_compose_edges\EntityTypePluginQueryHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Queries entities on the platform.
 *
 * @DataProducer(
 *   id = "graphql_compose_edges_entity_type",
 *   name = @Translation("Query a list of entity type"),
 *   description = @Translation("Loads the entity type entities."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("EntityConnection")
 *   ),
 *   consumes = {
 *     "first" = @ContextDefinition("integer",
 *       label = @Translation("First"),
 *       required = FALSE
 *     ),
 *     "after" = @ContextDefinition("string",
 *       label = @Translation("After"),
 *       required = FALSE
 *     ),
 *     "last" = @ContextDefinition("integer",
 *       label = @Translation("Last"),
 *       required = FALSE
 *     ),
 *     "before" = @ContextDefinition("string",
 *       label = @Translation("Before"),
 *       required = FALSE
 *     ),
 *     "reverse" = @ContextDefinition("boolean",
 *       label = @Translation("Reverse"),
 *       required = FALSE,
 *     ),
 *     "sortKey" = @ContextDefinition("string",
 *       label = @Translation("Sort key"),
 *       required = FALSE
 *     ),
 *     "langcode" = @ContextDefinition("string",
 *       label = @Translation("Language code"),
 *       required = FALSE
 *     ),
 *   },
 *   deriver = "Drupal\graphql_compose_edges\Plugin\Derivative\QueryEntityTypePluginTypeDeriver"
 * )
 */
class EntityTypePluginEdge extends DataProducerPluginBase implements ContainerFactoryPluginInterface {


  /**
   * Drupal language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected LanguageManagerInterface $languageManager;

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected AccountProxyInterface $currentUser;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );

    $instance->languageManager = $container->get('language_manager');
    $instance->currentUser = $container->get('current_user');

    return $instance;
  }

  /**
   * Resolves the request to the requested values.
   *
   * @param int|null $first
   *   Fetch the first X results.
   * @param string|null $after
   *   Cursor to fetch results after.
   * @param int|null $last
   *   Fetch the last X results.
   * @param string|null $before
   *   Cursor to fetch results before.
   * @param bool|null $reverse
   *   Reverses the order of the data.
   * @param string|null $sortKey
   *   Key to sort by.
   * @param string|null $langcode
   *   Language code to filter with.
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
   *   Cacheability metadata for this request.
   *
   * @return \Drupal\graphql_compose\GraphQL\ConnectionInterface
   *   An entity connection with results and data about the paginated results.
   */
  public function resolve(?int $first, ?string $after, ?int $last, ?string $before, ?bool $reverse, ?string $sortKey, ?string $langcode, RefinableCacheableDependencyInterface $metadata) {

    [$entityType, $entityBundle] = explode(':', $this->getDerivativeId());

    $langcode = $langcode ?: $this->languageManager->getCurrentLanguage()->getId();

    $query_helper = new EntityTypePluginQueryHelper(
      $sortKey,
      $langcode,
      $entityType,
      $entityBundle,
    );

    $connection = new EntityConnection($query_helper);
    $connection->setPagination($first, $after, $last, $before, $reverse);
    $connection->setAccessAccount($this->currentUser);
    $connection->setCacheContext($metadata);

    return $connection;
  }

}
