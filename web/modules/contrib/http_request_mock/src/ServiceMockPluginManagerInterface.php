<?php

declare(strict_types = 1);

namespace Drupal\http_request_mock;

use Drupal\Component\Plugin\PluginManagerInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Interface for the service mock plugin manager.
 */
interface ServiceMockPluginManagerInterface extends PluginManagerInterface {

  /**
   * Checks all plugins, ordered by weight, and return the one that matches.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The HTTP request.
   * @param array $options
   *   The request options.
   *
   * @return \Drupal\http_request_mock\ServiceMockPluginInterface|null
   *   The plugin that satisfies the passed request or NULL if none.
   */
  public function getMatchingPlugin(RequestInterface $request, array $options): ?ServiceMockPluginInterface;

}
