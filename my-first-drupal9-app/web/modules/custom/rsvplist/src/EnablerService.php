<?php

/**
 * @file
 * Contains the RSVP Enabler service.
 */

namespace Drupal\rsvplist;

use Drupal\Core\Database\Connection;
use Drupal\node\Entity\Node;

class EnablerService {

  protected Connection $database_connection;

  public function __construct(Connection $connection) {
    $this->database_connection = $connection;
  }

  /**
   * Checks if an individual node is RSVP enabled.
   *
   * @param Node $node
   * @return bool
   * whether the node is enabled for the  RSVP functionality.
   */
  public function isEnabled(Node &$node): ?bool {
    if ($node->isNew()) {
      return FALSE;
    }
    try {
      $select = $this->database_connection->select('rsvplist_enabled', 're');
      $select->fields('re', ['nid']);
      $select->condition('nid', $node->id());
      $results = $select->execute();

      return !(empty($results->fetchCol()));
    }
    catch (\Exception $e){
      \Drupal::messenger()->addError(
        t('Unable to determine RSVP settings at this time. Please try again later.')
      );
      return NULL;
    }
  }


 /**
  * Sets an individual node to be RSVP enabled.
  *
  * @param Node $node
  * @throw Exception
  */
 public function setEnabled(Node $node): void {
    try {
      if (!($this->isEnabled($node))){
        $insert = $this->database_connection->insert('rsvplist_enabled');
        $insert->fields(['nid']);
        $insert->values([$node->id()]);
        $insert->execute();
      }
    }
    catch (\Exception $e){
      \Drupal::messenger()->addError(
        t('Unable to save the RSVP settings at this time. Please try again.')
      );
    }
 }

 /**
  * Deletes RSVP enabled settings for an individual node.
  *
  * @param Node $node
  *
  */
 public function delEnabled(Node $node): void {
   try {
     $delete = $this->database_connection->delete('rsvplist_enabled');
     $delete->condition('nid', $node->id());
     $delete->execute();
   }
   catch (\Exception $e) {
     \Drupal::messenger()->addError(
       t('Unable to save the RSVP settings at this time. Please try again.')
     );
   }
 }
}
