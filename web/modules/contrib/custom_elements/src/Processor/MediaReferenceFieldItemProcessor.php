<?php

namespace Drupal\custom_elements\Processor;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\custom_elements\CustomElement;
use Drupal\custom_elements\CustomElementGeneratorTrait;
use Drupal\custom_elements\CustomElementsProcessorFieldUtilsTrait;

/**
 * Default processor for media field items.
 */
class MediaReferenceFieldItemProcessor implements CustomElementProcessorInterface {

  use CustomElementGeneratorTrait;
  use CustomElementsProcessorFieldUtilsTrait;

  /**
   * {@inheritdoc}
   */
  public function supports($data, $viewMode) {
    if ($data instanceof FieldItemInterface) {
      $field_definition = $data->getFieldDefinition();
      return $field_definition->getType() == 'entity_reference' &&
        $field_definition->getFieldStorageDefinition()->getSetting('target_type') == 'media';
    }
    else {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function addtoElement($data, CustomElement $element, $viewMode) {
    assert($data instanceof FieldItemInterface);
    $field_item = $data;

    /** @var \Drupal\media\MediaInterface $media_entity */
    $media_entity = $field_item->entity;
    if ($this->entityIsAccessible($media_entity, $element)) {
      $media_element = $this->getCustomElementGenerator()->generate($media_entity, $viewMode);
      // Set element content from media element to avoid a wrapper tag.
      $element->setFromCustomElement($media_element);
    }
  }

}
