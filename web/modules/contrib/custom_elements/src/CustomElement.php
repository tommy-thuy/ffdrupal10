<?php

namespace Drupal\custom_elements;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\Markup;

/**
 * Custom element data model.
 */
class CustomElement implements CacheableDependencyInterface {

  use RefinableCacheableDependencyTrait;

  /**
   * Whether Drupal's "field-" prefixes should be removed.
   *
   * This is a global settings and can be set early in the bootstrap, e.g. from
   * settings.php.
   *
   * @var bool
   */
  public static $removeFieldPrefix = TRUE;

  /**
   * List of no-end tags.
   *
   * @var array
   */
  protected static $noEndTags = [
    'area',
    'base',
    'br',
    'col',
    'embed',
    'hr',
    'img',
    'input',
    'link',
    'meta',
    'param',
    'source',
    'track',
    'wbr',
  ];

  /**
   * Custom element tag prefix.
   *
   * Used for prefixing a bunch of custom elements the same way.
   *
   * @var string
   */
  protected $tagPrefix;

  /**
   * Custom element tag name.
   *
   * @var string
   */
  protected $tag = 'div';

  /**
   * List of other attributes.
   *
   * @var array
   */
  protected $attributes = [];

  /**
   * Array of slots, represented as array of nested custom elements.
   *
   * @var array[][]
   *   Array of slots, keyed slot name and entry index. Each entry is an array
   *   with the following keys:
   *   - key: Slot name
   *   - weight: Slot weight
   *   - content: Slot markup (MarkupInterface) or element (CustomElement)
   *     object.
   */
  protected $slots = [];

  /**
   * Array of normalization styles.
   *
   * @var array[][]
   *   Array keyed by slot name and normalization style constant, with the value
   *   indicating whether it's enabled.
   *
   * @see self::NORMALIZE_AS_SIMPLE_VALUE
   * @see self::NORMALIZE_AS_SINGLE_VALUE
   */
  protected $slotNormalizationStyles = [];

  /**
   * Normalize assuming the slot is single-valued.
   */
  const NORMALIZE_AS_SINGLE_VALUE = 'single';

  /**
   * Normalize assuming each slot entry is a simple value, with no attributes.
   */
  const NORMALIZE_AS_SIMPLE_VALUE = 'simple';

  /**
   * Normalize as single and simple value.
   */
  const NORMALIZE_AS_SINGLE_SIMPLE_VALUE = 'single+simple';

  /**
   * Creates a new custom element.
   *
   * @param string $tag
   *   The element tag name.
   *
   * @return static
   *   The created custom element.
   */
  public static function create($tag = 'div') {
    $element = new static();
    $element->setTag($tag);
    return $element;
  }

  /**
   * Creates a custom element from the given render array.
   *
   * If no custom-element is pre-existing in the render array, the render
   * array will be rendered and wrapped in a <drupal-markup> element.
   *
   * @param array $render
   *   The render array.
   *
   * @return \Drupal\custom_elements\CustomElement|mixed
   *   Returns the custom element data model.
   */
  public static function createFromRenderArray(array $render) {
    if (isset($render['#custom_element'])) {
      $element = $render['#custom_element'];
      if (isset($render['#cache'])) {
        $element->addCacheableDependency(BubbleableMetadata::createFromRenderArray($render));
      }
    }
    // If no custom-element is pre-existing, wrap the result in a drupal-markup
    // tag.
    else {
      $element = static::create('drupal-markup');
      $element->setSlotFromRenderArray('default', $render);
    }
    return $element;
  }

  /**
   * Sets the slots of the element.
   *
   * @param array[] $slots
   *   The slots as returned by getSlots().
   */
  public function setSlots(array $slots) {
    $this->slots = $slots;
  }

  /**
   * Gets the element slots, keyed by slot name and entry index.
   *
   * @return array[][]
   *   Array of slots, keyed slot name and entry index. Each entry is an array
   *   with the following keys:
   *   - key: Slot name
   *   - weight: Slot weight
   *   - content: Slot markup (MarkupInterface) or element (CustomElement)
   *     object.
   */
  public function getSlots() {
    return $this->slots;
  }

