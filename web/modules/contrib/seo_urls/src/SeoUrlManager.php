<?php

namespace Drupal\seo_urls;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\seo_urls\Entity\SeoUrlInterface;
use Drupal\seo_urls\Event\ClearPathPrefixEvent;
use Drupal\seo_urls\Event\SetAllowedEntityTypesEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * The default SEO Url manager implementation.
 */
class SeoUrlManager implements SeoUrlManagerInterface {

  protected const ALLOWED_ENTITY_TYPES = ['node', 'taxonomy_term', 'user'];

  /**
   * The cache key to use when caching paths.
   *
   * @var string
   *
   * @todo Implement caching on the same/similar way as AliasManager did.
   */
  protected $cacheKey;

  /**
   * Whether the cache needs to be written.
   *
   * @var bool
   *
   * @todo Implement caching on the same/similar way as AliasManager did.
   */
  protected $cacheNeedsWriting = FALSE;

  /**
   * Holds the map of path lookups per language.
   *
   * @var array
   */
  protected $lookupMap = [];

  /**
   * Holds an array of aliases for which no path was found.
   *
   * @var array
   */
  protected $noPath = [];

  /**
   * Holds an array of paths that have no alias.
   *
   * @var array
   */
  protected $noAlias = [];

  /**
   * Whether preloaded path lookups has already been loaded.
   *
   * @var array
   *
   * @todo Implement caching on the same/similar way as AliasManager did.
   */
  protected $langcodePreloaded = [];

  /**
   * Holds an array of previously looked up paths for the current request path.
   *
   * This will only get populated if a cache key has been set, which for example
   * happens if the alias manager is used in the context of a request.
   *
   * @var array|false
   *
   * @todo Implement caching on the same/similar way as AliasManager did.
   */
  protected $preloadedPathLookups = FALSE;

  /**
   * The langcode of the current language.
   *
   * @var string
   */
  protected string $currentLangcode;

  /**
   * An interface for entity type managers.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The alias manager.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected AliasManagerInterface $aliasManager;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected LanguageManagerInterface $languageManager;

  /**
   * Cache backend.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected ConfigFactory $configFactory;

  /**
   * Event dispatcher service.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected EventDispatcherInterface $eventDispatcher;

  /**
   * Constructs an SeoUrlManager.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   An interface for entity type managers.
   * @param \Drupal\path_alias\AliasManagerInterface $aliasManager
   *   The alias manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   Cache backend.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   Event dispatcher service.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    AliasManagerInterface $aliasManager,
    LanguageManagerInterface $languageManager,
    ConfigFactory $configFactory,
    EventDispatcherInterface $eventDispatcher
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->aliasManager = $aliasManager;
    $this->languageManager = $languageManager;
    $this->configFactory = $configFactory;
    $this->eventDispatcher = $eventDispatcher;
    $this->currentLangcode = $this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_URL)->getId();
  }

  /**
   * {@inheritDoc}
   */
  public function clearPathPrefix(string $path): string {
    // Remove url prefix if it exists.
    if ($url_prefixes = $this->configFactory->get('language.negotiation')->get('url.prefixes')) {
      $path = preg_replace('/^\/(' . implode('|', $url_prefixes) . ')\//', '/', $path);
    }
    // Allow to remove custom prefixes from the path.
    /** @var \Drupal\seo_urls\Event\ClearPathPrefixEvent $event */
    $event = $this->eventDispatcher->dispatch(new ClearPathPrefixEvent($path), ClearPathPrefixEvent::EVENT_NAME);
    return $event->getPath();
  }

  /**
   * {@inheritDoc}
   */
  public function getAllowedEntityTypes(): array {
    /** @var \Drupal\seo_urls\Event\SetAllowedEntityTypesEvent $event */
    $event = $this->eventDispatcher->dispatch(new SetAllowedEntityTypesEvent(self::ALLOWED_ENTITY_TYPES), SetAllowedEntityTypesEvent::EVENT_NAME);
    return $event->getEntityTypes();
  }

