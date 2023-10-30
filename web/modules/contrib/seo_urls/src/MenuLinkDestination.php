<?php

declare(strict_types = 1);

namespace Drupal\seo_urls;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Security\TrustedCallbackInterface;

/**
 * Provides a pre-render callback for links.
 */
class MenuLinkDestination implements TrustedCallbackInterface {

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return ['preRenderLink'];
  }

  /**
   * Adds cache metadata to menu links with destination.
   *
   * @param array $element
   *   The link element.
   *
   * @return array
   *   The returning link element.
   */
  public static function preRenderLink(array $element): array {
    /** @var \Drupal\Core\Url|null $url */
    $url = $element['#url'] ?? NULL;

    if ($url && $url->isRouted() && $url->getRouteName() === 'seo_url.create') {
      (new CacheableMetadata())
        // Such links cache is varying by URL.
        ->setCacheContexts(['url'])
        ->applyTo($element);
    }

    return $element;
  }

}
