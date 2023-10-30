<?php

namespace Drupal\seo_urls;

use Drupal\seo_urls\Entity\SeoUrlInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Find an SEO url for a path and vice versa.
 *
 * @see \Drupal\path_alias\AliasStorageInterface
 */
interface SeoUrlManagerInterface {

  /**
   * Remove prefixed from the path.
   *
   * Also. we can use ClearPathPrefixEvent event to remove custom prefixes.
   *
   * @param string $path
   *   The path.
   *
   * @return string
   *   The path without prefixes.
   *
   * @see ClearPathPrefixEvent::class
   */
  public function clearPathPrefix(string $path): string;

  /**
   * Retrieve a list of the allowed entity types.
   *
   * Also. we can use SetAllowedEntityTypesEvent event to update allowed types.
   *
   * @return string[]
   *   Entity types.
   *
   * @see SetAllowedEntityTypesEvent::class
   */
  public function getAllowedEntityTypes(): array;

  /**
   * Determine if the path is SEO Url.
   *
   * @param string $path
   *   The path.
   * @param string|null $langcode
   *   An optional language code to look up the path in.
   *
   * @return bool
   *   True - is SEO, False - isn't.
   */
  public function isSeoUrl(string $path, ?string $langcode = NULL): bool;

  /**
   * Get entity link by SEO or Canonical path.
   *
   * @param string $path
   *   SEO url.
   * @param string $field_name
   *   Field name to search for. Default is 'field_seo_url'.
   * @param string|null $langcode
   *   An optional language code to look up the path in.
   *
   * @return \Drupal\seo_urls\Entity\SeoUrlInterface|null
   *   SEO Url entity.
   */
  public function getSeoUrlEntity(string $path, string $field_name = SeoUrlInterface::SEO_URL_FIELD, ?string $langcode = NULL): ?SeoUrlInterface;

  /**
   * Retrieve all related entities to the Canonical Url.
   *
   * @param \Drupal\seo_urls\Entity\SeoUrlInterface $seo_url_entity
   *   SEO Url entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   Canonical entities.
   */
  public function getCanonicalEntities(SeoUrlInterface $seo_url_entity): array;

  /**
   * Given the SEO url, return the path it represents.
   *
   * @param string $seo
   *   SEO url.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The HttpRequest object representing the request to process. Note, if this
   *   method is being called via the path_processor_manager service and is not
   *   part of routing, the current request object must be cloned before being
   *   passed in.
   * @param string|null $langcode
   *   An optional language code to look up the path in.
   *
   * @return string
   *   The path represented by SEO url.
   *
   * @throws \InvalidArgumentException
   *   Thrown when the path does not start with a slash.
   */
  public function getCanonicalUrlBySeoUrl(string $seo, Request $request, ?string $langcode = NULL): string;

  /**
   * Given a path, return the SEO url.
   *
   * @param string $path
   *   A path.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The HttpRequest object representing the request to process. Note, if this
   *   method is being called via the path_processor_manager service and is not
   *   part of routing, the current request object must be cloned before being
   *   passed in.
   * @param string|null $langcode
   *   An optional language code to look up the path in.
   * @param bool $absolute
   *   Determine if URL must be absolute.
   *
   * @return string
   *   SEO url that represents the path, or path if no alias was found.
   *
   * @throws \InvalidArgumentException
   *   Thrown when the path does not start with a slash.
   */
  public function getSeoUrlByCanonicalUrl(string $path, Request $request, ?string $langcode = NULL, bool $absolute = FALSE): string;

  /**
   * Clears the static caches in SEO Url manager.
   *
   * @param string|null $source
   *   Source path of the SEO url that is being inserted/updated.
   */
  public function cacheClear(?string $source = NULL);

}
