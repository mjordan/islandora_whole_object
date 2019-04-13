<?php

/**
 * @file
 */

namespace Drupal\islandora_whole_object\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a block showing the Fedora Turtle representation of the object.
 *
 * @Block(
 * id = "islandora_whole_object_fedora",
 * admin_label = @Translation("Fedora RDF (Turtle) for this object"),
 * category = @Translation("Islandora"),
 * )
 */
class IslandoraWholeObjectFedoraBlock extends BlockBase implements BlockPluginInterface {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = \Drupal::routeMatch()->getParameter('node');
    $nid = $node->id();
    $drupal_url = 'http://localhost:8000/node/' . $nid . '?_format=json';
    $response = \Drupal::httpClient()->get($drupal_url);
    $response_body = (string) $response->getBody();
    $body_array = json_decode($response_body, true);
    $uuid = $body_array['uuid'][0]['value'];

    // Assemble the Fedora URL.
    $uuid_parts = explode('-', $uuid);
    $subparts = str_split($uuid_parts[0], 2);
    $config = $this->getConfiguration();
    $fedora_url = $config['fedora_endpoint'] . implode('/', $subparts) . '/'. $uuid;

    // Get the Turtle from Fedora.
    $client = \Drupal::httpClient();
    $response = $client->request('GET', $fedora_url, ['http_errors' => false]);
    if ($response->getStatusCode() == 404) {
      $response_output = t('Resource @fedora_url not found.', array('@fedora_url' => $fedora_url));
    } else {
      $response_output = (string) $response->getBody();
    }
    return array (
      '#theme' => 'islandora_whole_object_block_pre',
      '#content' => $response_output,
    );
  }

   /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['fedora_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Fedora endpoint'),
      '#description' => $this->t('Be sure to include the trailing /, e.g., "http://localhost:8080/fcrepo/rest/".'),
      '#default_value' => isset($config['fedora_endpoint']) ? $config['fedora_endpoint'] : 'http://localhost:8080/fcrepo/rest/',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['fedora_endpoint'] = $values['fedora_endpoint'];
  }
}
