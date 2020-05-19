<?php

/**
 * @file
 */

namespace Drupal\islandora_whole_object\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a block containing the Media view for the object.
 *
 * @Block(
 * id = "islandora_whole_object_media",
 * admin_label = @Translation("Media associated with this object"),
 * category = @Translation("Islandora"),
 * )
 */
class IslandoraWholeObjectMediaBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node) {
      $nid = $node->id();
      $output = views_embed_view('media_of', 'page_1', $nid);

      return array (
        '#theme' => 'islandora_whole_object_block_media',
        '#content' => $output,
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
