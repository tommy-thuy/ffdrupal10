<?php

namespace Drupal\Tests\jsonlog\Unit;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LogMessageParserInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\jsonlog\Logger\JsonLog;
use Drupal\jsonlog\Logger\JsonLogData;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;

/**
 * Unit tests for JsonLog class
 *
 * @group JsonLog
 *
 * Class JsonLogTest
 * @package Drupal\Tests\jsonlog\Unit
 */
class JsonLogTest extends UnitTestCase {

  const DEFAULT_THRESHOLD = 4;

  const DEFAULT_LOG_DIR = '/var/log';

  const DEFAULT_TIME_FORMAT = 'Ymd';

  const DEFAULT_SITE_ID = 'jsonlog_test';

  /**
   * @var mixed - mock of \Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStackMock;

  /**
   * The mocked config factory.
   *
   * @var \PHPUnit\Framework\MockObject\MockBuilder|\Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactoryMock;

  /**
   * The mocked log message parser.
   *
   * @var \PHPUnit\Framework\MockObject\MockBuilder|\Drupal\Core\Logger\LogMessageParserInterface
   */
  protected $messageParserMock;

  /**
   * The mocked module handler.
   *
   * @var \PHPUnit\Framework\MockObject\MockBuilder|\Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandlerMock;

  /**
   * @var \Drupal\jsonlog\Logger\JsonLog
   */
  private $jsonLogger;

  protected function setUp(): void {
    parent::setUp();

    $this->setUpMocks();

    $this->jsonLogger = new JsonLog($this->configFactoryMock, $this->messageParserMock, $this->moduleHandlerMock, $this->requestStackMock);
  }

  /**
   * Creates mocked services to inject into the JsonLog class.
   *
   * @param array $settings
   *   An array of JsonLog settings.
   */
  protected function setUpMocks(array $settings = []) {
    $config_stub['jsonlog.settings'] = $settings + [
      'jsonlog_severity_threshold' => self::DEFAULT_THRESHOLD,
      'jsonlog_channels' => '',
      'jsonlog_truncate' => 64,
      'jsonlog_siteid' => self::DEFAULT_SITE_ID,
      'jsonlog_canonical' => '',
      'jsonlog_file_time' => self::DEFAULT_TIME_FORMAT,
      'jsonlog_dir' => self::DEFAULT_LOG_DIR,
      'jsonlog_tags' => 'test',
    ];

    $this->configFactoryMock = $this->getConfigFactoryStub($config_stub);
    $this->messageParserMock = $this->getEmptyMessageParserMock();
    $this->moduleHandlerMock = $this->createMock('Drupal\Core\Extension\ModuleHandlerInterface');
    $this->requestStackMock = $this->createMock('Symfony\Component\HttpFoundation\RequestStack');
  }

  /**
   * test to see if a log file is well prepared
   */
  public function testCanPrepareLogFile() {
    $request_mock = $this->createMock('Symfony\Component\HttpFoundation\Request');
    $request_mock->expects($this->exactly(1))
      ->method('getRealMethod')
      ->willReturn('POST');

    /** @var Request $request_mock */
    $this->setupContainerCurrentRequest($request_mock);

    $test_context = [
      'user' => NULL,
      'uid' => 12345,
      'ip' => '127.0.0.9',
      'request_uri' => 'admin/help',
      'channel' => '500',
      'link' => '',
      'referer' => '',
    ];

    $level = self::DEFAULT_THRESHOLD - 1;
    $log_entry = $this->jsonLogger->prepareLog($level, 'test', $test_context);

    $this->assertFalse($log_entry === FALSE, 'Log entry constructed');
    $this->assertEquals(JsonLogData::class, get_class($log_entry));
    $this->assertEquals('500', $log_entry->getData()['subtype'], 'Correct subtype logged');
    $this->assertEquals('127.0.0.9', $log_entry->getData()['client_ip'], 'Correct client_ip logged');
    $this->assertEquals('admin/help', $log_entry->getData()['request_uri'], 'Correct request_uri logged');
    $this->assertEquals('POST', $log_entry->getData()['method'], 'Correct method logged');
    $this->assertEquals(12345, $log_entry->getData()['uid'], 'Uid logged.');
  }

