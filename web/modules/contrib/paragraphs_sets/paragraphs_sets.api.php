<?php

/**
 * @file
 * Hooks and documentation related to paragraphs_sets module.
 */

use Drupal\paragraphs_sets\ParagraphsSetInterface;

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter the default field data provided by all sets.
 *
 * @param array $data
 *   Default field values for the paragraph bundles in the set.
 * @param array $context
 *   An associative array containing the following key-value pairs:
 *   - field: Name of field currently operated on.
 *   - form: The form render array.
 *   - form_state: The current form state.
 *   - key: Internal key of paragraph in set.
 *   - paragraphs_bundle: Bundle name of paragraph.
 *   - set: Machine name of current set.
 */
function hook_paragraphs_set_data_alter(array &$data, array $context) {
}

/**
 * Alter the default field data provided by a specific set.
 *
 * @param array $data
 *   Default field values for the paragraph bundles in the set.
 * @param array $context
 *   An associative array containing the following key-value pairs:
 *   - field: Name of field currently operated on.
 *   - form: The form render array.
 *   - form_state: The current form state.
 *   - key: Internal key of paragraph in set.
 *   - paragraphs_bundle: Bundle name of paragraph.
 *   - set: Machine name of current set.
 */
function hook_paragraphs_set_SET_data_alter(array &$data, array $context) {
}

/**
 * Alter the default field data provided by a specific set for a single field.
 *
 * @param array $data
 *   Default field values for the paragraph bundles in the set.
 * @param array $context
 *   An associative array containing the following key-value pairs:
 *   - field: Name of field currently operated on.
 *   - form: The form render array.
 *   - form_state: The current form state.
 *   - key: Internal key of paragraph in set.
 *   - paragraphs_bundle: Bundle name of paragraph.
 *   - set: Machine name of current set.
 */
function hook_paragraphs_set_SET_FIELD_NAME_data_alter(array &$data, array $context) {
}

/**
 * Alter the static icon uri for a ParagraphsSet.
 *
 * @param string $uri
 *   The uri to alter.
 * @param \Drupal\paragraphs_sets\ParagraphsSetInterface $paragraphs_set
 *   The current ParagraphsSet.
 */
function hook_paragraphs_sets_set_static_icon_uri_alter(string &$uri, ParagraphsSetInterface $paragraphs_set) {
  if ('my_paragraphs_set' === $paragraphs_set->id()) {
    $uri = 'public://my_image_library/images/library/gallery.png';
  }
}

/**
 * @} End of "addtogroup hooks".
 */
