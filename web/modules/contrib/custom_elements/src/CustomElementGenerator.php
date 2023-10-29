<?php

namespace Drupal\custom_elements;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\custom_elements\Processor\CustomElementProcessorInterface;

/**
 * Service to preprocess template variables for custom elements.
 */
class CustomElementGenerator {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Array of all processors and their priority.
   *
   * @var array
   */
  protected $processorByPriority = [];

  /**
   * Sorted list of registered processors.
   *
   * @var \Drupal\custom_elements\Processor\CustomElementProcessorInterface[]
   */
  protected $sortedProcessors;

  /**
   * CustomElementGenerator constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   */
  public function __construct(ModuleHandlerInterface $moduleHandler, EntityRepositoryInterface $entity_repository) {
    $this->moduleHandler = $moduleHandler;
    $this->entityRepository = $entity_repository;
  }

  /**
   * Adds a processor.
   *
   * @param \Drupal\custom_elements\Processor\CustomElementProcessorInterface $processor
   *   The processor to add.
   * @param int $priority
   *   The priority for the processor.
   */
  public function addProcessor(CustomElementProcessorInterface $processor, $priority = 0) {
    $this->processorByPriority[$priority][] = $processor;
    // Force the processors to be re-sorted.
    $this->sortedProcessors = NULL;
  }

  /**
   * Gets an array of processors, sorted by their priority.
   *
   * @return \Drupal\custom_elements\Processor\CustomElementProcessorInterface[]
   *   Returns sorted processors.
   */
  public function getSortedProcessors() {
    if (!isset($this->sortedProcessors)) {
      // Sort the processors according to priority.
      krsort($this->processorByPriority);

      // Merge nested processors from $this->processors
      // into $this->sortedProviders.
      $this->sortedProcessors = [];
      foreach ($this->processorByPriority as $processors) {
        $this->sortedProcessors = array_merge($this->sortedProcessors, $processors);
      }
    }
    return $this->sortedProcessors;
  }

  /**
   * Generates a custom element tag for the given entity and view mode.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Entity to process.
   * @param string $viewMode
   *   View mode used for rendering field values into slots.
   * @param string|null $langcode
   *   (optional) For which language the entity should be rendered, defaults to
   *   the current content language.
   *
   * @return \Drupal\custom_elements\CustomElement
   *   Extracted custom elements containing data attributes and slots.
   */
  public function generate(ContentEntityInterface $entity, string $viewMode, string $langcode = NULL) {
    $custom_element = new CustomElement();
    // Get desired entity translation.
    $entity = $this->entityRepository->getTranslationFromContext($entity, $langcode);
    $custom_element->addCacheableDependency($entity);

    // By default output tags like drupal-node, drupal-comment and for
    // paragraphs pg-text, pg-image etc.
    if ($entity->getEntityTypeId() == 'paragraph') {
      $tag = 'pg-' . $entity->bundle();
      $prefix = '';
    }
    elseif ($entity->getEntityTypeId() == 'node') {
      $tag = 'node';
      $prefix = '';
    }
    else {
      $tag = $entity->getEntityTypeId();
      $prefix = 'drupal';
    }
    $custom_element->setTag($tag);
    $custom_element->setTagPrefix($prefix);

    // Add the bundle as type.
    if ($entity->bundle() != $entity->getEntityTypeId()) {
      $custom_element->setAttribute('type', $entity->bundle());
    }
    // Add the view mode.
    $custom_element->setAttribute('view-mode', $viewMode);

    // Allow altering the element for the given before and after.
    $this->moduleHandler->alter('custom_element_entity_defaults', $custom_element, $entity, $viewMode);
    $this->process($entity, $custom_element, $viewMode);
    $this->moduleHandler->alter('custom_element_entity', $custom_element, $entity, $viewMode);

    return $custom_element;
  }

  /**
   * Process the given data and adds it to the custom element.
   *
   * @param mixed $data
   *   The data.
   * @param \Drupal\custom_elements\CustomElement $custom_element
   *   The custom element to which to add it.
   * @param string $viewMode
   *   The current view-mode.
   */
  public function process($data, CustomElement $custom_element, $viewMode) {
    foreach ($this->getSortedProcessors() as $processor) {
      if ($processor->supports($data, $viewMode)) {
        $processor->addtoElement($data, $custom_element, $viewMode);
        break;
      }
    }
  }

}
