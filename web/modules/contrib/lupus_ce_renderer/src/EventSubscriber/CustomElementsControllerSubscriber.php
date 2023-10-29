<?php

namespace Drupal\lupus_ce_renderer\EventSubscriber;

use Symfony\Component\HttpKernel\Event\ControllerEvent;
use drunomics\ServiceUtils\Core\Entity\EntityTypeManagerTrait;
use drunomics\ServiceUtils\Symfony\HttpFoundation\RequestStackTrait;
use Drupal\Core\Controller\ControllerResolverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Replaces the controller for entity routes if custom elements is enabled.
 */
class CustomElementsControllerSubscriber implements EventSubscriberInterface {

  use EntityTypeManagerTrait;
  use RequestStackTrait;

  /**
   * The controller resolver.
   *
   * @var \Drupal\Core\Controller\ControllerResolverInterface
   */
  protected $controllerResolver;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Controller\ControllerResolverInterface $controller_resolver
   *   The controller resolver.
   */
  public function __construct(ControllerResolverInterface $controller_resolver) {
    $this->controllerResolver = $controller_resolver;
  }

  /**
   * Take over entity view routes.
   *
   * @param \Symfony\Component\HttpKernel\Event\ControllerEvent $event
   *   The event.
   */
  public function onKernelController(ControllerEvent $event) {
    if ($event->getRequest()->getRequestFormat() != 'custom_elements') {
      return;
    }
    $matches = [];
    if (preg_match('/^entity\.([a-z_]*)\.canonical$/', $event->getRequest()->get('_route'), $matches) && $this->getEntityTypeManager()->getDefinition($matches[1])) {
      $controller_definition = '\Drupal\lupus_ce_renderer\Controller\CustomElementsController::entityView';
      $new_controller = $this->controllerResolver->getControllerFromDefinition($controller_definition);

      $event->setController(function () use ($new_controller) {
        $response = call_user_func($new_controller);
        return $response;
      });
    }
    else {
      // Else handle the request as usually. Modules may implement routes
      // and return CustomElement objects as usual, or overtake other routes as
      // we did.
      // However, EarlyRenderingControllerWrapperSubscriber messes with our
      // response, even if it's not a render array. So re-set the original
      // controller.
      $event->setController($this->controllerResolver->getController($this->getCurrentRequest()));
    }

  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      // Run after EarlyRenderingControllerWrapperSubscriber and generally
      // last, so our controller is taken.
      KernelEvents::CONTROLLER => ['onKernelController', -300],
    ];
  }

}
