<?php

/**
 * @file
 * A form to collect an email address for  RSVP details.
 */

namespace Drupal\rsvplist\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class RSVPForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'rsvplist_email_from';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $node = \Drupal::routeMatch()->getParameter('node');

    // If a node was loaded, get the node id.
    if (!is_null($node)) {
      $nid = $node->id();
    } else {
      // If a node could not be loaded, default to 0;
      $nid = 0;
    }


    $form['email'] = [
     '#type'=>'textfield',
     '#title'=> t('Email address'),
     '#size'=> 25,
     '#description'=> t('We will send updates to the email address you provide.'),
     '#required' => TRUE,
   ];

   $form['submit'] = [
     '#type'=>'submit',
     '#value'=> t('RSVP')
   ];
    $form['nid'] = [
      '#type'=>'hidden',
      '#value'=> $nid,
    ];

   return $form;
  }


  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $value = $form_state->getValue('email');
    if (!(\Drupal::service('email.validator')->isValid($value))){
      $form_state->setErrorByName('email', $this->t('It appears that %mail is not a valid email.
      Please try again.', ['%mail'=>$value]));
    }
  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
  
    //$submitted_email = $form_state->getValue('email');
    //$this->messenger()->addMessage(t("The form is working! You entered @entry.",
    //['@entry' => $submitted_email]));
    
    try {

      // Get current user ID.
      $uid = \Drupal::currentUser()->id();

      $nid = $form_state->getValue('nid');
      $email = $form_state->getValue('email');

      $current_time = \Drupal::time()->getRequestTime();

      // Save the values to the database.
      $query = \Drupal::database()->insert('rsvplist');

      // Specify the fields that the query will insert into.
      $query->fields([
        'uid',
        'nid',
        'mail',
        'created',
        ]);

      // Set the values of the fields we selected.
      $query->values([
        $uid,
        $nid,
        $email,
        $current_time,
      ]);

      // Execute the query.
      $query->execute();

      \Drupal::messenger()->addMessage(t
      ('Thank you for your RSVP, you are on the list for the event!'));
    }

    catch (\Exception $e){
      \Drupal::messenger()->addError(t
      ('Unable to save RSVP settings at this time due to database error.
      Please try again.'));
    }
  }
}
