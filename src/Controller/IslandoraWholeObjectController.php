<?php

namespace Drupal\islandora_whole_object\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;
use Drupal\Core\Access\AccessResult;

/**
* Controller.
*/
class IslandoraWholeObjectController extends ControllerBase {
  /**
   * JSON-LD or JSON representation of the object, via Islandora's REST interface.
   *
   * @param string $format
   *   Either 'jsonld' or 'json', as used by the Islandora REST interface.
   *
   * @return string
   */
   public function wholeObject(NodeInterface $node = NULL, $format = 'jsonld') {
     $node = \Drupal::routeMatch()->getParameter('node');
     if ($node instanceof \Drupal\node\NodeInterface) {
       $nid = $node->id();
     }
     $url = 'http://localhost:8000/node/' . $nid . '?_format=' . $format;
     $response = \Drupal::httpClient()->get($url);
     $jsonld = (string) $response->getBody();

     dsm(json_decode($jsonld, true));
     return [
       '#markup' => ''
     ];
   }

   /**
    * Only show out tab on nodes with the 'islandora_object' content type.
    */
   public function islandoraContentTypeOnly(NodeInterface $node = NULL) {
     return ($node->getType() == 'islandora_object') ? AccessResult::allowed() : AccessResult::forbidden();
   }
}
