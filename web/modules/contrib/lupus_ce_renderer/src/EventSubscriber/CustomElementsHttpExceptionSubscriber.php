<?php

namespace Drupal\lupus_ce_renderer\EventSubscriber;

use Drupal\Component\Utility\Html;
use Drupal\Core\EventSubscriber\CustomPageExceptionHtmlSubscriber;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

/**
 * Catches http exceptions and turns it into error pages.
 *
 * Regular exceptions thrown during routing are cached already with the
 * core exception subscriber since they happen before the format is switched
 * to custom_elements. Those are turned into custom_element responses via
 * \Drupal\lupus_ce_renderer\EventSubscriber\CustomElementsViewSubscriber::handleClientErrorResponses().
 *
 * However, exception may be thrown later in the request handling stack as well,
 * then we need to catch and handle them directly.
 *
 * @see \Drupal\lupus_ce_renderer\EventSubscriber\CustomElementsViewSubscriber::handleClientErrorResponses
 */
class CustomElementsHttpExceptionSubscriber extends CustomPageExceptionHtmlSubscriber {

  /**
   * {@inheritdoc}
   */
  protected function getHandledFormats() {
    return ['custom_elements'];
  }

  /**
   * {@inheritdoc}
   */
  protected static function getPriority() {
    // Execute before subscribe which is -50.
    return -30;
  }

  /**
   * {@inheritdoc}
   */
  public function onException(ExceptionEvent $event) {
    // We need to render error pages (404,403) via custom_elements as requested
    // or configured. If there is 404/403 error and lupus_ce_renderer is
    // enabled, we need to default to ce-rendered errors unless another format
    // is specified.
    $request = $event->getRequest();
    if ($request->attributes->get('lupus_ce_renderer') && is_null($request->getRequestFormat(NULL))) {
      $request->setRequestFormat('custom_elements');
    }
    parent::onException($event);
  }

  /**
   * {@inheritdoc}
   */
  public function on403(ExceptionEvent $event) {
    $the_403_path = $this->configFactory->get('system.site')->get('page.403');
    if ($the_403_path) {
      $this->makeSubrequestToCustomPath($event, $the_403_path, Response::HTTP_FORBIDDEN);
    }
    else {
      $this->makeSubrequest($event, '/system/403', Response::HTTP_FORBIDDEN);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function on404(ExceptionEvent $event) {
    $request = $event->getRequest();

    // @see \Drupal\Core\EventSubscriber\Fast404ExceptionHtmlSubscriber
    $config = $this->configFactory->get('system.performance');
    $exclude_paths = $config->get('fast_404.exclude_paths');
    if ($config->get('fast_404.enabled') && $exclude_paths && !preg_match($exclude_paths, $request->getPathInfo())) {
      $fast_paths = $config->get('fast_404.paths');
      if ($fast_paths && preg_match($fast_paths, $request->getPathInfo())) {
        $fast_404_html = strtr($config->get('fast_404.html'), ['@path' => Html::escape($request->getUri())]);
        $response = new Response($fast_404_html, Response::HTTP_NOT_FOUND);
        $event->setResponse($response);
      }
    }
    $the_404_path = $this->configFactory->get('system.site')->get('page.404');
    if ($the_404_path) {
      $this->makeSubrequestToCustomPath($event, $the_404_path, Response::HTTP_NOT_FOUND);
    }
    else {
      $this->makeSubrequest($event, '/system/404', Response::HTTP_NOT_FOUND);
    }
  }

  /**
   * {@inheritDoc}
   */
  protected function makeSubrequest(ExceptionEvent $event, $url, $status_code) {
    $event->getRequest()->query->set('_format', 'custom_elements');
    parent::makeSubrequest($event, $url, $status_code);
  }

}
