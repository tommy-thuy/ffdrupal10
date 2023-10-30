CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Recommended modules
 * Installation
 * Configuration
 * Maintainers

INTRODUCTION
------------

This module is a fork of AmazonS3 CORS Upload, re-written to work with the
S3 File System module, rather than AmazonS3.

REQUIREMENTS
------------

No special requirements.

RECOMMENDED MODULES
-------------------

 * This module requires S3 File System 8.x-3.x
   (https://www.drupal.org/project/s3fs)
 * Token 8.x-1.x. (https://www.drupal.org/project/token)
 * These dependencies are installed automatically if composer is used to manage
   this  module.

INSTALLATION
------------

 * Install as you would normally install a contributed Drupal module.
   See: https://www.drupal.org/node/895232 for further information.

CONFIGURATION
-------------

Ensure your S3 file system is correctly configured via the admin page at
"/admin/config/media/s3fs".

To configure your S3 bucket so that it will accept CORS uploads, go to the
"/admin/config/media/s3fs/cors" page on your admin site, fill the "CORS Origin"
field with your site's domain name or names, and submit it. Note the warnings
regarding changes to or deletion of the CORS data from your S3 bucket.

MAINTAINERS
-----------

Current maintainers:
  * coredumperror (https://www.drupal.org/u/coredumperror)
  * webankit (https://www.drupal.org/u/webankit)
  * jlscott (https://www.drupal.org/u/jlscott)
