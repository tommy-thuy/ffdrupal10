<?php

namespace Drupal\custom_entity_example\Entity;

use Drupal\address\AddressInterface;
use Drupal\user\EntityOwnerTrait;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityChangedTrait;

/**
 * Defines the custom_entity_example entity class.
 *
 * @ContentEntityType(
 *   id = "custom_entity_example",
 *   label = @Translation("Custom Entity Example", context = "Custom Entity Modules"),
 *   label_collection = @Translation("Custom Entity Example items", context = "Custom Entity Modules"),
 *   label_singular = @Translation("custom entity example item", context = "Custom Entity Modules"),
 *   label_plural = @Translation("custom entity example items", context = "Custom Entity Modules"),
 *   label_count = @PluralTranslation(
 *     singular = "@count custom entity example item",
 *     plural = "@count custom entity example items",
 *     context = "Custom Entity Modules",
 *   ),
 *   bundle_label = @Translation("Custom Entity Example type", context = "Custom Entity Modules"),
 *   handlers = {
 *     "event" = "Drupal\custom_entity_example\Event\CustomEntityExampleEvent",
 *     "storage" = "Drupal\custom_entity_example\CustomEntityExampleStorage",
 *     "access" = "Drupal\entity\EntityAccessControlHandler",
 *     "query_access" = "Drupal\entity\QueryAccess\QueryAccessHandler",
 *     "permission_provider" = "Drupal\entity\EntityPermissionProvider",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\custom_entity_example\CustomEntityExampleListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "default" = "Drupal\custom_entity_example\Form\CustomEntityExampleForm",
 *       "add" = "Drupal\custom_entity_example\Form\CustomEntityExampleForm",
 *       "edit" = "Drupal\custom_entity_example\Form\CustomEntityExampleForm",
 *       "duplicate" = "Drupal\custom_entity_example\Form\CustomEntityExampleForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "local_task_provider" = {
 *       "default" = "Drupal\entity\Menu\DefaultEntityLocalTaskProvider",
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\entity\Routing\AdminHtmlRouteProvider",
 *       "delete-multiple" = "Drupal\entity\Routing\DeleteMultipleRouteProvider",
 *     },
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler"
 *   },
 *   base_table = "custom_entity_example",
 *   data_table = "custom_entity_example_field_data",
 *   admin_permission = "administer custom_entity_example",
 *   permission_granularity = "bundle",
 *   translatable = TRUE,
 *   entity_keys = {
 *     "id" = "custom_entity_example_id",
 *     "uuid" = "uuid",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "langcode" = "langcode",
 *     "owner" = "uid",
 *     "uid" = "uid",
 *   },
 *   links = {
 *     "canonical" = "/custom-entity-example/{custom_entity_example}",
 *     "add-page" = "/custom-entity-example/add",
 *     "add-form" = "/custom-entity-example/add/{custom_entity_example_type}",
 *     "edit-form" = "/custom-entity-example/{custom_entity_example}/edit",
 *     "duplicate-form" = "/custom-entity-example/{custom_entity_example}/duplicate",
 *     "delete-form" = "/custom-entity-example/{custom_entity_example}/delete",
 *     "delete-multiple-form" = "/admin/content/custom-entity-example-items/delete",
 *     "collection" = "/admin/content/custom-entity-example-items",
 *   },
 *   bundle_entity_type = "custom_entity_example_type",
 *   field_ui_base_route = "entity.custom_entity_example_type.edit_form",
 * )
 */
class CustomEntityExample extends ContentEntityBase implements CustomEntityExampleInterface {

  use EntityOwnerTrait;
  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['type'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Type'))
      ->setDescription(t('The custom_entity_example type.'))
      ->setSetting('target_type', 'custom_entity_example_type')
      ->setReadOnly(TRUE);

    $fields['uid']
      ->setLabel(t('Owner'))
      ->setDescription(t('The custom_entity_example owner.'))
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The custom_entity_example name.'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
      ])      
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time when the custom_entity_example was created.'))
      ->setTranslatable(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time when the custom_entity_example was last edited.'))
      ->setTranslatable(TRUE);

    return $fields;
  }

  /**
   * Default value callback for the 'timezone' base field definition.
   *
   * @see ::baseFieldDefinitions()
   *
   * @return array
   *   An array of default values.
   */
  public static function getSiteTimezone() {
    $site_timezone = \Drupal::config('system.date')->get('timezone.default');
    if (empty($site_timezone)) {
      $site_timezone = @date_default_timezone_get();
    }

    return [$site_timezone];
  }

  /**
   * Gets the allowed values for the 'timezone' base field.
   *
   * @return array
   *   The allowed values.
   */
  public static function getTimezones() {
    return system_time_zones(NULL, TRUE);
  }

  /**
   * Gets the allowed values for the 'billing_countries' base field.
   *
   * @return array
   *   The allowed values.
   */
  public static function getAvailableCountries() {
    return \Drupal::service('address.country_repository')->getList();
  }

}
