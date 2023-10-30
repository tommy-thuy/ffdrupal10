<?php

namespace Drupal\Tests\aws\Kernel;

/**
 * Tests the AWS Profile entity.
 *
 * @group aws
 */
class ProfileTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $storage = $this->entityTypeManager->getStorage('aws_profile');
    $this->profile = $storage->load('aws_test');
  }

  /**
   * Tests checking if a profile is the default.
   *
   * @covers ::isDefault
   */
  public function testIsDefault() {
    $this->assertTrue($this->profile->isDefault());
  }

  /**
   * Tests setting the default status of a profile.
   *
   * @covers ::setDefault
   */
  public function testSetDefault() {
    $this->profile->setDefault(FALSE);
    $this->assertFalse($this->profile->isDefault());
  }

  /**
   * Tests getting the access key.
   *
   * @covers ::getAccessKey
   */
  public function testGetAccessKey() {
    $this->assertEquals('TestAccessKey', $this->profile->getAccessKey());
  }

  /**
   * Tests setting the access key.
   *
   * @covers ::setAccessKey
   */
  public function testSetAccessKey() {
    $key = $this->randomString();

    $this->profile->setAccessKey($key);
    $this->assertEquals($key, $this->profile->getAccessKey());
  }

  /**
   * Tests getting the secret access key.
   *
   * @covers ::getSecretAccessKey
   */
  public function testGetSecretAccessKey() {
    $this->assertEquals('TestSecretKey', $this->profile->getSecretAccessKey());
  }

  /**
   * Tests setting the secret access key.
   *
   * @covers ::setSecretAccessKey
   */
  public function testSetSecretAccessKey() {
    $key = $this->randomString();

    $this->profile->setSecretAccessKey($key);
    $this->assertEquals($key, $this->profile->getSecretAccessKey());
  }

}
