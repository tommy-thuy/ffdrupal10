<?php

namespace Drupal\custom_entity_example;

use Drupal\custom_entity_example\Entity\CustomEntityExampleType;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Defines the list builder for custom entity example items.
 */
class CustomEntityExampleListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['name'] = $this->t('Name');
    $header['type'] = $this->t('Type');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\custom_entity_example\Entity\CustomEntityExampleInterface $entity */
    $custom_entity_example_type = CustomEntityExampleType::load($entity->bundle());

    $row['name']['data'] = [
      '#type' => 'link',
      '#title' => $entity->label(),
    ] + $entity->toUrl()->toRenderArray();
    $row['type'] = $custom_entity_example_type->label();

    return $row + parent::buildRow($entity);
  }

}
