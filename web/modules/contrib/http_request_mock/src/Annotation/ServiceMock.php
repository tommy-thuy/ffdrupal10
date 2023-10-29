<?php

declare(strict_types = 1);

namespace Drupal\http_request_mock\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a service mock plugin annotation object.
 *
 * @Annotation
 */
class ServiceMock extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The plugin label.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * The weight of this plugin.
   *
   * @var int
   */
  public $weight = 0;

}
