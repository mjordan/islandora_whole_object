<?php

/**
 * @file
 */

namespace Drupal\islandora_whole_object\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Solarium\Core\Client\Client;

/**
 * Provides a block showing the Solr representation of the object.
 *
 * @Block(
 * id = "islandora_whole_object_solr",
 * admin_label = @Translation("Solr document for this object"),
 * category = @Translation("Islandora"),
 * )
 */
class IslandoraWholeObjectSolrBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node) {
      $solr_config = \Drupal::config('search_api.server.default_solr_server');
      $solr_configs = $solr_config->get();
      $host = $solr_configs['backend_config']['connector_config']['host'];
      $port = $solr_configs['backend_config']['connector_config']['port'];
      $core = $solr_configs['backend_config']['connector_config']['core'];
      $scheme = $solr_configs['backend_config']['connector_config']['scheme'];

      $nid = $node->id();
      $solr_url = $scheme . '://' . $host . ':' . $port . '/solr/' . $core . '/select?q=ss_search_api_id:%22entity:node/' . $nid . ':en%22';
      $response = \Drupal::httpClient()->get($solr_url, ['http_errors' => false]);
      if ($response->getStatusCode() == 404) {
        $response_output = t('Solr URL @solr_url not found.', array('@solr_url' => $solr_url));
      } else {
       $response_body = (string) $response->getBody();
      }
      return array (
        '#theme' => 'islandora_whole_object_block_pre',
        '#content' => $response_body,
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
