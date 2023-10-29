<?php

declare(strict_types = 1);

namespace Drupal\http_request_mock\Plugin\ServiceMock;

use Drupal\Core\Plugin\PluginBase;
use Drupal\http_request_mock\ServiceMockPluginInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Intercepts any HTTP request made to example.com.
 *
 * @ServiceMock(
 *   id = "example_com",
 *   label = @Translation("example.com"),
 *   weight = 0,
 * )
 */
class ExampleComPlugin extends PluginBase implements ServiceMockPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(RequestInterface $request, array $options): bool {
    return $request->getUri()->getHost() === 'example.com';
  }

  /**
   * {@inheritdoc}
   */
  public function getResponse(RequestInterface $request, array $options): ResponseInterface {
    return new Response(200, [], 'Mocking example.com response');
  }

}
