<?php

namespace Drupal\seo_urls\EventSubscriber;

use Drupal\Core\Path\CurrentPathStack;
use Drupal\seo_urls\SeoUrlManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

/**
 * Provides a path subscriber that converts SEO urls.
 *
 * @todo Implement caching on the same/similar way as PathAliasSubscriber did.
 */
class SeoUrlSubscriber implements EventSubscriberInterface {

  /**
   * SEO url manager.
   *
   * @var \Drupal\seo_urls\SeoUrlManager
   */
  protected SeoUrlManager $seoUrlManager;

  /**
   * The current path.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected CurrentPathStack $currentPath;

  /**
   * Constructs a new SeoUrlSubscriber instance.
   *
   * @param \Drupal\seo_urls\SeoUrlManager $seoUrlManager
   *   SEO url manager.
   * @param \Drupal\Core\Path\CurrentPathStack $currentPath
   *   The current path.
   */
  public function __construct(SeoUrlManager $seoUrlManager, CurrentPathStack $currentPath) {
    $this->seoUrlManager = $seoUrlManager;
    $this->currentPath = $currentPath;
  }

  /**
   * Sets the cache key on the Seo Url manager cache decorator.
   *
   * KernelEvents::CONTROLLER is used in order to be executed after routing.
   *
   * @param \Symfony\Component\HttpKernel\Event\ControllerEvent $event
   *   The Event to process.
   */
  public function onKernelController(ControllerEvent $event) {
    // Set the cache key on the alias manager cache decorator.
    if ($event->isMainRequest()) {
      // Ignore phpstan check for now because the service is a template for now.
      // @phpstan-ignore-next-line.
      $this->seoUrlManager->setCacheKey(rtrim($this->currentPath->getPath($event->getRequest()), '/'));
    }
  }

  /**
   * Ensures system paths for the request get cached.
   */
  public function onKernelTerminate(TerminateEvent $event) {
    // Ignore phpstan check for now because the service is a template for now.
    // @phpstan-ignore-next-line.
    $this->seoUrlManager->writeCache();
  }

  /**
   * Registers the methods in this class that should be listeners.
   *
   * @return array
   *   An array of event listener definitions.
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::CONTROLLER][] = ['onKernelController', 200];
    $events[KernelEvents::TERMINATE][] = ['onKernelTerminate', 200];
    return $events;
  }

}
