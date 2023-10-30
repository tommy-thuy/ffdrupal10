<?php

namespace Drupal\s3fs_file_proxy_to_s3;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\stage_file_proxy\FetchManager;
use GuzzleHttp\Exception\ClientException;

class S3fsFileProxyToS3FetchManager extends FetchManager {

  /**
   * {@inheritdoc}
   */
  public function fetch($server, $remote_file_dir, $relative_path, array $options): bool {
    try {
      // Fetch remote file.
      $url = $server . '/' . UrlHelper::encodePath($remote_file_dir . '/' . $relative_path);
      $response = $this->client->get($url, $options);

      if ($response->getStatusCode() === 200) {
        // Prepare local target directory and save downloaded file.
        $file_dir = $this->filePublicDestination();
        $target_dir = $file_dir . dirname($relative_path);
        if (\Drupal::service('file_system')->prepareDirectory($target_dir, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS)) {
          file_put_contents($file_dir . $relative_path, $response->getBody());
          return TRUE;
        }
      }
      return FALSE;
    }
    catch (ClientException $e) {
      // Do nothing.
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function filePublicPath(): string {
    return 's3fs_to_s3/files';
  }

  /**
   * @return string
   */
  private function filePublicDestination(): string {
    return 'public://';
  }

  /**
   * {@inheritdoc}
   */
  public function styleOriginalPath($uri, $style_only = TRUE) {
    $scheme = StreamWrapperManager::getScheme($uri);
    if ($scheme !== FALSE) {
      $path = \Drupal::service('stream_wrapper_manager')->getTarget($uri);
    }
    else {
      $path = $uri;
    }

    // It is a styles path, so we extract the different parts.
    if (str_starts_with($path, 's3fs_to_s3/files/styles')) {
      // Then the path is like styles/[style_name]/[schema]/[original_path].
      return preg_replace('/s3fs_to_s3\/files\/styles\/.*\/(.*)\/(.*)/U', '$1://$2', $path);
    }
    return parent::styleOriginalPath($uri, $style_only);
  }

}
