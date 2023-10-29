<?php

namespace Drupal\custom_elements_thunder\Processor;

use Drupal\custom_elements\CustomElement;
use Drupal\custom_elements\Processor\CustomElementProcessorInterface;
use Drupal\paragraphs\ParagraphInterface;

/**
 * Default processor for thunder image paragraph.
 */
class ParagraphImageProcessor implements CustomElementProcessorInterface {

  use ParagraphProcessorTrait;

  /**
   * {@inheritdoc}
   */
  public function supports($data, $viewMode) {
    if ($data instanceof ParagraphInterface) {
      return $data->getEntityTypeId() == 'paragraph' &&
        $data->bundle() == 'image';
    }
    else {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function addtoElement($paragraph, CustomElement $element, $viewMode) {
    assert($paragraph instanceof ParagraphInterface);
    if ($this->fieldIsAccessible($paragraph, 'field_image', $element)) {
      /** @var \Drupal\media\Entity\Media $media_entity */
      $media_entity = $paragraph->field_image->entity;
      if ($this->entityIsAccessible($media_entity, $element)) {
        // Add common data with trait.
        $this->addtoElementCommon($paragraph, $element);

        if ($this->fieldIsAccessible($media_entity, 'field_image', $element)) {
          $element->setAttribute('src', $media_entity->field_image->entity->uri->url);
          // Add attributes for media entity fields.
          if ($this->fieldIsAccessible($media_entity, 'field_copyright', $element)) {
            $element->setAttribute('copyright', $media_entity->field_copyright->value);
          }
          if ($this->fieldIsAccessible($media_entity, 'field_source', $element)) {
            $element->setAttribute('source', $media_entity->field_source->value);
          }
          if ($this->fieldIsAccessible($media_entity, 'field_description', $element)) {
            $element->setAttribute('caption', $media_entity->field_description->processed);
          }
        }
      }
    }
  }

}
