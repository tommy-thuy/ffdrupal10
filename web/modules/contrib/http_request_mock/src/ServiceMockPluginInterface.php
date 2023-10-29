<?php

declare(strict_types = 1);

namespace Drupal\http_request_mock;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Provides an interface for service test plugins.
 */
interface ServiceMockPluginInterface {

  /**
   * Checks if this plugin is qualifying to handle this request.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The HTTP request.
   * @param array $options
   *   The request options.
   *
   * @return bool
   *   If this plugin is qualifying to handle this request.
   */
  public function applies(RequestInterface $request, array $options): bool;

  /**
   * The response to be returned if this plugin applies.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The HTTP request.
   * @param array $options
   *   The request options.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   The HTTP response.
   */
  public function getResponse(RequestInterface $request, array $options): ResponseInterface;

}
