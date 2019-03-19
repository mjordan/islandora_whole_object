<?php

/**
 * @file
 */

namespace Drupal\islandora_whole_object\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a block showing the Fedora Turtle representation of the object.
 *
 * @Block(
 * id = "islandora_whole_object_fedora",
 * admin_label = @Translation("Fedora RDF (Turtle) for this object"),
 * category = @Translation("Islandora"),
 * )
 */
class IslandoraWholeObjectFedoraBlock extends BlockBase {
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
    $fedora_url = 'http://localhost:8080/fcrepo/rest/' . implode('/', $subparts) . '/'. $uuid;

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
}
