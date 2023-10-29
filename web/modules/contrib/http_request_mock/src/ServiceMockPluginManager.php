<?php

declare(strict_types = 1);

namespace Drupal\http_request_mock;

use Drupal\Component\Utility\SortArray;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\State\StateInterface;
use Drupal\http_request_mock\Annotation\ServiceMock;
use Psr\Http\Message\RequestInterface;

/**
 * Provides a default implementation for 'plugin.manager.service_mock' service.
 */
class ServiceMockPluginManager extends DefaultPluginManager implements ServiceMockPluginManagerInterface {

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Constructs a new service instance.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler, StateInterface $state) {
    parent::__construct('Plugin/ServiceMock', $namespaces, $module_handler, ServiceMockPluginInterface::class, ServiceMock::class);
    $this->alterInfo('service_mock_info');
    $this->setCacheBackend($cache_backend, 'service_mock_plugins');
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public function getMatchingPlugin(RequestInterface $request, array $options): ?ServiceMockPluginInterface {
    $allowed_plugins = $this->state->get('http_request_mock.allowed_plugins');
    /** @var \Drupal\http_request_mock\ServiceMockPluginInterface $plugin */
    foreach ($this->getDefinitions() as $plugin_id => $definition) {
      // Allow tests to limit to a list of plugins.
      if (!$allowed_plugins || in_array($plugin_id, $allowed_plugins, TRUE)) {
        $plugin = $this->createInstance($plugin_id);
        if ($plugin->applies($request, $options)) {
          return $plugin;
        }
      }
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  protected function findDefinitions(): array {
    $definitions = parent::findDefinitions();
    // Sort by weight. We're doing the sort in this method because is just
    // before the definitions are cached.
    uasort($definitions, [SortArray::class, 'sortByWeightElement']);
    return $definitions;
  }

}
