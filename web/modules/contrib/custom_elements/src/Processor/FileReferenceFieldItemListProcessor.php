<?php

namespace Drupal\custom_elements\Processor;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\custom_elements\CustomElement;
use Drupal\file\Plugin\Field\FieldType\FileItem;

/**
 * Renders file references (including images) into single slots.
 */
class FileReferenceFieldItemListProcessor implements CustomElementProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function supports($data, $viewMode) {
    return $data instanceof FieldItemListInterface && $data->first() instanceof FileItem;
  }

  /**
   * {@inheritdoc}
   */
  public function addtoElement($data, CustomElement $element, $viewMode) {
    assert($data instanceof FieldItemListInterface);
    $field_item_list = $data;

    $nested_element = CustomElement::createFromRenderArray($field_item_list->view($viewMode))
      ->setTagPrefix('field')
      ->setTag($field_item_list->getFieldDefinition()->getType());

    $element->setSlotFromCustomElement($field_item_list->getName(), $nested_element);
  }

}
