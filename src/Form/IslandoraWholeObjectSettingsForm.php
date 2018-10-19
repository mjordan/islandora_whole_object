<?php
namespace Drupal\islandora_whole_object\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure example settings for this site.
 */
class IslandoraWholeObjectSettingsForm extends ConfigFormBase {
  /** 
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'islandora_whole_object_admin_settings';
  }

  /** 
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'islandora_whole_object.settings',
    ];
  }

  /** 
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('islandora_whole_object.settings');

    $options = array(
      'table' => 'RDF properties in a table',
      'media' => 'Media',
      'fedora' => 'Fedora',
      'solr' => 'Solr',
    );

    // Initial value for the checkboxes should be all checked.
    $initial_default = array();
    foreach ($options as $key => $value) {
      $initial_default[$key] = $key;
    }
    $form['show_representations'] = array(
      '#type' => 'checkboxes',
      '#title' => $this->t('Enable the following representations'),
      '#options' => $options,
      '#default_value' => $config->get('show_representations') ? $config->get('show_representations') : $initial_default,
    );

    return parent::buildForm($form, $form_state);
  }

  /** 
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
       $this->configFactory->getEditable('islandora_whole_object.settings')
      ->set('show_representations', $form_state->getValue('show_representations'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}

