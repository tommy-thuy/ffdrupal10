# Paragraphs Sets

The "Paragraphs Sets" module allows to create sets of paragraphs added to an
entity by default or selected while creating the entity.

For a full description of the module, visit the
[project page](https://www.drupal.org/project/paragraphs_sets).

Submit bug reports and feature suggestions, or track changes in the
[issue queue](https://www.drupal.org/project/issues/paragraphs_sets).


## Requirements

Obviously you'll need to install the [Paragraphs](https://www.drupal.org/project/paragraphs)
module.


## Installation

Install as usual, see
[Installing Drupal Modules](https://www.drupal.org/docs/extending-drupal/installing-modules)
for further information.


## Widget settings

"Paragraphs Sets" exposes settings on the "Paragraphs EXPERIMENTAL" widget.

- **Enable Paragraphs Sets:** Whether to show the Paragraphs Sets form element
  at the top of the Paragraphs widget.
- **Limit sets to:** Restrict the sets available in the Paragraphs Sets form
  element.
- **Default set:** Use this Paragraphs Sets set as the "default value" for the
  widget. Note that the "Default paragraph type" must be set to "- None -"
  for this to work.


## Configuration examples

There is no UI yet for creating paragraph sets so you have to create the
config entities manually. Setting default values is limited (and tested only)
for primitive field types. Setting complex field values requires implementing
hook_paragraphs_set_data_alter(). See paragraphs_sets.api.php for all hooks.

- Create a set containing a single paragraph of type "text":

  ```
    <code>
    id: ps_example_text
    label: 'Simple text (empty)'
    description: 'Simple text paragraph without values'
    paragraphs:
      -
        bundle: text
        data: {  }
    </code>
  ```

- Create a set containing multiple paragraphs with default values:

  ```
    <code>
    id: ps_example_text_multiple
    label: 'Multiple paragraphs'
    description: 'Multiple paragraphs with default values'
    paragraphs:
      -
        bundle: text
        data:
          field_headline: 'First item'
          field_content: '<p>You may add HTML, too!</p>'
      -
        bundle: text_with_image
        data:
          field_headline: 'Second item'
      -
        bundle: text
        data:
          field_content: '<p>This is another text paragraph.</p>'
    </code>
  ```


## Maintainers

- Stefan Borchert - [stborchert](https://www.drupal.org/u/stborchert)

**This project has been sponsored by:**

- undpaul - Drupal experts providing professional Drupal development services.
  Visit [undpaul](https://www.undpaul.de) for more information.
