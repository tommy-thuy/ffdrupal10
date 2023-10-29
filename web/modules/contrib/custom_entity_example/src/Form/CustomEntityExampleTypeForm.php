<?php

namespace Drupal\custom_entity_example\Form;

use Drupal\Core\Entity\BundleEntityFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\entity\Form\EntityDuplicateFormTrait;
use Drupal\language\Entity\ContentLanguageSettings;

class CustomEntityExampleTypeForm extends BundleEntityFormBase {

  use EntityDuplicateFormTrait;

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    /** @var \Drupal\custom_entity_example\Entity\CustomEntityExampleTypeInterface $custom_entity_example_type */
    $custom_entity_example_type = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $custom_entity_example_type->label(),
      '#required' => TRUE,
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $custom_entity_example_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\custom_entity_example\Entity\CustomEntityExampleType::load',
      ],
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#disabled' => !$custom_entity_example_type->isNew(),

    ];
    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#description' => $this->t('This text will be displayed on the <em>Add custom entity example item</em> page.'),
      '#default_value' => $custom_entity_example_type->getDescription(),
    ];

    if ($this->moduleHandler->moduleExists('language')) {
      $form['language'] = [
        '#type' => 'details',
        '#title' => $this->t('Language settings'),
        '#group' => 'additional_settings',
      ];
      $form['language']['language_configuration'] = [
        '#type' => 'language_configuration',
        '#entity_information' => [
          'entity_type' => 'custom_entity_example',
          'bundle' => $custom_entity_example_type->id(),
        ],
        '#default_value' => ContentLanguageSettings::loadByEntityTypeBundle('custom_entity_example', $custom_entity_example_type->id()),
      ];
      $form['#submit'][] = 'language_configuration_element_submit';
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $this->entity->save();

    $this->messenger()->addMessage($this->t('Saved the %label custom_entity_example type.', [
      '%label' => $this->entity->label(),
    ]));
    $form_state->setRedirect('entity.custom_entity_example_type.collection');
  }

}
