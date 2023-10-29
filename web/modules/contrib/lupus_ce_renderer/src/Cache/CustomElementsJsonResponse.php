<?php

namespace Drupal\lupus_ce_renderer\Cache;

use Drupal\Core\Cache\CacheableJsonResponse;

/**
 * A JsonResponse that stores raw data and allows change it easier.
 *
 * @see \Drupal\Core\Cache\CacheableJsonResponse
 * @see \Drupal\lupus_ce_renderer\EventSubscriber\CustomElementsDynamicResponseSubscriber
 */
class CustomElementsJsonResponse extends CacheableJsonResponse {

  /**
   * Response data.
   *
   * @var array
   */
  protected $responseData;

  /**
   * {@inheritDoc}
   */
  public function setData($data = []): static {
    $this->setResponseData($data);
    return parent::setData($data);
  }

  /**
   * Sets the response data.
   *
   * @param mixed $data
   *   Response data value.
   *
   * @return $this
   *   The class instance.
   */
  public function setResponseData($data = []) {
    $this->responseData = $data;
    return $this;
  }

  /**
   * {@inheritDoc}
   */
  public function isRedirect($location = NULL): bool {
    return !empty($this->responseData['redirect']) || parent::isRedirect($location);
  }

  /**
   * Gets the response data.
   *
   * @return mixed
   *   Response data.
   */
  public function getResponseData() {
    return $this->responseData ?? [];
  }

}
