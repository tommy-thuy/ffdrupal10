<?php

namespace Drupal\seo_urls\Event;

use Drupal\Component\EventDispatcher\Event;

/**
 * Event to remove path prefix.
 *
 * Required for 'seo_url' token if some custom URL prefix exists.
 */
class ClearPathPrefixEvent extends Event {

  public const EVENT_NAME = 'clear_path_prefix';

  /**
   * The path.
   *
   * @var string
   */
  private string $path;

  /**
   * Construct a ClearPathPrefixEvent object.
   *
   * @param string $path
   *   The path.
   */
  public function __construct(string $path) {
    $this->path = $path;
  }

  /**
   * Get the path.
   *
   * @return string
   *   The path.
   */
  public function getPath() {
    return $this->path;
  }

  /**
   * Set back the transformed path.
   *
   * @param string $path
   *   The path.
   */
  public function setPath(string $path): void {
    $this->path = $path;
  }

}
