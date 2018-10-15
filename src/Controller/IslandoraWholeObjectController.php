<?php

namespace Drupal\islandora_whole_object\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Component\Utility\SafeMarkup;

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
     $nid = $node->id();
     $url = 'http://localhost:8000/node/' . $nid . '?_format=' . $format;
     $response = \Drupal::httpClient()->get($url);
     $response_body = (string) $response->getBody();
     $whole_object = json_decode($response_body, true);
     $whole_object = var_export($whole_object, true);

     if ($format == 'jsonld') {
       $heading = 'JSON-LD';
     }
     if ($format == 'json') {
       $heading = 'JSON';
     }

     return [
       '#theme' => 'islandora_whole_object_content',
       '#whole_object' => SafeMarkup::checkPlain($whole_object),
       '#heading' => $heading,
     ];
   }

   /**
    * Only show tab on nodes with the 'islandora_object' content type.
    */
   public function islandoraContentTypeOnly(NodeInterface $node = NULL) {
     return ($node->getType() == 'islandora_object') ? AccessResult::allowed() : AccessResult::forbidden();
   }
}
