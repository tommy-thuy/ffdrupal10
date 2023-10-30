# SEO Urls

Sometimes when we use, for example, a view with filters on the
page (node, term, etc.), we can get a large URL path. But in some
cases we want the SEO data to have a better, more readable look for
certain sets of filters.

This module allows creating an alternative URL for some existing one
and use seo_url token in metatags.

For example: node:seo_url

In general, this token works as a default url token, but if it sees that
there is an alternative for the current URL, it will replace it with
the new one.


## Requirements

- Core Link module - to use a link field
- PHP version more than 8.0
- Recommended Drupal version 9.5 (Drupal 10 isn't supported yet)
- Recommended [metatag](https://www.drupal.org/project/link) - to provide SEO Url via **[seo_url]** token

## Installation

Install as you would normally install a contributed Drupal module. For further
information, see
[Installing Drupal Modules](https://www.drupal.org/docs/extending-drupal/installing-drupal-modules).

## Configuration

The module has no menu or modifiable settings. There is no configuration. When
enabled, the module will prevent the links from appearing. To get the links
back, disable the module and clear caches.
