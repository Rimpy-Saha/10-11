<?php

namespace Drupal\newproduct_signup\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\MessageCommand;
use Drupal\Core\Ajax\AppendCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\nor_forms\Libraries\Zoho\RecordWrapper;


class NewProductSignUpForm extends FormBase
{

  public function getFormId()
  {
    return 'newproduct_signup_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {

    $form['#prefix'] = '<div id="signup-container" class="signup-container">';


    $form['header'] = [
      '#type' => 'markup',
      '#markup' => '',
      '#class' => 'work_pls',
    ];

    

    $form['signup_email'] = [
      '#type' => 'email',
      '#prefix' => '<div class="email-country">',
      '#title' => $this->t('Email Address'),
      '#default_value' => nor_forms_user_email(),
      '#required' => TRUE,
    ];

    $form['signup_country'] = [
      '#type' => 'select',
      '#title' => $this->t('Country'),
      '#options' => getCountryOptions(),
      '#attributes' => ['class' => ['aligned-country-list']],
      '#required' => TRUE,
    ];

    $form['subscribe'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Yes, I would like to receive emails from Norgen Biotek regarding promotions, blogs, and other marketing material.'),
    ];

    $form['google_recaptcha'] = [
      '#type'=> 'fieldset',
      '#description' => '<div class="g-recaptcha" data-sitekey="6Lcr4u0pAAAAAGj32knXkUzuHAXzj3CoAhtbJ1t5"></div>',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Email Me'),
      '#button_type' => 'primary',
      '#submit' => ['::submitForm'],
      '#ajax' => [
        'callback' => '::submitCallback',
        'event' => 'click',
        'method' => 'append', 'effect' => 'fade',
      ]
    ];

    $form['message'] = [
      '#type' => 'markup',
      '#markup' => '<div id="result-message"></div>',
    ];

    $form['#suffix'] = '</div>';

    return $form;
  }


  public function validateForm(array &$form, FormStateInterface $form_state)
  {

     $email = $form_state->getValue('signup_email');
     if (empty($email) || strlen($email) < 2) {
      $form_state->setErrorByName('signup_email', $this->t('Please enter your email address.'));
    }

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
  
  public function submitCallback(array &$form, FormStateInterface $form_state)
  {

    $Selector = '#signup-container';
    $response = new AjaxResponse();

    if ($form_state->hasAnyErrors()) {
      $response = nor_forms_ajax_error($form, $form_state, $Selector);
      return $response;
    } else {

      $response = newproduct_signup_email_sent_ajax($form, $form_state, $Selector);
      return $response;
    }
  }

  /**
   * Final submit handler.
   *
   * Reports what values were finally set.
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {

  
    $email = $form_state->getValue('signup_email');
    $country = $form_state->getValue('signup_country');
    $country_name = getCountryNames($country);
    
    $mailing = $form_state->getValue('subscribe');

    $date = date("Ymd");
    $form_name = $this->t('New Product Sign Up Form');

    // if($mailing == 'yes'){
    //   $mailing_boolean = 1;
    // }
    // else{
    //   $mailing_boolean = 0;
    // }
    // $mailing_boolean = ($mailing === 'yes'); //for db opt in 
    // $mailing_out = ($mailing === 'no'); //for zoho opt out 


    if (!isset($email)) {
      $email = 'NULL';
    }

    $query = \Drupal::database()->insert('forms_to_zoho');
    $query->fields(['created_on','email', 'country', 'record', 'timestamp', 'form_name', 'opt_in']);
    $query->values([$date, $email, $country, '', time(), $form_name, $mailing]);
    $query->execute();

    /* try { //Zoho upsert
      $zoho = new RecordWrapper('leads');
      $record = [
        'First_Name' => $first_name,
        'Last_Name' => $last_name,
        'Email' => $email,
        'Company' => $company,
        'Job_Position' => $job,
        'Phone' => $phone,
        'Country' => $country_name,
        'State' => $state,
        'Lead_Source' => 'Website Form',
        'Web_Forms' => [$form_name],
      ];
      $upsert_result = $zoho->upsert($record);
    } catch (Exception $e) {
    } */



    $output = '<p>Hello,</p>
    <p>A customer signed up to get update on new kits.</p>
    <p>Email: ' . $email . '<br>Counrty: ' . $country . ' <br>Subscribe to Mailing: ' . $mailing;

    $time = time();

    if ($form_state->hasAnyErrors()) {
    } else {
      $subject = '[Contact form] - new product sign up ' . date("F j, Y, g:i a", $time);
      $recipient_email = 'info@norgenbiotek.com, sabah.butt@norgenbiotek.com, sebastian.szopa@norgenbiotek.com';// real addresses
      //$recipient_email = 'liam.howes@norgenbiotek.com';
      nor_forms_email_redirect($output, $recipient_email, $subject);
    }
  }
}
