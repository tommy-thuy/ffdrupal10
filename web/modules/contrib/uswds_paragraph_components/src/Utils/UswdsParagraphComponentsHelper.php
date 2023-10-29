<?php

namespace Drupal\uswds_paragraph_components\Utils;

use Drupal\Component\Serialization\Yaml;

/**
 * Class MyHelperFunctions.
 */
class UswdsParagraphComponentsHelper {

  protected const CONFIG_LIST = [
    'uswds_2_column_breakpoints' => [
      'core.entity_form_display.paragraph.uswds_2_column_breakpoints.default',
      'core.entity_view_display.paragraph.uswds_2_column_breakpoints.default',
      'field.field.paragraph.uswds_2_column_breakpoints.field_2_column_grid_options',
      'field.storage.paragraph.field_2_column_grid_options',
      'field.field.paragraph.uswds_2_column_breakpoints.field_uswds_breakpoints',
      'field.storage.paragraph.field_uswds_breakpoints',
      'paragraphs.paragraphs_type.uswds_2_column_breakpoints',
    ],
    'uswds_2_columns' => [
      'core.entity_form_display.paragraph.uswds_2_columns.default',
      'core.entity_view_display.paragraph.uswds_2_columns.default',
      'field.field.paragraph.uswds_2_columns.field_2_column_content',
      'field.storage.paragraph.field_2_column_content',
      'field.field.paragraph.uswds_2_columns.field_column_grid_gap',
      'field.storage.paragraph.field_column_grid_gap',
      'field.field.paragraph.uswds_2_columns.field_uswds_2_column_breakpoints',
      'field.storage.paragraph.field_uswds_2_column_breakpoints',
      'paragraphs.paragraphs_type.uswds_2_columns',
    ],
    'uswds_3_column_breakpoints' => [
      'core.entity_form_display.paragraph.uswds_3_column_breakpoints.default',
      'core.entity_view_display.paragraph.uswds_3_column_breakpoints.default',
      'field.field.paragraph.uswds_3_column_breakpoints.field_3_column_grid_options',
      'field.storage.paragraph.field_3_column_grid_options',
      'field.field.paragraph.uswds_3_column_breakpoints.field_uswds_breakpoints',
      'field.storage.paragraph.field_uswds_breakpoints',
      'paragraphs.paragraphs_type.uswds_3_column_breakpoints',
    ],
    'uswds_3_columns' => [
      'core.entity_form_display.paragraph.uswds_3_columns.default',
      'core.entity_view_display.paragraph.uswds_3_columns.default',
      'field.field.paragraph.uswds_3_columns.field_3_column_content',
      'field.storage.paragraph.field_3_column_content',
      'field.field.paragraph.uswds_3_columns.field_column_grid_gap',
      'field.storage.paragraph.field_column_grid_gap',
      'field.field.paragraph.uswds_3_columns.field_uswds_3_column_breakpoints',
      'field.storage.paragraph.field_uswds_3_column_breakpoints',
      'paragraphs.paragraphs_type.uswds_3_columns',
    ],
    'uswds_accordion' => [
      'core.entity_form_display.paragraph.uswds_accordion.default',
      'core.entity_view_display.paragraph.uswds_accordion.default',
      'field.field.paragraph.uswds_accordion.field_accordion_section',
      'field.storage.paragraph.field_accordion_section',
      'field.field.paragraph.uswds_accordion.field_bordered',
      'field.storage.paragraph.field_bordered',
      'field.field.paragraph.uswds_accordion.field_default_open',
      'field.storage.paragraph.field_default_open',
      'field.field.paragraph.uswds_accordion.field_multiselect',
      'field.storage.paragraph.field_multiselect',
      'paragraphs.paragraphs_type.uswds_accordion',
    ],
    'uswds_accordion_section' => [
      'core.entity_form_display.paragraph.uswds_accordion_section.default',
      'core.entity_view_display.paragraph.uswds_accordion_section.default',
      'field.field.paragraph.uswds_accordion_section.field_accordion_section_body',
      'field.storage.paragraph.field_accordion_section_body',
      'field.field.paragraph.uswds_accordion_section.field_accordion_section_title',
      'field.storage.paragraph.field_accordion_section_title',
      'paragraphs.paragraphs_type.uswds_accordion_section',
    ],
    'uswds_alert' => [
      'core.entity_form_display.paragraph.uswds_alert.default',
      'core.entity_view_display.paragraph.uswds_alert.default',
      'field.field.paragraph.uswds_alert.field_alert_body',
      'field.storage.paragraph.field_alert_body',
      'field.field.paragraph.uswds_alert.field_alert_status',
      'field.storage.paragraph.field_alert_status',
      'field.field.paragraph.uswds_alert.field_alert_title',
      'field.storage.paragraph.field_alert_title',
      'field.field.paragraph.uswds_alert.field_no_icon',
      'field.storage.paragraph.field_no_icon',
      'field.field.paragraph.uswds_alert.field_slim',
      'field.storage.paragraph.field_slim',
      'paragraphs.paragraphs_type.uswds_alert',
    ],
    'uswds_card_breakpoints' => [
      'core.entity_form_display.paragraph.uswds_card_breakpoints.default',
      'core.entity_view_display.paragraph.uswds_card_breakpoints.default',
      'field.field.paragraph.uswds_card_breakpoints.field_number_of_columns',
      'field.storage.paragraph.field_number_of_columns',
      'field.field.paragraph.uswds_card_breakpoints.field_uswds_breakpoints',
      'field.storage.paragraph.field_uswds_breakpoints',
      'paragraphs.paragraphs_type.uswds_card_breakpoints',
    ],
    'uswds_card_group_flag' => [
      'core.entity_form_display.paragraph.uswds_card_group_flag.default',
      'core.entity_view_display.paragraph.uswds_card_group_flag.default',
      'field.field.paragraph.uswds_card_group_flag.field_alternating_flags',
      'field.storage.paragraph.field_alternating_flags',
      'field.field.paragraph.uswds_card_group_flag.field_cards',
      'field.storage.paragraph.field_cards',
      'field.field.paragraph.uswds_card_group_flag.field_uswds_classes',
      'field.storage.paragraph.field_uswds_classes',
      'paragraphs.paragraphs_type.uswds_card_group_flag',
    ],
    'uswds_card_group_regular' => [
      'core.entity_form_display.paragraph.uswds_card_group_regular.default',
      'core.entity_view_display.paragraph.uswds_card_group_regular.default',
      'field.field.paragraph.uswds_card_group_regular.field_cards',
      'field.storage.paragraph.field_cards',
      'field.field.paragraph.uswds_card_group_regular.field_uswds_classes',
      'field.storage.paragraph.field_uswds_classes',
      'paragraphs.paragraphs_type.uswds_card_group_regular',
    ],
    'uswds_card_regular' => [
      'core.entity_form_display.paragraph.uswds_card_regular.default',
      'core.entity_view_display.paragraph.uswds_card_regular.default',
      'field.field.paragraph.uswds_card_regular.field_button',
      'field.storage.paragraph.field_button',
      'field.field.paragraph.uswds_card_regular.field_card_breakpoints',
      'field.storage.paragraph.field_card_breakpoints',
      'field.field.paragraph.uswds_card_regular.field_card_image',
      'field.storage.paragraph.field_card_image',
      'field.field.paragraph.uswds_card_regular.field_card_title',
      'field.storage.paragraph.field_card_title',
      'field.field.paragraph.uswds_card_regular.field_extend_media',
      'field.storage.paragraph.field_extend_media',
      'field.field.paragraph.uswds_card_regular.field_indent_media',
      'field.storage.paragraph.field_indent_media',
      'field.field.paragraph.uswds_card_regular.field_make_card_link',
      'field.storage.paragraph.field_make_card_link',
      'field.field.paragraph.uswds_card_regular.field_text',
      'field.storage.paragraph.field_text',
      'field.field.paragraph.uswds_card_regular.field_title_first',
      'field.storage.paragraph.field_title_first',
      'paragraphs.paragraphs_type.uswds_card_regular',
    ],
    'uswds_cards_flag' => [
      'core.entity_form_display.paragraph.uswds_cards_flag.default',
      'core.entity_view_display.paragraph.uswds_cards_flag.default',
      'field.field.paragraph.uswds_cards_flag.field_button',
      'field.storage.paragraph.field_button',
      'field.field.paragraph.uswds_cards_flag.field_card_breakpoints',
      'field.storage.paragraph.field_card_breakpoints',
      'field.field.paragraph.uswds_cards_flag.field_card_image',
      'field.storage.paragraph.field_card_image',
      'field.field.paragraph.uswds_cards_flag.field_card_title',
      'field.storage.paragraph.field_card_title',
      'field.field.paragraph.uswds_cards_flag.field_image_position',
      'field.storage.paragraph.field_image_position',
      'field.field.paragraph.uswds_cards_flag.field_make_card_link',
      'field.storage.paragraph.field_make_card_link',
      'field.field.paragraph.uswds_cards_flag.field_text',
      'field.storage.paragraph.field_text',
      'paragraphs.paragraphs_type.uswds_cards_flag',
    ],
    'uswds_modal' => [
      'core.entity_form_display.paragraph.uswds_modal.default',
      'core.entity_view_display.paragraph.uswds_modal.default',
      'field.field.paragraph.uswds_modal.field_button_text',
      'field.storage.paragraph.field_button_text',
      'field.field.paragraph.uswds_modal.field_display_as_button',
      'field.storage.paragraph.field_display_as_button',
      'field.field.paragraph.uswds_modal.field_force_action',
      'field.storage.paragraph.field_force_action',
      'field.field.paragraph.uswds_modal.field_large_modal',
      'field.storage.paragraph.field_large_modal',
      'field.field.paragraph.uswds_modal.field_modal_body',
      'field.storage.paragraph.field_modal_body',
      'field.field.paragraph.uswds_modal.field_modal_no_button_text',
      'field.storage.paragraph.field_modal_no_button_text',
      'field.field.paragraph.uswds_modal.field_modal_title',
      'field.storage.paragraph.field_modal_title',
      'field.field.paragraph.uswds_modal.field_modal_yes_button_text',
      'field.storage.paragraph.field_modal_yes_button_text',
      'paragraphs.paragraphs_type.uswds_modal',
    ],
    'uswds_process_item' => [
      'core.entity_form_display.paragraph.uswds_process_item.default',
      'core.entity_view_display.paragraph.uswds_process_item.default',
      'field.field.paragraph.uswds_process_item.field_header',
      'field.storage.paragraph.field_header',
      'field.field.paragraph.uswds_process_item.field_text',
      'field.storage.paragraph.field_text',
      'paragraphs.paragraphs_type.uswds_process_item',
    ],
    'uswds_process_list' => [
      'core.entity_form_display.paragraph.uswds_process_list.default',
      'core.entity_view_display.paragraph.uswds_process_list.default',
      'field.field.paragraph.uswds_process_list.field_process_items',
      'field.storage.paragraph.field_process_items',
      'paragraphs.paragraphs_type.uswds_process_list',
    ],
    'uswds_step_indicator_item' => [
      'core.entity_form_display.paragraph.uswds_step_indicator_item.default',
      'core.entity_view_display.paragraph.uswds_step_indicator_item.default',
      'field.field.paragraph.uswds_step_indicator_item.field_current',
      'field.storage.paragraph.field_current',
      'field.field.paragraph.uswds_step_indicator_item.field_item_title',
      'field.storage.paragraph.field_item_title',
      'paragraphs.paragraphs_type.uswds_step_indicator_item',
    ],
    'uswds_step_indicator_list' => [
      'core.entity_form_display.paragraph.uswds_step_indicator_list.default',
      'core.entity_view_display.paragraph.uswds_step_indicator_list.default',
      'field.field.paragraph.uswds_step_indicator_list.field_centered',
      'field.storage.paragraph.field_centered',
      'field.field.paragraph.uswds_step_indicator_list.field_counters',
      'field.storage.paragraph.field_counters',
      'field.field.paragraph.uswds_step_indicator_list.field_header',
      'field.storage.paragraph.field_header',
      'field.field.paragraph.uswds_step_indicator_list.field_no_labels',
      'field.storage.paragraph.field_no_labels',
      'field.field.paragraph.uswds_step_indicator_list.field_small_counters',
      'field.storage.paragraph.field_small_counters',
      'field.field.paragraph.uswds_step_indicator_list.field_step_indicator_items',
      'field.storage.paragraph.field_step_indicator_items',
      'paragraphs.paragraphs_type.uswds_step_indicator_list',
    ],
    'uswds_summary_box' => [
      'core.entity_form_display.paragraph.uswds_summary_box.default',
      'core.entity_view_display.paragraph.uswds_summary_box.default',
      'field.field.paragraph.uswds_summary_box.field_header',
      'field.field.paragraph.uswds_summary_box.field_text',
      'field.storage.paragraph.field_text',
      'paragraphs.paragraphs_type.uswds_summary_box',
    ],
  ];

