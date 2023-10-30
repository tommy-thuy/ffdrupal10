<?php

namespace Drupal\seo_urls;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the class SEO URL entity.
 *
 * @see \Drupal\seo_urls\Entity\Question.
 */
class SeoUrlAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    if ($account->hasPermission($this->entityType->getAdminPermission())) {
      return AccessResult::allowed()->cachePerPermissions();
    }
    if ($operation == 'delete' && $entity->isNew()) {
      return AccessResult::forbidden()->addCacheableDependency($entity);
    }

    /** @var \Drupal\seo_urls\Entity\SeoUrlInterface $entity */
    $result = AccessResult::neutral();
    if ($entity->getOwnerId() === \Drupal::currentUser()->id()) {
      $result = $result->orIf(AccessResult::allowedIfHasPermission($account, "{$operation} own {$this->entityTypeId} entities"));
    }
    $result = $result->orIf(AccessResult::allowedIfHasPermission($account, "{$operation} {$this->entityTypeId} entities"));
    $result->cachePerPermissions();
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, "add {$this->entityTypeId} entities")->cachePerPermissions();
  }

}
