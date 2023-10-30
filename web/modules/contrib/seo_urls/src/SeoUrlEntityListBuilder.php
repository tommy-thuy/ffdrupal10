<?php

namespace Drupal\seo_urls;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of SEO URL entities.
 *
 * @ingroup seo_urls
 */
class SeoUrlEntityListBuilder extends EntityListBuilder {

  /**
   * {@inheritDoc}
   */
  protected function getEntityIds() {
    $query = $this->getStorage()->getQuery()
      ->accessCheck(TRUE)
      ->sort($this->entityType->getKey('id'), 'DESC');

    // Only add the pager if a limit is specified.
    if ($this->limit) {
      $query->pager($this->limit);
    }
    return $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [];
    $header['id'] = $this->t('ID');
    $header['canonical_url'] = $this->t('Canonical URL');
    $header['seo_url'] = $this->t('SEO URL');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\seo_urls\Entity\SeoUrl $entity */
    $row['id'] = $entity->id();
    $row['canonical_url'] = [
      'data' => [
        '#type' => 'link',
        '#title' => $entity->getCanonicalPath(),
        '#url' => Url::fromUri($entity->getCanonicalUri()),
        '#options' => ['attributes' => ['target' => '_blank']],
      ],
    ];
    $row['seo_url'] = [
      'data' => [
        '#type' => 'link',
        '#title' => $entity->getSeoPath(),
        '#url' => Url::fromUri($entity->getSeoUriBase()),
        '#options' => ['attributes' => ['target' => '_blank']],
      ],
    ];

    return $row + parent::buildRow($entity);
  }

}