  /**
   * test to see if a log file is not prepared when the log-level is too high
   */
  public function testCannotPrepareLogFileIfThresholdExceeded() {
    $level = self::DEFAULT_THRESHOLD + 1;
    $log_entry = $this->jsonLogger->prepareLog($level, 'test', []);

    $this->assertTrue($log_entry === FALSE, 'Log entry not built');
  }

  /**
   * test correct filename can be constructed
   */
  public function testGetCorrectFilename() {
    $time = date(self::DEFAULT_TIME_FORMAT);
    $filename = $this->jsonLogger->getFileName(self::DEFAULT_TIME_FORMAT);

    $this->assertEquals(
      self::DEFAULT_LOG_DIR . '/' . self::DEFAULT_SITE_ID . '.' . $time . '.json.log',
      $filename,'Correct filename constructed.');
  }

  /**
   * Checks that the logs can be filtered by log channels.
   *
   * @dataProvider logChannelFilterProvider
   */
  public function testLogChannelFilter($whitelisted_channels, $log_channel, $expected) {
    $this->setUpMocks(['jsonlog_channels' => $whitelisted_channels]);

    $json_logger = new JsonLog($this->configFactoryMock, $this->messageParserMock, $this->moduleHandlerMock, $this->requestStackMock);

    $level = self::DEFAULT_THRESHOLD;

    $context = [
      'user' => NULL,
      'uid' => 12345,
      'ip' => '127.0.0.9',
      'request_uri' => '',
      'channel' => $log_channel,
      'link' => '',
      'referer' => '',
    ];

    $log_entry = $json_logger->prepareLog($level, 'Test', $context);

    $this->assertEquals($expected, (bool) $log_entry);
  }

  /**
   * Data provider for ::testLogChannelFilter()
   */
  public function logChannelFilterProvider() {
    return [
      // If the log channel filter is left empty, and we are reporting on the
      // 'jsonlog' channel, the message should be logged.
      ['', 'jsonlog', TRUE],
      // If the log channel filter is left empty, and we are not reporting on a
      // particular channel, the message should be logged.
      ['', NULL, TRUE],
      // If the log channel filter is set to the reported channel, the message
      // should be logged.
      ['jsonlog', 'jsonlog', TRUE],
      // If the log channel filter is set, but we are not reporting on a
      // particular channel, the message should not be logged.
      ['jsonlog', NULL, FALSE],
      // If the log channel filter is set to multiple comma-separated channels
      // which includes the reported channel, the message should be logged.
      ['php,jsonlog', 'jsonlog', TRUE],
      // If the log channel filter is set to multiple comma-separated channels
      // but we are not reporting on a particular channel, the message should
      // not be logged.
      ['php,jsonlog', NULL, FALSE],
      // If the log channel filter is set to a different channel, the message
      // should not be logged.
      ['php', 'jsonlog', FALSE],
      // If the log channel filter is set to multiple comma-separated channels
      // which don't include the reported channel, the message should not be
      // logged.
      ['php,user', 'jsonlog', FALSE],
    ];
  }

  /**
   * @return \PHPUnit\Framework\MockObject\MockObject
   */
  private function getEmptyMessageParserMock() {
    $messageParserMock = $this->createMock('Drupal\Core\Logger\LogMessageParserInterface');
    $messageParserMock
      ->expects($this->atMost(1))
      ->method('parseMessagePlaceholders')
      ->withAnyParameters()
      ->willReturn([]);

    return $messageParserMock;
  }

  /**
   * @param \Symfony\Component\HttpFoundation\Request $request
   */
  private function setupContainerCurrentRequest(Request $request) {
    $this->requestStackMock->expects($this->any())
      ->method('getCurrentRequest')
      ->willReturn($request);

    $containerMock = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');
    $containerMock->expects($this->any())
      ->method('get')
      ->with('request_stack')
      ->willReturn($this->requestStackMock);

    /** @var \Symfony\Component\DependencyInjection\ContainerInterface $containerMock */
    \Drupal::setContainer($containerMock);
  }

}
