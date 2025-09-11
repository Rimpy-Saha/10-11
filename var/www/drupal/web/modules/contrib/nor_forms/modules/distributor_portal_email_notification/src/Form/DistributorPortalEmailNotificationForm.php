<?php

namespace Drupal\distributor_portal_email_notification\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\file\Entity\File;
use Drupal\nor_forms\Libraries\Zoho\RecordWrapper;

class DistributorPortalEmailNotificationForm extends FormBase
{

  public function getFormId()
  {
    return 'distributor_portal_email_notification_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {

    $form['#prefix'] = '<div id="distributorportalemailnotificationform-container" class="distributorportalemailnotificationform-container">';

    $form['header'] = [
      '#type' => 'markup',
      '#markup' => '',
      '#class' => 'send_email',
    ];
   
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send Email To Notify Distributors'),
      '#button_type' => 'primary',
      '#submit' => ['::submitForm'],
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

    $Selector = '#distributorportalemailnotificationform-container';
    $response = new AjaxResponse();

    if ($form_state->hasAnyErrors()) {
      $response = nor_forms_ajax_error($form, $form_state, $Selector);
    } else {

      $response = distributor_portal_email_notification_email_sent_ajax($form, $form_state, $Selector);
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
   
    //$email = 'test@test.ca';

    $query = \Drupal::database()->select('distributor_portal_accesses', 't');
    $query->fields('t', ['mail']);
    $result = $query->execute();

    $emails = array();
    foreach ($result as $row) {
      $username = $row->mail;
      if(!in_array($username, array('rimpy.saha@norgenbio.com', 'test@norgenbiotek.com', 'tha@norgenbiotek.com', 'moemen@norgenbiotek.com', 'ben.milnes@norgenbiotek.com', 'regan.bak@norgenbiotek.com', 'mha@norgenbiote.com', 'pandora.huang@norgenbio.com', 'dawn.eggleton.norgenbio.com', 'haylee.spiller@norgenbio.com', 'pedro.menezes@norgenbiotek.com', 'alex.white@norgenbiotek.com', 'sohaib.siddiqui@norgenbiotek.com', 'lea.milkin@norgenbiotek.com', 'seema.shamim@norgenbiotek.com', 'nikola.breberina@norgenbiotek.com', 'patricia.barbalho@norgenbiotek.com', 'norelle.cardy@norgenbiotek.com', 'Samirkumar.patel@norgenbiotek.com', 'juan.gijzelaar@norgenbiotek.com', 'felipe.reis@norgenbiotek.com', 'weini.lei@norgenbiotek.com', 'pablo.cepeda@norgenbiotek.com', 'morgan.buchan@norgenbiotek.com', 'irene.brundula@norgebiotek.com'), true)){
        $emails[] = $username; 
      }
      // Do something with the results.
    }

    foreach ($emails as $email){
      $output = '<p>Hello,</p>
      <p>Norgen Biotek Corp. has updated the distributor portal with an important announcement.</p><p>Please <a href="https://norgenbiotek.com/user/?redirect=%2F">login</a> to the distributor portal to access the latest information.</p><p>If you have any questions, please do not hesitate to contact us.</p><br><p>Best Regards,</p><p>Norgen Biotek Corp.</p>';

      $time = time();

      if ($form_state->hasAnyErrors()) {
      } else {
        $subject = '[New Annoucement] - Distributor Portal ' . date("F j, Y, g:i a", $time);
        // $recipient_email = 'sowmya.movva@norgenbiotek.com';
        // $recipient_email = 'sabah.butt@norgenbiotek.com';
        nor_forms_email_redirect($output, $email, $subject);
      }
    }
    
  }


}
