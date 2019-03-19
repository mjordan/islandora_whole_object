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
 * admin_label = @Translation("Islandora Whole Object Media block"),
 * category = @Translation("Islandora"),
 * )
 */
class IslandoraWholeObjectMediaBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = \Drupal::routeMatch()->getParameter('node');
    $nid = $node->id();
    $output = views_embed_view('media_of', 'page_1', $nid);

    return array (
      '#theme' => 'islandora_whole_object_block_media',
      '#content' => $output,
    );
  }
}
