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
 * admin_label = @Translation("Drupal RDF (JSON-LD) properties for this object"),
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

    $properties_to_skip = array();
    foreach ($whole_object['@graph'][0] as $property => $value) {
      if (!in_array($property, $properties_to_skip)) {
        if ($property == '@id') {
          $output[] = array('@id', $value, '', '');
          continue;
        }
        if ($property == '@type') {
          foreach ($value as $type) {
            $output[] = array('@type', $type, '', '');
	  }
          continue;
        }
        if ($property == 'http://schema.org/author') {
          $output[] = array($property, '', '', $value[0]['@id']);
        }
	else {
	  // All other properties.
          foreach ($value as $v) {
	    $row = [];
            $row[] = $property;
            if (array_key_exists('@value', $value[0])) {
              $row[] = $v['@value'];
	    }
	    else {
              $row[] = '';
	    }
            if (array_key_exists('@type', $value[0])) {
              $row[] = $v['@type'];
	    }
	    else {
              $row[] = '';
	    }
            if (array_key_exists('@id', $value[0])) {
              $row[] = $v['@id'];
	    }
	    else {
              $row[] = '';
	    }
            if (array_key_exists('@language', $value[0])) {
              $row[] = $v['@language'];
	    }
	    else {
              $row[] = '';
	    }
	    $output[] = $row;
	  }
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
