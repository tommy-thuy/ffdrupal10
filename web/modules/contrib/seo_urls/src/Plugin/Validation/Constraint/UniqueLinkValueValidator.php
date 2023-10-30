<?php

namespace Drupal\seo_urls\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates that a field is unique for the given entity type.
 */
class UniqueLinkValueValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    if (!$item = $items->first()) {
      return;
    }
    $field_name = $items->getFieldDefinition()->getName();
    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = $items->getEntity();
    $entity_type_id = $entity->getEntityTypeId();
    $id_key = $entity->getEntityType()->getKey('id');

    $query = \Drupal::entityQuery($entity_type_id)
      ->accessCheck(FALSE);

    // If the entity already exists in the storage, ensure that we don't compare
    // the field value with the pre-existing one.
    if (!$entity->isNew()) {
      $query->condition($id_key, $entity->id(), '<>');
    }

    $value_taken = (bool) $query
      ->condition($field_name, $item->uri)
      ->range(0, 1)
      ->count()
      ->execute();

    if ($value_taken) {
      /** @var \Drupal\seo_urls\Plugin\Validation\Constraint\UniqueLinkConstraint $constraint */
      $this->context->addViolation($constraint->message, [
        '%value' => str_replace('internal:', '', $item->uri),
        '@entity_type' => $entity->getEntityType()->getSingularLabel(),
        '@field_name' => mb_strtolower($items->getFieldDefinition()->getLabel()),
      ]);
    }
  }

}
