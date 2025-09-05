<?php

namespace Drupal\norgenius_saveworkflow\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\file\Entity\File;
use Drupal\nor_forms\Libraries\Zoho\RecordWrapper;

class NorgeniusSaveworkflowForm extends FormBase
{

  public function getFormId() {
    return 'norgenius_saveworkflow_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['#prefix'] = '<div id="norgeniussaveworkflowform-container" class="norgeniussaveworkflowform-container">';

    $form['header'] = [
      '#type' => 'markup',
      '#markup' => '<div style="text-align:center;"><img src="https://jango.norgenbiotek.com/sites/default/files/images/norgenius/bookmark.png"><h2>Give a name to your custom workflow</h2><p>Save your custom workflow to view anytime</p></div>',
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

   
     $form['category'] = [
      '#type' => 'textfield',
      '#title' => $this->t('category'),
    ];
     $form['organism'] = [
      '#type' => 'textfield',
      '#title' => $this->t('organism'),
    ];
     $form['pathogen'] = [
      '#type' => 'textfield',
      '#title' => $this->t('pathogen'),
    ];
     $form['analyte'] = [
      '#type' => 'textfield',
      '#title' => $this->t('analyte'),
    ];
     $form['sample'] = [
      '#type' => 'textfield',
      '#title' => $this->t('sample'),
    ];
     $form['wf_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Workflow Name'),
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
        'method' => 'append', 'effect' => 'fade',
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

    $Selector = '#norgeniussaveworkflowform-container';
    $response = new AjaxResponse();

    if ($form_state->hasAnyErrors()) {
      $response = nor_forms_ajax_error($form, $form_state, $Selector);
      return $response;
    } else {

      $response = nor_forms_email_sent_norgenius_saved($form, $form_state, $Selector);

      return $response;
    }
  }


  public function submitForm(array &$form, FormStateInterface $form_state){

    
    $email = $form_state->getValue('norgenius_email');
    $category = $form_state->getValue('category');
    $organism = $form_state->getValue('organism');
    $pathogen = $form_state->getValue('pathogen');
    $analyte = $form_state->getValue('analyte');
    $sample = $form_state->getValue('sample');
    $wf_name = $form_state->getValue('wf_name');
   

    $date = date("Ymd");
    $form_name = $this->t('Account Issues Form');
   

    
    if (!isset($email)) {
      $email = 'NULL';
    }


    $query = \Drupal::database()->insert('saved_ng_workflows');
    $query->fields(['date', 'wf_name', 'email', 'category', 'organism', 'pathogen', 'analyte', 'sample']); //wrong syntax here breaks entire submit function 
    $query->values([$date, $wf_name, $email, $category, $organism, $pathogen, $analyte, $sample]);
    $query->execute();



    

    
  }
}
