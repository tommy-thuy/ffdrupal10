<?php

namespace Drupal\Tests\custom_elements\Functional;

use Drupal\custom_elements\CustomElement;
use Drupal\custom_elements\CustomElementGeneratorTrait;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Tests\BrowserTestBase;
use PHPUnit\Framework\Assert;

/**
 * Test rendering custom elements into markup with .
 *
 * @group custom_elements
 */
class CustomElementsRenderMarkupVue3Test extends BrowserTestBase {

  use CustomElementGeneratorTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stable';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'custom_elements_test_paragraphs',
    'custom_elements_everywhere',
    'custom_elements_thunder',
  ];

  /**
   * {@inheritdoc}
   */
  protected $strictConfigSchema = FALSE;

  /**
   * The node to use for testing.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * The image used for testing.
   *
   * @var \Drupal\file\FileInterface
   */
  protected $image;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->node = Node::create([
      'type' => 'article',
      'title' => 'test',
    ]);
    \Drupal::service('file_system')
      ->copy($this->root . '/core/misc/druplicon.png', 'public://example.jpg');
    $this->image = File::create([
      'uri' => 'public://example.jpg',
    ]);
    $this->image->save();
    $config = $this->config('custom_elements.settings');
    $config->set('markup_style', 'vue-3');
    $config->save();
  }

  /**
   * Helper to render a custom element into markup.
   *
   * @param \Drupal\custom_elements\CustomElement $element
   *   The element.
   *
   * @return string
   *   The rendered markup.
   */
  private function renderCustomElement(CustomElement $element) {
    $render = [
      '#theme' => 'custom_element',
      '#custom_element' => $element,
    ];
    return (string) $this->container->get('renderer')->renderPlain($render);
  }

  /**
   * Helper to trim strings. Removes line-endings.
   *
   * @param string $string
   *   String to trim.
   *
   * @return string
   *   Trimmed sting.
   */
  private function trim($string) {
    // Editors strip trailing spaces, so do so for the generated markup.
    // Besides that drop new lines.
    return preg_replace("/ *\n/m", "", $string);
  }

  /**
   * Tests paragraphs.
   */
  public function testParagraphs() {
    // We test all paragraph types from a single test method so the setup()
    // routine is only run once for all of them - saves time.
    $this->doTestTextParagraph();
    $this->doTestQuoteParagraph();
    $this->doTestLinkParagraph();
    $this->doTestTwitterParagraph();
    $this->doTestVideoParagraph();
    $this->doTestImageParagraph();
    $this->doTestGalleryParagraph();
  }

  /**
   * @covers \Drupal\custom_elements_thunder\Processor\ParagraphTextProcessor
   */
  public function doTestTextParagraph() {
    $paragraph = Paragraph::create([
      'type' => 'text',
      'field_title' => 'The title',
      'field_text' => [
        'value' => '<strong>Some</strong> example text',
        'format' => 'restricted_html',
      ],
    ]);

    $custom_element = $this->getCustomElementGenerator()
      ->generate($paragraph, 'full');
    $markup = $this->renderCustomElement($custom_element);
    $expected_markup = <<<EOF
<pg-text type="text" view-mode="full" title="The title">
<p><strong>Some</strong> example text</p>
</pg-text>
EOF;
    Assert::assertEquals($this->trim($expected_markup), $this->trim($markup));
  }

  /**
   * @covers \Drupal\custom_elements_thunder\Processor\ParagraphQuoteProcessor
   */
  public function doTestQuoteParagraph() {
    $paragraph = Paragraph::create([
      'type' => 'quote',
      'field_text' => [
        'value' => '<strong>Some</strong> example text',
        'format' => 'restricted_html',
      ],
    ]);

    $custom_element = $this->getCustomElementGenerator()
      ->generate($paragraph, 'full');
    $markup = $this->renderCustomElement($custom_element);
    $expected_markup = <<<EOF
<pg-quote type="quote" view-mode="full">
<p><strong>Some</strong> example text</p>
</pg-quote>
EOF;
    Assert::assertEquals($this->trim($expected_markup), $this->trim($markup));
  }

  /**
   * @covers \Drupal\custom_elements_thunder\Processor\ParagraphLinkProcessor
   */
  public function doTestLinkParagraph() {
    $paragraph = Paragraph::create([
      'type' => 'link',
      'field_link' => [
        'uri' => 'http://example.com',
        'title' => 'Example site',
      ],
    ]);

    $custom_element = $this->getCustomElementGenerator()
      ->generate($paragraph, 'full');
    $markup = $this->renderCustomElement($custom_element);
    $expected_markup = <<<EOF
<pg-link type="link" view-mode="full" title="Example site" href="http://example.com"></pg-link>
EOF;
    Assert::assertEquals($this->trim($expected_markup), $this->trim($markup));
  }

  /**
   * @covers \Drupal\custom_elements_thunder\Processor\ParagraphTwitterProcessor
   */
  public function doTestTwitterParagraph() {
    $paragraph = Paragraph::create([
      'type' => 'twitter',
      'field_media' => [
        Media::create([
          'bundle' => 'twitter',
          'field_url' => 'https://twitter.com/the_real_fago/status/1189191210709049344',
        ]),
      ],
    ]);

    $custom_element = $this->getCustomElementGenerator()
      ->generate($paragraph, 'full');
    $markup = $this->renderCustomElement($custom_element);
    $expected_markup = <<<EOF
<pg-twitter type="twitter" view-mode="full" src="https://twitter.com/the_real_fago/status/1189191210709049344"></pg-twitter>
EOF;
    Assert::assertEquals($this->trim($expected_markup), $this->trim($markup));
  }

  /**
   * @covers \Drupal\custom_elements_thunder\Processor\ParagraphVideoProcessor
   */
  public function doTestVideoParagraph() {
    $paragraph = Paragraph::create([
      'type' => 'video',
      'field_video' => [
        Media::create([
          'bundle' => 'video',
          'field_media_video_embed_field' => 'https://www.youtube.com/watch?v=IPR36uraNwc',
        ]),
      ],
    ]);

    $custom_element = $this->getCustomElementGenerator()
      ->generate($paragraph, 'full');
    $markup = $this->renderCustomElement($custom_element);
    $expected_markup = <<<EOF
<pg-video type="video" view-mode="full" src="https://www.youtube.com/embed/IPR36uraNwc" thumbnail-src="https://img.youtube.com/vi/IPR36uraNwc/maxresdefault.jpg"></pg-video>
EOF;
    Assert::assertEquals($this->trim($expected_markup), $this->trim($markup));
  }

  /**
   * @covers \Drupal\custom_elements_thunder\Processor\ParagraphImageProcessor
   */
  public function doTestImageParagraph() {
    $paragraph = Paragraph::create([
      'type' => 'image',
      'field_image' => [
        Media::create([
          'bundle' => 'image',
          'field_image' => [
            'target_id' => $this->image->id(),
          ],
          'field_copyright' => 'custom elements copyright',
          'field_description' => '<strong>Custom Elements</strong> <p>image</p> description',
          'field_source' => 'custom elements images source',
        ]),
      ],
    ]);

    $custom_element = $this->getCustomElementGenerator()
      ->generate($paragraph, 'full');
    $markup = $this->renderCustomElement($custom_element);
    $expected_markup = <<<EOF
<pg-image type="image" view-mode="full" src="{$this->image->uri->url}" copyright="custom elements copyright" caption="&lt;p&gt;&amp;lt;strong&amp;gt;Custom Elements&amp;lt;/strong&amp;gt; &amp;lt;p&amp;gt;image&amp;lt;/p&amp;gt; description&lt;/p&gt;"></pg-image>
EOF;
    Assert::assertEquals($this->trim($expected_markup), $this->trim($markup));
  }

  /**
   * @covers \Drupal\custom_elements_thunder\Processor\ParagraphGalleryProcessor
   */
  public function doTestGalleryParagraph() {
    $media_image_data = [
      'bundle' => 'image',
      'thumbnail' => [
        'target_id' => $this->image->id(),
      ],
      'field_image' => [
        'target_id' => $this->image->id(),
      ],
    ];
    $media_image_1 = Media::create($media_image_data);
    $media_image_2 = Media::create($media_image_data +
      [
        'field_copyright' => 'copyright',
        'field_description' => 'description',
        'field_source' => 'source',
      ]);
    $paragraph = Paragraph::create([
      'type' => 'gallery',
      'field_media' => [
        Media::create([
          'bundle' => 'gallery',
          'field_media_images' => [
            0 => ['entity' => $media_image_1],
            1 => ['entity' => $media_image_2],
          ],
        ]),
      ],
    ]);

    $custom_element = $this->getCustomElementGenerator()
      ->generate($paragraph, 'full');
    $markup = $this->renderCustomElement($custom_element);
    $image_url = $this->image->uri->url;
    $expected_json = htmlspecialchars(json_encode([
      [
        'url' => $image_url,
        'thumbnail-url' => $image_url,
        'alt' => '',
      ],
      [
        'url' => $image_url,
        'thumbnail-url' => $image_url,
        'alt' => '',
        'copyright' => 'copyright',
        'description' => '<p>description</p>
',
      ],
    ]));
    $expected_markup = <<<EOF
<pg-gallery type="gallery" view-mode="full" :sources="$expected_json"></pg-gallery>
EOF;
    Assert::assertEquals($this->trim($expected_markup), $this->trim($markup));
  }

  /**
   * Test nested elements rendering.
   */
  public function testNestedElementsRendering() {
    $listing_element = CustomElement::create('test-list');
    $paragraphs[] = Paragraph::create([
      'title' => 'First Paragraph',
      'type' => 'text',
      'field_text' => [
        'value' => 'Some example text for first paragraph',
      ],
    ]);
    $paragraphs[] = Paragraph::create([
      'title' => 'Second Paragraph',
      'type' => 'text',
      'field_text' => [
        'value' => 'Some another example text',
      ],
    ]);
    $nested_elements = [];
    foreach ($paragraphs as $key => $paragraph) {
      $nested_elements[$key] = $this->getCustomElementGenerator()->generate($paragraph, 'full');
    }
    $listing_element->setSlotFromNestedElements('paragraphs', $nested_elements);
    $markup = $this->renderCustomElement($listing_element);

    $expected_markup = <<<EOF
<test-list>
<template #paragraphs>
<pg-text type="text" view-mode="full">
<p>Some example text for first paragraph</p>
</pg-text>
<pg-text type="text" view-mode="full">
<p>Some another example text</p>
</pg-text>
</template>
</test-list>
EOF;
    Assert::assertEquals($this->trim($expected_markup), $this->trim($markup));
  }

  /**
   * @covers \Drupal\custom_elements\Processor\DefaultContentEntityProcessor
   */
  public function testNodeRendering() {
    // Test rendering with new vue-3 style.
    $paragraph = Paragraph::create([
      'title' => 'Title',
      'type' => 'text',
      'field_text' => [
        'value' => 'Some example text',
      ],
    ]);
    $this->node->field_paragraphs = [
      0 => ['entity' => $paragraph],
    ];

    $custom_element = $this->getCustomElementGenerator()
      ->generate($this->node, 'full');
    $markup = $this->renderCustomElement($custom_element);
    $expected_markup = <<<EOF
<node type="article" view-mode="full" uid="0" title="test" created="{$this->node->created->value}">
<template #paragraphs>
<pg-text type="text" view-mode="full">
<p>Some example text</p>
</pg-text>
</template>
</node>
EOF;
    Assert::assertEquals($this->trim($expected_markup), $this->trim($markup));
  }

}
