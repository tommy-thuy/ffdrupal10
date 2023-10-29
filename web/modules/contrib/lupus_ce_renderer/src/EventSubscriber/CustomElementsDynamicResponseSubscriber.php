<?php

namespace Drupal\lupus_ce_renderer\EventSubscriber;

use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Drupal\lupus_ce_renderer\Cache\CustomElementsJsonResponse;
use Drupal\lupus_ce_renderer\CustomElementsRenderer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Add uncacheable content to response and keep dynamic page cache works.
 *
 * @see \Drupal\Core\Render\MainContent\HtmlRenderer
 * @see \Drupal\Core\Cache\CacheableResponseInterface
 */
class CustomElementsDynamicResponseSubscriber implements EventSubscriberInterface {

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
   * Stores a response in case of a Dynamic Page Cache miss, if cacheable.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   The event to process.
   */
  public function onResponse(ResponseEvent $event) {
    $response = $event->getResponse();
    // Do not add dynamic data to redirects.
    if ($response instanceof CustomElementsJsonResponse && !$response->isRedirect()) {
      if ($additional_content = $this->customElementRenderer->getDynamicContent()) {
        $response_data = $response->getResponseData();
        $response->setData($response_data + $additional_content);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    // Run after DynamicPageCacheSubscriber::onRespond(),
    // which has priority 100.
    $events[KernelEvents::RESPONSE][] = ['onResponse', 10];
    return $events;
  }

}
