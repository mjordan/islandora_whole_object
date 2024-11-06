<?php

namespace Drupal\islandora_whole_object\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Site\Settings;

/**
 * Provides a block showing the Fedora Turtle representation of the object.
 *
 * @Block(
 * id = "islandora_whole_object_blazegraph",
 * admin_label = @Translation("Blazegraph RDF (N-Triples) for this object"),
 * category = @Translation("Islandora"),
 * )
 */
class IslandoraWholeObjectBlazegraphBlock extends BlockBase implements BlockPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = \Drupal::routeMatch()->getParameter('node');
    if (!$node) {
      return [];
    }

    global $base_url;
    $current_url = $base_url . \Drupal::request()->getRequestUri();
    $current_url = urlencode($current_url);

    // Assemble the Blazegraph URL.
    $config = $this->getConfiguration();
    $blazegraph_url = $config['blazegraph_endpoint'] . 'namespace/islandora/sparql?query=DESCRIBE%20%3C' . $current_url . '%3E';

    // Get the N-Triples from Blazegraph.
    $client = \Drupal::httpClient();
    $headers = ['Accept' => 'text/plain'];
    $response = $client->request('GET', $blazegraph_url, ['headers' => $headers, 'http_errors' => FALSE]);
    if ($response->getStatusCode() == 404) {
      $response_output = t('Resource @blazegraph_url not found.', ['@blazegraph_url' => $blazegraph_url]);
    }
    else {
      $response_output = (string) $response->getBody();
    }
    return [
      '#theme' => 'islandora_whole_object_block_pre',
      '#content' => $response_output,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['blazegraph_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Blazegraph endpoint'),
      '#description' => $this->t('Be sure to include the trailing /, e.g., "http://localhost:8080/bigdata/".'),
      '#default_value' => $config['blazegraph_endpoint'] ?? 'http://localhost:8080/bigdata/',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['blazegraph_endpoint'] = $values['blazegraph_endpoint'];
  }

}
