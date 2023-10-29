<?php

namespace Drupal\ffw_site01\Plugin\paragraphs\Behavior;

use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\paragraphs\ParagraphsBehaviorBase;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;

/**
 * Provides a way to define grid based layouts.
 *
 * @ParagraphsBehavior(
 *   id = "custom",
 *   label = @Translation("Custom paragraphs"),
 *   description = @Translation("Add custom options to Paragraphs."),
 *   weight = 0
 * )
 */
class CustomParagraphsOptionsPlugin extends ParagraphsBehaviorBase {

  /**
   * Get this plugins Behavior settings.
   *
   * @return array
   */
  private function getSettings(ParagraphInterface $paragraph) {
    var_dump($paragraph);
    $settings = $paragraph->getAllBehaviorSettings();
    return $settings[$this->pluginId] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildBehaviorForm(ParagraphInterface $paragraph, array &$form, FormStateInterface $form_state) {
    $config = $this->getSettings($paragraph);
    $current_site = \Drupal::service('custom.site')->getSite();

    // Paragraph's layout
    $form['layout'] = [
      '#title' => $this->t('Layout'),
      '#type' => 'select',
      '#options' => [
        'contained' => $this->t('Contained'),
        'fullwidth' => $this->t('Fullwidth'),
      ],
      '#default_value' => $config['layout'] ?? 'contained',
      '#description' => $this->t('Select the layout of this block.'),
    ];

    // Paragraph's color
    $form['background'] = [
      '#title' => $this->t('Background color'),
      '#type' => 'select',
      '#options' => [
        NULL => $this->t('None'),
        'color-background--grey' => $this->t('Grey'),
      ],
      '#default_value' => $config['background'] ?? NULL,
      '#description' => $this->t('Select the background background of this block.'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function view(array &$build, Paragraph $paragraph, EntityViewDisplayInterface $display, $view_mode) {
    // Initialize defaults.
    $config = $this->getSettings($paragraph);
    $build['#attributes']['class'] = $build['#attributes']['class'] ?? [];
    $keys = ['layout', 'background'];
    foreach ($keys as $key) {
      if (isset($config[$key]) && is_string($config[$key])) {
        $build['#attributes']['class'][] = $config[$key];
      }
    }
  }

}