<?php

namespace Drupal\lupus_ce_renderer;

use drunomics\ServiceUtils\Core\Routing\CurrentRouteMatchTrait;
use drunomics\ServiceUtils\Symfony\HttpFoundation\RequestStackTrait;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableResponse;
use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Drupal\Core\Render\RendererInterface;
use Drupal\custom_elements\CustomElement;
use Drupal\custom_elements\CustomElementGeneratorTrait;
use Drupal\custom_elements\CustomElementNormalizerTrait;
use Drupal\lupus_ce_renderer\Cache\CustomElementsJsonResponse;
use Drupal\metatag\MetatagManagerInterface;
use Drupal\Core\Block\BlockManager;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Site\Settings;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Renders main content only.
 */
class CustomElementsRenderer {

  use CustomElementGeneratorTrait;
  use CustomElementNormalizerTrait;
  use CurrentRouteMatchTrait;
  use RequestStackTrait;
  use CustomElementsMetatagsGeneratorTrait;

  /**
   * Custom elements content formats.
   */
  const CONTENT_FORMAT_MARKUP = 'markup';
  const CONTENT_FORMAT_JSON   = 'json';

  /**
   * The title resolver.
   *
   * @var \Drupal\Core\Controller\TitleResolverInterface
   */
  protected $titleResolver;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The renderer configuration array.
   *
   * @var array
   * @see sites/default/default.services.yml
   */
  protected $rendererConfig;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The messenger interface.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The block plugin manager.
   *
   * @var \Drupal\Core\Block\BlockManager
   */
  protected $blockManager;

  /**
   * The drupal settings.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected $settings;

  /**
   * The metatag manager.
   *
   * @var \Drupal\metatag\MetatagManagerInterface
   */
  protected $metatagManager;

  /**
   * The kill switch.
   *
   * @var \Drupal\Core\PageCache\ResponsePolicy\KillSwitch
   */
  protected $killSwitch;

  /**
   * Constructs the renderer.
   *
   * @param \Drupal\Core\Controller\TitleResolverInterface $title_resolver
   *   The title resolver.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param array $renderer_config
   *   The renderer configuration array.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger interface.
   * @param \Drupal\Core\Block\BlockManager $blockManager
   *   The block manager.
   * @param \Drupal\Core\Site\Settings $settings
   *   The drupal settings.
   * @param \Drupal\metatag\MetatagManagerInterface $metatagManager
   *   The module handler.
   * @param \Drupal\Core\PageCache\ResponsePolicy\KillSwitch $killSwitch
   *   The kill switch.
   */
  public function __construct(TitleResolverInterface $title_resolver, RendererInterface $renderer, array $renderer_config, ModuleHandlerInterface $module_handler, MessengerInterface $messenger, BlockManager $blockManager, Settings $settings, MetatagManagerInterface $metatagManager, KillSwitch $killSwitch) {
    $this->titleResolver = $title_resolver;
    $this->renderer = $renderer;
    $this->rendererConfig = $renderer_config;
    $this->moduleHandler = $module_handler;
    $this->messenger = $messenger;
    $this->blockManager = $blockManager;
    $this->settings = $settings;
    $this->metatagManager = $metatagManager;
    $this->killSwitch = $killSwitch;
  }

