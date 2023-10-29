Inspired by Danny Sipos's (Upchuk) article:
https://www.webomelette.com/simple-guzzle-api-mocking-functional-testing-drupal-8

Problem
-------

When running tests, you don't want to perform external HTTP requests while you
still want to test code that consumes such webservices. This module intercepts 
the requests made by the Drupal `http_client` service and allows a plugin to
respond with a mocked response.

Usage
-----

In order to mock a webservice, you'll need to create a _service mock_ plugin.
Such a plugin implements the `ServiceMockPluginInterface`. The plugin manager
will pickup the first plugin that matches the HTTP request and will call the
plugin's `::getResponse()` method. Note that several plugins may qualify for the
same request, but you can specify a `weight` in plugin's annotation so that such
plugins are prioritized.

Implement one or more plugins for each webservice that you want to mock. In your
tests, enable this module, and the modules that are shipping such plugins. This
module ships a testing plugin that intercepts all outgoing HTTP requests made to
example.com.

In some cases, tests may narrow the list of plugins to a limited set. Such tests
should pass an array of plugin IDs to the `http_request_mock.allowed_plugins`
state variable. Leaving this variable empty or not set, will allow all plugins
to apply to the following outgoing HTTP requests.

Claudiu Cristea (claudiu.cristea) | https://www.drupal.org/u/claudiucristea
