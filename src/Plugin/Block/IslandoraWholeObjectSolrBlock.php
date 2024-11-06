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

      $output = [
        '#theme' => 'islandora_whole_object_block_pre',
        '#content' => $response_body,
      ];

      // If highlighter is available, use it to improve Solr json display.
      if (\Drupal::service('module_handler')->moduleExists('highlight_js')) {
        $output['#attached'] = [
          'library' => [
            'highlight_js/highlight_js.js',
            'highlight_js/highlight_js.custom',
            'highlight_js/highlight_js.style-atom-one-light',
          ],
          'drupalSettings' => [
            'button_data' => [
              'copy_enable' => TRUE,
              'copy_btn_text' => 'Copy',
            ],
          ]
        ];
      }

      return $output;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
