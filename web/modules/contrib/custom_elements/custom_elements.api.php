<?php

/**
 * @file
 * Hooks specific to the module.
 */

use Drupal\custom_elements\CustomElement;
use Drupal\Core\Entity\EntityInterface;

/**
 * Allows preparing custom element defaults before it is processed.
 *
 * @param \Drupal\custom_elements\CustomElement $element
 *   The custom element to be rendered.
 * @param \Drupal\Core\Entity\EntityInterface $entity
 *   The entity for which the custom element is generated.
 * @param string $view_mode
 *   The view mode.
 */
function hook_custom_element_entity_defaults_alter(CustomElement $element, EntityInterface $entity, $view_mode) {
  $element->setTagPrefix('myVendor');
}

/**
 * Allows altering custom elements after generation, before they are rendered.
 *
 * @param \Drupal\custom_elements\CustomElement $element
 *   The custom element to be rendered.
 * @param \Drupal\Core\Entity\EntityInterface $entity
 *   The entity for which the custom element is generated.
 * @param string $view_mode
 *   The view mode.
 */
function hook_custom_element_entity_alter(CustomElement $element, EntityInterface $entity, $view_mode) {
  $element->setTagPrefix('myVendor');
}
