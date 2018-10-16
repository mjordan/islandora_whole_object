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
     $node = \Drupal::routeMatch()->getParameter('node');
     $nid = $node->id();

     switch ($format) {
       // The case 'jsonldvisualized' is handled in islandora_whole_object_page_attachments().
       case 'node':
         $heading = 'Raw Drupal node as a PHP array';
         $output = $this->getDrupalRepresentations($nid, 'json', 'node');
         break;
       case 'jsonld':
         $heading = 'JSON-LD as a PHP array';
         $output = $this->getDrupalRepresentations($nid, 'jsonld', 'jsonld');
         break;
       case 'table':
         $heading = 'Linked Data properties as a table';
         $output = $this->getDrupalRepresentations($nid, 'jsonld', 'table');
         break;
       case 'fedora':
         $heading = "Fedora's Turtle representation";
         $output = $this->getFedoraRepresentation($nid);
         break;
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

   private function getDrupalRepresentations($nid, $get_param, $format) {
     $url = 'http://localhost:8000/node/' . $nid . '?_format=' . $get_param;
     $response = \Drupal::httpClient()->get($url);
     $response_body = (string) $response->getBody();
     $whole_object = json_decode($response_body, true);

     if ($format == 'table') {
       $properties_to_skip = array('@id', '@type');
       foreach ($whole_object['@graph'][0] as $property => $value) {
         if (!in_array($property, $properties_to_skip)) {
           // Note: For now, we only pick out the first of multivalued properties.
           if ($property == 'http://schema.org/author') {
             $output[] = array($property, $value[0]['@id']);
           }
           else {
             // @todo: Check for the presence of @type and @language.
             $output[] = array($property, $value[0]['@value'], $value[0]['@type'], $value[0]['@language']);
           }
         }
       }
     }
     else {
       // $output will be rendered in the template using <pre> tags.
       $output = var_export($whole_object, true);
       $output = SafeMarkup::checkPlain($output);
     }
     return $output;
   }

   private function getFedoraRepresentation($nid) {
     // Get the node's UUID from Drupal.
     $drupal_url = 'http://localhost:8000/node/' . $nid . '?_format=json';
     $response = \Drupal::httpClient()->get($drupal_url);
     $response_body = (string) $response->getBody();
     $body_array = json_decode($response_body, true);
     $uuid = $body_array['uuid'][0]['value'];

     // Assemble the Fedora URL.
     $uuid_parts = explode('-', $uuid);
     $subparts = str_split($uuid_parts[0], 2);
     $fedora_url = 'http://localhost:8080/fcrepo/rest/' . implode('/', $subparts) . '/'. $uuid;

     // Get the Turtle from Fedora.
     $response = \Drupal::httpClient()->get($fedora_url);
     $response_body = (string) $response->getBody();
     return $response_body;
   }

}
