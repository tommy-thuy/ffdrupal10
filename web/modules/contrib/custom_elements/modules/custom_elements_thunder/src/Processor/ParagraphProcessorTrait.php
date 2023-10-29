<?php

namespace Drupal\custom_elements_thunder\Processor;

use Drupal\custom_elements\CustomElement;
use Drupal\custom_elements\CustomElementsProcessorFieldUtilsTrait;
use Drupal\paragraphs\ParagraphInterface;

/**
 * Trait for paragraph processors.
 */
trait ParagraphProcessorTrait {

  use CustomElementsProcessorFieldUtilsTrait;

  /**
   * Adds paragraph common data to the element.
   *
   * @param mixed $paragraph
   *   The paragraph data to be processed and added.
   * @param \Drupal\custom_elements\CustomElement $element
   *   The custom element that is generated.
   */
  public function addtoElementCommon($paragraph, CustomElement $element) {
    assert($paragraph instanceof ParagraphInterface);

    // Always add a title attribute if field_title is there.
    if (isset($paragraph->field_title) && $paragraph->field_title->value) {
      $element->setAttribute('title', $paragraph->field_title->value);
    }
  }

}
