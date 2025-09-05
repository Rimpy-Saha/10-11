<?php

namespace Drupal\norgenius_pathogen\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\file\Entity\File;
use Drupal\nor_forms\Libraries\Zoho\RecordWrapper;

class NorgeniusPathogenForm extends FormBase
{

  public function getFormId() {
    return 'norgenius_pathogen_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['#prefix'] = '<div id="norgeniuspathogenform-container" class="norgeniuspathogenform-container">';

    $form['header'] = [
      '#type' => 'markup',
      '#markup' => '<div style="text-align:center;"><p>Tell Us About Your Pathogen that you were looking for</p></div>',
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

   
     $form['Pathogen_notlisted'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Pathogen Not Listed'),
      '#placeholder' => 'Pathogen Not Listed',
    ];
    
  

    // $form['google_recaptcha'] = [
    //   '#type'=> 'fieldset',
    //   '#description' => '<div class="g-recaptcha" data-sitekey="6Lcr4u0pAAAAAGj32knXkUzuHAXzj3CoAhtbJ1t5"></div>',
    // ];

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

  // public function validateForm(array &$form, FormStateInterface $form_state){

   

  //   if (isset($_POST['g-recaptcha-response']) && $_POST['g-recaptcha-response'] != '') {
  //     $captcha_response = $_POST['g-recaptcha-response'];
  //     $remote_ip = $_SERVER['REMOTE_ADDR'];
  
  //     $result = $this->verifyGoogleRecaptcha($captcha_response, $remote_ip);
  
  //     $data = json_decode($result, true);
  
  //     if (!$data['success']) {
  //         $form_state->setErrorByName('google_recaptcha', t('Please complete the captcha to prove you are human'));
  //     }
  //   } else {
  //       $form_state->setErrorByName('google_recaptcha', t('Please complete the reCAPTCHA verification.'));
  //   }
  //   if (empty($_POST['g-recaptcha-response'])) {
  //       $form_state->setErrorByName('google_recaptcha', t('Please complete the reCAPTCHA verification.'));
  //   }

  // }

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

    $Selector = '#norgeniuspathogenform-container';
    
    $response = new AjaxResponse();
    
    if ($form_state->hasAnyErrors()) {
      $response = nor_forms_ajax_error($form, $form_state, $Selector);
      return $response;
    } else {

      $response = nor_forms_pathogen_sent_norgenius($form, $form_state, $Selector);
      // $form_state['rebuild'] = true;
      $form_state->setRebuild(TRUE);
      //$_POST['g-recaptcha-response'] = '';
      return $response;
      
    }
    
  }



  public function submitForm(array &$form, FormStateInterface $form_state){

    
    $email = $form_state->getValue('norgenius_email');
    $pathogen = $form_state->getValue('Pathogen_notlisted');

   
   

    $date = date("Ymd");
    //$form_name = $this->t('Account Issues Form');

    $output = "";
   

    
    if (!isset($email)) {
      $email = 'NULL';
    }

    



    $output = '<p style="font-size:16px;">Hello,</p>
        <p style="font-size:16px;">Customer has requested pathogen</p>';

      

      
      
      
      $output .= '<div style="border:1px solid #cfc6c6;border-radius:20px;padding: 2%;text-align: left;font-size:16px;">';
      $output .= 'Pathogen: ';
      $output .= $pathogen;
      $output .= '<br>';
      $output .= 'Email Address: ';
       $output .= $email;
      $output .= '</div>';
      
      
      $output .= '<div class="powered_result" style="font-size: 12px;font-size:16px;text-align:center;"><p><strong>Powered by  <img src="https://norgenbiotek.com/sites/default/files/images/norgenius/norgenius_icon.png" style="width: 50px;">  NorGenius</strong></p></div>';
      $clink = 'https://norgenbiotek.com/contact';
      $output .= '<div style="font-size:16px;"><p>Please <a href='.$clink.'>Contact Us</a> if you have any additional questions or concerns</p><br><br><p>Regards,</p><p>Norgen Biotek</p></div>';

    
     
   

    $time = time();

    if ($form_state->hasAnyErrors()) {
    } else {
      $subject = '[Contact form] - Norgenius Email ' . date("F j, Y, g:i a", $time);
      //$recipient_email = $email;
      // $recipient_email = 'sabah.butt@norgenbiotek.com';// real addresses
      $recipient_email = 'sabah.butt@norgenbiotek.com';
      nor_forms_email_redirect($output, $recipient_email, $subject);
    }
  }
}
