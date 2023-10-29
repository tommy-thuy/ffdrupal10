<?php

namespace Drupal\lupus_ce_renderer;

/**
 * Allows setter injection and simple usage of the service.
 */
trait CustomElementsRendererTrait {

  /**
   * CE Renderer.
   *
   * @var \Drupal\lupus_ce_renderer\CustomElementsRenderer
   */
  protected CustomElementsRenderer $ceRenderer;

  /**
   * Sets the CE renderer.
   *
   * @param \Drupal\lupus_ce_renderer\CustomElementsRenderer $ce_renderer
   *   Custom Element Renderer.
   *
   * @return $this
   */
  public function setCustomElementsRenderer(CustomElementsRenderer $ce_renderer) : self {
    $this->ceRenderer = $ce_renderer;
    return $this;
  }

  /**
   * Gets the CE renderer.
   *
   * @return \Drupal\lupus_ce_renderer\CustomElementsRenderer
   *   Custom elements renderer.
   */
  public function getCustomElementsRenderer() : CustomElementsRenderer {
    if (empty($this->ceRenderer)) {
      $this->ceRenderer = \Drupal::service('lupus_ce_renderer.custom_elements_renderer');
    }
    return $this->ceRenderer;
  }

}
