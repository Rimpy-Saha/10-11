<?php

namespace Drupal\liquid_biopsy\Form;

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


class LiquidBiopsyForm extends FormBase
{

  public function getFormId()
  {
    return 'liquid_biopsy_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {

    $form['#prefix'] = '<div id="liquid-biopsy-container" class="liquid-biopsy-container">';


    $form['header'] = [
      '#type' => 'markup',
      '#markup' => '<h2>Access Free Content</h2><p>After entering your information below, you will be granted access to our exclusive Liquid Biopsy solutions booklet!</p>',
      '#class' => 'work_pls',
    ];

    $form['message'] = [
      '#type' => 'markup',
      '#markup' => '<div id="result-message"></div>',
    ];

    $form['biopsy_fname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Name'),
      '#default_value' => nor_forms_user_first_name(),
      '#required' => TRUE,
    ];

    $form['biopsy_lname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last Name'),
      '#default_value' => nor_forms_user_last_name(),
      '#required' => TRUE,
    ];

    $form['biopsy_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#default_value' => nor_forms_user_email(),
      '#required' => TRUE,
    ];

    $form['biopsy_company'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Company'),
      '#required' => TRUE,
    ];

    $form['biopsy_country'] = [
      '#type' => 'select',
      '#title' => $this->t('Country'),
      '#required' => TRUE,
      '#empty_option'=>t('- Please Select Country -'),
      '#options' => getCountryOptions(), // Use the global function to get country options
      '#attributes' => [
      'autocomplete'=> "country",
      ],
    ];

    
    $form['biopsy_mailing'] = [
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
      '#value' => $this->t('Access Content'),
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

    $first_name = $form_state->getValue('biopsy_fname');
    $last_name = $form_state->getValue('biopsy_lname');

    if (empty($first_name) || strlen($first_name) < 2) {
      $form_state->setErrorByName('biopsy_fname', $this->t('Please enter your first name.'));
    }

    if (empty($last_name) || strlen($last_name) < 2) {
      $form_state->setErrorByName('biopsy_lname', $this->t('Please enter your last name.'));
    }

    if ($first_name && $last_name && $first_name === $last_name) {
      // Add an error to the last name field
      $form_state->setErrorByName('biopsy_lname', $this->t('Last name should not be the same as first name.'));
    }
    if ($first_name && $last_name && strlen($last_name) >= 6 && strpos($last_name, $first_name) !== false) {
      // Add an error if last name contains first name for 6 or more characters
      $form_state->setErrorByName('biopsy_lname', $this->t('Last name should not contain first name for 6 or more characters.'));
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


  public function submitCallback(array &$form, FormStateInterface $form_state) {

    $Selector = '#liquid-biopsy-container';

    if ($form_state->hasAnyErrors()) {
      $response = nor_forms_ajax_error($form, $form_state, $Selector);
      return $response;
   
    } else {

      $response = new AjaxResponse();
      $response->addCommand(new InvokeCommand($Selector, 'delay', [500]));

      $response->addCommand(new RedirectCommand('https://norgenbiotek.com/sites/default/files/catalogs_ebooks/Liquid-Biopsy-Solutions-Early-Cancer-Detection-NIPT-3.0-web.pdf'));

      return $response;
    }
  }


  public function submitForm(array &$form, FormStateInterface $form_state){

    $first_name = $form_state->getValue('biopsy_fname');
    $last_name = $form_state->getValue('biopsy_lname');
    $email = $form_state->getValue('biopsy_email');
    $company = $form_state->getValue('biopsy_company');

    $mailing = $form_state->getValue('biopsy_mailing');
    $mailing_text = $mailinbg == 1 ? 'Yes' : 'No';

    $country_code = $form_state->getValue('biopsy_country');

    $date = date("Ymd");
    $form_name = $this->t('Liquid Biopsy');

    $output = "";
    $country_name = getCountryNames($country_code);



    if (!isset($first_name)) {
      $first_name = 'NULL';
    }
    if (!isset($email)) {
      $email = 'NULL';
    }

    $query = \Drupal::database()->insert('forms_to_zoho');
    $query->fields(['created_on', 'first_name', 'last_name', 'email', 'company', 'record', 'timestamp', 'form_name', 'opt_in', 'country']);
    $query->values([$date, $first_name, $last_name, $email, $company, '', time(), $form_name, $mailing, $country_name]);
    $query->execute();

    try {  //Zoho upsert
      if (1) {

        $zoho = new RecordWrapper('leads');
        $record = [
          'First_Name' => $first_name,
          'Last_Name' => $last_name,
          'Email' => $email,
          'Company' => $company,
          'Country' => $country_name,
          'Email_Opt_Out' => $mailing,
          'Lead_Source' => 'Website Form',
          'Web_Forms' => [$form_name],
        ];
        $upsert_result = $zoho->upsert($record);
      }
    } catch (Exception $e) {
    }



    $output = '<p>Hello,</p>
    <p>A customer submitted their request for Liquid Biopsy Form.</p>
    <p>Last name: ' . $last_name . '<br>First name: ' . $first_name . '<br>Email: ' . $email . '<br>First name: ' . $company . '<br>Country: ' . $country_name . ' <br>Subscribe to Mailing: ' . $mailing_text;

    $time = time();

    if ($form_state->hasAnyErrors()) {
    } else {
      $subject = '[Contact form] - Liquid Biopsy ' . date("F j, Y, g:i a", $time);
      // $recipient_email = 'sowmya.movva@norgenbiotek.com';
      //$recipient_email = 'info@norgenbiotek.com, sebastian.szopa@norgenbiotek.com, sabah.butt@norgenbiotek.com';// real addresses
      $recipient_email = 'liam.howes@norgenbiotek.com';
      //$recipient_email = 'huraira.khan@norgenbiotek.com';

      nor_forms_email_redirect($output, $recipient_email, $subject);
    }
  }
}
