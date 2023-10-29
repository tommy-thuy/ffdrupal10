<?php

/**
 * @file
 * Drupal api docs.
 */

use Drupal\Core\Render\BubbleableMetadata;
use Symfony\Component\HttpFoundation\Request;

/**
 * Alters response data of the custom elements renderer.
 *
 * This hook applies after response data is modified by data provided from
 * lupus_ce_renderer_response_data property from request attributes.
 *
 * @param array $data
 *   The response data.
 * @param \Drupal\Core\Render\BubbleableMetadata $bubbleable_metadata
 *   The cache metadata.
 * @param \Symfony\Component\HttpFoundation\Request $request
 *   The request being handled.
 */
function hook_lupus_ce_renderer_response_alter(array $data, BubbleableMetadata $bubbleable_metadata, Request $request) {
  $data['title'] = 'foo';
}
