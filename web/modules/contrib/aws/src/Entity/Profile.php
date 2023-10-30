<?php

declare(strict_types=1);

namespace Drupal\aws\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\encrypt\EncryptServiceInterface;

/**
 * Defines the AWS Profile entity.
 *
 * @ConfigEntityType(
 *   id = "aws_profile",
 *   label = @Translation("AWS Profile"),
 *   label_collection = @Translation("AWS Profiles"),
 *   label_singular = @Translation("AWS profile"),
 *   label_plural = @Translation("AWS profiles"),
 *   label_count = @PluralTranslation(
 *     singular = "@count AWS profile",
 *     plural = "@count AWS profiles",
 *   ),
 *   handlers = {
 *     "storage" = "Drupal\aws\Entity\Storage\ProfileStorage",
 *     "list_builder" = "Drupal\aws\Entity\ListBuilder\ProfileListBuilder",
 *     "form" = {
 *       "default" = "Drupal\aws\Entity\Form\ProfileForm",
 *       "edit" = "Drupal\aws\Entity\Form\ProfileForm",
 *       "delete" = "Drupal\aws\Entity\Form\ProfileDeleteConfirmForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer aws",
 *   config_prefix = "profile",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *   },
 *   config_export = {
 *     "id",
 *     "name",
 *     "default",
 *     "aws_access_key_id",
 *     "aws_secret_access_key",
 *     "region",
 *     "encryption_profile"
 *   },
 *   links = {
 *     "canonical" = "/admin/config/services/aws/profile/{aws_profile}",
 *     "add-form" = "/admin/config/services/aws/add-profile",
 *     "edit-form" = "/admin/config/services/aws/profile/{aws_profile}/edit",
 *     "delete-form" = "/admin/config/services/aws/profile/{aws_profile}/delete",
 *     "collection" = "/admin/config/services/aws/profiles",
 *   }
 * )
 */
class Profile extends ConfigEntityBase implements ProfileInterface {

  /**
   * The ID of the profile.
   *
   * @var string
   */
  protected $id;

  /**
   * The name of the profile.
   *
   * @var string
   */
  protected $name;

  /**
   * Whether the profile is the default or not.
   *
   * @var int
   */
  protected $default;

  /**
   * The access key of the profile.
   *
   * @var string
   */
  protected $aws_access_key_id;

  /**
   * The secret access key of the profile.
   *
   * @var string
   */
  protected $aws_secret_access_key;

  /**
   * The region of the profile.
   *
   * @var string
   */
  protected $region;

  /**
   * The encryption profile for the profile.
   *
   * @var string
   */
  protected $encryption_profile;

  /**
   * The encryption service.
   *
   * @var \Drupal\encrypt\EncryptServiceInterface|null
   */
  protected $encryption;

  /**
   * Constructs an Entity object.
   *
   * @param array $values
   *   An array of values to set, keyed by property name.
   * @param string $entity_type
   *   The type of the entity to create.
   * @param \Drupal\encrypt\EncryptServiceInterface|null $encryption
   *   The encryption service.
   */
  public function __construct(array $values, $entity_type, ?EncryptServiceInterface $encryption) {
    parent::__construct($values, $entity_type);
    $this->encryption = $encryption;
  }

  /**
   * {@inheritdoc}
   */
  public function isDefault() {
    return (bool) $this->default;
  }

  /**
   * {@inheritdoc}
   */
  public function setDefault(bool $default) {
    $this->default = (int) $default;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getAccessKey() {
    return $this->aws_access_key_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setAccessKey(string $aws_access_key_id) {
    $this->aws_access_key_id = $aws_access_key_id;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSecretAccessKey() {
    if (!$this->encryption || !$this->encryption_profile || $this->encryption_profile == '_none') {
      $key = $this->aws_secret_access_key;
    }
    else {
      $storage = $this->entityTypeManager()->getStorage('encryption_profile');
      /** @var \Drupal\encrypt\EncryptionProfileInterface  $encryption_profile */
      $encryption_profile = $storage->load($this->encryption_profile);
      $key = $this->encryption->decrypt($this->aws_secret_access_key, $encryption_profile);
    }

    return $key;
  }

  /**
   * {@inheritdoc}
   */
  public function setSecretAccessKey(string $aws_secret_access_key) {
    if (!$this->encryption || $this->encryption_profile == '_none') {
      $this->aws_secret_access_key = $aws_secret_access_key;
    }
    else {
      $storage = $this->entityTypeManager()->getStorage('encryption_profile');
      /** @var \Drupal\encrypt\EncryptionProfileInterface  $encryption_profile */
      $encryption_profile = $storage->load($this->encryption_profile);
      $this->aws_secret_access_key = $this->encryption->encrypt($aws_secret_access_key, $encryption_profile);
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRegion() {
    return $this->region;
  }

  /**
   * {@inheritdoc}
   */
  public function setRegion(string $region) {
    $this->region = $region;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getEncryptionProfile() {
    return $this->encryption_profile;
  }

  /**
   * {@inheritdoc}
   */
  public function setEncryptionProfile(string $encryption_profile) {
    $this->encryption_profile = $encryption_profile;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getClientArgs(string $version = 'latest') {
    return [
      'credentials' => [
        'key' => $this->getAccessKey(),
        'secret' => $this->getSecretAccessKey(),
      ],
      'region' => $this->getRegion(),
      'version' => $version,
    ];
  }

}
