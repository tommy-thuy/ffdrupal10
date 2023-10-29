<?php

namespace Drupal\custom_elements_thunder\Processor;

use Drupal\custom_elements\CustomElement;
use Drupal\custom_elements\Processor\CustomElementProcessorInterface;
use Drupal\paragraphs\ParagraphInterface;

/**
 * Default processor for thunder instagram, pinterest and twitter paragraphs.
 */
class ParagraphSocialMediaProcessor implements CustomElementProcessorInterface {

  use ParagraphProcessorTrait;

  /**
   * {@inheritdoc}
   */
  public function supports($data, $viewMode) {
    if ($data instanceof ParagraphInterface) {
      return $data->getEntityTypeId() == 'paragraph' &&
        in_array($data->bundle(), ['instagram', 'pinterest', 'twitter']);
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
    /** @var \Drupal\media\Entity\Media $media_entity */
    $media_entity = $paragraph->field_media->entity;
    if ($this->entityIsAccessible($media_entity, $element)) {
      // Add common data with trait.
      $this->addtoElementCommon($paragraph, $element);

      $element->setAttribute('src', $media_entity->field_url->uri);
    }
  }

}