  /**
   * Gets a sorted and flattened list of slots.
   *
   * @return \Drupal\custom_elements\CustomElement|\Drupal\Core\Render\Markup[]
   *   A numerically indexed and sorted array of slot arrays as defined
   *   by ::getSlot().
   */
  public function getSortedSlots() {
    $slots = $this->getSortedSlotsByName();
    // Flatten the array.
    $slot_entries = [];
    foreach ($slots as $entries) {
      foreach ($entries as $slot_entry) {
        $slot_entries[] = $slot_entry;
      }
    }
    return $slot_entries;
  }

  /**
   * Gets a sorted list of slots, keyed by slot name.
   *
   * @return array[]
   *   A sorted array of slot entries as defined by ::getSlot(), keyed by slot
   *   name.
   */
  public function getSortedSlotsByName() {
    $slots = $this->getSlots();
    foreach ($slots as &$entries) {
      $i = 0;
      $count = count($entries);
      foreach ($entries as &$entry) {
        $entry['weight'] = floor($entry['weight'] * 1000) + $i / $count;
        $i++;
      }
      usort($entries, 'Drupal\Component\Utility\SortArray::sortByWeightElement');
    }
    return $slots;
  }

  /**
   * Gets a slot entry from a the given slot key and index.
   *
   * @param string $key
   *   Name of the slot to get.
   * @param int $index
   *   (optional) The index of the slot entry to retrieve.
   *
   * @return array[][]
   *   Array of slots, keyed slot name and entry index. Each entry is an array
   *   with the following keys:
   *   - key: Slot name
   *   - weight: Slot weight
   *   - content: Slot markup (MarkupInterface) or element (CustomElement)
   *     object.
   */
  public function getSlot($key, $index = 0) {
    return $this->slots[$key][$index] ?? NULL;
  }

  /**
   * Sets a slot from a render array.
   *
   * The render-array is rendered into markup and the slot is set with this
   * markup. Falling-back to adding render arrays should be avoid as much as
   * possible, instead content should be rendered into nested CustomElement
   * objects.
   *
   * @param string $key
   *   Name of the slot to set value for.
   * @param array $build
   *   The render array.
   * @param string $tag
   *   (optional) The element tag to use for the slot.
   * @param array $attributes
   *   (optional) Attributes to add to the slot tag.
   * @param int|null $index
   *   (optional) The index of the slot entry to set. By default, if no value is
   *   given it's assumed that the slot is single-valued and the index 0 gets
   *   set.
   * @param int $weight
   *   (optional) A weight for ordering output slots. Defaults to 0.
   *
   * @return $this
   */
  public function setSlotFromRenderArray($key, array $build, string $tag = 'div', array $attributes = [], int $index = NULL, int $weight = 0) {
    $markup = \Drupal::service('renderer')->renderPlain($build);
    // Add cache metadata as needed from the cache metadata attached to the
    // render array.
    $this->addCacheableDependency(BubbleableMetadata::createFromRenderArray($build));
    return $this->setSlot($key, $markup, $tag, $attributes, $index, $weight);
  }

