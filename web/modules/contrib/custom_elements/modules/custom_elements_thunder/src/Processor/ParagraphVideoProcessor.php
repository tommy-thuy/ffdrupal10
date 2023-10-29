<?php

namespace Drupal\custom_elements_thunder\Processor;

use Drupal\custom_elements\CustomElement;
use Drupal\custom_elements\CustomElementsProcessorFieldUtilsTrait;
use Drupal\custom_elements\Processor\CustomElementProcessorInterface;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\video_embed_field\ProviderManager;

/**
 * Default processor for thunder video paragraph.
 */
class ParagraphVideoProcessor implements CustomElementProcessorInterface {

  use CustomElementsProcessorFieldUtilsTrait;

  /**
   * Video embed provider manager.
   *
   * @var \Drupal\video_embed_field\ProviderManager
   */
  protected $providerManager;

  /**
   * Video embed provider.
   *
   * @var \Drupal\media\OEmbed\Provider
   */
  protected $provider;

  /**
   * Constructs the renderer.
   *
   * @param \Drupal\video_embed_field\ProviderManager $provider_manager
   *   Video embed provider manager.
   */
  public function __construct(ProviderManager $provider_manager) {
    $this->providerManager = $provider_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function supports($data, $viewMode) {
    if ($data instanceof ParagraphInterface) {
      return $data->getEntityTypeId() == 'paragraph' &&
        $data->bundle() == 'video';
    }
    else {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function addtoElement($paragraph, CustomElement $element, $viewMode) {
    assert($paragraph instanceof ParagraphInterface);
    if ($this->fieldIsAccessible($paragraph, 'field_video', $element)) {
      /** @var \Drupal\media\Entity\Media $media_entity */
      $media_entity = $paragraph->field_video->entity;
      if ($this->entityIsAccessible($media_entity, $element)) {
        if ($this->fieldIsAccessible($media_entity, 'field_media_video_embed_field', $element)) {
          $this->provider = $this->providerManager->loadProviderFromInput($media_entity->field_media_video_embed_field->value);
          if (!$this->provider) {
            return;
          }
          $embed_code = $this->provider->renderEmbedCode('0', '0', FALSE);
          $element->setAttribute('src', $embed_code['#url']);
          $preview_image_src = $this->provider->getRemoteThumbnailUrl();
          // Make sure video thumbnail is loaded from https and pre-fetch size.
          if (strpos($preview_image_src, 'http:') !== FALSE) {
            $preview_image_src = str_replace('http:', 'https:', $preview_image_src);
          }
          $element->setAttribute('thumbnail-src', $preview_image_src);

        }
      }
    }
  }

}
