<?php

namespace Drupal\custom_entity_example;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\custom_entity_example\Entity\CustomEntityExampleInterface;

/**
 * Defines the custom_entity_example storage.
 */
class CustomEntityExampleStorage extends SqlContentEntityStorage implements CustomEntityExampleStorageInterface {
}