  /**
   * Sets a value for the given slot.
   *
   * @param string $key
   *   Name of the slot to set value for.
   * @param \Drupal\Component\Render\MarkupInterface|string $value
   *   Slot value or markup.
   * @param string $tag
   *   (optional) The element tag to use for the slot.
   * @param array $attributes
   *   (optional) Attributes to add to the slot tag.
   * @param int|null $index
   *   (optional) The index of the slot entry to set. By default, if no value is
   *   given it's assumed that the slot is single-valued and the index 0 gets
   *   set. For adding a slot, use ::addSlot().
   * @param int $weight
   *   (optional) A weight for ordering output slots. Defaults to 0.
   *
   * @return $this
   *
   * @see ::addSlot()
   */
  public function setSlot(string $key, $value, string $tag = 'div', array $attributes = [], int $index = NULL, int $weight = 0) {
    if (in_array($tag, static::$noEndTags) && !empty($value)) {
      throw new \LogicException(sprintf('Tag %s is no-end tag and should not have a content.', $tag));
    }

    // We do not support passing render arrays directly anymore.
    if (is_array($value)) {
      trigger_error('Setting slot value to an array is deprecated in custom_elements:8.x-2.0 (alpha13) and is removed from custom_elements:8.x-3.0. Provide a CustomElement, MarkupInterface or a markup string. Treating content as a render array. See https://www.drupal.org/project/custom_elements/issues/3144191', E_USER_DEPRECATED);
      return $this->setSlotFromRenderArray($key, $value, $tag, $attributes, $index, $weight);
    }

    if (!isset($index)) {
      $index = 0;
      $this->setSlotNormalizationStyle($key, self::NORMALIZE_AS_SINGLE_VALUE);
    }

    $key = $this->fixSlotKey($key);

    if ($value instanceof CustomElement) {
      $this->setSlotFromCustomElement($key, $value, $index, $weight);
      return $this;
    }

    if ($value && !$value instanceof MarkupInterface) {
      $value = Markup::create((string) $value);
    }

    // If markup and attributes are given, we need to wrap the content in
    // another custom element.
    if ($value && ($attributes || ($tag != 'div' && $tag != 'span'))) {
      $slot = CustomElement::create($tag)
        ->setAttributes($attributes)
        ->setSlot('default', $value);
    }
    // If just markup is given, we can use it directly as slot and assume this
    // is a simple value.
    elseif ($value) {
      $slot = $value;
      $this->setSlotNormalizationStyle($key, self::NORMALIZE_AS_SIMPLE_VALUE);
    }
    // If no value is given, simply create a nested custom element.
    else {
      $slot = CustomElement::create($tag)
        ->setAttributes($attributes);
    }

    $this->slots[$key][$index] = [
      'weight' => $weight,
      'key' => $key,
      'content' => $slot,
    ];

    return $this;
  }

  /**
   * Adds a value for the given slot by appending to any pre-existing ones.
   *
   * @param string $key
   *   Name of the slot to set value for.
   * @param \Drupal\Core\Render\Markup|string $value
   *   Slot markup.
   * @param string $tag
   *   (optional) The tag to use for the slot.
   * @param array $attributes
   *   (optional) Attributes to add to the slot tag.
   * @param int $weight
   *   (optional) A weight for ordering output slots. Defaults to 0.
   *
   * @return $this
   *
   * @see ::setSlot()
   */
  public function addSlot($key, $value, $tag = 'div', array $attributes = [], $weight = 0) {
    return $this->setSlot($key, $value, $tag, $attributes, $this->getIndexForNewSlotEntry($key), $weight);
  }

  /**
   * Gets the index to use for a new slot entry.
   *
   * @param string $key
   *   The slot key.
   *
   * @return int
   *   Return index for new slot entry.
   */
  private function getIndexForNewSlotEntry($key) {
    // Determine last index.
    if (isset($this->slots[$key])) {
      // Determine array index by appending a new one. ::setSlot() will
      // overwrite the array with the right slot array afterwards.
      $this->slots[$key][] = [];
      end($this->slots[$key]);
      return key($this->slots[$key]);
    }
    else {
      return 0;
    }
  }

  /**
   * Sets the slot with a single custom element on a certain index.
   *
   * Note: This is overwriting possibly already existing slots.
   *
   * This method avoids a wrapper div as necessary by the helper for multiple
   * elements.
   *
   * @param string $key
   *   Name of the slot to set value for.
   * @param \Drupal\custom_elements\CustomElement $nestedElement
   *   The nested custom element.
   * @param int|null $index
   *   (optional) The index of the slot entry to set. By default, if no value is
   *   given it's assumed that the slot is single-valued and the index 0 gets
   *   set.
   * @param int $weight
   *   (optional) A weight for ordering output slots. Defaults to 0.
   *
   * @return $this
   *
   * @see ::addSlotFromCustomElement()
   */
  public function setSlotFromCustomElement(string $key, CustomElement $nestedElement, $index = NULL, $weight = 0) {
    if (!isset($index)) {
      $index = 0;
      $this->setSlotNormalizationStyle($key, self::NORMALIZE_AS_SINGLE_VALUE);
    }

    $key = $this->fixSlotKey($key);
    $this->slots[$key][$index] = [
      'weight' => $weight,
      'key' => $key,
      'content' => $nestedElement,
    ];
    return $this;
  }

