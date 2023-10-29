<?php

namespace Drupal\custom_entity_example\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the custom_entity_example type entity class.
 *
 * @ConfigEntityType(
 *   id = "custom_entity_example_type",
 *   label = @Translation("Custom Entity Example type", context = "Custom Entity Modules"),
 *   label_collection = @Translation("Custom Entity Example types", context = "Custom Entity Modules"),
 *   label_singular = @Translation("custom entity example type", context = "Custom Entity Modules"),
 *   label_plural = @Translation("custom entity example types", context = "Custom Entity Modules"),
 *   label_count = @PluralTranslation(
 *     singular = "@count custom entity example type",
 *     plural = "@count custom entity example types",
 *     context = "Custom Entity Modules",
 *   ),
 *   handlers = {
 *     "access" = "Drupal\entity\BundleEntityAccessControlHandler",
 *     "list_builder" = "Drupal\custom_entity_example\CustomEntityExampleTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\custom_entity_example\Form\CustomEntityExampleTypeForm",
 *       "edit" = "Drupal\custom_entity_example\Form\CustomEntityExampleTypeForm",
 *       "duplicate" = "Drupal\custom_entity_example\Form\CustomEntityExampleTypeForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     },
 *     "local_task_provider" = {
 *       "default" = "Drupal\entity\Menu\DefaultEntityLocalTaskProvider",
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer custom_entity_example_type",
 *   config_prefix = "custom_entity_example_type",
 *   bundle_of = "custom_entity_example",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "uuid",
 *     "description",
 *     "traits",
 *     "locked",
 *   },
 *   links = {
 *     "add-form" = "/admin/content/custom-entity-example-types/add",
 *     "edit-form" = "/admin/content/custom-entity-example-types/{custom_entity_example_type}/edit",
 *     "duplicate-form" = "/admin/content/custom-entity-example-types/{custom_entity_example_type}/duplicate",
 *     "delete-form" = "/admin/content/custom-entity-example-types/{custom_entity_example_type}/delete",
 *     "collection" = "/admin/content/custom-entity-example-types",
 *   }
 * )
 */
class CustomEntityExampleType extends ConfigEntityBundleBase implements CustomEntityExampleTypeInterface {

  /**
   * A brief description of this custom_entity_example type.
   *
   * @var string
   */
  protected $description;

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($description) {
    $this->description = $description;
    return $this;
  }

}
