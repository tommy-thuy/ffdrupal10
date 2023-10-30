<?php

namespace Drupal\seo_urls\EventSubscriber;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\RouteCollection;

/**
 * Subscriber for Field UI routes.
 */
class SeoUrlRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    $disable_routes = [
      'entity.seo_url.canonical',
      'entity.entity_view_display.seo_url.default',
      'entity.entity_view_display.seo_url.view_mode',
    ];
    foreach ($disable_routes as $route_name) {
      $route = $collection->get($route_name);
      $route->setRequirement('_access', 'FALSE');
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Disable some routes provided by Drupal\field_ui\Routing\RouteSubscriber.
    $events = parent::getSubscribedEvents();
    $events[RoutingEvents::ALTER] = ['onAlterRoutes', -110];
    return $events;
  }

}
