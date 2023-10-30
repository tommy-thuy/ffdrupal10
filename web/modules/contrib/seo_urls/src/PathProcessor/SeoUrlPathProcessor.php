<?php

namespace Drupal\seo_urls\PathProcessor;

use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\seo_urls\SeoUrlManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Processes the inbound path using SEO Url vocabulary.
 */
class SeoUrlPathProcessor implements InboundPathProcessorInterface, OutboundPathProcessorInterface {

  /**
   * SEO url manager.
   *
   * @var \Drupal\seo_urls\SeoUrlManager|null
   */
  protected ?SeoUrlManager $seoUrlManager = NULL;

  /**
   * The service container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected ContainerInterface $container;

  /**
   * Constructs a SeoUrlPathProcessor object.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The current service container.
   */
  public function __construct(ContainerInterface $container) {
    $this->container = $container;
  }

  /**
   * Retrieve SEO URL manager service.
   *
   * @return \Drupal\seo_urls\SeoUrlManager|null
   *   SEO url manager.
   */
  public function getSeoUrlManager() {
    // We shouldn't receive this service via dependency injection.
    // The services like current user, request stack always must be actual
    // to the moment when they are called.
    if (is_null($this->seoUrlManager)) {
      $this->seoUrlManager = $this->container->get('seo_urls.manager');
    }
    return $this->seoUrlManager;
  }

  /**
   * {@inheritdoc}
   */
  public function processInbound($path, Request $request) {
    return $this->getSeoUrlManager()->getCanonicalUrlBySeoUrl($path, $request);
  }

  /**
   * {@inheritdoc}
   */
  public function processOutbound($path, &$options = [], Request $request = NULL, BubbleableMetadata $bubbleable_metadata = NULL) {
    $langcode = isset($options['language']) ? $options['language']->getId() : NULL;
    $path = $this->getSeoUrlManager()->getSeoUrlByCanonicalUrl($path, $request, $langcode);
    // Ensure the resulting path has at most one leading slash, to prevent it
    // becoming an external URL without a protocol like //example.com. This
    // is done in \Drupal\Core\Routing\UrlGenerator::generateFromRoute()
    // also, to protect against this problem in arbitrary path processors,
    // but it is duplicated here to protect any other URL generation code
    // that might call this method separately.
    if (strpos($path, '//') === 0) {
      $path = '/' . ltrim($path, '/');
    }
    return $path;
  }

}
