<?php

namespace Drupal\seo_urls\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks if an entity field has a unique value.
 *
 * @Constraint(
 *   id = "UniqueLink",
 *   label = @Translation("Unique link constraint", context = "Validation"),
 * )
 */
class UniqueLinkConstraint extends Constraint {

  /**
   * Error message.
   *
   * @var string
   */
  public string $message = 'A @entity_type with @field_name %value already exists.';

  /**
   * {@inheritdoc}
   */
  public function validatedBy() {
    return '\Drupal\seo_urls\Plugin\Validation\Constraint\UniqueLinkValueValidator';
  }

}
