<?php

namespace Drupal\custom_elements_thunder\Processor;

use Drupal\custom_elements\CustomElement;
use Drupal\custom_elements\Processor\CustomElementProcessorInterface;
use Drupal\paragraphs\ParagraphInterface;

/**
 * Default processor for thunder gallery paragraph.
 */
class ParagraphGalleryProcessor implements CustomElementProcessorInterface {

  use ParagraphProcessorTrait;

  /**
   * {@inheritdoc}
   */
  public function supports($data, $viewMode) {
    if ($data instanceof ParagraphInterface) {
      return $data->getEntityTypeId() == 'paragraph' &&
        $data->bundle() == 'gallery';
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
    if ($this->fieldIsAccessible($paragraph, 'field_media', $element)) {
      /** @var \Drupal\media\Entity\Media $media_entity */
      $media_entity = $paragraph->get('field_media')->entity;
      if ($this->entityIsAccessible($media_entity, $element)) {
        // Add common data with trait.
        $this->addtoElementCommon($paragraph, $element);

        $sources = [];
        if ($this->fieldIsAccessible($media_entity, 'field_media_images', $element)) {
          foreach ($media_entity->field_media_images as $media_reference) {
            $media_image = $media_reference->entity;
            if ($this->entityIsAccessible($media_image, $element)) {
              if ($this->fieldIsAccessible($media_image, 'field_image', $element)) {
                $source = [];
                if ($this->fieldIsAccessible($media_image, 'field_copyright', $element)) {
                  $source['copyright'] = $media_image->field_copyright->value;
                }
                if ($this->fieldIsAccessible($media_image, 'field_source', $element)) {
                  $source['source'] = $media_image->field_source->value;
                }
                if ($this->fieldIsAccessible($media_image, 'field_description', $element)) {
                  $source['description'] = $media_image->field_description->processed;
                }
                $sources[] = [
                  'url' => $media_image->field_image->entity->uri->url,
                  'thumbnail-url' => $media_image->thumbnail->entity->uri->url,
                  'alt' => $media_image->field_image->alt->value ?? '',
                ] + $source;
              }
            }
          }
          if ($sources) {
            $element->setAttribute('sources', $sources);
          }
        }
      }
    }
  }

}
