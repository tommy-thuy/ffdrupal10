<?php

namespace Drupal\s3fs_file_proxy_to_s3\EventSubscriber;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Drupal\Component\Utility\Unicode;
use Drupal\stage_file_proxy\EventSubscriber\ProxySubscriber;

/**
 * S3fs Stage file proxy to S3 subscriber for controller requests.
 */
class S3fsFileProxyToS3Subscriber extends ProxySubscriber {

  /**
   * Fetch the file according the its origin.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The Event to process.
   */
  public function checkFileOrigin(RequestEvent $event): void {
    $file_dir = $this->manager->filePublicPath();
    $uri = $event->getRequest()->getPathInfo();

    $uri = mb_substr($uri, 1);

    if (!str_starts_with($uri, '' . $file_dir)) {
      return;
    }

    $uri = rawurldecode($uri);
    $relative_path = mb_substr($uri, mb_strlen($file_dir) + 1);

    $uri = "public://{$relative_path}";

    if (file_exists($uri)) {
      $url = \Drupal::service('file_url_generator')->generateAbsoluteString($uri);
      header("Location: $url");
      exit;
    }

    parent::checkFileOrigin($event);
  }

}
