<?php

namespace Drupal\custom_elements_thunder\Processor;

use Drupal\paragraphs\ParagraphInterface;

/**
 * Processor for thunder quote paragraphs.
 *
 * @deprecated in custom_elements:1.0.0 and is removed from custom_elements:2.0.0.
 *   Use ParagraphTextProcessor instead.
 *
 * @see https://www.drupal.org/project/custom_elements/issues/3273275
 */
class ParagraphQuoteProcessor extends ParagraphTextProcessor {

  /**
   * {@inheritdoc}
   */
  public function supports($data, $viewMode) {
    if ($data instanceof ParagraphInterface) {
      return $data->getEntityTypeId() == 'paragraph' &&
        $data->bundle() == 'quote';
    }
    else {
      return FALSE;
    }
  }

}