  /**
   * Sets a slot normalization style.
   *
   * @param string $key
   *   The slot name.
   * @param string $style
   *   One of the following constants:
   *   - Drupal\custom_elements\CustomElement::NORMALIZE_AS_SIMPLE_VALUE
   *   - Drupal\custom_elements\CustomElement::NORMALIZE_AS_SINGLE_VALUE
   *   - Drupal\custom_elements\CustomElement::NORMALIZE_AS_SINGLE_SIMPLE_VALUE.
   * @param bool $enable
   *   Whether to enable the style (default) or disable it.
   *
   * @return $this
   */
  public function setSlotNormalizationStyle($key, $style, $enable = TRUE) {
    $key = $this->fixSlotKey($key);
    // Special support for often need style covering both cases.
    if ($style == self::NORMALIZE_AS_SINGLE_SIMPLE_VALUE) {
      $this->slotNormalizationStyles[$key][self::NORMALIZE_AS_SIMPLE_VALUE] = $enable;
      $this->slotNormalizationStyles[$key][self::NORMALIZE_AS_SINGLE_VALUE] = $enable;
    }
    else {
      $this->slotNormalizationStyles[$key][$style] = $enable;
    }
    return $this;
  }

  /**
   * Returns whether a slot normalization style is set.
   *
   * @param string $key
   *   The slot name.
   * @param string $style
   *   One of the following constants:
   *    - \Drupal\custom_elements\CustomElement::NORMALIZE_AS_SIMPLE_VALUE
   *    - \Drupal\custom_elements\CustomElement::NORMALIZE_AS_SINGLE_VALUE.
   *
   * @return bool
   *   Whether the style is enabled.
   */
  public function hasSlotNormalizationStyle($key, $style) {
    return !empty($this->slotNormalizationStyles[$key][$style]);
  }

  /**
   * Sets the slot with a single custom element.
   *
   * This method avoids a wrapper div as necessary by the helper for multiple
   * elements.
   *
   * @param string $key
   *   Name of the slot to set value for.
   * @param \Drupal\custom_elements\CustomElement $nestedElement
   *   The nested custom element.
   * @param int $weight
   *   (optional) A weight for ordering output slots. Defaults to 0.
   *
   * @return $this
   */
  public function addSlotFromCustomElement($key, CustomElement $nestedElement, $weight = 0) {
    return $this->setSlotFromCustomElement($key, $nestedElement, $this->getIndexForNewSlotEntry($key), $weight);
  }

  /**
   * Sets the slot content from multiple nested custom elements.
   *
   * Note: This is overwriting possibly already existing slots.
   *
   * @param string $key
   *   Name of the slot to set value for.
   * @param \Drupal\custom_elements\CustomElement[] $nestedElements
   *   The (ordered) array of nested custom elements.
   * @param int $weight
   *   (optional) A weight for ordering output slots. Defaults to 0.
   *
   * @return $this
   *
   * @see ::addSlotFromNestedElements()
   */
  public function setSlotFromNestedElements($key, array $nestedElements, $weight = 0) {
    $key = $this->fixSlotKey($key);
    $this->slots[$key] = [];
    if (empty($nestedElements)) {
      return $this;
    }
    $this->addSlotFromNestedElements($key, $nestedElements, $weight);
    return $this;
  }

  /**
   * Adds multiple nested custom elements to the given slot.
   *
   * @param string $key
   *   Name of the slot to set value for.
   * @param \Drupal\custom_elements\CustomElement[] $nestedElements
   *   The (ordered) array of nested custom elements to append.
   * @param int $weight
   *   (optional) A weight for ordering output slots. Defaults to 0.
   *
   * @return $this
   *
   * @see ::setSlotFromNestedElements()
   */
  public function addSlotFromNestedElements($key, array $nestedElements, $weight = 0) {
    $key = $this->fixSlotKey($key);
    $offset = $this->getIndexForNewSlotEntry($key);
    foreach (array_values($nestedElements) as $delta => $nestedElement) {
      $this->slots[$key][$offset + $delta] = [
        'weight' => $weight,
        'key' => $key,
        'content' => $nestedElement,
      ];
    }
    return $this;
  }

