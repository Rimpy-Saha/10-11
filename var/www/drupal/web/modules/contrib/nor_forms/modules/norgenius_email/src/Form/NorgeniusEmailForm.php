<?php

namespace Drupal\norgenius_email\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\file\Entity\File;
use Drupal\nor_forms\Libraries\Zoho\RecordWrapper;

class NorgeniusEmailForm extends FormBase
{

  public function getFormId() {
    return 'norgenius_email_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['#prefix'] = '<div id="norgeniusemailform-container" class="norgeniusemailform-container">';

    $form['header'] = [
      '#type' => 'markup',
      '#markup' => '<div style="text-align:center;"><img src="https://jango.norgenbiotek.com/sites/default/files/images/norgenius/email.png"><h2>Get This Workflow In Your Email</h2><p>Let us know your information and we will be happy to send your custom workflow to you</p></div>',
      '#class' => 'work_pls',
    ];

    $form['message'] = [
      '#type' => 'markup',
      '#markup' => '<div id="result-message"></div>',
    ];

    $form['norgenius_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#default_value' => nor_forms_user_email(),
      '#required' => TRUE,
      '#placeholder' => 'Email Address (Required)',
    ];

   
     $form['step4_1'] = [
      '#type' => 'textarea',
      '#title' => $this->t('step4_1'),
    ];
    
  

    $form['google_recaptcha'] = [
      '#type'=> 'fieldset',
      '#description' => '<div class="g-recaptcha" data-sitekey="6Lcr4u0pAAAAAGj32knXkUzuHAXzj3CoAhtbJ1t5"></div>',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#button_type' => 'primary',
      '#submit' => ['::submitForm'],
      '#ajax' => [
        'callback' => '::submitCallback',
        'event' => 'click',
        'method' => 'replace', 'effect' => 'fade',
      ]
    ];

    $form['#suffix'] = '</div>';
    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state){

   

    if (isset($_POST['g-recaptcha-response']) && $_POST['g-recaptcha-response'] != '') {
      $captcha_response = $_POST['g-recaptcha-response'];
      $remote_ip = $_SERVER['REMOTE_ADDR'];
  
      $result = $this->verifyGoogleRecaptcha($captcha_response, $remote_ip);
  
      $data = json_decode($result, true);
  
      if (!$data['success']) {
          $form_state->setErrorByName('google_recaptcha', t('Please complete the captcha to prove you are human'));
      }
    } else {
        $form_state->setErrorByName('google_recaptcha', t('Please complete the reCAPTCHA verification.'));
    }
    if (empty($_POST['g-recaptcha-response'])) {
        $form_state->setErrorByName('google_recaptcha', t('Please complete the reCAPTCHA verification.'));
    }

  }

  private function verifyGoogleRecaptcha($response, $remote_ip) {
    if (!function_exists('curl_init')) {
        die('CURL is not installed!');
    }
  
    $url_get = 'https://www.google.com/recaptcha/api/siteverify?secret=6Lcr4u0pAAAAAKDDRrTBlbkrV3ClMD9hz-z8DCfZ&response=' . $response . '&remoteip=' . $remote_ip;
  
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_get);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
  
    return $response;
  }

  public function submitCallback(array &$form, FormStateInterface $form_state) {

    $Selector = '#norgeniusemailform-container';
    
    $response = new AjaxResponse();
    
    if ($form_state->hasAnyErrors()) {
      $response = nor_forms_ajax_error($form, $form_state, $Selector);
      return $response;
    } else {

      $response = nor_forms_email_sent_norgenius($form, $form_state, $Selector);
      // $form_state['rebuild'] = true;
      $form_state->setRebuild(TRUE);
      //$_POST['g-recaptcha-response'] = '';
      return $response;
      
    }
    
  }



  public function submitForm(array &$form, FormStateInterface $form_state){

    
    $email = $form_state->getValue('norgenius_email');
    $step4_1 = $form_state->getValue('step4_1');

   
   

    $date = date("Ymd");
    //$form_name = $this->t('Account Issues Form');

    $output = "";
   

    
    if (!isset($email)) {
      $email = 'NULL';
    }

    



    $output = '<p style="font-size:16px;">Hello,</p>
        <p style="font-size:16px;">This custom workflow was shared with you and generated using Norgen Biotek Norgenius Tool</p>';

      

      if($step4_1 != null){
        $recommended_choice_4 = $step4_1;
      }
      
      
      $output .= '<div style="border:7px solid #004893;border-radius:20px;padding: 2%;text-align: left;font-size:16px;width:fit-content;">';
      $output .= $recommended_choice_4;
      $output .= '</div>';
      
      
      $output .= '<div class="powered_result" style="font-size: 12px;font-size:16px;text-align:center;"><p><strong>Powered by  <img src="https://norgenbiotek.com/sites/default/files/images/norgenius/norgenius_icon.png" style="width: 50px;">  NorGenius</strong></p></div>';
      $clink = 'https://norgenbiotek.com/contact';
      $output .= '<div style="font-size:16px;"><p>Please <a href='.$clink.'>Contact Us</a> if you have any additional questions or concerns</p><br><br><p>Regards,</p><p>Norgen Biotek</p></div>';

    
     
   

    $time = time();

    if ($form_state->hasAnyErrors()) {
    } else {
      $subject = '[Contact form] - Norgenius Email ' . date("F j, Y, g:i a", $time);
      $recipient_email = $email;
      // $recipient_email = 'sowmya.movva@norgenbiotek.com';
      nor_forms_email_redirect($output, $recipient_email, $subject);
    }
  }
}
