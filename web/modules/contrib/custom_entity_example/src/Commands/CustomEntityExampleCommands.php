<?php

namespace Drupal\custom_entity_example\Commands;

use Drush\Commands\DrushCommands;
use Drush\SiteAlias\SiteAliasManagerAwareInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\custom_entity_example\Service\Utils;
use stdClass;


/**
 * A drush command file.
 *
 * @package Drupal\custom_entity_example\Commands
 */
class CustomEntityExampleCommands extends DrushCommands {

  private $utils;

  public function __construct(Utils $utils) {
    $this->utils = $utils;
  }  
  /**
   * A custom Drush command to input Drupal sites from MongoDB
   *
   * @command custom_entity_example:generate_entity
   *
   * @param $src_module
   * @param $des_module
   * @param $src_url
   * @param $des_url
   * @param $src_class
   * @param $des_class
   * @param $src_lower_text
   * @param $des_lower_text
   * @param $src_upper_text
   * @param $des_upper_text
   * @param $src_package
   * @param $des_package
   * @param $ignore_files
   *
   * @validate-module-enabled custom_entity_example
   * 
   * @aliases cee-ge
   * 
   */

  public function generate_entity($src_module = 'custom_entity_example', $des_module = 'my_entity',
    $src_url = 'custom-entity-example', $des_url = 'my-entity',
    $src_class = 'CustomEntityExample', $des_class = 'MyEntity',
    $src_lower_text = 'custom entity example', $des_lower_text = 'my entity',
    $src_upper_text = 'Custom Entity Example', $des_upper_text = 'My Entity',
    $src_package = 'Custom Entity Modules', $des_package = 'Custom Entity Modules',
    $ignore_files = "drush.services.yml,custom_entity_example.routing.yml,CustomEntityExampleCommands.php,CustomEntityExampleCloneForm.php,README.txt"
  ) {
    $entities_path = \Drupal::service('extension.list.module')->getPath('custom_entity_example');

    $src = __DIR__ . "/../../../{$src_module}";
    $dst = __DIR__ . "/../../../{$des_module}";
    $ignore_files = explode(",", $ignore_files);

    $this->utils->cloneEntity(
      $src, $dst,
      $src_module, $des_module,
      $src_url, $des_url,
      $src_class, $des_class,
      $src_lower_text, $des_lower_text,
      $src_upper_text, $des_upper_text,
      $src_package, $des_package,
      $ignore_files
    );
  }
}