  /**
   * Fix slot key and remove prefix if set via static::$removeFieldPrefix.
   *
   * @param string $key
   *   The slot key.
   *
   * @return string
   *   The fixed slot key.
   */
  protected function fixSlotKey($key) {
    $key = str_replace('_', '-', $key);
    if (static::$removeFieldPrefix && strpos($key, 'field-') === 0) {
      $key = substr($key, strlen('field-'));
    }
    return $key;
  }

  /**
   * Gets html tags which don't have an end-tag.
   *
   * @return string[]
   *   List of no-end tags.
   */
  public static function getNoEndTags() {
    return static::$noEndTags;
  }

  /**
   * Sets the custom element from the given element.
   *
   * This overwrites the current custom element with content from the given
   * element.
   *
   * @param \Drupal\custom_elements\CustomElement $element
   *   The element to set values from.
   */
  public function setFromCustomElement(CustomElement $element) {
    $this->setTag($element->getTag());
    $this->setTagPrefix($element->getTagPrefix());
    $this->setAttributes($element->getAttributes());
    $this->setSlots($element->getSlots());
    $this->slotNormalizationStyles = $element->slotNormalizationStyles;
    $this->addCacheableDependency($element);
  }

  /**
   * Gets the custom element tag name.
   *
   * @return string
   *   Custom element tag.
   */
  public function getTag() {
    return $this->tag;
  }

  /**
   * Sets the custom element tag name.
   *
   * @param string $tag
   *   The element tag name.
   *
   * @return $this
   */
  public function setTag($tag) {
    $tag = str_replace('_', '-', $tag);
    $this->tag = $tag;
    return $this;
  }

  /**
   * Gets value for given attribute.
   *
   * @param string $key
   *   Name of the attribute to get value for.
   *
   * @return string
   *   Value for a given attribute.
   */
  public function getAttribute($key) {
    return $this->attributes[$key] ?? NULL;
  }

  /**
   * Sets value for the given attribute.
   *
   * @param string $key
   *   Name of the attribute to set value for.
   * @param string|null $value
   *   Attribute value or NULL to unset the attribute.
   *
   * @return $this
   */
  public function setAttribute($key, $value = NULL) {
    $key = str_replace('_', '-', $key);
    if (static::$removeFieldPrefix && strpos($key, 'field-') === 0) {
      $key = substr($key, strlen('field-'));
    }
    if (isset($value)) {
      $this->attributes[$key] = $value;
    }
    else {
      unset($this->attributes[$key]);
    }
    return $this;
  }

  /**
   * Gets all attributes.
   *
   * @return array
   *   All attributes.
   */
  public function getAttributes() {
    return $this->attributes;
  }

  /**
   * Sets all attributes.
   *
   * @param array $attributes
   *   The attributes.
   *
   * @return $this
   */
  public function setAttributes(array $attributes) {
    $this->attributes = $attributes;
    return $this;
  }

  /**
   * Gets the tag prefix.
   *
   * @return string
   *   Tag prefix.
   */
  public function getTagPrefix() {
    return $this->tagPrefix;
  }

  /**
   * Sets the tag prefix.
   *
   * Note that the tag prefix is separated by a dash from the tag.
   *
   * @param string $tagPrefix
   *   The tag prefix.
   *
   * @return $this
   */
  public function setTagPrefix($tagPrefix) {
    $tagPrefix = str_replace('_', '-', $tagPrefix);
    $this->tagPrefix = $tagPrefix;
    return $this;
  }

  /**
   * Returns the tag including the prefix, separated by a dash.
   *
   * @return string
   *   The tag with prefix.
   */
  public function getPrefixedTag() {
    return $this->tagPrefix ? $this->tagPrefix . '-' . $this->tag : $this->tag;
  }

  /**
   * Gets a render array for rendering the custom element into markup.
   *
   * @return array
   *   Render array for the custom element.
   */
  public function toRenderArray() {
    return [
      '#theme' => 'custom_element',
      '#custom_element' => $this,
    ];
  }

}
