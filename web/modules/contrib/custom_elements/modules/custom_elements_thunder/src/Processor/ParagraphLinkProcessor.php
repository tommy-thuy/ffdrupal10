<?php

namespace Drupal\custom_elements_thunder\Processor;

use Drupal\custom_elements\CustomElement;
use Drupal\custom_elements\Processor\CustomElementProcessorInterface;
use Drupal\paragraphs\ParagraphInterface;

/**
 * Processor for thunder quote paragraphs.
 */
class ParagraphLinkProcessor implements CustomElementProcessorInterface {

  use ParagraphProcessorTrait;

  /**
   * {@inheritdoc}
   */
  public function supports($data, $viewMode) {
    if ($data instanceof ParagraphInterface) {
      return $data->getEntityTypeId() == 'paragraph' &&
        $data->bundle() == 'link';
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
    if ($this->fieldIsAccessible($paragraph, 'field_link', $element)) {
      // Add common data with trait.
      $this->addtoElementCommon($paragraph, $element);

      $element->setAttribute('title', $paragraph->field_link->title);
      $element->setAttribute('href', $paragraph->field_link->uri);
    }
  }

}
