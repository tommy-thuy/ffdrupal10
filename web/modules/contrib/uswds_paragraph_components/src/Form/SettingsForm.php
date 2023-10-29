<?php

namespace Drupal\uswds_paragraph_components\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\uswds_paragraph_components\Utils\UswdsParagraphComponentsHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for USWDS Paragraph Components settings.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * Entity Type Manager variable.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Entity Field Manager variable.
   *
   * @var \Drupal\Core\Entity\EntityFieldManager
   */
  protected EntityFieldManagerInterface $entityFieldManager;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entityTypeManager, EntityFieldManagerInterface $entityFieldManager, MessengerInterface $messenger) {
    parent::__construct($config_factory);
    $this->entityTypeManager = $entityTypeManager;
    $this->entityFieldManager = $entityFieldManager;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('messenger'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'uswds_paragraph_components_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $options = [];
    $types = $this->entityTypeManager->getStorage('paragraphs_type')->loadMultiple();
    foreach ($types as $type) {
      if (str_starts_with($type->id(), 'uswds')) {
        $options[$type->id()] = $type->label();
      }
    }

    $form['warning'] = [
      '#type' => 'item',
      '#markup' => '<strong>This will reset the selected paragraph bundles to module settings. Any custom changes will be erased! You have been warned</strong>',
    ];

    $form['uswds_paragraph_bundles'] = [
      '#type' => 'checkboxes',
      '#options' => $options,
      '#title' => $this->t('Paragraph bundles to reset'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $bundles_replace = array_filter($form_state->getValue('uswds_paragraph_bundles'));
    $entityFieldManager = $this->entityFieldManager;
    $databaseConfig = [];
    $finalConfig = [];
    foreach ($bundles_replace as $bundle) {
      $paragraph_type = $this->entityTypeManager->getStorage('paragraphs_type')->load($bundle);
      $databaseConfig[] = $paragraph_type->getConfigDependencyName();
      $databaseConfig[] = 'core.entity_form_display.paragraph.' . $bundle . '.default';
      $databaseConfig[] = 'core.entity_view_display.paragraph.' . $bundle . '.default';

      foreach ($entityFieldManager->getFieldDefinitions('paragraph', $bundle) as $field_name => $field_definition) {
        if (!empty($field_definition->getTargetBundle())) {

          if (isset($entityFieldManager->getFieldStorageDefinitions('paragraph')[$field_name])) {
            $storage = $entityFieldManager->getFieldStorageDefinitions('paragraph')[$field_name];
            $databaseConfig[] = 'field.storage.paragraph.' . $storage->getName();
          }
          $databaseConfig[] = 'field.field.paragraph.' . $bundle . '.' . $field_name;
        }
      }

      unset($databaseConfig[5]);
      $hardSetList = UswdsParagraphComponentsHelper::getConfigListByBundle($bundle);
      $finalConfig = array_unique(array_merge($hardSetList, $databaseConfig));
    }

    $output = UswdsParagraphComponentsHelper::updateExistingConfig($finalConfig);
    $message = '';
    if (!empty($output['updated'])) {
      foreach ($output['updated'] as $updated) {
        $message .= 'Updated: ' . $updated . PHP_EOL;
      }
    }

    if (!empty($output['created'])) {
      foreach ($output['created'] as $created) {
        $message .= 'Created: ' . $created . PHP_EOL;
      }
    }

    $this->messenger->addMessage($message);
    parent::submitForm($form, $form_state);
  }

}
