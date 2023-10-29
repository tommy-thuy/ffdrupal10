<?php

namespace Drupal\custom_elements\Processor;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\custom_elements\CustomElement;
use Drupal\custom_elements\CustomElementGeneratorTrait;
use Drupal\custom_elements\CustomElementsProcessorFieldUtilsTrait;

/**
 * Default processor for paragraph field items.
 */
class ParagraphFieldItemProcessor implements CustomElementProcessorInterface {

  use CustomElementGeneratorTrait;
  use CustomElementsProcessorFieldUtilsTrait;

  /**
   * {@inheritdoc}
   */
  public function supports($data, $viewMode) {
    if ($data instanceof FieldItemInterface) {
      $field_definition = $data->getFieldDefinition();
      return $field_definition->getType() == 'entity_reference_revisions' &&
        $field_definition->getFieldStorageDefinition()->getSetting('target_type') == 'paragraph';
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

    /** @var \Drupal\paragraphs\ParagraphInterface $paragraph */
    $paragraph = $field_item->entity;
    if ($this->entityIsAccessible($paragraph, $element)) {
      $paragraph_element = $this->getCustomElementGenerator()->generate($paragraph, $viewMode);
      // Set content without wrapping tag.
      $element->setFromCustomElement($paragraph_element);
    }
  }

}
