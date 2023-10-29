<?php

namespace Drupal\custom_entity_example\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\custom_entity_example\Service\Utils;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Exception;
use stdClass;

/**
 * Implements the SimpleForm form controller.
 *
 * This example demonstrates a simple form with a single text input element. We
 * extend FormBase which is the simplest form base class used in Drupal.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class CustomEntityExampleCloneForm extends FormBase {
  private $utils;

  public function __construct(Utils $utils) {
    $this->utils = $utils;  
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get("custom_entity_example.utils")
    );
  }

  /**
   * Build the simple form.
   *
   * A build form method constructs an array that defines how markup and
   * other form elements are included in an HTML form.
   *
   * @param array $form
   *   Default form array structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object containing current form state.
   *
   * @return array
   *   The render array defining the elements of the form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['description_group'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'replace-group',
        ],
        'style' => 'color: red;'
      ],
    ];

    $form['description_group']['label'] = [
      '#type' => 'markup',
      '#markup' => "<strong style='color: red; font-weight: bold;'>***" . $this->t('Please ensure the folder containing this module is writable for your web account.') . "***<strong>",
    ];

    $form['module_name'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'replace-group',
        ],
        'style' => 'display: flex;'
      ],
    ];
    $form['module_name']['src_module_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Source module name'),
      '#required' => TRUE,
      '#default_value' => 'custom_entity_example',
      '#attributes' => [
        'style' => 'margin-right: 25px',
      ],
    ];

    $form['module_name']['des_module_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Destination module name'),
      '#required' => TRUE,
      '#default_value' => 'my_entity',
    ];

    $form['url_keyword_group'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'replace-group',
        ],
        'style' => 'display: flex;'
      ],
    ];
    $form['url_keyword_group']['src_url_keyword'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Source url keyword'),
      '#required' => TRUE,
      '#default_value' => 'custom-entity-example',
      '#attributes' => [
        'style' => 'margin-right: 25px',
      ],
    ];

    $form['url_keyword_group']['des_url_keyword'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Destination url keyword'),
      '#required' => TRUE,
      '#default_value' => 'my-entity',
    ];

    $form['class_group'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'replace-group',
        ],
        'style' => 'display: flex;'
      ],
    ];
    $form['class_group']['src_class'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Source class keyword'),
      '#required' => TRUE,
      '#default_value' => 'CustomEntityExample',
      '#attributes' => [
        'style' => 'margin-right: 25px',
      ],
    ];

    $form['class_group']['des_class'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Destination class keyword'),
      '#required' => TRUE,
      '#default_value' => 'MyEntity',
    ];

    $form['lowercase_text_title_group'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'replace-group',
        ],
        'style' => 'display: flex;'
      ],
    ];
    $form['lowercase_text_title_group']['src_lowercase_text_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Source lowercase text title'),
      '#required' => TRUE,
      '#default_value' => 'custom entity example',
      '#attributes' => [
        'style' => 'margin-right: 25px',
      ],
    ];

    $form['lowercase_text_title_group']['des_lowercase_text_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Destination lowercase text title'),
      '#required' => TRUE,
      '#default_value' => 'my entity',
    ];

    $form['text_title_group'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'replace-group',
        ],
        'style' => 'display: flex;'
      ],
    ];
    $form['text_title_group']['src_text_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Source text title'),
      '#required' => TRUE,
      '#default_value' => 'Custom Entity Example',
      '#attributes' => [
        'style' => 'margin-right: 25px',
      ],
    ];

    $form['text_title_group']['des_text_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Destination text title'),
      '#required' => TRUE,
      '#default_value' => 'My Entity',
    ];

    $form['package_group'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'replace-group',
        ],
        'style' => 'display: flex;'
      ],
    ];
    $form['package_group']['src_package'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Source package'),
      '#required' => TRUE,
      '#default_value' => 'Custom Entity Modules',
      '#attributes' => [
        'style' => 'margin-right: 25px',
      ],
    ];

    $form['package_group']['des_package'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Destination package'),
      '#required' => TRUE,
      '#default_value' => 'Custom Entity Modules',
    ];

    $form['ignore_files'] = [
      '#type' => 'textfield',
      '#title' => $this->t('ignore_files'),
      '#required' => TRUE,
      '#default_value' => 'drush.services.yml,custom_entity_example.routing.yml,CustomEntityExampleCommands.php,CustomEntityExampleCloneForm.php,README.txt',
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Generate entity'),
    ];
    return $form;
  }

  /**
   * Getter method for Form ID.
   *
   * The form ID is used in implementations of hook_form_alter() to allow other
   * modules to alter the render array built by this form controller. It must be
   * unique site wide. It normally starts with the providing module's name.
   *
   * @return string
   *   The unique ID of the form defined by this class.
   */
  public function getFormId() {
    return 'custom_entity_example_clone_form';
  }

  /**
   * Implements form validation.
   *
   * The validateForm method is the default method called to validate input on
   * a form.
   *
   * @param array $form
   *   The render array of the currently built form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object describing the current state of the form.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {    

  }

  /**
   * Implements a form submit handler.
   *
   * The submitForm method is the default method called for any submit elements.
   *
   * @param array $form
   *   The render array of the currently built form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object describing the current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    set_time_limit(0);

    $src_module_name = $form_state->getValue('src_module_name', 'custom_entity_example');
    $des_module_name = $form_state->getValue('des_module_name', 'my_entity');
    $src_url_keyword = $form_state->getValue('src_url_keyword', 'custom-entity-example');
    $des_url_keyword = $form_state->getValue('des_url_keyword', 'my-entity');
    $src_class = $form_state->getValue('src_class', 'CustomEntityExample');
    $des_class = $form_state->getValue('des_class', 'MyEntity');
    $src_lowercase_text_title = $form_state->getValue('src_lowercase_text_title', 'custom entity example');
    $des_lowercase_text_title = $form_state->getValue('des_lowercase_text_title', 'my entity');
    $src_text_title = $form_state->getValue('src_text_title', 'Custom Entity Example');
    $des_text_title = $form_state->getValue('des_text_title', 'My Entity');
    $src_package = $form_state->getValue('src_package', 'Custom Entity Modules');
    $des_package = $form_state->getValue('des_package', 'Custom Entity Modules');
    $ignore_files = $form_state->getValue('ignore_files', 'drush.services.yml,custom_entity_example.routing.yml,CustomEntityExampleCommands.php,CustomEntityExampleCloneForm.php,README.txt');

    $module_path = \Drupal::service('extension.list.module')->getPath('custom_entity_example');

    $real_path = \Drupal::service('file_system')->realpath($module_path . "/..");
    // $real_path = \Drupal::service('file_system')->realpath( __DIR__ . "/../../../");

    $src = $real_path . "/{$src_module_name}";
    $dst = $real_path . "/{$des_module_name}";

    // $command_line = "drush cee-ge '$src_module_name' '$des_module_name' '$src_url_keyword' '$des_url_keyword' '$src_class' '$des_class' '$src_lowercase_text_title' '$des_lowercase_text_title' '$src_text_title' '$des_text_title' '$src_package' '$des_package' '$ignore_files'";
    // print_r($command_line);
    // shell_exec($command_line);

    $ignore_files = explode(",", $ignore_files);
    $this->utils->cloneEntity(
      $src, $dst,
      $src_module_name, $des_module_name,
      $src_url_keyword, $des_url_keyword,
      $src_class, $des_class,
      $src_lowercase_text_title, $des_lowercase_text_title,
      $src_text_title, $des_text_title,
      $src_package, $des_package,
      $ignore_files
    );
  }
}
