<?php

declare(strict_types = 1);

namespace Drupal\http_request_mock;

use GuzzleHttp\Promise\FulfilledPromise;
use Psr\Http\Message\RequestInterface;

/**
 * Middleware that intercepts the outgoing HTTP request and respond with a mock.
 */
class HttpRequestMockMiddleware {

  /**
   * The "service mock" plugin manager service.
   *
   * @var \Drupal\http_request_mock\ServiceMockPluginManagerInterface
   */
  protected $serviceMockPluginManager;

  /**
   * Constructs a new HTTP client middleware service.
   *
   * @param \Drupal\http_request_mock\ServiceMockPluginManagerInterface $service_mock_plugin_manager
   *   The "service mock" plugin manager service.
   */
  public function __construct(ServiceMockPluginManagerInterface $service_mock_plugin_manager) {
    $this->serviceMockPluginManager = $service_mock_plugin_manager;
  }

  /**
   * Returns a callback to handle the outgoing HTTP request.
   *
   * @return callable
   *   Callback to handle the outgoing HTTP request.
   */
  public function __invoke(): callable {
    return function (callable $handler): callable {
      return function (RequestInterface $request, array $options) use ($handler) {
        if ($plugin = $this->serviceMockPluginManager->getMatchingPlugin($request, $options)) {
          $response = $plugin->getResponse($request, $options);
          return new FulfilledPromise($response);
        }
        // Defer to the handler stack.
        return $handler($request, $options);
      };
    };
  }

}
