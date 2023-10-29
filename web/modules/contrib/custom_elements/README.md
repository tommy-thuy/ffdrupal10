# Custom elements

The Custom Elements module provides the framework for rendering Drupal data
(entities, fields, ...) into custom elements markup. Custom elements can be
easily rendered by frontend components, e.g. via web components or various
Javascript frontend frameworks. This enables Drupal to render into high-level
theme components, while the actually rendering of the components is handled by
a frontend application (possibly in the browser).

The Custom Elements module provides
 * the API to build a (nested tree) of custom element objects, with associated
cache metadata
 * the API to serialize a tree of custom objects into markup or into
a JSON representation
 * the API for other modules to customize how data is rendered into custom
elements via Custom element processors

## Frontend rendering

Today's browsers provide [an API](https://html.spec.whatwg.org/multipage/custom-elements.html#custom-elements-autonomous-example)
for developers to define their own HTML elements, like
`<flag-icon country="nl"></flag-icon>`. Besides that, many frontend frameworks
render their components using the same, or similar custom elements syntax.
That way, we can render a custom element with [Web components](https://developer.mozilla.org/de/docs/Web/Web_Components) or suiting frontend
frameworks, like [Vue.js](https://vuejs.org/).

## Custom Element markup styles

Custom elements use "slots" for handling content distribution, i.e. for passing
nested content to an element. However, the concrete syntax used for handling
slots may differ by various frameworks. Thus, the module supports rendering to
different markup styles while it defaults to the Web component style syntax,
which is supported by Vue 2 as well (via its legacy slot syntax). In addition,
the module supports the more [recent Vue2 and Vue 3](https://vuejs.org/v2/guide/components-slots.html#Named-Slots-Shorthand)
syntax which can be enabled via config:

    drush config:set custom_elements.settings  markup_style vue-3

## Default rendering

The module comes with Custom element processors that try to implement
a reasonable default for content entities. This defaults can be further
customized with custom modules as suiting, as shown by the included
`custom_elements_thunder`example module.

By default, the module renders the data of all visible fields either as
attribute to the custom element tag, or as nested markup via a slot. The module
maps simple fields and their properties to attributes and falls back to
rendering more complex fields to regular markup, which gets added as slot to
the parent custom element.

Finally, the module supports rendering layouts implemented via the core
"Layout builder". It does so by rendering them into`<layout-section>` elements
and allows the contained blocks to render into custom elements markup.

## Custom element view modes

By default, the modules does nothing unless an entity view-mode is prefixed
with `custom_elements. Every entity rendered with such a view mode is then
automatically processed via the module, what might be useful for progressively
decoupling parts of Drupal only. For that use case any Javascript libraries
needed for rendering the markup may be added to the custom_elements/main
library, which the module is attaching to custom element markup.

## Rendering complete pages

The [Lupus Custom Elements Renderer](https://www.drupal.org/project/lupus_ce_renderer)
modules switches Drupal's main content renderer to provide API responses using
custom elements markup or a custom elements json serialization for
complete pages.

## Credits

  - [drunomics GmbH](https://www.drupal.org/drunomics): Concept, Development, Maintenance
  - [Österreichischer Wirtschaftsverlag GmbH](https://www.drupal.org/%C3%B6sterreichischer-wirtschaftsverlag-gmbh): Initial sponsor of v1
