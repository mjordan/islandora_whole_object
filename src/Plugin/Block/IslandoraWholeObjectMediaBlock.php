<?php

/**
 * @file
 */

namespace Drupal\islandora_whole_object\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a block containing the Media view for the object.
 *
 * @Block(
 * id = "islandora_whole_object_media",
 * admin_label = @Translation("File details"),
 * category = @Translation("Islandora"),
 * )
 */
class IslandoraWholeObjectMediaBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node) {
      // Get children, sorted by field_weight.
      $entity = \Drupal::entityTypeManager()->getStorage('media');
      $query = $entity->getQuery();
      $mids = $query->condition('field_media_of', $node->id(), '=')
        ->execute();

      $output = [];
      foreach ($mids as $mid) {
        $media = \Drupal::entityTypeManager()->getStorage('media')->load($mid);
        $media_use_terms = $media->get('field_media_use')->getValue();
	$media_use_labels = [];
	foreach ($media_use_terms as $media_use_term) {
          $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($media_use_term['target_id']);
          $media_use_labels[] = $term->label();
	}
	$media_output = [$mid, $media->label(), implode(', ', $media_use_labels)];

        switch ($media) {
          case $media->hasField('field_media_image'):
            $file_field = 'field_media_image';
            break;
          case $media->hasField('field_media_file'):
            $file_field = 'field_media_file';
            break;
          case $media->hasField('field_media_document'):
            $file_field = 'field_media_document';
            break;
          case $media->hasField('field_media_audio_file'):
            $file_field = 'field_media_audio_file';
            break;
          case $media->hasField('field_media_video_file'):
            $file_field = 'field_media_video_file';
            break;
          default:
            $file_field = 'field_media_file';
        }

	if ($media->hasField($file_field)) {
          $target_file_id = $media->get($file_field)->getValue();
          $target_file_id = $target_file_id[0]['target_id'];
	  $file = \Drupal::entityTypeManager()->getStorage('file')->load($target_file_id);
	  $uri = $file->get('uri')->getValue();
	  $uri = $uri[0]['value'];
	  $media_output[] = $uri;
	  $filemime = $file->get('filemime')->getValue();
	  $filemime = $filemime[0]['value'];
	  $media_output[] = $filemime;
	  $changed = $file->get('changed')->getValue();
	  $media_output[] = date("F j, Y, g:i a", $changed[0]['value']);
	  $filehashes = $file->filehash;
	  $hash_output = [];
	  foreach ($filehashes as $algo => $hash) {
            $hash_output[] = $algo . ': ' . $hash; 
	  }
	  $media_output[] = implode(', ', $hash_output);
         }
         $output[] = $media_output; 
	}

      return array (
        '#theme' => 'islandora_whole_object_block_media',
        '#content' => $output,
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
