<?php

/**
 * @file
 */

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
      $nid = $node->id();
      $solr_url = 'http://localhost:8983/solr/ISLANDORA/select?q=ss_search_api_id:%22entity:node/' . $nid . ':en%22';
      $response = \Drupal::httpClient()->get($solr_url);
      $response_body = (string) $response->getBody();
      return array (
        '#theme' => 'islandora_whole_object_block_pre',
        '#content' => $response_body,
      );
    }
  }
}
