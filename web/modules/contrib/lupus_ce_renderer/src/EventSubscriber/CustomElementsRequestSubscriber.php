<?php

namespace Drupal\lupus_ce_renderer\EventSubscriber;

use Drupal\Core\Routing\CacheableRouteProviderInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\Site\Settings;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Request subscriber for customizing route cache IDs.
 */
class CustomElementsRequestSubscriber implements EventSubscriberInterface {

  /**
   * The route provider.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * Constructs the object.
   *
   * @param \Drupal\Core\Routing\RouteProviderInterface $routeProvider
   *   The route provider.
   */
  public function __construct(RouteProviderInterface $routeProvider) {
    $this->routeProvider = $routeProvider;
  }

  /**
   * Handles the request.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The event.
   */
  public function onRequest(RequestEvent $event) {
    $request = $event->getRequest();

    // Make it easy to check whether the lupus_ce_renderer is enabled by
    // making sure it gets set into the attributes.
    if (Settings::get('lupus_ce_renderer_enable', FALSE)) {
      $request->attributes->set('lupus_ce_renderer', TRUE);
    }

    // Disable Redirect module normalization if this is a lupus_ce_renderer
    // request.
    if ($request->attributes->get('lupus_ce_renderer')) {
      $request->attributes->set('_disable_route_normalizer', TRUE);
    }

    // We change the routing default format. Make sure the routing system
    // takes that into account when caching.
    if ($this->routeProvider instanceof CacheableRouteProviderInterface) {
      $this->routeProvider->addExtraCacheKeyPart('lupus_ce_renderer', $request->attributes->get('lupus_ce_renderer') ? 1 : 0);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      // Must run before Symfony's router listener (priority 32)
      // @see \Symfony\Component\HttpKernel\EventListener\RouterListener::getSubscribedEvents()
      KernelEvents::REQUEST => ['onRequest', 40],
    ];
  }

}
