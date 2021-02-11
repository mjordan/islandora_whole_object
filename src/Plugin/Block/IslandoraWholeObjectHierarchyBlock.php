<?php

/**
 * @file
 */

namespace Drupal\islandora_whole_object\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a block showing the properties of the object.
 *
 * @Block(
 * id = "islandora_whole_object_hierarchy",
 * admin_label = @Translation("Current object's parents and children"),
 * category = @Translation("Islandora"),
 * )
 */
class IslandoraWholeObjectHierarchyBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    global $base_url;
    $node = \Drupal::routeMatch()->getParameter('node');
    if (!$node) {
      return array();
    }
    $nid = $node->id();
    if ($node) {
      $output_node = ['label' => $node->label(), 'nid' => $nid];

      // Get parents.
      $output_parents = [];
      $node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);;
      $parents = $node->field_member_of->referencedEntities();
      foreach ($parents as $parent) {
	$output_parents[] = ['nid' => $parent->id(), 'label' => $parent->label()];
      }

      // Get children, sorted by field_weight.
      $entity = \Drupal::entityTypeManager()->getStorage('node');
      $query = $entity->getQuery();
      $children_nids = $query->condition('field_member_of', $nid, '=')
        ->sort('field_weight', 'ASC')
        ->execute();

      $total_children = count($children_nids);
      // Slice the list of children to the first 5 so we don't load every
      // member of a large collection or book, etc.
      $children_nids = array_slice($children_nids, 0, 5);
      $output_children = [];
      foreach ($children_nids as $child_nid) {
        $child = \Drupal::entityTypeManager()->getStorage('node')->load($child_nid);
        $output_children[] = ['nid' => $child_nid, 'label' => $child->label()];
      }

      return array (
        '#theme' => 'islandora_whole_object_block_hierarchy',
        '#parents' => $output_parents,
        '#children' => $output_children,
        '#total_children' => $total_children,
        '#node' => $output_node,
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
