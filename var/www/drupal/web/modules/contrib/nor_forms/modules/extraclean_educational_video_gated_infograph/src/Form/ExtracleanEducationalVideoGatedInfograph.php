<?php

namespace Drupal\extraclean_educational_video_gated_infograph\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\file\Entity\File;
use Drupal\nor_forms\Libraries\Zoho\RecordWrapper;

class ExtracleanEducationalVideoGatedInfograph extends FormBase
{

  public function getFormId()
  {
    return 'extraclean_educational_video_gated_infograph';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {

    $form['#prefix'] = '<div id = "ExtracleanEducationalVideoGatedInfograph-container" div class="ExtracleanEducationalVideoGatedInfograph-container">';

    $form['extraclean_fname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Name'),
      '#required' => TRUE,
      '#placeholder' => $this->t('First Name (Required)'),
    ];

    $form['extraclean_lname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last Name'),
      '#required' => TRUE,
      '#placeholder' => $this->t('Last Name (Required)'),
    ];

    $form['extraclean_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#required' => TRUE,
      '#placeholder' => $this->t('Email Address (Required)'),
    ];

    $form['extraclean_phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Phone Number'),
      '#required' => TRUE,
      '#placeholder' => $this->t('Phone Number (Required)'),
      '#attributes' => array(
        'pattern' => '[0-9]{7,20}',
      )
    ];

    $form['extraclean_job'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Job Title'),
      '#required' => TRUE,
      '#placeholder' => $this->t('Job Title (Required)'),
    ];

    $form['extraclean_company'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Company / Institution'),
      '#required' => TRUE,
      '#placeholder' => $this->t('Company / Institution (Required)'),
    ];

    $form['extraclean_country'] = [
      '#type' => 'select',
      '#title' => $this->t('Country'),
      '#options' => getCountryOptions(),
      '#attributes' => ['class' => ['aligned-country-list']],
      '#required' => TRUE,
    ];

    $form['extraclean_state'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Province / State'),
      '#placeholder' => $this->t('Province / State (Required)'),
      '#required' => TRUE,
    ];

    $form['extraclean_subscribe'] = [
      '#type' => 'checkbox',
      '#title' => t('I would like to receive emails from Norgen Biotek regarding webinars, services, and other marketing material.*'),
      '#suffix' => '<div class="disclaimer">It is our responsibility to protect and guarantee that your data will be completely confidential. You can unsubscribe from Norgen emails at any time by clicking the link in the footer of our emails. For more information please view our <a href="/content/privacy-policy">Privacy Policy</a>.</div>',
    ];

    $form['google_recaptcha'] = [
      '#type'=> 'fieldset',
      '#description' => '<div class="g-recaptcha" data-sitekey="6Lcr4u0pAAAAAGj32knXkUzuHAXzj3CoAhtbJ1t5"></div>',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Access Now'),
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

  public function validateForm(array &$form, FormStateInterface $form_state){

    $first_name = $form_state->getValue('extraclean_fname');
    $last_name = $form_state->getValue('extraclean_lname');

    if (empty($first_name) || strlen($first_name) < 2) {
      $form_state->setErrorByName('extraclean_fname', $this->t('Please enter your first name.'));
    }

    if (empty($last_name) || strlen($last_name) < 2) {
      $form_state->setErrorByName('extraclean_lname', $this->t('Please enter your last name.'));
    }
    if ($first_name && $last_name && $first_name === $last_name) {
      $form_state->setErrorByName('extraclean_lname', $this->t('Last name should not be the same as first name.'));
    }
    if ($first_name && $last_name && strlen($last_name) >= 6 && strpos($last_name, $first_name) !== false) {
      $form_state->setErrorByName('extraclean_lname', $this->t('Last name should not contain first name for 6 or more characters.'));
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

  public function submitCallback(array &$form, FormStateInterface $form_state){

    $Selector = '#ExtracleanEducationalVideoGatedInfograph-container';
    $response = new AjaxResponse();

    if ($form_state->hasAnyErrors()) {
      $response = nor_forms_ajax_error($form, $form_state, $Selector); 
    } else {
      $response = extraclean_educational_video_gated_infograph_email_sent_ajax($form, $form_state, $Selector); //replace with appropriate lottie animation function found in nor_forms.module file
    }
    return $response;
  }


  public function submitForm(array &$form, FormStateInterface $form_state){

    $first_name = $form_state->getValue('extraclean_fname');
    $last_name = $form_state->getValue('extraclean_lname');
    $email = $form_state->getValue('extraclean_email');
    $phone = $form_state->getValue('extraclean_phone');
    $job = $form_state->getValue('extraclean_job');
    $company = $form_state->getValue('extraclean_company');
    $country = $form_state->getValue('extraclean_country');
    $country_name = getCountryNames($country);
    $state = $form_state->getValue('extraclean_state');

    $subscribe = $form_state->getValue('extraclean_subscribe');
    $subscribe_text = $subscribe == 1 ? 'Yes' : 'No';

    $date = date("Ymd");
    $form_name = $this->t('Extraclean Infograph Lead Capture');


    if (!isset($first_name)) {
      $first_name = 'NULL';
    }
    if (!isset($email)) {
      $email = 'NULL';
    }

    $query = \Drupal::database()->insert('forms_to_zoho');
    $query->fields(['created_on', 'first_name', 'last_name', 'email', 'record', 'timestamp', 'form_name', 'phone', 'company', 'job_title', 'country', 'state', 'opt_in']); //wrong syntax here breaks entire submit function 
    $query->values([$date, $first_name, $last_name, $email, '', time(), $form_name, $phone, $company, $job, $country_name, $state, $subscribe]);
    $query->execute();

    try { //Zoho upsert

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
    }


    $output = '<p>Hello,</p>
    <p>A user accessed the Extraclean Infograph Gated Resource</p>
    <p>First name: ' . $first_name . '<br>Last name: ' . $last_name . '<br>Email: ' . $email . '<br>Phone: ' . $phone . '<br>Job: ' . $job . '<br>Company: ' . $company . '<br>Country: ' . $country_name . '<br>State: ' . $state . '<br>Subscribed?: ' . $subscribe_text;

    $time = time();

    if ($form_state->hasAnyErrors()) {
    } else {
      $subject = '[Extraclean Infograph] - Gated Resource Accessed ' . date("F j, Y, g:i a", $time);
      // $recipient_email = 'sowmya.movva@norgenbiotek.com';
      $recipient_email = 'support@norgenbiotek.com, sabah.butt@norgenbiotek.com';// real addresses
      //$recipient_email = 'liam.howes@norgenbiotek.com';
      //$recipient_email = 'sabah.butt@norgenbiotek.com';
      //$recipient_email = 'sabah.butt@norgenbiotek.com';
      //$recipient_email = 'huraira.khan@norgenbiotek.com';
      nor_forms_email_redirect($output, $recipient_email, $subject);
    }
  }
}
