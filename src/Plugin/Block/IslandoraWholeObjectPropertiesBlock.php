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
 * id = "islandora_whole_object_properties",
 * admin_label = @Translation("Drupal RDF properties for this object"),
 * category = @Translation("Islandora"),
 * )
 */
class IslandoraWholeObjectPropertiesBlock extends BlockBase {
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

    $url = $base_url . '/node/' . $nid . '?_format=jsonld';
    $response = \Drupal::httpClient()->get($url);
    $response_body = (string) $response->getBody();
    $whole_object = json_decode($response_body, true);

    $properties_to_skip = array('@id', '@type');
    foreach ($whole_object['@graph'][0] as $property => $value) {
      if (!in_array($property, $properties_to_skip)) {
        // Note: For now, we only pick out the first of multivalued properties.
        if ($property == 'http://schema.org/author') {
          $output[] = array($property, $value[0]['@id']);
        }
	else {
	  $row = [];
          // $output[] = array($property, $value[0]['@value'], $value[0]['@type'], $value[0]['@language']);
          $row[] = $property;
          if (array_key_exists('@value', $value[0])) {
            $row[] = $value[0]['@value'];
	  }
	  else {
            $row[] = '';
	  }
          if (array_key_exists('@type', $value[0])) {
            $row[] = $value[0]['@type'];
	  }
	  else {
            $row[] = '';
	  }
          if (array_key_exists('@id', $value[0])) {
            $row[] = $value[0]['@id'];
	  }
	  else {
            $row[] = '';
	  }
          if (array_key_exists('@language', $value[0])) {
            $row[] = $value[0]['@language'];
	  }
	  else {
            $row[] = '';
	  }
	  $output[] = $row;
        }
      }
    }

    return array (
      '#theme' => 'islandora_whole_object_block_properties',
      '#content' => $output,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