  /**
   * Renders the given custom element into a response.
   *
   * @param \Drupal\custom_elements\CustomElement $custom_element
   *   The custom element.
   * @param string $format
   *   (optional) The content format, markup or json.
   * @param string $select
   *   (optional) The response attribute to select. Allows selecting parts of
   *   the response. Only supports 'content' at the moment. Defaults to NULL.
   *
   * @return \Drupal\lupus_ce_renderer\Cache\CustomElementsJsonResponse|\Drupal\Core\Cache\CacheableResponse
   *   The response.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   *   Thrown when entity loading goes wrong.
   */
  public function renderResponse(CustomElement $custom_element, $format = 'markup', $select = NULL) {
    $custom_element->addCacheContexts($this->rendererConfig['required_cache_contexts']);
    [$content, $bubbleable_metadata] = $this->renderCustomElement($custom_element, $format);

    if ($select == 'content') {
      if ($format == static::CONTENT_FORMAT_JSON) {
        return new CacheableJsonResponse($content);
      }
      else {
        return new CacheableResponse((string) $content, 200, ['Content-Type' => 'text/plain']);
      }
    }
    elseif ($select) {
      throw new \LogicException('Unsupported response attribute selected.');
    }

    $route_match = $this->getCurrentRouteMatch();
    $request = $this->getCurrentRequest();
    $title = $this->titleResolver->getTitle($request, $route_match->getRouteObject());
    if (is_array($title)) {
      $title = strip_tags($this->renderer->renderRoot($title));
    }
    $messages = $this->getMessages();
    $breadcrumbs = $this->getBreadcrumbs($bubbleable_metadata);
    $metatags = $this->getCeMetagasGenerator()->getMetatags($route_match);

    $data = [
      'title' => $title,
      'messages' => $messages,
      'breadcrumbs' => $breadcrumbs['data'],
      'metatags' => $metatags,
      'content_format' => $format,
      'content' => $content ?: FALSE,
      'page_layout' => 'default',
    ];

    // Apply overrides from request attributes.
    $this->modifyResponseData($data, $request);
    // Apply overrides from alter hook.
    $this->moduleHandler->alter('lupus_ce_renderer_response', $data, $bubbleable_metadata, $request);

    $response = new CustomElementsJsonResponse();
    $response->setData($data);
    $response->addCacheableDependency($bubbleable_metadata);
    return $response;
  }

  /**
   * Gets content format from request parameter.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return string
   *   The content format.
   */
  public function getContentFormatFromRequest(Request $request): string {
    $content_format_settings = Settings::get('lupus_ce_renderer_default_format', CustomElementsRenderer::CONTENT_FORMAT_MARKUP);
    $default_content_format = $request->attributes->get('lupus_ce_renderer.content_format', $content_format_settings);
    return $request->query->get('_content_format', $default_content_format);
  }

  /**
   * Renders a custom element into the given content format.
   *
   * @param \Drupal\custom_elements\CustomElement $custom_element
   *   The custom element to render.
   * @param string $format
   *   The content format, markup or json.
   *
   * @return array
   *   Array with content as first element and bubbleable metadata as second.
   *
   * @throws \LogicException
   *   Thrown when an unsupported format was given.
   */
  public function renderCustomElement(CustomElement $custom_element, string $format): array {
    $bubbleable_metadata = BubbleableMetadata::createFromObject($custom_element);
    if ($format == static::CONTENT_FORMAT_MARKUP) {
      $build = $custom_element->toRenderArray();
      $content = $this->renderer->renderRoot($build);
      $bubbleable_metadata = $bubbleable_metadata
        ->merge(BubbleableMetadata::createFromRenderArray($build));
    }
    elseif ($format == static::CONTENT_FORMAT_JSON) {
      $content = $this->getCustomElementNormalizer()->normalize($custom_element, NULL, ['cache_metadata' => $bubbleable_metadata]);
    }
    else {
      throw new \LogicException('Unsupported content format given.');
    }
    return [$content, $bubbleable_metadata];
  }

  /**
   * Get additional dynamic content.
   *
   * Content will be added to response on late stage. It will not be cached and
   * will not impact dynamic page cache.
   *
   * @return array[]
   *   Data array.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function getDynamicContent() {
    return [
      'local_tasks' => $this->getLocalTasks(),
    ];
  }

  /**
   * Get drupal messages.
   *
   * @return array
   *   Array of messages.
   */
  private function getMessages() {
    $messages = $this->messenger->all();
    if (!empty($messages)) {
      $this->killSwitch->trigger();
    }
    if (isset($messages['status'])) {
      $success_messages = $messages['status'];
      unset($messages['status']);
      $messages['success'] = $success_messages;
    }
    $this->messenger->deleteAll();
    return $messages;
  }

