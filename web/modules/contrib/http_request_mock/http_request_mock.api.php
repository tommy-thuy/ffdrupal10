<?php

/**
 * @file
 * Hooks specific to the HTTP Request Mock module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Allows to change the list of service mock plugin definitions.
 *
 * NOTE: Tests can use also the `http_request_mock.allowed_plugins` state
 * variable to limit the list of plugins to a certain set.
 *
 * @param array $plugins
 *   An associative list of plugin definitions keyed by plugin ID.
 *
 * @see \Drupal\http_request_mock\ServiceMockPluginManager::getMatchingPlugin()
 */
function hook_service_mock_info_alter(array &$plugins): void {
  // Remove the 'example_com' plugin.
  unset($plugins['example_com']);
  // Change 'foo' plugin weight.
  $plugins['foo']['weight'] = 1000;
  // Swap the class of 'bar' plugin.
  $plugins['bar']['class'] = '\My\Space\PluginClass';
}

/**
 * @} End of "addtogroup hooks".
 */
