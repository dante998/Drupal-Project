<?php
/**
 * @file
 * Provide site administrators with a list of all the RSVP List signups,
 * so they know who is attending their events.
 */

namespace Drupal\rsvplist\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;

class ReportController extends ControllerBase {
  /**
   * Gets and returns all RSVPs for all nodes.
   * These are returned as an associative array with each row.
   * Containing the username, the node title, the email of RSVP.
   * @return array|null
   */

  protected function load(): ?array {
    try {
      $database = \Drupal::database();
      $select_query = $database->select('rsvplist', 'r');

      // Join the user table, so we can get the entry creator`s username.
      $select_query->join('users_field_date', 'u', 'r.uid = u.uid');

      // Join the node table, so we can get the event`s name.
      $select_query->join('node_field_date', 'n', 'r.nid = u.nid');

      // Select these specific fields for the output.
      $select_query->addField('u', 'name', ' username');
      $select_query->addField('n', 'title');
      $select_query->addField('r', 'mail');

      $entires = $select_query->execute()->fetchAll(\PDO::FETCH_ASSOC);

       // Returns the associative array of RSVPList entries.
      return $entires;
    }

    catch (\Exception $e) {
      // Display a user-friendly error message.
      \Drupal::messenger()->addStatus(t('Unable to access the database at this time. Please try again later.'));

      return NULL;
    }
  }


/**
 * Creates the RSVPList report page.
 *
 * @return array
 * Render array for RSVPList report output.
 */
public function report(): array {
  $content = [];


  $content['message'] = [
    '#markup' => t('Below is a list of all Event RSVPs including username,
            email address and the name of the event they will be attending.')
  ];

  $headers = [
    t('Username'),
    t('Event'),
    t('Email'),
  ];

  $table_rows = $this->load();

  // Create the render array for rendering an HTML table.
  $content['table'] = [
    '#type' => 'table',
    '#header' => $headers,
    '#rows' => $table_rows,
    '#empty' => t('No entries available.'),
  ];

  // Do not cache this page by setting the max-age to 0.
  $content['#cache']['max-age'] = 0;


  // Return the populated render array.
  return $content;
}
}


