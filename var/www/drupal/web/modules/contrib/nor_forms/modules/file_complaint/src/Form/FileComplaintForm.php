<?php

namespace Drupal\file_complaint\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\MessageCommand;
use Drupal\Core\Ajax\AppendCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\file\Entity\File;
use \Drupal\Core\File\FileSystemInterface;
use Drupal\nor_forms\Libraries\Zoho\RecordWrapper;


class FileComplaintForm extends FormBase
{

  public function getFormId()
  {
    return 'file_complaint_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {

    $form['#prefix'] = '<div id ="filecomplaintform-container" div class="filecomplaintform-container">';

    $form['header'] = [
      '#type' => 'markup',
      '#markup' => '<h2>File a Complaint</h2><p>We value your feedback and would like to hear customer concerns. Please submit the following form and a representative will follow up with you shortly.</p>',
    ];

    $form['complaint_fname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Name'),
      '#default_value' => nor_forms_user_first_name(),
      '#required' => TRUE,
      '#placeholder' => 'First Name (Required)',
    ];

    $form['complaint_lname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last Name'),
      '#default_value' => nor_forms_user_last_name(),
      '#required' => TRUE,
      '#placeholder' => 'Last Name (Required)',
    ];


    $form['complaint_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#default_value' => nor_forms_user_email(),
      '#required' => TRUE,
      '#placeholder' => 'Email Address (Required)',
    ];

    //add area code options
    $form['complaint_phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Phone Number'),
      '#maxlength' => 20, // Limit input to 14 characters
      '#required' => TRUE,
      '#placeholder' => 'Phone Number (Required)',
    ];

    $form['complaint_company'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Company Name'),
      '#required' => TRUE,
      '#placeholder' => 'Company Name (Required)',
    ];

    $form['complaint_country'] = [
      '#type' => 'select',
      '#title' => $this->t('Country'),
      '#options' => getCountryOptions(),
      '#required' => TRUE,
      '#attributes' => ['class' => ['aligned-country-list']],
    ];

    $form['complaint_PON'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Sales Order Number or Purchase Order Number'),
      '#pattern' => '^[a-zA-Z0-9]+$', // Regular expression for alphanumeric strings
      '#maxlength' => 255, // Limit input to 255 characters
      '#required' => TRUE,
      '#placeholder' => 'Eg. 12345 (Required)',
    ];

    $form['complaint_cat'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Product SKU (Cat.)'),
      '#required' => TRUE,
      '#placeholder' => 'Eg. 17200 (Required)',
    ];


    $form['complaint_message'] = [
      '#title' => $this->t('Please Describe Your Concern and Attach any Relevant Files Below'),
      '#type' => 'textarea',
      '#required' => TRUE,
    ];

    $form['complaint_attachment'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Attach a File (pdf, png, jpg, jpeg)'),
      '#upload_validators' => [
        'file_validate_extensions' => [' png jpeg jpg pdf'],
      ],
      '#upload_location' => 'public://file_complaint',
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
        
      ]
    ];

    $form['#suffix'] = '</div>';
    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state)
  {

    $first_name = $form_state->getValue('complaint_fname');
    $last_name = $form_state->getValue('complaint_lname');

    if (empty($first_name) || strlen($first_name) < 2) {
      $form_state->setErrorByName('complaint_fname', $this->t('Please enter your first name.'));
    }

    if (empty($last_name) || strlen($last_name) < 2) {
      $form_state->setErrorByName('complaint_lname', $this->t('Please enter your last name.'));
    }
    if ($first_name && $last_name && $first_name === $last_name) {
      // Add an error to the last name field
      $form_state->setErrorByName('complaint_lname', $this->t('Last name should not be the same as first name.'));
    }

    if ($first_name && $last_name && strlen($last_name) >= 6 && strpos($last_name, $first_name) !== false) {
        // Add an error if last name contains first name for 6 or more characters
        $form_state->setErrorByName('complaint_lname', $this->t('Last name should not contain first name for 6 or more characters.'));
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

    $Selector = '#filecomplaintform-container';
    $response = new AjaxResponse();

    if ($form_state->hasAnyErrors()) {
      $response = nor_forms_ajax_error($form, $form_state, $Selector);
      return $response;
    } else {

      $response = nor_forms_email_sent_ajax($form, $form_state, $Selector);

    }
    return $response;
  }


  public function submitForm(array &$form, FormStateInterface $form_state)
  {

    $first_name = $form_state->getValue('complaint_fname');
    $last_name = $form_state->getValue('complaint_lname');
    $email = $form_state->getValue('complaint_email');
    $country_code = $form_state->getValue('complaint_country');
    $phone = $form_state->getValue('complaint_phone');
    $company = $form_state->getValue('complaint_company');
    $productnum = $form_state->getValue('complaint_PON');
    $sku = $form_state->getValue('complaint_cat');
    $message = $form_state->getValue('complaint_message');
    $date = date("Ymd");
    $form_name = $this->t('File Complaint');
    $output = "";
    //Get the countries with codes directly from the CountryRepository service
    $country_name = getCountryNames($country_code);
    
    if (!isset($first_name)) {
      $first_name = 'NULL';
    }
    if (!isset($email)) {
      $email = 'NULL';
    }

    $moved_file = null;
    $email_dir_name = nor_forms_email_to_directory_name($email);
    $fid = $form_state->getValue('complaint_attachment')[0] ?? NULL;
    if($fid){
      $file = File::load($fid);
      $permanent_uri = 'private://file_complaint/' .$email_dir_name . '/' . date('Y-m-d'); // uses email and date to store the files. E.g. liam.howes@norgenbiotek.com submitting on May 23 2025 saves to: private://account_issues/liam_howes_norgenbiotek_com_4901bb87/2025-05-23
      $moved_file = nor_forms_file_upload_move_permanent($file, $permanent_uri, ['move_method' => FileSystemInterface::EXISTS_REPLACE]);
      $file_path = $moved_file->getFileUri();  
      $form_state->set('file_path', $file_path);
    }
    $new_fid = null;
    if($moved_file) $new_fid = $moved_file->id() ?? null;

    $query = \Drupal::database()->insert('forms_to_zoho');
    $query->fields(['created_on', 'first_name', 'last_name', 'country', 'email', 'record', 'timestamp', 'form_name', 'phone', 'company', 'notes', 'so_id', 'products', 'doc_fid']); //wrong syntax here breaks entire submit function 
    $query->values([$date, $first_name, $last_name, $country_name, $email, '', time(), $form_name, $phone, $company, $message, $productnum, $sku, $new_fid]);
    $query->execute();

    try { //Zoho upsert
      if (1) {

        $zoho = new RecordWrapper('leads');
        $record = [
          'First_Name' => $first_name,
          'Last_Name' => $last_name,
          'Email' => $email,
          'Company' => $company,
          'Country' => $country_name,
          'Phone' => $phone,
          'Lead_Source' => 'Website Form',
          'Web_Forms' => [$form_name],
        ];
        $upsert_result = $zoho->upsert($record);
      }
    } catch (Exception $e) {
    }


    $output = '<p>Hello,</p>
    <p>A customer submitted their request for File Complaint Form.</p>
    <p>Last name: ' . $last_name . '<br>First name: ' . $first_name . '<br>Email: ' . $email . '<br>Primary Phone: ' . $phone . '<br>Company: ' . $company . '<br>Country: ' . $country_name . ' <br>Purchase Order Number: ' . $productnum . ' <br>Product SKU: ' . $sku . ' <br>Message: ' . $message;

    $time = time();

    if ($form_state->hasAnyErrors()) {
    } else {
      $subject = '[Contact form] - File Complaint ' . date("F j, Y, g:i a", $time);
      $recipient_email = 'complaints@norgenbiotek.com,sebastian.szopa@norgenbiotek.com';// real addresses
      //$recipient_email = 'liam.howes@norgenbiotek.com';
      // $recipient_email = 'sowmya.movva@norgenbiotek.com';
      if ($moved_file) {
        nor_forms_submit_attachment($output, $recipient_email, $moved_file, $subject);
      } else {
        nor_forms_email_redirect($output, $recipient_email, $subject);
      }
    }
  }
}