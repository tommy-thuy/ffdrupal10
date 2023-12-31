<?php

namespace Drupal\Tests\domain_alias\Functional;

use Drupal\Tests\domain\Functional\DomainTestBase;
use Drupal\Tests\domain_alias\Traits\DomainAliasTestTrait;

/**
 * Base class and helper methods for testing domain aliases.
 */
abstract class DomainAliasTestBase extends DomainTestBase {

  use DomainAliasTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['domain', 'domain_alias'];

}
