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
   *   Either 'whole', 'jsonld', 'node', 'fedora', 'table', or 'media'.
   *
   * @return string
   */
   public function wholeObject(NodeInterface $node = NULL, $format = 'whole') {
     $node = \Drupal::routeMatch()->getParameter('node');
     $nid = $node->id();

     switch ($format) {
       // The case 'jsonldvisualized' is handled in islandora_whole_object_page_attachments().
       case 'whole':
         $heading = array(
           'table' => 'RDF properties of this Islandora object',
           'media' => 'Media associated with this object',
           'fedora' => "Fedora's RDF (Turtle) representation of this object",
         );
         $output = array(
           'table' => $this->getDrupalRepresentations($nid, 'jsonld', 'table'),
           'media' => $this->getDrupalRepresentations($nid, '', 'media'),
           'fedora' => $this->getFedoraRepresentation($nid),
         );
         break;
       case 'node':
         $heading = 'Raw Drupal node as a PHP array';
         $output = $this->getDrupalRepresentations($nid, 'json', 'node');
         break;
       case 'jsonld':
         $heading = 'JSON-LD as a PHP array';
         $output = $this->getDrupalRepresentations($nid, 'jsonld', 'jsonld');
         break;
       case 'table':
         $heading = 'RDF properties of this Islandora object';
         $output = $this->getDrupalRepresentations($nid, 'jsonld', 'table');
         break;
       case 'media':
         $heading = 'Media associated with this object';
         $output = $this->getDrupalRepresentations($nid, '', 'media');
         break;
       case 'fedora':
         $heading = "Fedora's RDF (Turtle) representation of this object";
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

   /**
    * Get various representations of the object.
    */
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
     elseif ($format == 'media') {
       $output = views_embed_view('whole_islandora_object_media', 'embed_1', $nid);
     }
     else {
       // $output will be rendered in the template using <pre> tags.
       $output = var_export($whole_object, true);
       $output = SafeMarkup::checkPlain($output);
     }
     return $output;
   }

   /**
    * Get Fedora's Turtle representation of the object.
    */
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
