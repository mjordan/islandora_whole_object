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
  protected $representations;

  public function __construct() {
    $config = \Drupal::config('islandora_whole_object.settings');
    $representations = array(
      'table' => 'table',
      'media' => 'media',
      'fedora' => 'fedora',
      'solr' => 'solr',
    );
    $this->representations = $config->get('show_representations') ?: $representations;
  }

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
       // The case 'jsonldvisualized' will be handled in islandora_whole_object_page_attachments().
       case 'whole':
         // These headings are used in the 'whole' output, but they are slightly
         // different (and assigned within each case) when only one representation
         // is rendered.
         $heading = array(
           'table' => 'RDF properties of this Islandora object',
           'media' => 'Media associated with this object',
           'fedora' => "Fedora's RDF (Turtle) representation of this object",
           'solr' => "Solr document for this object",
         );
         $heading = $this->filterRepresentations($heading);
         $output = array(
           'table' => $this->getDrupalRepresentations($nid, 'jsonld', 'table'),
           'media' => $this->getDrupalRepresentations($nid, '', 'media'),
           'fedora' => $this->getFedoraRepresentation($nid),
           'solr' => $this->getSolrDocument($nid),
         );
         $output = $this->filterRepresentations($output);
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
       case 'solr':
         $heading = "Solr document for this object";
         $output = $this->getSolrDocument($nid);
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
       $output = views_embed_view('media_of', 'page_1', $nid);
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
     $client = \Drupal::httpClient();
     $response = $client->request('GET', $fedora_url, ['http_errors' => false]);
     if ($response->getStatusCode() == 404) {
       $response_output = t('Resource @fedora_url not found.', array('@fedora_url' => $fedora_url));
     } else {
       $response_output = (string) $response->getBody();
     }
     return $response_output;
   }

   /**
    * Get the node's Solr document.
    *
    * @todo: Get Solr base URL from config; wrap in exception handling code.
    */
   private function getSolrDocument($nid) {
     $solr_url = 'http://localhost:8983/solr/CLAW/select?q=ss_search_api_id:%22entity:node/' . $nid . ':en%22';
     $response = \Drupal::httpClient()->get($solr_url);
     $response_body = (string) $response->getBody();
     return $response_body;
   }

   /**
    * Filter out the configuration values that are not selected in the admin settings.
    *
    * @param array $array
    *   One of the arrays defined in the 'whole' case,
    *   $heading or $output.
    *
    * @return array
    *   The same array minus members whose checkboxes were
    *   not checked in the admin form.
    */
   private function filterRepresentations($array) {
     $wanted_keys = array();
     foreach($this->representations as $key => $value) {
       if (strlen($value) > 1) {
         $wanted_keys[] = $key;
       }
     }
     $filtered = array();
     foreach ($array as $key => $value) {
       if (in_array($key, $wanted_keys)) {
         $filtered[$key] = $value;
       }
     } 
     return $filtered;
  }

}
