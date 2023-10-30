<?php

namespace Drupal\seo_urls\Event;

use Drupal\Component\EventDispatcher\Event;

/**
 * Event to extend/override the default list of the allowed entity types.
 *
 * @see SeoUrlManager::getAllowedEntityTypes().
 */
class SetAllowedEntityTypesEvent extends Event {

  public const EVENT_NAME = 'set_allowed_entity_types';

  /**
   * Entity types.
   *
   * @var array
   */
  private array $entityTypes;

  /**
   * Construct a SetAllowedEntityTypesEvent object.
   *
   * @param array $entity_types
   *   Entity types.
   */
  public function __construct(array $entity_types) {
    $this->entityTypes = $entity_types;
  }

  /**
   * Get the path.
   *
   * @return array
   *   Entity types.
   */
  public function getEntityTypes() {
    return $this->entityTypes;
  }

  /**
   * Set a new list of the entity types.
   *
   * @param array $entity_types
   *   The path.
   */
  public function setEntityTypes(array $entity_types): void {
    $this->entityTypes = $entity_types;
  }

  /**
   * Extend a list of the entity types.
   *
   * @param array $entity_types
   *   The path.
   */
  public function mergeEntityTypes(array $entity_types): void {
    $this->entityTypes = array_merge($this->entityTypes, $entity_types);
  }

}
