<?php

namespace Drupal\seo_urls\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\seo_urls\Entity\SeoUrlInterface;
use Drupal\seo_urls\SeoUrlManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides route responses for entity.seo_url.add-form.
 */
class SeoUrlCreateController extends ControllerBase {

  /**
   * SEO Url manager interface.
   *
   * @var \Drupal\seo_urls\SeoUrlManagerInterface
   */
  protected SeoUrlManagerInterface $seoUrlManager;

  /**
   * The currently active route match object.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected RouteMatchInterface $routeMatch;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->seoUrlManager = $container->get('seo_urls.manager');
    $instance->routeMatch = $container->get('current_route_match');
    return $instance;
  }

  /**
   * Returns a form to add a new SEO URL.
   *
   * @return array
   *   The SEO URL add form.
   */
  public function addForm() {
    /** @var \Drupal\seo_urls\Entity\SeoUrlInterface $entity */
    $entity = $this->entityTypeManager()
      ->getStorage(SeoUrlInterface::ENTITY_TYPE)
      ->create();

    // Set the default canonical Url.
    if ($redirect_path = $this->getRedirectDestination()->get()) {
      $entity->set(SeoUrlInterface::CANONICAL_URL_FIELD, [
        'uri' => 'internal:' . $this->seoUrlManager->clearPathPrefix($redirect_path),
        'title' => '',
        'options' => [],
      ]);
    }

    return $this->entityFormBuilder()->getForm($entity);
  }

  /**
   * Access.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   Whether the current content type is allowed to create SEO Url.
   */
  public function access(): AccessResult {
    $parameters = $this->routeMatch->getParameters()->all();
    $allowed_entity_types = $this->seoUrlManager->getAllowedEntityTypes();
    $filtered_parameters = array_intersect_key($parameters, array_fill_keys($allowed_entity_types, TRUE));
    return AccessResult::allowedIf(!empty($filtered_parameters) || $this->routeMatch->getRouteName() === 'seo_url.create');
  }

}
