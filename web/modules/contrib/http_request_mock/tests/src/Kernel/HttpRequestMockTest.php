<?php

namespace Drupal\Tests\http_request_mock\Kernel;

use Drupal\KernelTests\KernelTestBase;
use GuzzleHttp\Psr7\Request;

/**
 * Tests the HTTP layer mocking.
 *
 * @group http_request_mock
 */
class HttpRequestMockTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['http_request_mock'];

  /**
   * Tests a simple mocking of any HTTP request made to example.com.
   */
  public function testGuzzleMiddleware(): void {
    $http_client = $this->container->get('http_client');
    $response = $http_client->get('http://example.com/foo/bar/baz');
    $this->assertSame('Mocking example.com response', $response->getBody()->getContents());

    // Check that limiting to a list of plugins really works.
    $this->container->get('state')->set('http_request_mock.allowed_plugins', [
      'other_plugin',
    ]);
    $request = new Request('GET', 'http://example.com/foo/bar/baz');
    $this->assertNull($this->container->get('plugin.manager.service_mock')->getMatchingPlugin($request, []));
  }

}
