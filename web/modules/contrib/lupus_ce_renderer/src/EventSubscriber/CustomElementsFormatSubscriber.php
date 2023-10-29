<?php

namespace Drupal\lupus_ce_renderer\EventSubscriber;

use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheableResponseInterface;
use Drupal\Core\Site\Settings;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Post-processes responses if the custom elements format is enabled.
 *
 * Note that
 * \Drupal\lupus_ce_renderer\Routing\CustomElementsRequestFormatRouteFilter
 * is making the default format 'custom_elements' when our renderer is enabled
 * already.
 */
class CustomElementsFormatSubscriber implements EventSubscriberInterface {

  /**
   * Responds to kernel responses.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   The event.
   */
  public function onKernelResponse(ResponseEvent $event) {
    $response = $event->getResponse();
    $request = $event->getRequest();

    // Dis-allow html format when custom elements rendering is enabled. Other
    // formats like 'json' should be allowed, so custom APIs can be added.
    // @todo: Make this alterable somehow.
    if ($request->attributes->get('lupus_ce_renderer') && $request->getRequestFormat('custom_elements') == 'html') {
      // Throw status 406 http exception, but take care to not run in an
      // end-less loop here.
      if (!$event->getRequest()->attributes->get('exception') || !$event->getRequest()->attributes->get('exception') instanceof NotAcceptableHttpException) {
        throw new NotAcceptableHttpException('Not accepted format.');
      }
    }

    // Ensure redirects point to the given base URL if any.
    $base_url = Settings::get('lupus_ce_renderer_redirect_base_url');
    if ($base_url && $response instanceof RedirectResponse && strpos($response->getTargetUrl(), $base_url) !== 0) {
      $target = $response->getTargetUrl();
      $request = $event->getRequest();
      $new_url = str_replace($request->getSchemeAndHttpHost(), $base_url, $target);
      if ($new_url != $target) {
        $response->setTargetUrl($new_url);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      // Run before dynamic page cache which has prio 100.
      KernelEvents::RESPONSE => ['onKernelResponse', 500],
    ];
  }

}
