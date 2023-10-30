<?php

namespace Drupal\seo_urls\Entity;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\seo_urls\SeoUrlManagerInterface;
use Drupal\user\UserInterface;

/**
 * Defines the new entity.
 *
 * @ContentEntityType(
 *   id = "seo_url",
 *   label = @Translation("SEO URL"),
 *   label_collection = @Translation("SEO URLs"),
 *   label_singular = @Translation("SEO URL item"),
 *   label_plural = @Translation("SEO URL items"),
 *   label_count = @PluralTranslation(
 *     singular = "@count SEO URL",
 *     plural = "@count SEO URLs",
 *   ),
 *   bundle_label = @Translation("SEO URL"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\seo_urls\SeoUrlEntityListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\seo_urls\SeoUrlAccessControlHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\seo_urls\Form\SeoUrlEntityForm",
 *       "add" = "Drupal\seo_urls\Form\SeoUrlEntityForm",
 *       "edit" = "Drupal\seo_urls\Form\SeoUrlEntityForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *
 *     "route_provider" = {
 *       "html" = "\Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "seo_url_data",
 *   data_table = "seo_url_field_data",
 *   translatable = FALSE,
 *   admin_permission = "administer seo_url entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/content/seo_url/{seo_url}",
 *     "add-form" = "/admin/content/seo_url/add",
 *     "edit-form" = "/admin/content/seo_url/{seo_url}/edit",
 *     "delete-form" = "/admin/content/seo_url/{seo_url}/delete",
 *     "collection" = "/admin/content/seo_url",
 *   },
 *   field_ui_base_route = "seo_url.settings"
 * )
 */
class SeoUrl extends ContentEntityBase implements SeoUrlInterface {

  use EntityChangedTrait;
  use StringTranslationTrait;

  /**
   * SEO Url manager interface.
   *
   * @var \Drupal\seo_urls\SeoUrlManagerInterface|null
   */
  protected ?SeoUrlManagerInterface $seoUrlManager = NULL;

  /**
   * Gets SEO URL service.
   *
   * @return \Drupal\seo_urls\SeoUrlManagerInterface
   *   SEO Url manager interface.
   */
  protected function seoUrlManager(): SeoUrlManagerInterface {
    if (!$this->seoUrlManager) {
      $this->seoUrlManager = \Drupal::service('seo_urls.manager');
    }
    return $this->seoUrlManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    /** @var \Drupal\Core\Field\BaseFieldDefinition[] $fields */
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of the entity author.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'region' => 'hidden',
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the entity is published.'))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => TRUE,
        ],
        'weight' => 100,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['uuid']->setDescription(t('The SEO URL UUID.'));

    $fields['canonical_url'] = BaseFieldDefinition::create('link')
      ->setLabel(t('Canonical URL'))
      ->setDescription(t('The URL of the overview.'))
      ->setSetting('title', 0)
      ->setSetting('link_type', 1)
      ->addConstraint('UniqueLink')
      ->setDisplayOptions('view', [
        'type' => 'hidden',
      ])
      ->setDisplayOptions('form', [
        'type' => 'link_default',
        'weight' => 0,
        'settings' => [
          'placeholder_url' => 'Input original URL',
          'placeholder_title' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['seo_url'] = BaseFieldDefinition::create('link')
      ->setLabel(t('SEO URL'))
      ->setDescription(t('The SEO friendly URL.'))
      ->setSetting('title', 0)
      ->setSetting('link_type', 1)
      ->addConstraint('UniqueLink')
      ->setDisplayOptions('view', [
        'type' => 'hidden',
      ])
      ->setDisplayOptions('form', [
        'type' => 'link_default',
        'weight' => 1,
        'settings' => [
          'placeholder_url' => 'Input pretty URL',
          'placeholder_title' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->getSeoPath();
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished(): bool {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished(bool $published): SeoUrlInterface {
    $this->set('status', $published);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime(): int {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp): SeoUrlInterface {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSeoUri(): string {
    return $this->get(self::SEO_URL_FIELD)->uri;
  }

  /**
   * {@inheritdoc}
   */
  public function getSeoPath(): string {
    return str_replace('internal:', '', $this->getSeoUri());
  }

  /**
   * {@inheritdoc}
   */
  public function getSeoUriBase(): string {
    return str_replace('internal:', 'base:', $this->getSeoUri());
  }

  /**
   * {@inheritdoc}
   */
  public function getCanonicalUri(): string {
    return $this->get(self::CANONICAL_URL_FIELD)->uri;
  }

  /**
   * {@inheritdoc}
   */
  public function getCanonicalPath(): string {
    return str_replace('internal:', '', $this->getCanonicalUri());
  }

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage, array &$values) {
    parent::preCreate($storage, $values);

    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    $tags = Cache::mergeTags(['route_match']);
    $canonical_entities = [];

    $this->clearCacheByEntity($this, $canonical_entities);
    if (isset($this->original)) {
      $this->clearCacheByEntity($this->original, $canonical_entities);
    }

    foreach ($canonical_entities as $canonical_entity) {
      $tags = Cache::mergeTags($tags, $canonical_entity->getCacheTags());
    }

    Cache::invalidateTags($tags);
  }

  /**
   * Clear cache of the SEO Url manager by entity.
   *
   * @param \Drupal\seo_urls\Entity\SeoUrlInterface $entity
   *   SEO Url entity.
   * @param \Drupal\Core\Entity\EntityInterface[] $canonical_entities
   *   Canonical entities related to the Canonical Url.
   */
  protected function clearCacheByEntity(SeoUrlInterface $entity, array &$canonical_entities) {
    // Clear cache.
    $this->seoUrlManager()->cacheClear($entity->get(SeoUrlInterface::SEO_URL_FIELD)->uri);
    // Update related canonical entities.
    $canonical_entities = array_merge($canonical_entities, $this->seoUrlManager()->getCanonicalEntities($this));
  }

}
