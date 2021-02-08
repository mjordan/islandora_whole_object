<?php

/**
 * @file
 */

namespace Drupal\islandora_whole_object\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Site\Settings;

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
    if (!$node) {
      return array();
    }
    $uuid = $node->uuid();

    // Assemble the Fedora URL.
    $uuid_parts = explode('-', $uuid);
    $subparts = str_split($uuid_parts[0], 2);
    $settings = Settings::get('flysystem');
    $fedora_url = $settings['fedora']['config']['root'] . implode('/', $subparts) . '/'. $uuid;

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
  public function getCacheMaxAge() {
    return 0;
  }

}
