<?php

namespace Drupal\aws\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining an AWS Profile entity.
 */
interface ProfileInterface extends ConfigEntityInterface {

  /**
   * Whether the profile is the default or not.
   *
   * @return bool
   *   TRUE if the profile is the default.
   */
  public function isDefault();

  /**
   * Set the profile as the default.
   *
   * @return $this
   */
  public function setDefault(bool $default);

  /**
   * Get the access key of the profile.
   *
   * @return string
   *   The access key of the profile.
   */
  public function getAccessKey();

  /**
   * Set the access key of the profile.
   *
   * @return $this
   */
  public function setAccessKey(string $aws_access_key_id);

  /**
   * Get the secret access key of the profile.
   *
   * @return string
   *   The secret access key of the profile.
   */
  public function getSecretAccessKey();

  /**
   * Set the secret access key of the profile.
   *
   * @return $this
   */
  public function setSecretAccessKey(string $aws_secret_access_key);

  /**
   * Get the region of the profile.
   *
   * @return string
   *   The region of the profile.
   */
  public function getRegion();

  /**
   * Set the region of the profile.
   *
   * @return $this
   */
  public function setRegion(string $region);

  /**
   * Get the encryption profile for the profile.
   *
   * @return string
   *   The encryption profile of the profile.
   */
  public function getEncryptionProfile();

  /**
   * Set the encryption profile for the profile.
   *
   * @return $this
   */
  public function setEncryptionProfile(string $encryption_profile);

  /**
   * Returns the arguments required to instantiate an AWS service client.
   *
   * @param string $version
   *   The API version to use. Defaults to "latest".
   *
   * @return array
   *   The client arguments.
   */
  public function getClientArgs(string $version = 'latest');

}
