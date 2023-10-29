<?php

namespace Drupal\lupus_ce_renderer;

/**
 * Allows setter injection and simple usage of the service.
 */
trait CustomElementsMetatagsGeneratorTrait {

  /**
   * Custom elements metatags generator.
   *
   * @var \Drupal\lupus_ce_renderer\CustomElementsMetatagsGenerator
   */
  protected CustomElementsMetatagsGenerator $ceMetatagsGenerator;

  /**
   * Sets the CE metatags generator.
   *
   * @param \Drupal\lupus_ce_renderer\CustomElementsMetatagsGenerator $ceMetatagsGenerator
   *   Custom elements metatags generator service.
   *
   * @return $this
   */
  public function setCeMetagasGenerator(CustomElementsMetatagsGenerator $ceMetatagsGenerator) : self {
    $this->ceMetatagsGenerator = $ceMetatagsGenerator;
    return $this;
  }

  /**
   * Gets the CE metatags generator.
   *
   * @return \Drupal\lupus_ce_renderer\CustomElementsMetatagsGenerator
   *   Custom elements metatags generator.
   */
  public function getCeMetagasGenerator() : CustomElementsMetatagsGenerator {
    if (empty($this->ceMetatagsGenerator)) {
      $this->ceMetatagsGenerator = \Drupal::service('lupus_ce_renderer.ce_metatags_generator');
    }
    return $this->ceMetatagsGenerator;
  }

}
