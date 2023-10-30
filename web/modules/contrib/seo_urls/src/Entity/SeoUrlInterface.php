<?php

namespace Drupal\seo_urls\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining SEO URL entities.
 *
 * @ingroup seo_urls
 */
interface SeoUrlInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Entity type of the SEO URL entity.
   */
  public const ENTITY_TYPE = 'seo_url';

  /**
   * Bundle of the SEO URL entity.
   */
  public const ENTITY_BUNDLE = 'seo_url';

  /**
   * The field with SEO URL link.
   */
  public const SEO_URL_FIELD = 'seo_url';

  /**
   * The field with Canonical URL link.
   */
  public const CANONICAL_URL_FIELD = 'canonical_url';

  /**
   * Returns the SEO URL entity published status indicator.
   *
   * Unpublished SEO URL entity are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the SEO URL entity is published.
   */
  public function isPublished(): bool;

  /**
   * Sets the published status of a SEO URL entity.
   *
   * @param bool $published
   *   TRUE to set this SEO URL entity to published,
   *   FALSE to set it to unpublished.
   *
   * @return $this
   *   The called SEO URL entity entity.
   */
  public function setPublished(bool $published): SeoUrlInterface;

  /**
   * Gets the SEO URL entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the SEO URL entity.
   */
  public function getCreatedTime(): int;

  /**
   * Sets the SEO URL entity creation timestamp.
   *
   * @param int $timestamp
   *   The SEO URL entity creation timestamp.
   *
   * @return $this
   */
  public function setCreatedTime(int $timestamp): SeoUrlInterface;

  /**
   * Gets URI of the the SEO URL field.
   *
   * @return string
   *   URI of the field.
   */
  public function getSeoUri(): string;

  /**
   * Gets path of the the SEO URL field.
   *
   * @return string
   *   Path of the field.
   */
  public function getSeoPath(): string;

  /**
   * Gets URI of the the SEO URL field.
   *
   * @return string
   *   URI of the field.
   */
  public function getSeoUriBase(): string;

  /**
   * Gets URI of the the Canonical URL field.
   *
   * @return string
   *   URI of the field.
   */
  public function getCanonicalUri(): string;

  /**
   * Gets path of the the Canonical URL field.
   *
   * @return string
   *   Path of the field.
   */
  public function getCanonicalPath(): string;

}
