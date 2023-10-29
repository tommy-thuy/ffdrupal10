<?php

namespace Drupal\custom_entity_example\Entity;

use Drupal\address\AddressInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Defines the interface for custom entity example items.
 */
interface CustomEntityExampleInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

  /**
   * Gets the custom_entity_example name.
   *
   * @return string
   *   The custom_entity_example name.
   */
  public function getName();

  /**
   * Sets the custom_entity_example name.
   *
   * @param string $name
   *   The custom_entity_example name.
   *
   * @return $this
   */
  public function setName($name);

  /**
   * Gets the custom_entity_example creation timestamp.
   *
   * @return int
   *   The custom_entity_example creation timestamp.
   */
  public function getCreatedTime();

  /**
   * Sets the custom_entity_example creation timestamp.
   *
   * @param int $timestamp
   *   The custom_entity_example creation timestamp.
   *
   * @return $this
   */
  public function setCreatedTime($timestamp);

}
