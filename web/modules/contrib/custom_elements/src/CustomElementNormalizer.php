<?php

namespace Drupal\custom_elements;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Template\Attribute;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Formats a custom element structure into an array.
 */
class CustomElementNormalizer implements NormalizerInterface {

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = []) {
    $cache_metadata = $context['cache_metadata'] ?? new BubbleableMetadata();
    $result = $this->normalizeCustomElement($object, $cache_metadata);
    return $this->convertKeysToCamelCase($result);
  }

  /**
   * {@inheritdoc}
   */
  public function supportsNormalization($data, $format = NULL) {
    return $data instanceof CustomElement;
  }

  /**
   * Normalize custom element.
   *
   * @param \Drupal\custom_elements\CustomElement $element
   *   The custom element.
   * @param \Drupal\Core\Render\BubbleableMetadata $cache_metadata
   *   The cache metadata.
   *
   * @return array
   *   Normalized custom element.
   */
  protected function normalizeCustomElement(CustomElement $element, BubbleableMetadata $cache_metadata) {
    $result = ['element' => $element->getPrefixedTag()];
    $result = array_merge($result, $this->normalizeAttributes($element->getAttributes(), $cache_metadata));

    // Remove dumb default html wrapping elements.
    if ($result['element'] == 'div' || $result['element'] == 'span') {
      unset($result['element']);
    }

    // Support Vue.js "is" attribute for defining element names.
    if (!empty($result['is'])) {
      $result['element'] = $result['is'];
      unset($result['is']);
    }
    unset($result['view-mode']);

    // Collect cache metadata. Since the cache metadata object is passed down
    // to slots, custom elements of slots will add their metadata as well.
    $cache_metadata->addCacheableDependency($element);

    $normalized_slots = $this->normalizeSlots($element, $cache_metadata);
    $result = array_merge($result, $normalized_slots);
    return $result;
  }

  /**
   * Normalize custom element attributes.
   *
   * @param array $attributes
   *   The attributes.
   * @param \Drupal\Core\Render\BubbleableMetadata $cache_metadata
   *   The cache metadata.
   *
   * @return array
   *   Normalized custom element attributes.
   */
  protected function normalizeAttributes(array $attributes, BubbleableMetadata $cache_metadata) {
    $result = [];
    foreach ($attributes as $key => $value) {
      if ($key == 'slot') {
        continue;
      }
      $result_key = strpos($key, ':') === 0 ? substr($key, 1) : $key;
      $result[$result_key] = $value;
    }
    return $result;
  }

  /**
   * Normalize slots.
   *
   * @param \Drupal\custom_elements\CustomElement $element
   *   The element for which to normalize slots.
   * @param \Drupal\Core\Render\BubbleableMetadata $cache_metadata
   *   The cache metadata.
   *
   * @return array
   *   Normalized slots.
   */
  protected function normalizeSlots(CustomElement $element, BubbleableMetadata $cache_metadata) {
    $data = [];
    foreach ($element->getSortedSlotsByName() as $slot_key => $slot_entries) {
      $slot_data = [];
      $normalize_as_simple_value = $element->hasSlotNormalizationStyle($slot_key, CustomElement::NORMALIZE_AS_SIMPLE_VALUE);
      foreach ($slot_entries as $index => $slot) {
        $slot_key = $slot['key'];

        // Handle slots set via nested custom element and markup.
        if (!empty($slot['content']) && $slot['content'] instanceof CustomElement) {
          $slot_data[$index] = $this->normalizeCustomElement($slot['content'], $cache_metadata);
        }
        elseif ($slot['content'] instanceof MarkupInterface) {
          $slot_data[$index]['content'] = (string) $slot['content'];
          if (!empty($slot['attributes']) && !$normalize_as_simple_value && $slot['attributes'] instanceof Attribute) {
            $slot_data[$index] = array_merge($slot_data[$index], $slot['attributes']->toArray());
          }
        }

        // Remove possible doubled slot attributes.
        unset($slot_data[$index]['slot']);

        if ($normalize_as_simple_value) {
          $slot_data[$index] = $slot_data[$index]['content'] ?? NULL;
        }
      }

      if ($element->hasSlotNormalizationStyle($slot_key, CustomElement::NORMALIZE_AS_SINGLE_VALUE)) {
        $slot_data = reset($slot_data);
      }

      // Default to 'content' key for default slots.
      $data_key = $slot_key == 'default' ? 'content' : $slot_key;
      $data[$data_key] = $slot_data;
    }

    return $data;
  }

  /**
   * Converts keys to camel case.
   *
   * @param array $array
   *   Array of keys to convert.
   *
   * @return array
   *   Converted keys.
   */
  protected function convertKeysToCamelCase(array $array) {
    $keys = array_map(function ($key) use (&$array) {
      if (is_array($array[$key])) {
        $array[$key] = $this->convertKeysToCamelCase($array[$key]);
      }
      return preg_replace_callback('/[_-]([a-z])/', function ($matches) {
        return strtoupper($matches[1]);
      }, $key);
    }, array_keys($array));

    return array_combine($keys, $array);
  }

}