  /**
   * Get breadcrumbs data and markup.
   *
   * @param \Drupal\Core\Render\BubbleableMetadata $bubbleable_metadata
   *   The bubbleable metatada.
   *
   * @return array
   *   Array of breadcrumbs and markup data.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  private function getBreadcrumbs(BubbleableMetadata &$bubbleable_metadata) {
    try {
      $breadcrumbs = [];
      $breadcrumb_block = $this->blockManager->createInstance('system_breadcrumb_block', []);
      $breadcrumb_render = $breadcrumb_block->build();
      if (!empty($breadcrumb_render['#links'])) {
        /** @var \Drupal\Core\Link $crumb */
        foreach ($breadcrumb_render['#links'] as $crumb) {
          $text = $crumb->getText();
          if (is_array($crumb->getText()) && isset($text['#markup'])) {
            $text = strip_tags($text['#markup']);
          }
          $breadcrumbs[] = [
            'frontpage' => $crumb->getUrl()->getRouteName() == '<front>',
            'url' => $crumb->getUrl()->toString(),
            'label' => (string) $text,
          ];
        }
        $bubbleable_metadata = BubbleableMetadata::createFromRenderArray($breadcrumb_render)
          ->merge($bubbleable_metadata);
      }

      return [
        'data' => $breadcrumbs,
        'markup' => $this->renderer->renderRoot($breadcrumb_render),
      ];
    }
    // On not-found routes breadcrumbs might not be derivable, thus set them
    // empty.
    catch (NotAcceptableHttpException $exception) {
      return [
        'data' => [],
        'markup' => '',
      ];
    }
  }

  /**
   * Get local-tasks data.
   *
   * @return array
   *   Array of local-task links.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  private function getLocalTasks() {
    $local_tasks = [];
    $local_tasks_block = $this->blockManager->createInstance('local_tasks_block', []);
    $local_tasks_render = $local_tasks_block->build();
    if (isset($local_tasks_render['#primary'])) {
      $local_tasks['primary'] = $this->prepareLocalTaskLinks($local_tasks_render['#primary']);
    }
    if (isset($local_tasks_render['#secondary'])) {
      $local_tasks['secondary'] = $this->prepareLocalTaskLinks($local_tasks_render['#secondary']);
    }
    return $local_tasks;
  }

  /**
   * Prepare local task links data.
   *
   * @param array $render_links
   *   Renderable links array.
   *
   * @return array
   *   Array of local-task links.
   */
  private function prepareLocalTaskLinks(array $render_links) {
    $local_tasks = [];
    if (!empty($render_links)) {
      // Order the links by weight.
      usort($render_links, function (array $link1, array $link2): int {
        return $link1['#weight'] <=> $link2['#weight'];
      });
      foreach ($render_links as $render_link) {
        /** @var \Drupal\Core\Access\AccessResult $access */
        $access = $render_link['#access'] ?? FALSE;
        // Check access to links.
        if ($access instanceof AccessResult && $access->isAllowed() || $access === TRUE) {
          $url = $render_link['#link']['url'] ?? FALSE;
          $title = $render_link['#link']['title'] ?? FALSE;
          if ($url && $title) {
            $local_tasks[] = [
              'url' => $url->toString(),
              'label' => $title,
              'active' => $render_link['#active'] ?? FALSE,
            ];
          }
        }
      }
    }
    return $local_tasks;
  }

  /**
   * Modify response data with request attributes.
   *
   * @param array $data
   *   Response data.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request.
   */
  private function modifyResponseData(array &$data, Request $request) {
    $overrides = $request->attributes->get('lupus_ce_renderer_response_data');
    if (is_array($overrides)) {
      $data = array_replace_recursive($data, $overrides);
    }
  }

}
