# Changelog

All notable changes to **drupal/jsonlog** will be documented in this file,
using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.


## [Unreleased 4.x]

### Added
- Do not translate severity levels:
  issue [\#3314848](https://www.drupal.org/project/jsonlog/issues/3314848)
- Filter logs based on logger channel channel name
  issue [\#3045452](https://www.drupal.org/project/jsonlog/issues/3045452)
- Gitlab CI support for:
  - _TARGET_PHP: "8.1"
  - _TARGET_CORE: "10.1"

### Changed

### Fixed


## [3.1.0] - 2023-10-13

### Added
- Gitlab CI support for: 
  - _TARGET_PHP: "7.4"
  - _TARGET_CORE: "9.5.11"

### Changed
  - Use/initialise empty strings instead of NULL values (JsonLogData) to prevent parse failures in external systems.

### Fixed
- Formattable strings are not supported
  (issue [\#3309387](https://www.drupal.org/project/jsonlog/issues/3309387)).
- PHP 8.1, fix warnings about "ctype_digit()"
  (issue [\#3314283](https://www.drupal.org/project/jsonlog/issues/3314283)).

## [3.0.0] - 2021-08-22

### Added
- Option to log to STDOUT.
- Settings form handle stdout vs. filing gracefully and do support logging
  test entry to stdout.

### Changed
- Removed old release_note.txt.
- Default to append newline to log file entry; instead of prepending.

### Fixed
- Fix logging of current user ID (uid) as the current user object is no longer
  available in the logging $context array (issue [\#3213933](https://www.drupal.org/project/jsonlog/issues/3213933)).
  Also see the related Drupal core change record [\#2986320](https://www.drupal.org/node/2986320).


## [8.x-1.3] - 2020-05-30

### Added
- Support log file entry appending newline instead of prepending; new setting
(bool) jsonlog_newline_prepend. Do default to old behaviour (prepending) until
next major version
(issue [\#3095077](https://www.drupal.org/project/jsonlog/issues/3095077)).
- Drupal 9 compatibility, including D9 deprecations and .info.yml
core_version_requirement flag
(issue [\#3140879](https://www.drupal.org/project/jsonlog/issues/3140879#comment-13636523)).

### Changed
- Changelog in standard keepachangelog format; previous was idiosyncratic.
- Changelog include changes in the 7.x original until port to 8.x.

### Fixed
- Credit both developers of the module.
- Drupal 9 deprecation: PHPUnit_Framework_MockObject_MockObject;
Use PHPUnit\Framework\MockObject\MockObject instead.
- PHP 7.4 deprecation: The array and string offset access syntax using curly
braces is deprecated
(issue [\#3109087](https://www.drupal.org/project/jsonlog/issues/3109087)).
- Submitting entry example in settings form shan't err due to tags as array
in hidden field which doesn't support multidimensional array (Notice: Array to
string conversion in Drupal\Core\Template\AttributeArray->__toString()).


## [8.x-1.2] - 2019-03-16

### Added
- [\#3020527](https://www.drupal.org/project/jsonlog/issues/3020527)
Support TranslatableMarkup messages.

### Fixed
- [\#3028292](https://www.drupal.org/project/jsonlog/issues/3028292)
No longer using config->getEditable in JSONlog class.
- Fix truncation for larger messages.
- [\#3040691](https://www.drupal.org/project/jsonlog/issues/3040691)
Remove several deprecated calls.


## [8.x-1.1] - 2018-03-21

### Fixed
- [\#2954891](https://www.drupal.org/project/jsonlog/issues/2954891)
Properly handle empty http-request logs.
- Replace deprecated getMock with createMock.


## [8.x-1.0] - 2017-08-31

### Changed
- Updated release_notes.


## [8.x-1.0-beta1] - 2017-05-03

### Added
- Initial D8 version.
- Added unit tests
- Added functional test


<!-- Further changes to the 7.x track will not be reflected in this file. -->

## [7.x-2.1] - 2015-06-12

### Added
- Replace variables into message.
- Support event/error 'code'; an exploitation of Drupal watchdog's 'link'
property (when that is integer or 'integer').
- New 'canonical' name property for simpler identification across load balanced
site instances.

### Changed
- Variable replacement must take place _before_ escaping message.
- Reset jsonlog_dir to default by submitting empty jsonlog_dir field.

### Fixed
- Settings page: fieldsets have no description - doh.


## [7.x-2.0] - 2014-12-16

### Changed
- Watchdog 'type' is now being logged as JSON 'subtype', and JSON 'type' is
always 'drupal'.
- Replaced the setting jsonlog_file with jsonlog_dir.
- Now defaults to write to daily log files instead of one file forever.
- Increased default truncation to 64 (Kb, was 4).

### Fixed
- Don't escape newlines, (drupal_)json_encode() does that.


## [7.x-1.3] - 2014-11-12

### Changed
- Site id and file settings are required.

### Fixed
- Fixed that settings form didn't work at all
(issue [\#2373857](https://www.drupal.org/project/jsonlog/issues/2373857)).
- Test filing shan't manipulate drupal file setting unnecessarily.


## [7.x-1.2] - 2014-11-11

### Changed
- Renamed 'event_id' to 'message_id'.

### Fixed
- Use file locking
(issue[#2373039](https://www.drupal.org/project/jsonlog/issues/2373039)).


## [7.x-1.1] - 2014-10-21

### Added
- jsonlog_test_filing() is now usable for drush.
- Better phpdoc'umentation.

### Changed
- Moved helper functions which aren't needed - except initially and when
administating the module - to include file.

### Fixed
- Fix negation of writable in filing test
(issue [\#2360577](https://www.drupal.org/project/jsonlog/issues/2360577)).


## [7.x-1.0] - 2014-10-20

### Added
- Initial.
- Truncation.
- Newline escaping (json_encode() doesn't escape control chars).
- Log to standard log if filing fails.
- Establish default log dir when error_log directive is 'syslog'.
- Remove null byte.
- Test write when user submits the logging settings form.
- All conf variables overridable by server environment variables.
- Implemented tags.
