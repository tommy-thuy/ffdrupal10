<?php

namespace Drupal\smart_content\Condition\Type;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Provides the Smart condition type plugin manager.
 */
class ConditionTypeManager extends DefaultPluginManager {

  /**
   * Constructor for ConditionTypeManager objects.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/smart_content/Condition/Type', $namespaces,
      $module_handler, 'Drupal\smart_content\Condition\Type\ConditionTypeInterface', 'Drupal\smart_content\Annotation\SmartConditionType');

    $this->alterInfo('smart_content_smart_condition_type_info');
    $this->setCacheBackend($cache_backend, 'smart_content_smart_condition_type_plugins');
  }

}