  /**
   * {@inheritDoc}
   */
  public function isSeoUrl(string $path, ?string $langcode = NULL): bool {
    // Remove prefixed from the path.
    $path = $this->clearPathPrefix($path);

    // If no language is explicitly specified we default to the current URL
    // language. If we used a language different from the one conveyed by the
    // requested URL, we might end up being unable to check if there is a path
    // alias matching the URL path.
    $langcode = $langcode ?: $this->currentLangcode;

    // If we already know that there are no paths for this alias simply return.
    if (empty($path) || !empty($this->noPath[$langcode][$path])) {
      return FALSE;
    }

    // Look for the alias within the cached map.
    if (isset($this->lookupMap[$langcode][$path])) {
      return TRUE;
    }

    // Look for path in storage.
    if ($entity = $this->getSeoUrlEntity($path)) {
      $this->lookupMap[$langcode][$path] = $entity->getCanonicalUri();
      return TRUE;
    }

    // We can't record anything into $this->lookupMap because we didn't find any
    // paths for this alias. Thus cache to $this->noPath.
    $this->noPath[$langcode][$path] = TRUE;

    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function isCanonicalUrl(string $path, ?string $langcode = NULL): bool {
    // Remove prefixed from the path.
    $path = $this->clearPathPrefix($path);

    // If no language is explicitly specified we default to the current URL
    // language. If we used a language different from the one conveyed by the
    // requested URL, we might end up being unable to check if there is a path
    // alias matching the URL path.
    $langcode = $langcode ?: $this->currentLangcode;

    // If we already know that there are no paths for this alias simply return.
    if (empty($path) || !empty($this->noAlias[$langcode][$path])) {
      return FALSE;
    }

    // Look for the alias within the cached map.
    if (isset($this->lookupMap[$langcode][$path])) {
      return TRUE;
    }

    // Look for path in storage.
    if ($entity = $this->getSeoUrlEntity($path, SeoUrlInterface::CANONICAL_URL_FIELD)) {
      $this->lookupMap[$langcode][$path] = $entity->getSeoUri();
      return TRUE;
    }

    // We can't record anything into $this->lookupMap because we didn't find any
    // paths for this alias. Thus cache to $this->noAlias.
    $this->noAlias[$langcode][$path] = TRUE;

    return FALSE;
  }

  /**
   * {@inheritDoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getSeoUrlEntity(string $path, string $field_name = SeoUrlInterface::SEO_URL_FIELD, ?string $langcode = NULL): ?SeoUrlInterface {
    $entity_storage = $this->entityTypeManager->getStorage(SeoUrlInterface::ENTITY_TYPE);
    if ($entities = $entity_storage->loadByProperties([
      $field_name => 'internal:' . $path,
      'status' => TRUE,
    ])) {
      /** @var \Drupal\seo_urls\Entity\SeoUrlInterface $entity */
      $entity = reset($entities);
      // Return entity only if it contains required data.
      if (!$entity->get(SeoUrlInterface::CANONICAL_URL_FIELD)->isEmpty()
        && !$entity->get(SeoUrlInterface::SEO_URL_FIELD)->isEmpty()) {

        return $entity;
      }
    }
    return NULL;
  }

  /**
   * {@inheritDoc}
   */
  public function getCanonicalEntities(SeoUrlInterface $seo_url_entity): array {
    $entities = [];
    $canonical_path = $seo_url_entity->getCanonicalPath();
    $canonical_path = preg_replace('/\?.*$/', '', $canonical_path);

    // Get path for each language.
    $paths = [];
    if ($url_prefixes = $this->configFactory->get('language.negotiation')->get('url.prefixes')) {
      foreach ($url_prefixes as $langcode) {
        $paths[] = $this->aliasManager->getPathByAlias($canonical_path, $langcode);
      }
    }

    // Prepare pattern according to the definitions.
    $links = [];
    $allowed_entity_types = $this->getAllowedEntityTypes();
    foreach ($allowed_entity_types as $entity_type) {
      try {
        $definition = $this->entityTypeManager->getDefinition($entity_type);
      }
      catch (PluginNotFoundException $e) {
        continue;
      }
      if (!$definition) {
        continue;
      }
      $link_templates = $definition->getLinkTemplates();
      if (empty($link_templates['canonical'])) {
        continue;
      }
      $links[] = str_replace(
        ['/', "{{$entity_type}}"],
        ['\/', "(?<{$entity_type}>\d+)"],
        $link_templates['canonical']);
    }
    if (!$links) {
      return $entities;
    }
    $pattern = '/^(' . implode('|', $links) . ')/';

    // Find entities by canonical path.
    foreach ($paths as $path) {
      if (!preg_match($pattern, $path, $matches)) {
        continue;
      }
      $filtered_matches = array_intersect_key($matches, array_fill_keys($allowed_entity_types, TRUE));
      if (!$filtered_matches) {
        // Skip if something went wrong.
        continue;
      }

      $entity_type = array_key_first($filtered_matches);
      $entity_id = reset($filtered_matches);
      $key = $entity_type . ':' . $entity_id;
      if (isset($entities[$key])) {
        // Skip if entity is already in the list.
        continue;
      }

      // Load an entity.
      try {
        if ($entity = $this->entityTypeManager->getStorage($entity_type)->load($entity_id)) {
          $entities[$key] = $entity;
        }
      }
      catch (PluginNotFoundException | InvalidPluginDefinitionException $e) {
        // Ignore such cases.
      }
    }

    return $entities;
  }

  /**
   * {@inheritdoc}
   */
  public function getCanonicalUrlBySeoUrl($seo, $request, $langcode = NULL): string {
    // If no language is explicitly specified we default to the current URL
    // language. If we used a language different from the one conveyed by the
    // requested URL, we might end up being unable to check if there is a path
    // alias matching the URL path.
    $langcode = $langcode ?: $this->currentLangcode;

    // If we already know that there are no paths for this alias simply return.
    if (empty($seo) || !empty($this->noPath[$langcode][$seo])) {
      return $seo;
    }

    // Look for the alias within the cached map.
    if (isset($this->lookupMap[$langcode][$seo])) {
      return $this->getCorrectCanonicalUrl($this->lookupMap[$langcode][$seo], $request);
    }

    // Look for path in storage.
    if ($entity = $this->getSeoUrlEntity($seo)) {
      $uri = $entity->getCanonicalUri();
      $this->lookupMap[$langcode][$seo] = $uri;
      return $this->getCorrectCanonicalUrl($uri, $request);
    }

    // We can't record anything into $this->lookupMap because we didn't find any
    // paths for this alias. Thus cache to $this->noPath.
    $this->noPath[$langcode][$seo] = TRUE;

    return $seo;
  }

  /**
   * Retrieve canonical url and request parameters.
   *
   * @param string $uri
   *   Uri.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The HttpRequest object representing the request to process. Note, if this
   *   method is being called via the path_processor_manager service and is not
   *   part of routing, the current request object must be cloned before being
   *   passed in.
   *
   * @return string
   *   Canonical url.
   */
  protected function getCorrectCanonicalUrl(string $uri, Request $request): string {
    // Make a new request from URI.
    $new_request = Request::create($uri);
    // Set the same query parameters.
    $request->query->add($new_request->query->all());
    // Prevent redirect from redirect module.
    $request->attributes->set('_disable_route_normalizer', TRUE);
    return $new_request->getPathInfo();
  }

  /**
   * {@inheritdoc}
   */
  public function getSeoUrlByCanonicalUrl($path, $request, $langcode = NULL, bool $absolute = FALSE): string {
    // @todo Rework a solution to fetch and compare each query parameter.
    // If no language is explicitly specified we default to the current URL
    // language. If we used a language different from the one conveyed by the
    // requested URL, we might end up being unable to check if there is a path
    // alias matching the URL path.
    $langcode = $langcode ?: $this->currentLangcode;

    // If we already know that there are no paths for this alias simply return.
    if (empty($path) || !empty($this->noAlias[$langcode][$path])) {
      return $path;
    }

    // Look for the alias within the cached map.
    if (isset($this->lookupMap[$langcode][$path])) {
      return $this->getCorrectSeoUrl($this->lookupMap[$langcode][$path], $request, $absolute);
    }

    // Look for path in storage.
    if ($entity = $this->getSeoUrlEntity($path, SeoUrlInterface::CANONICAL_URL_FIELD)) {
      $uri = $entity->getSeoUri();
      $this->lookupMap[$langcode][$path] = $uri;
      return $this->getCorrectSeoUrl($uri, $request, $absolute);
    }

    // We can't record anything into $this->lookupMap because we didn't find any
    // paths for this alias. Thus cache to $this->noAlias.
    $this->noAlias[$langcode][$path] = TRUE;

    return $path;
  }

  /**
   * Retrieve seo url.
   *
   * @param string $uri
   *   Uri.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The HttpRequest object representing the request to process. Note, if this
   *   method is being called via the path_processor_manager service and is not
   *   part of routing, the current request object must be cloned before being
   *   passed in.
   * @param bool $absolute
   *   Determine if URL must be absolute.
   *
   * @return string
   *   Canonical url.
   */
  protected function getCorrectSeoUrl(string $uri, Request $request, bool $absolute = FALSE): string {
    // Make a new request from URI.
    $new_request = Request::create($uri, $request->getMethod(), [], [], [], $request->server->all());
    return $absolute ? $new_request->getUri() : $new_request->getPathInfo();
  }

  /**
   * {@inheritdoc}
   */
  public function cacheClear(?string $source = NULL) {
    // Note this method does not flush the preloaded path lookup cache. This is
    // because if a path is missing from this cache, it still results in the
    // alias being loaded correctly, only less efficiently.
    if (!is_null($source)) {
      foreach (array_keys($this->lookupMap) as $lang) {
        unset($this->lookupMap[$lang][$source]);
      }
    }
    else {
      $this->lookupMap = [];
    }
    $this->noPath = [];
    $this->noAlias = [];
    // @todo Implement caching on the same/similar way as AliasManager did.
    $this->langcodePreloaded = [];
    $this->preloadedPathLookups = [];
  }

}
