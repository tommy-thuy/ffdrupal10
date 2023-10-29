<?php

namespace Drupal\lupus_ce_renderer;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Service Provider for lupus_ce_renderer.
 */
class LupusCeRendererServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $container->getDefinition('request_format_route_filter')
      ->setClass('Drupal\lupus_ce_renderer\Routing\CustomElementsRequestFormatRouteFilter');
  }

}