  /**
   * Given a list of config files update.
   *
   * @param array $config_list
   *   List of config files to update.
   *
   * @return array[]
   *   Return output of what was updated or created.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public static function updateExistingConfig(array $config_list) {
    // Sets variable for the path.
    $config_path = \Drupal::service('extension.list.module')->getPath('uswds_paragraph_components') . '/config/optional';

    $updated = [];
    $created = [];
    $config_manger = \Drupal::service('config.manager');
    foreach ($config_list as $file) {
      $raw = file_get_contents($config_path . '/' . $file . '.yml');
      if ($raw) {
        $value = Yaml::decode($raw);
        if (!is_array($value)) {
          throw new \RuntimeException('Invalid YAML file %s', $file);
        }
        // Lazy hack here since that code ignores the file extension.
        $type = $config_manger->getEntityTypeIdByName(basename($file));
        $entity_manager = $config_manger->getEntityTypeManager();
        $definition = $entity_manager->getDefinition($type);
        $id_key = $definition->getKey('id');
        $id = $value[$id_key];
        /** @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface $entity_storage */
        $entity_storage = $entity_manager->getStorage($type);
        $entity = $entity_storage->load($id);
        if ($entity) {
          $entity = $entity_storage->updateFromStorageRecord($entity, $value);
          $entity->save();
          $updated[] = $id;
        }
        else {
          $entity = $entity_storage->createFromStorageRecord($value);
          $entity->save();
          $created[] = $id;
        }
      }
    }
    return [
      'updated' => $updated,
      'created' => $created,
    ];
  }

  /**
   * Get list of config files by a specifc bundle.
   */
  public static function getConfigListByBundle($string) {
    return self::CONFIG_LIST[$string] ?: [];
  }

}
