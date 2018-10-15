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
   *   Either 'jsonld', 'node', or 'table'.
   *
   * @return string
   */
   public function wholeObject(NodeInterface $node = NULL, $format = 'jsonld') {
     switch ($format) {
       case 'node':
         $get_param = 'json';
         $heading = 'Raw Drupal node as a PHP array';
         break;
       case 'jsonld':
         $get_param = 'jsonld';
         $heading = 'JSON-LD as a PHP array';
         break;
       case 'table':
         $get_param = 'jsonld';
         $heading = 'Linked Data properties as a table';
         break;
     }

     $node = \Drupal::routeMatch()->getParameter('node');
     $nid = $node->id();
     $url = 'http://localhost:8000/node/' . $nid . '?_format=' . $get_param;
     $response = \Drupal::httpClient()->get($url);
     $response_body = (string) $response->getBody();
     $whole_object = json_decode($response_body, true);
     if ($format == 'table') {
       $properties_to_skip = array('@id', '@type');
       foreach ($whole_object['@graph'][0] as $property => $value) {
         if (!in_array($property, $properties_to_skip)) {
           $output[] = array($property, $value[0]['@value']);
         }
       }
     }
     else {
       $output = var_export($whole_object, true);
       $output = SafeMarkup::checkPlain($output);
     }

     return [
       '#theme' => 'islandora_whole_object_content',
       '#format' => $format,
       '#whole_object' => $output,
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
