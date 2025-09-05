<?php

namespace Drupal\contact_distributor\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\file\Entity\File;
use \Drupal\Core\File\FileSystemInterface;
use Drupal\nor_forms\Libraries\Zoho\RecordWrapper;

class ContactDistributorForm extends FormBase {

  public function getFormId() {
  
    return 'contact_distributor_form';
  
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['#prefix'] = '<div id="contactdistributorform-container" class="contactdistributorform-container">';

    $form['header'] = [
      '#type' => 'markup',
      '#markup' => '<h2>Become a Norgen Biotek Distributor</h2><p>Enter your information below to apply to become an official Norgen Biotek distributor.</p>',
      '#class' => 'work_pls',
    ];

    $form['message'] = [
      '#type' => 'markup',
      '#markup' => '<div id="result-message"></div>',
    ];

    $form['distributor_fname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Name'),
      '#default_value' => nor_forms_user_first_name(),
      '#required' => TRUE,
      '#placeholder' => 'First Name (Required)',
    ];

    $form['distributor_lname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last Name'),
      '#default_value' => nor_forms_user_last_name(),
      '#required' => TRUE,
      '#placeholder' => 'Last Name (Required)',
    ];

    $form['distributor_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#default_value' => nor_forms_user_email(),
      '#required' => TRUE,
      '#placeholder' => 'Email Address (Required)',
    ];

    $form['distributor_company'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Company / Institution'),
      '#required' => TRUE,
      '#placeholder' => 'Company / Institution (Required)',
    ];

    $form['distributor_country'] = [
      '#type' => 'select',
      '#title' => $this->t('Country'),
      '#options' => getCountryOptions(), // Use the global function to get country options
      '#required' => TRUE,
      '#attributes' => ['class' => ['aligned-country-list']],
    ];

    $form['distributor_attachment'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Attach Company Profile / Supporting Document (pdf)'),
      '#upload_validators' => [
        'file_validate_extensions' => [' pdf'],
      ],
      '#upload_location' => 'public://contact_distributor',
      '#required' => TRUE,
    ];

    $form['distributor_message'] = [
      '#title' => $this->t('Additional Company / Institution Information'),
      '#type' => 'textarea',
      '#description' => 'Please provide any additional information about your company in this text area.',
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
      ],
      '#attributes' => [
        'style' => 'display: block; margin: 0 auto;'
      ],
    ];

    $form['#suffix'] = '</div>';
    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state)
  {

    $first_name = $form_state->getValue('distributor_fname');
    $last_name = $form_state->getValue('distributor_lname');

    if (empty($first_name) || strlen($first_name) < 2) {
      $form_state->setErrorByName('distributor_fname', $this->t('Please enter your first name.'));
    }

    if (empty($last_name) || strlen($last_name) < 2) {
      $form_state->setErrorByName('distributor_lname', $this->t('Please enter your last name.'));
      if ($first_name && $last_name && $first_name === $last_name) {
        // Add an error to the last name field
        $form_state->setErrorByName('distributor_lname', $this->t('Last name should not be the same as first name.'));
      }
    }
  
      if ($first_name && $last_name && strlen($last_name) >= 6 && strpos($last_name, $first_name) !== false) {
          // Add an error if last name contains first name for 6 or more characters
          $form_state->setErrorByName('distributor_lname', $this->t('Last name should not contain first name for 6 or more characters.'));
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

    $Selector = '#contactdistributorform-container';
    $response = new AjaxResponse();

    if ($form_state->hasAnyErrors()) {
      $response = nor_forms_ajax_error($form, $form_state, $Selector);
      return $response;
    } else {
      $response = nor_forms_email_sent_ajax($form, $form_state, $Selector);
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

    $first_name = $form_state->getValue('distributor_fname');
    $last_name = $form_state->getValue('distributor_lname');
    $email = $form_state->getValue('distributor_email');
    $company = $form_state->getValue('distributor_company');
    $country_code = $form_state->getValue('distributor_country');
    $message = $form_state->getValue('distributor_message');
    $date = date("Ymd");
    $form_name = $this->t('Distributor Application Form');
    $output = "";
    //Get the countries with codes directly from the CountryRepository service
    $country_name = getCountryNames($country_code);

    $moved_file = null;
    $email_dir_name = nor_forms_email_to_directory_name($email);
    $fid = $form_state->getValue('distributor_attachment')[0] ?? NULL;
    if($fid){
      $file = File::load($fid);
      $permanent_uri = 'private://distributor_applications/' .$email_dir_name . '/' . date('Y-m-d'); // uses email and date to store the files. E.g. liam.howes@norgenbiotek.com submitting on May 23 2025 saves to: private://distributor_applications/liam_howes_norgenbiotek_com_4901bb87/2025-05-23
      $moved_file = nor_forms_file_upload_move_permanent($file, $permanent_uri, ['move_method' => FileSystemInterface::EXISTS_REPLACE]);
      $file_path = $moved_file->getFileUri();  
      $form_state->set('file_path', $file_path);
    }
    $new_fid = null;
    if($moved_file) $new_fid = $moved_file->id() ?? null;

    $query = \Drupal::database()->insert('forms_to_zoho');
    $query->fields(['created_on', 'first_name', 'last_name', 'country', 'email', 'company', 'record', 'timestamp', 'form_name', 'notes', 'doc_fid']); //wrong syntax here breaks entire submit function 
    $query->values([$date, $first_name, $last_name, $country_name, $email, $company, '', time(), $form_name, $message, $new_fid]);
    $query->execute();

    
    if (!isset($first_name)) {
      $first_name = 'NULL';
    }
    if (!isset($email)) {
      $email = 'NULL';
    }

    try {
        $zoho = new RecordWrapper('leads');
        $record = [
          'First_Name' => $first_name,
          'Last_Name' => $last_name,
          'Email' => $email,
          'Company' => $company,
          'Country' => $country_name,
          'Lead_Source' => 'Website Form',
          'Web_Forms' => [$form_name],
        ];
        $upsert_result = $zoho->upsert($record);

    } catch (Exception $e) {
    }


    $output = '<p>Hello,</p>
    <p>A customer submitted their request to become a Norgen Biotek distributor.</p>
    <p>Last name: ' . $last_name . '<br>First name: ' . $first_name . '<br>Email: ' . $email . '<br>Company: ' . $company . '<br>Country: ' . $country_name;
    if(isset($message) && $message!='') $output .= '<br>Message: ' . $message;

    $time = time();

    if ($form_state->hasAnyErrors()) {
    } else {
    
      $subject = '[Contact form] - Distributor Application ' . date("F j, Y, g:i a", $time);
      $recipient_email = 'pandora.huang@norgenbiotek.com';// real addresses
      $cc = 'info@norgenbiotek.com';
      //$recipient_email = 'liam.howes@norgenbiotek.com';
      //$cc = 'webteam@norgenbiotek.com';

      //$recipient_email = 'sowmya.movva@norgenbiotek.com';
      if ($moved_file) {
        nor_forms_submit_attachment($output, $recipient_email, $moved_file, $subject, $cc);
      } else {
        nor_forms_email_redirect($output, $recipient_email, $subject, $cc);
      }
    }
  }
}
