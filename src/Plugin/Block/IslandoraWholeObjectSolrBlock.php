<?php

namespace Drupal\islandora_whole_object\Plugin\Block;

use Drupal\Core\Block\BlockBase;

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
      $solr_url = $scheme . '://' . $host . ':' . $port . '/solr/' . $core . '/select?q=ss_search_api_id:entity\:node/' . $nid . '\:*';
      $response = \Drupal::httpClient()->get($solr_url, ['http_errors' => FALSE]);
      if ($response->getStatusCode() == 404) {
        $response_body = t('Solr URL @solr_url not found.', ['@solr_url' => $solr_url]);
      }
      else {
        $response_body = (string) $response->getBody();
      }
      return [
        '#theme' => 'islandora_whole_object_block_pre',
        '#content' => $response_body,
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
