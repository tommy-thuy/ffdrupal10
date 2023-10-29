<?php

namespace Drupal\custom_elements\Processor;

use Drupal\Core\Entity\Exception\UndefinedLinkTemplateException;
use Drupal\Core\Entity\TypedData\EntityDataDefinitionInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\TypedData\DataReferenceInterface;
use Drupal\Core\TypedData\PrimitiveInterface;
use Drupal\custom_elements\CustomElement;
use Drupal\custom_elements\CustomElementsProcessorFieldUtilsTrait;

/**
 * Default processor for field items.
 */
class DefaultFieldItemProcessor implements CustomElementProcessorInterface {

  use CustomElementsProcessorFieldUtilsTrait;

  /**
   * {@inheritdoc}
   */
  public function supports($data, $viewMode) {
    return $data instanceof FieldItemInterface;
  }

  /**
   * {@inheritdoc}
   */
  public function addtoElement($data, CustomElement $element, $viewMode) {
    assert($data instanceof FieldItemInterface);
    $field_item = $data;

    // Add all primitive properties by default.
    foreach ($field_item->getProperties(TRUE) as $name => $property) {
      if ($property instanceof PrimitiveInterface) {
        $element->setAttribute($name, $property->getValue());
      }
      elseif ($property instanceof DataReferenceInterface) {
        try {
          // Add links to referenced entities as slot if the entity is
          // accessible and linkable.
          if (($property->getTargetDefinition() instanceof EntityDataDefinitionInterface
            && $property->getTarget() && $entity = $property->getTarget()->getValue())
            && $this->entityIsAccessible($entity, $element) && $url = $entity->toUrl()) {
            $nested_element = new CustomElement();
            $nested_element->setTag('a');
            $generated_url = $url->toString(TRUE);
            $nested_element->addCacheableDependency($generated_url);
            $nested_element->setAttribute('href', $generated_url->getGeneratedUrl());
            $nested_element->setAttribute('type', $entity->getEntityTypeId());
            $nested_element->setSlot('default', $entity->label());
            $nested_element->addCacheableDependency($entity);
            $element->setSlotFromCustomElement($name, $nested_element);
          }
        }
        catch (UndefinedLinkTemplateException $exception) {
          // Skip if no link-template is defined.
        }
      }
      // We cannot generically other other properties since we do not know how
      // to render them and they are not primitive. So they are skipped.
    }

    // Add the main property as default slot if no content would be there else.
    // However, do not do this if this the only attribute, because in that
    // case we rather let the default field item list processor optimize the
    // whole tag into a parent attribute.
    if (count($element->getSlots()) == 0 && count($element->getAttributes()) != 1 && $property = $field_item->getFieldDefinition()->getFieldStorageDefinition()->getMainPropertyName()) {
      if ($field_item->get($property) instanceof PrimitiveInterface) {
        $element->setSlot('default', $field_item->get($property)->getValue());
        $element->setAttribute($property, NULL);
      }
    }

  }

}
