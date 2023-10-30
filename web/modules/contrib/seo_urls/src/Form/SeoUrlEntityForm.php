<?php

namespace Drupal\seo_urls\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for class SEO URL entity edit forms.
 *
 * @ingroup seo_urls
 */
class SeoUrlEntityForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = &$this->entity;
    $entity_type = $entity->getEntityType();
    $entity_type_id = $entity_type->id();

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created %label @type.', [
          '%label' => $entity->label(),
          '@type' => $entity_type->getLabel(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved %label @type.', [
          '%label' => $entity->label(),
          '@type' => $entity_type->getLabel(),
        ]));
    }

    $form_state->setRedirect("entity.{$entity_type_id}.collection", [$entity_type_id => $entity->id()]);

    return $status;
  }

}
