<?php

namespace Drupal\lupus_ce_renderer\EventSubscriber;

use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Drupal\trusted_redirect\EventSubscriber\TrustedRedirectSubscriber;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use drunomics\ServiceUtils\Core\Routing\CurrentRouteMatchTrait;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheableResponseInterface;
use Drupal\Core\Site\Settings;
use Drupal\custom_elements\CustomElement;
use Drupal\lupus_ce_renderer\CustomElementsRenderer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * View subscriber that renders custom elements into responses.
 */
class CustomElementsViewSubscriber implements EventSubscriberInterface {

  use CurrentRouteMatchTrait;

  /**
   * The custom element renderer.
   *
   * @var \Drupal\lupus_ce_renderer\CustomElementsRenderer
   */
  protected $customElementRenderer;

  /**
   * Constructs the object.
   *
   * @param \Drupal\lupus_ce_renderer\CustomElementsRenderer $customElementRenderer
   *   The custom element renderer.
   */
  public function __construct(CustomElementsRenderer $customElementRenderer) {
    $this->customElementRenderer = $customElementRenderer;
  }

  /**
   * Sets a response given a custom element.
   *
   * @param \Symfony\Component\HttpKernel\Event\ViewEvent $event
   *   The event to process.
   */
  public function onKernelView(ViewEvent $event) {
    $request = $event->getRequest();

    // Errors are handled by CustomElementsHttpExceptionSubscriber already.
    // But if there is no custom-error page configured, the system-default
    // error pages apply, which render in HTML. We handle this case here.
    // @todo: Re-factor this to provide custom-enabled versions of system
    // module provided routes (system.4**) instead and remove special-handling
    // here.
    if ($request->getRequestFormat() === 'custom_elements' && !$event->getControllerResult() instanceof CustomElement) {
      $this->handleClientErrorResponses($event);
    }

    $result = $event->getControllerResult();
    if ($result instanceof Response) {
      return;
    }

    // Render the controller result into a response if it's a render array.
    if ($result instanceof CustomElement) {
      $content_format = $this->customElementRenderer->getContentFormatFromRequest($request);
      $wrapper_format = $request->query->get('_select');
      $response = $this->customElementRenderer->renderResponse($result, $content_format, $wrapper_format);

      // Add cache metadata for query arguments.
      if ($response instanceof CacheableResponseInterface) {
        $cache_metadata = (new CacheableMetadata())->setCacheContexts([
          'url.query_args:_content_format',
          'url.query_args:_select',
        ]);
        $response->addCacheableDependency($cache_metadata);
      }
      $event->setResponse($response);
    }
    elseif ($request->getRequestFormat() == 'custom_elements') {
      // Custom elements requested, but the route does not handle it.
      // When the backend route does not support the custom_elements format, we
      // issue a redirect response that makes the frontend redirect to the
      // backend. That way users accessing routes like /admin or node/x/edit
      // become redirected to the working backend routes, when logged-in.
      $event->setResponse(new TrustedRedirectResponse(
        Url::fromUserInput($request->getRequestUri(), ['absolute' => TRUE])->toString()
      ));
      // In RedirectResponseSubscriber destination query parameter overrides
      // the url redirect target. For admin pages these redirects are not
      // desired.
      $request->query->remove('destination');
      // Note that the redirect is picked up by our RedirectResponseSubsriber
      // and turned into a redirect-API-response, which the frontend applies.
    }
  }

  /**
   * Handles http 4xx responses gracefully.
   *
   * @param \Symfony\Component\HttpKernel\Event\ViewEvent $event
   *   The event to process.
   *
   * @see \Drupal\lupus_ce_renderer\EventSubscriber\CustomElementsHttpExceptionSubscriber
   */
  protected function handleClientErrorResponses(ViewEvent $event) {
    // Gracefully handle 401, 403 and 404 routes with a simple #markup key.
    $result = $event->getControllerResult();
    if (!$result instanceof CustomElement) {
      $status_code = $event->getRequest()->get('_exception_statuscode');
      if (strpos($this->getCurrentRouteMatch()->getRouteName(), 'system.4') === 0) {
        $event->setControllerResult(CustomElement::create('drupal-markup')
          ->setSlot('default', $result['#markup'])
        );
      }
      elseif (isset($status_code) && in_array($status_code, [401, 403, 404])) {
        $event->setControllerResult(CustomElement::create('drupal-markup')
          ->setSlotFromRenderArray('default', $result)
        );
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Run before MainContentViewSubscriber so we can handle 40x responses.
    $events[KernelEvents::VIEW][] = ['onKernelView', 100];
    return $events;
  }

}
