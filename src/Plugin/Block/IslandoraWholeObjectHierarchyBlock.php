<?php

namespace Drupal\islandora_whole_object\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a block showing the properties of the object.
 *
 * @Block(
 *   id = "islandora_whole_object_hierarchy",
 *   admin_label = @Translation("Current object's parents and children"),
 *   category = @Translation("Islandora"),
 *   context_definitions = {
 *     "node" = @ContextDefinition(
 *       "entity:node",
 *       label = @Translation("Current node")
 *     )
 *   }
 * )
 */
class IslandoraWholeObjectHierarchyBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = $this->getContextValue('node');
    if (!$node) {
      return [];
    }

    if ($node) {
      $output_node = ['label' => $node->label(), 'nid' => $node->id()];

      // Get parents.
      $output_parents = [];
      $node = \Drupal::entityTypeManager()->getStorage('node')->load($node->id());

      $parents = $node->field_member_of->referencedEntities();
      foreach ($parents as $parent) {
        $output_parents[] = ['nid' => $parent->id(), 'label' => $parent->label()];
      }

      // Get children, sorted by field_weight.
      $entity = \Drupal::entityTypeManager()->getStorage('node');
      $query = $entity->getQuery();
      $children_nids = $query->condition('field_member_of', $node->id(), '=')
        ->sort('field_weight', 'ASC')
        ->accessCheck(TRUE)
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

      $cache_tags = array_merge([$node->id()], array_column($output_parents, 'nid'), array_column($children_nids, 'nid'));
      array_walk($cache_tags, function(&$item){
        $item = 'node:' . $item;
      });

      return [
        '#theme' => 'islandora_whole_object_block_hierarchy',
        '#parents' => $output_parents,
        '#children' => $output_children,
        '#total_children' => $total_children,
        '#node' => $output_node,
        '#cache' => [
          '#tags' => $cache_tags,
        ]
      ];
    }
  }

}
