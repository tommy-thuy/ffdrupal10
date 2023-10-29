<?php

namespace Drupal\custom_elements_thunder\Processor;

use Drupal\custom_elements\CustomElement;
use Drupal\custom_elements\Processor\CustomElementProcessorInterface;
use Drupal\paragraphs\ParagraphInterface;

/**
 * Processor for thunder text paragraphs.
 */
class ParagraphTextProcessor implements CustomElementProcessorInterface {

  use ParagraphProcessorTrait;

  /**
   * {@inheritdoc}
   */
  public function supports($data, $viewMode) {
    if ($data instanceof ParagraphInterface) {
      return $data->getEntityTypeId() == 'paragraph' &&
        in_array($data->bundle(), ['quote', 'text']);
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
    if ($this->fieldIsAccessible($paragraph, 'field_text', $element)) {
      // Add common data with trait.
      $this->addtoElementCommon($paragraph, $element);

      $element->setSlot('default', $paragraph->field_text->processed);
    }
  }

}
