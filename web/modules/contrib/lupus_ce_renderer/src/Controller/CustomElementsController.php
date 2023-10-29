<?php

namespace Drupal\lupus_ce_renderer\Controller;

use drunomics\ServiceUtils\Core\Entity\EntityTypeManagerTrait;
use drunomics\ServiceUtils\Core\Routing\CurrentRouteMatchTrait;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\custom_elements\CustomElement;
use Drupal\custom_elements\CustomElementGeneratorTrait;
use Drupal\node\NodeInterface;
use Symfony\Component\Routing\Exception\InvalidParameterException;

/**
 * Custom elements controller for entity view pages.
 */
class CustomElementsController {

  use CustomElementGeneratorTrait;
  use CurrentRouteMatchTrait;
  use EntityTypeManagerTrait;
  use StringTranslationTrait;

  /**
   * Renders entity pages into custom elements.
   *
   * @param string $view_mode
   *   (optional) The view mode to use. Defaults to 'full'.
   *
   * @return \Drupal\custom_elements\CustomElement
   *   Returns CustomElement object.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function entityView($view_mode = 'full') {
    // Get the current entity type ID from the raw parameters, it's the first
    // one.
    $parameters = $this->getCurrentRouteMatch()->getRawParameters()->all();
    $entity_type_id = key($parameters);
    if (!$this->getEntityTypeManager()->getDefinition($entity_type_id)) {
      throw new \LogicException('No entity type found, but required.');
    }
    $entity = $this->getCurrentRouteMatch()->getParameter($entity_type_id);
    if (!isset($entity) || !$entity instanceof EntityInterface) {
      throw new InvalidParameterException('Missing entity parameter.');
    }

    // Generate custom elements and let the custom elements renderer into the
    // right serialization.
    $custom_element = $this->getCustomElementGenerator()->generate($entity, $view_mode);
    // Make sure the cache tag is set.
    $custom_element->addCacheTags([$entity_type_id . '_view']);
    return $custom_element;
  }

  /**
   * Preview an entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $node_preview
   *   The entity / node to preview.
   * @param string $view_mode
   *   The view mode to use.
   *
   * @return \Drupal\custom_elements\CustomElement
   *   Returns CustomElement object.
   */
  public function entityPreview(EntityInterface $node_preview, $view_mode = 'full') {
    $custom_element = $this->getCustomElementGenerator()->generate($node_preview, $view_mode);
    // Make sure the cache tag is set.
    $custom_element->addCacheTags([$node_preview->getEntityTypeId() . '_view']);
    // Disable caching for previews.
    $custom_element->mergeCacheMaxAge(0);
    return $custom_element;
  }

  /**
   * Renders entity revision into custom elements.
   *
   * @param \Drupal\node\NodeInterface $node_revision
   *   The node revision.
   * @param string $view_mode
   *   (optional) The view mode to use. Defaults to 'full'.
   *
   * @return \Drupal\custom_elements\CustomElement
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function nodeViewRevision(NodeInterface $node_revision, $view_mode = 'full') {
    // Generate custom elements and let the custom elements renderer into the
    // right serialization.
    $custom_element = $this->getCustomElementGenerator()->generate($node_revision, $view_mode);
    // Make sure the cache tag is set.
    $custom_element->addCacheTags(['node_view']);
    return $custom_element;
  }

  /**
   * Controller for the '/node' route.
   */
  public function node() {
    return CustomElement::create('drupal-markup')
      ->setSlot('default', $this->t('Welcome to your custom-elements enabled Drupal site!'));
  }

}
