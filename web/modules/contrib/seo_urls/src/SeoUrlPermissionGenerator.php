<?php

namespace Drupal\circularbuilt_entities;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Generates permission for each supported entity type.
 */
class SeoUrlPermissionGenerator implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * An interface for entity type managers.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Constructor.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Return an array of per-entity Circular permissions.
   *
   * @return array
   *   An array of permissions.
   */
  public function permissions(): array {
    $permissions = [];

    foreach ($this->entityTypeManager->getDefinitions() as $def) {
      if ($def->getProvider() !== 'circularbuilt_entities') {
        continue;
      }
      $entity_type_id = $def->id();
      $label = $def->getLabel();
      $permissions["administer {$entity_type_id} entities"] = [
        'title' => $this->t('Administer %label entity type', ['%label' => $label]),
        'description' => $this->t('Allow to access the administration form to configure %label entities.', ['%label' => $label]),
        'restrict access' => TRUE,
      ];
      $permissions["add {$entity_type_id} entities"] = [
        'title' => $this->t('Create new %label entities', ['%label' => $label]),
      ];
      $permissions["update {$entity_type_id} entities"] = [
        'title' => $this->t('Edit any %label entities', ['%label' => $label]),
      ];
      $permissions["update own {$entity_type_id} entities"] = [
        'title' => $this->t('Edit own %label entities', ['%label' => $label]),
      ];
      $permissions["update shared {$entity_type_id} entities"] = [
        'title' => $this->t('Edit shared %label entities', ['%label' => $label]),
      ];
      $permissions["view {$entity_type_id} entities"] = [
        'title' => $this->t('View any %label entities', ['%label' => $label]),
      ];
      $permissions["view own {$entity_type_id} entities"] = [
        'title' => $this->t('View own %label entities', ['%label' => $label]),
      ];
      $permissions["view shared {$entity_type_id} entities"] = [
        'title' => $this->t('View shared %label entities', ['%label' => $label]),
      ];
      $permissions["delete {$entity_type_id} entities"] = [
        'title' => $this->t('Delete any %label entities', ['%label' => $label]),
      ];
      $permissions["delete own {$entity_type_id} entities"] = [
        'title' => $this->t('Delete own %label entities', ['%label' => $label]),
      ];
      $permissions["delete shared {$entity_type_id} entities"] = [
        'title' => $this->t('Delete shared %label entities', ['%label' => $label]),
      ];
    }

    return $permissions;
  }

}
