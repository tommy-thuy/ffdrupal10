# Lupus Custom Elements Renderer
This module turns Drupal into an API backend that provides the main content and
page metadata only.

The module renders pages into a tree of custom elements and provides JSON
responses containing the page metadata and content. While the responses are
always served in JSON, the page content may be delivered using custom elements
serialized as markup or as JSON data structure.

## What's supported

* Entity rendering
* Node previews
* Layout builder - given the blocks provide a custom element.

Custom routes can be added, please refer to `Development`.

## Usage

There are multiple options to enable rendering into custom_elements format:

### By adding a _format query argument

Custom elements rendering can be requested by appending
`_format=custom_elements` to any Drupal URL, e.g.

    GET /node/1?_format=custom_elements

### By enabling it per site via settings.php

Optionally, the renderer can be enabled by default for certain Drupal
site-directories (e.g.api.yoursite.com) via settings.php:

    $settings['lupus_ce_renderer_enable'] = TRUE;

When doing so, you should also make sure your cache varies by site by
add the cache context `url.site` in your site's `services.yml`

    renderer.config:
      # Renderer required cache contexts:
      #
      # The Renderer will automatically associate these cache contexts with every
      # render array, hence varying every render array by these cache contexts.
      #
      # @default [['languages:language_interface', 'theme', 'user.permissions']
      required_cache_contexts: ['languages:language_interface', 'theme', 'user.permissions', 'url.site']

The "required_cache_contexts" of renderer.config get applied during regular Drupal
rendering as well as when rendering into the `custom_elements` format.

It's suggested to create a settings.php file for the admin backend,
which has it disabled.

### Enabling it programmatically

The renderer can be enabled programmatically on a per-request basis by setting the following
request attribute:

    $request->attributes->set('lupus_ce_renderer', TRUE);

This needs to be done early in the request bootstrap, e.g. via a [HTTP Middleware](https://www.drupal.org/docs/8/api/middleware-api/overview).

## Customizing rendered requests

The supported render formats for the `custom_elements` format are
`markup` and `json`. The default render format is `markup` and can be
globally set via `settings.php`:

    $settings['lupus_ce_renderer_default_format'] = 'json';

or on a per-request basis by setting the following
request attribute:

    $request->attributes->set('lupus_ce_renderer.content_format', 'json');

This needs to be done in the request bootstrap, e.g. via a [HTTP Middleware](https://www.drupal.org/docs/8/api/middleware-api/overview).

or overridden in the url:

    GET /node/1?_content_format=markup

Finally, the optional `_select` parameter allows limiting the output to only the
content, i.e. without the wrapping json object. This is in particular helpful
for visually inspecting the content markup. The only supported value
is 'content'.

    GET /node/1?_select=content

## Advanced usage

### Changing redirect base URLs

Optionally, the redirect base URL for API requests can be set. When done so the
modules ensures that any issued redirects keep using this base URL:

    $settings['lupus_ce_renderer_redirect_base_url'] = 'https://site.com/api';

This is useful when the renderer is enabled for a site with an /api sub-path,
like yoursite.com/api, while URLs are generated without the /api prefix else.

### Altering response data

Lupus Custom Elements Renderer provides two ways for altering response data
(title, messages, breadcrumbs, metatags, content format, local tasks, page
layout).

First the overrides from request attributes
(key `lupus_ce_renderer_response_data`) are applied to response data.

Secondly, `hook_lupus_ce_renderer_response_alter` hook is fired.

## Development

Custom routes may be added by add the `_format` requirement. Refer to
`lupus_ce_renderer.routing.yml` for a simple example. For more complex routes it
can make sense to take add variants of existing route definitions via a route
subscriber - see `lupus_ce_renderer.routing.yml` for an example.

## Credits

* Initial development by Wolfgang Ziegler, drunomics GmbH <hello@drunomics.com>
