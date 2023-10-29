<?php

namespace Drupal\lupus_ce_renderer\Routing;

use Drupal\Core\Routing\FilterInterface;
use Drupal\Core\Routing\RequestFormatRouteFilter;
use Drupal\Core\Site\Settings;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouteCollection;

/**
 * Overrides the request format route filter to change the default format.
 */
class CustomElementsRequestFormatRouteFilter extends RequestFormatRouteFilter implements FilterInterface {

  /**
   * {@inheritDoc}
   *
   * Overridden to set a default format depending on the incoming request.
   */
  public function filter(RouteCollection $collection, Request $request) {
    // If the request does not specify a format then use the default.
    if (is_null($request->getRequestFormat(NULL))) {
      $default_format = $this->getDefaultFormatForRequest($collection, $request);
      $request->setRequestFormat($default_format);
    }
    return parent::filter($collection, $request);
  }

  /**
   * Replaces the static::getDefaultFormat() so we can depend on the request.
   *
   * Depending on whether the renderer is enabled by default, we default to
   * render to custom_elements or to html.
   */
  protected function getDefaultFormatForRequest(RouteCollection $collection, Request $request): string {
    $formats = static::getAvailableFormats($collection);

    // The default format is applied unless ALL routes require the same format.
    // However, we do not allow using "html" if lupus_ce_renderer is active.
    if (count($formats) === 1) {
      $available_format = reset($formats);
      return $available_format == 'html' && $request->attributes->get('lupus_ce_renderer') ? 'custom_elements' : $available_format;
    }
    return $request->attributes->get('lupus_ce_renderer') ? 'custom_elements' : 'html';
  }

}
