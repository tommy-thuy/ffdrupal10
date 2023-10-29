<?php

namespace Drupal\lupus_ce_renderer\EventSubscriber;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\RouteCollection;

/**
 * Alters routes.
 */
class CustomElementsRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Provide a CE variant of the node preview route.
    $route = $collection->get('entity.node.preview');
    $ce_route = clone $route;
    $ce_route->setRequirement('_format', 'custom_elements');
    $ce_route->setDefault('_controller', '\Drupal\lupus_ce_renderer\Controller\CustomElementsController::entityPreview');
    $collection->add('custom_elements.entity.node.preview', $ce_route);
    // Provide a CE variant of the node revision route.
    $route = $collection->get('entity.node.revision');
    $ce_route = clone $route;
    $ce_route->setRequirement('_format', 'custom_elements');
    $ce_route->setDefault('_controller', '\Drupal\lupus_ce_renderer\Controller\CustomElementsController::nodeViewRevision');
    $collection->add('custom_elements.entity.node.revision', $ce_route);
    // Provide a CE variant of the node latest version route.
    // @see \Drupal\content_moderation\Entity\Routing\EntityModerationRouteProvider
    if ($route = $collection->get('entity.node.latest_version')) {
      $ce_route = clone $route;
      $ce_route->setRequirement('_format', 'custom_elements');
      // Replace '_entity_view' with a custom elements controller.
      $ce_route_defaults = $ce_route->getDefaults();
      unset($ce_route_defaults['_entity_view']);
      $ce_route->setDefaults($ce_route_defaults);
      // Re-use the entity.node.canonical route with latest node as parameter.
      $ce_route->setDefault('_controller', '\Drupal\lupus_ce_renderer\Controller\CustomElementsController::entityView');
      $collection->add('custom_elements.entity.node.latest_version', $ce_route);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[RoutingEvents::ALTER] = ['onAlterRoutes', 0];
    return $events;
  }

}
