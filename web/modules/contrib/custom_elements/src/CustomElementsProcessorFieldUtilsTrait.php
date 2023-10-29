<?php

namespace Drupal\custom_elements;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Trait for custom elements processors.
 */
trait CustomElementsProcessorFieldUtilsTrait {

  /**
   * Check an entity field accessibility.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Entity to check fields for.
   * @param string $field_name
   *   Field name to check for.
   * @param \Drupal\custom_elements\CustomElement $element
   *   The custom element to be rendered.
   * @param \Drupal\Core\Session\AccountInterface|null $account
   *   (optional) The user for which to check access, or NULL to check access
   *   for the current user. Defaults to NULL.
   * @param bool $return_as_object
   *   (optional) Defaults to FALSE.
   *
   * @return bool
   *   The access result. Returns a boolean if $return_as_object is FALSE (this
   *   is the default) and otherwise an AccessResultInterface object.
   *   When a boolean is returned, the result of AccessInterface::isAllowed() is
   *   returned, i.e. TRUE if entity has the field and field is viewable if
   *   requested, FALSE otherwise.
   */
  public function fieldIsAccessible(ContentEntityInterface $entity, string $field_name, CustomElement $element, AccountInterface $account = NULL, bool $return_as_object = FALSE) {
    if ($entity->hasField($field_name)) {
      $field = $entity->get($field_name);
      if ($field->isEmpty()) {
        return FALSE;
      }

      $field_view_access = $field->access('view', $account, TRUE);
      $element->addCacheableDependency($field_view_access);
      return $return_as_object ? $field_view_access : $field_view_access->isAllowed();
    }
    return FALSE;
  }

  /**
   * Check an entity accessibility.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface|null $entity
   *   Entity to check fields for.
   * @param \Drupal\custom_elements\CustomElement $element
   *   The custom element to be rendered.
   * @param \Drupal\Core\Session\AccountInterface|null $account
   *   (optional) The user for which to check access, or NULL to check access
   *   for the current user. Defaults to NULL.
   * @param bool $return_as_object
   *   (optional) Defaults to FALSE.
   *
   * @return bool
   *   The access result. Returns a boolean if $return_as_object is FALSE (this
   *   is the default) and otherwise an AccessResultInterface object.
   *   When a boolean is returned, the result of AccessInterface::isAllowed() is
   *   returned, i.e. TRUE means access is explicitly allowed, FALSE means
   *   access is either explicitly forbidden or "no opinion".
   */
  public function entityIsAccessible(?ContentEntityInterface $entity, CustomElement $element, AccountInterface $account = NULL, bool $return_as_object = FALSE) {
    if (!empty($entity)) {
      $element->addCacheableDependency($entity);
      $entity_access = $entity->access('view', $account, TRUE);
      $element->addCacheableDependency($entity_access);
      return $return_as_object ? $entity_access : $entity_access->isAllowed();
    }
    return FALSE;
  }

}
