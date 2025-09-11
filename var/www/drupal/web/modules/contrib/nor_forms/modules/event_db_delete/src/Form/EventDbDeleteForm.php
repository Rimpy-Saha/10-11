<?php

namespace Drupal\event_db_delete\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\file\Entity\File;
use Drupal\nor_forms\Libraries\Zoho\RecordWrapper;

class EventDbDeleteForm extends FormBase
{

  public function getFormId()
  {
    return 'event_db_delete_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {

    $form['#prefix'] = '<div id="eventdbdeleteform-container" class="eventdbdeleteform-container">';

    $form['header'] = [
      '#type' => 'markup',
      '#markup' => '<h1><p style = "color:red;">Are you sure you want to delete this entry?</p></h1>',
      '#class' => 'work_pls',
    ];
    $form['delete_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('ID'),
      '#attributes' => array('readonly' => 'readonly'),
      '#required' => TRUE,
    ];
   
    $form['actions']['delete'] = [
      '#type' => 'submit',
      '#value' => $this->t('Delete'),
      '#button_type' => 'primary',
      '#submit' => ['::submitFormDelete'],
      '#ajax' => [
        'callback' => '::submitCallback',
        'event' => 'click',
        
      ]
    ];

    $form['#suffix'] = '</div>';
    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state)
  {

    // $first_name = $form_state->getValue('update_fname');
    // $last_name = $form_state->getValue('update_lname');

    // if (empty($first_name) || strlen($first_name) < 2) {
    //   $form_state->setErrorByName('update_fname', $this->t('Please enter your first name.'));
    // }

    // if (empty($last_name) || strlen($last_name) < 2) {
    //   $form_state->setErrorByName('update_lname', $this->t('Please enter your last name.'));
    // }
  }



  public function submitCallback(array &$form, FormStateInterface $form_state)
  {

    $Selector = '#eventdbdeleteform-container';
    $response = new AjaxResponse();

    if ($form_state->hasAnyErrors()) {
      $response = nor_forms_ajax_error($form, $form_state, $Selector);
    } else {

      $response = event_db_delete_email_sent_ajax($form, $form_state, $Selector);
    }
    return $response;
  }


  /**
   * Final submit handler.
   *
   * Reports what values were finally set.
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $first_name = $form_state->getValue('update_fname');
    $last_name = $form_state->getValue('update_lname');
    $id = $form_state->getValue('update_id');
    $email= $form_state->getValue('update_email');

    $date = date("Ymd");
    $form_name = $this->t('Event Db Delete Form');


    if (!isset($first_name)) {
      $first_name = 'NULL';
    }
   
    //$email = 'test@test.ca';

    $query = \Drupal::database()->select('forms_to_zoho', 't');
    $query->fields('t', ['id', 'form_name']);
    $result = $query->execute();

    foreach ($result as $row) {
      $id2 = $row->id;
      $form_name2 = $row->form_name;
      if($form_name2 == $form_name && $id2 == $id){
        $dup = 'true';
        $dup_id = $id;
        $dup_form = $form_name;
      }
      // Do something with the results.
    }
    if($dup == 'true'){
      $query = \Drupal::database()->update('forms_to_zoho');
      $query->fields([
      'first_name' => $first_name,
      ]);
      $query->condition('form_name', $form_name);
      $query->condition('id', $dup_id);
      $query->execute();
    }
    else{
      $query = \Drupal::database()->insert('forms_to_zoho');
      $query->fields(['created_on','form_name', 'first_name', 'last_name','email','record','timestamp']); //wrong syntax here breaks entire submit function 
      $query->values([$date,$form_name, $first_name, $last_name, $email,'',time()]);
      $query->execute();
    }
    // if($dup == 'true'){
    //   $query = \Drupal::database()->update('forms_to_zoho');
    //   $query->fields(['created_on','form_name', 'first_name', 'last_name','email','record','timestamp']); //wrong syntax here breaks entire submit function 
    //   $query->values([$date,$form_name, $first_name, $last_name, $email,'',time()]);
    //   $query->condition('form_name', $form_name);
    //   $query->execute();
    // }
    // $query = \Drupal::database()->insert('forms_to_zoho');
    // $query->fields(['created_on','form_name', 'first_name', 'last_name','email','record','timestamp']); //wrong syntax here breaks entire submit function 
    // $query->values([$date,$form_name, $first_name, $last_name, $email,'',time()]);
    // $query->execute();

    $output = '<p>Hello,</p>
    <p>An entry was deleted from the database.</p>
    <p>Last name: ' .$dup.'';

    $time = time();

    if ($form_state->hasAnyErrors()) {
    } else {
      $subject = '[Contact form] - Covid Workflow ' . date("F j, Y, g:i a", $time);
      // $recipient_email = 'sowmya.movva@norgenbiotek.com';
      //$recipient_email = 'sabah.butt@norgenbiotek.com';// real addresses
      $recipient_email = 'liam.howes@norgenbiotek.com';
      nor_forms_email_redirect($output, $recipient_email, $subject);
    }
  }

  

  public function submitFormDelete(array &$form, FormStateInterface $form_state)
  {
    $id = $form_state->getValue('delete_id');
    $form_name = $this->t('Conference Lead');
    
    $query = \Drupal::database()->delete('forms_to_zoho');
    $query->condition('id', $id);
    $query->condition('form_name', $form_name);
    $query->execute();
  }

}
