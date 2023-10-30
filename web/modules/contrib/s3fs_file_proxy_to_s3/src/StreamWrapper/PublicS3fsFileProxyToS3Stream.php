<?php

namespace Drupal\s3fs_file_proxy_to_s3\StreamWrapper;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\s3fs\StreamWrapper\PublicS3fsStream;

/**
 * Defines a Drupal stream wrapper class for use with public scheme.
 *
 * Provides an external Url to be able to use File Proxy to download the files
 * and then upload to S3.
 */
class PublicS3fsFileProxyToS3Stream extends PublicS3fsStream {

  /**
   * {@inheritdoc}
   */
  public function getExternalUrl() {
    $uri = $this->getUri();
    if (file_exists($uri) ||  StreamWrapperManager::getScheme($uri) !== 'public') {
      return parent::getExternalUrl();
    }
    $s3_key = str_replace('\\', '/', \Drupal::service('stream_wrapper_manager')->getTarget($uri));
    $path_parts = explode('/', $s3_key);
    array_unshift($path_parts, 's3fs_to_s3', 'files');
    $path = implode('/', $path_parts);
    return $GLOBALS['base_url'] . '/' . UrlHelper::encodePath($path);
  }

}
