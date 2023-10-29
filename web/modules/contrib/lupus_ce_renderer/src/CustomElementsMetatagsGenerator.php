<?php

namespace Drupal\lupus_ce_renderer;

use drunomics\ServiceUtils\Core\Routing\CurrentRouteMatchTrait;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\Url;
use Drupal\metatag\MetatagManagerInterface;

/**
 * Generates metatags for custom elements.
 */
class CustomElementsMetatagsGenerator {

  use CurrentRouteMatchTrait;

  /**
   * The metatag manager.
   *
   * @var \Drupal\metatag\MetatagManagerInterface
   */
  protected $metatagManager;

  /**
   * Path matcher service.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected $pathMatcher;

  /**
   * The drupal settings.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected $settings;

  /**
   * Language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs the metatags generator.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\metatag\MetatagManagerInterface $metatagManager
   *   The module handler.
   * @param \Drupal\Core\Path\PathMatcherInterface $pathMatcher
   *   The path matcher service.
   * @param \Drupal\Core\Site\Settings $settings
   *   The drupal settings.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager service.
   */
  public function __construct(ModuleHandlerInterface $module_handler, MetatagManagerInterface $metatagManager, PathMatcherInterface $pathMatcher, Settings $settings, LanguageManagerInterface $languageManager) {
    $this->moduleHandler = $module_handler;
    $this->metatagManager = $metatagManager;
    $this->pathMatcher = $pathMatcher;
    $this->settings = $settings;
    $this->languageManager = $languageManager;
  }

  /**
   * Get metatags.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match, for context.
   *
   * @return array
   *   Array of metatags.
   */
  public function getMetatags(RouteMatchInterface $route_match) {
    $node = $route_match->getParameter('node');
    if ($node !== NULL) {
      $metatags = metatag_generate_entity_metatags($route_match->getParameter('node'));
    }
    else {
      $metatags = [];
    }
    $prepared_metatags = [
      'meta' => [],
      'link' => [],
    ];
    $blacklisted_metatags = $this->settings->get('blacklisted_metatags') ?? [];
    foreach ($metatags as $metatag_values) {
      $blacklisted = FALSE;
      if (isset($blacklisted_metatags[$metatag_values['#tag']])) {
        foreach ($metatag_values['#attributes'] as $value) {
          if (in_array($value, $blacklisted_metatags[$metatag_values['#tag']])) {
            $blacklisted = TRUE;
            break;
          }
        }
      }
      if (!$blacklisted) {
        $prepared_metatags[$metatag_values['#tag']][] = $metatag_values['#attributes'];
      }
    }

    // Add alternate-links on top of existing links if content_translation is enabled.
    if ($this->moduleHandler->moduleExists('content_translation')) {
      $prepared_metatags['link'] = $this->getAlternateLinks($route_match, $prepared_metatags['link']);
    }

    return $prepared_metatags;
  }

  /**
   * Get alternate-link metatags by logic from content_translation_page_attachments.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match, for context.
   * @param array $link
   *   Existing link from metatag module.
   *
   * @return array
   *   Array of link metatags.
   */
  private function getAlternateLinks(RouteMatchInterface $route_match, array $link) : array {
    // If the current route has no parameters, return.
    if (!($route = $route_match
      ->getRouteObject()) || !($parameters = $route
      ->getOption('parameters'))) {
      return $link;
    }

    if (!$this->languageManager->isMultilingual()) {
      return $link;
    }

    $is_front = $this->pathMatcher->isFrontPage();

    // Determine if the current route represents an entity.
    foreach ($parameters as $name => $options) {
      if (!isset($options['type']) || strpos($options['type'], 'entity:') !== 0) {
        continue;
      }

      $entity = $route_match->getParameter($name);
      if ($entity instanceof ContentEntityInterface &&
        $entity->hasLinkTemplate('canonical') &&
        // Only add alternate link if entity is translated.
        count($entity->getTranslationLanguages()) > 1) {

        // Current route represents a content entity. Build hreflang links.
        foreach ($entity->getTranslationLanguages() as $language) {
          // Skip any translation that cannot be viewed.
          $translation = $entity->getTranslation($language->getId());
          $access = $translation->access('view', NULL, TRUE);
          if (!$access->isAllowed()) {
            continue;
          }
          if ($is_front) {
            // If the current page is front page, do not create hreflang links
            // from the entity route, just add the languages to root path.
            $url = Url::fromRoute('<front>', [], [
              'absolute' => TRUE,
              'language' => $language,
            ])
              ->toString();
          }
          else {
            $url = $entity
              ->toUrl('canonical')
              ->setOption('language', $language)
              ->setAbsolute()
              ->toString();
          }
          $link[] = [
            'rel' => 'alternate',
            'hreflang' => $language
              ->getId(),
            'href' => $url,
          ];
        }
      }

      // Since entity was found, no need to iterate further.
      break;
    }

    return $link;
  }

}
