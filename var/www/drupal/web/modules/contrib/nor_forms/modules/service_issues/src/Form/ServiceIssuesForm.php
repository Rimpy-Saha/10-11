<?php

namespace Drupal\service_issues\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\MessageCommand;
use Drupal\Core\Ajax\AppendCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\Entity\File;
use Drupal\nor_forms\Libraries\Zoho\RecordWrapper;

class ServiceIssuesForm extends FormBase
{

  public function getFormId()
  {
    return 'service_issues_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {

    $form['#prefix'] = '<div id="serviceissuesform-container" div class="serviceissuesform-container">';

    $form['header'] = [
      '#type' => 'markup',
      '#markup' => '<h2>Services Issues</h2><p>Issues with one of our provided services? Please submit the following form and a representative will follow up with you shortly.</p>'
    ];

    $form['serviceissues_fname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Name'),
      '#default_value' => nor_forms_user_first_name(),
      '#required' => TRUE,
      '#placeholder' => 'First Name (Required)',
    ];

    $form['serviceissues_lname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last Name'),
      '#default_value' => nor_forms_user_last_name(),
      '#required' => TRUE,
      '#placeholder' => 'Last Name (Required)',
    ];


    $form['serviceissues_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#default_value' => nor_forms_user_email(),
      '#required' => TRUE,
      '#placeholder' => 'Email Address (Required)',
    ];

    $form['serviceissues_company'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Company/Institution'),
      '#required' => TRUE,
      '#placeholder' => 'Company/Institution (Required)',
    ];

    $form['serviceissues_country'] = [
      '#type' => 'select',
      '#title' => $this->t('Country'),
      '#options' => getCountryOptions(),
      '#required' => TRUE,
      '#attributes' => ['class' => ['aligned-country-list']],
    ];


    $form['serviceissues_phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Phone Number'),
      '#maxlength' => 20, // Limit input to 14 characters
      '#required' => TRUE,
      '#placeholder' => 'Phone Number (Required)',
    ];

    $form['serviceissues_PON'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Sales Order Number or Purchase Order Number'),
      '#pattern' => '^[a-zA-Z0-9]+$',
      '#maxlength' => 255,
      '#required' => TRUE,
      '#placeholder' => 'Eg. 12345 (Required)',
    ];

    $form['serviceissues_app'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Application'),
      '#required' => TRUE,
      '#placeholder' => 'Application (Required)',
    ];

    $form['serviceissues_message'] = [
      '#title' => $this->t('Describe Your Experience and Provide Feedback'),
      '#type' => 'textarea',
      '#description' => 'Please provide your feedback and attach any supporting files below to help us improve your experience.',
      '#required' => TRUE, // Optional feedback field
    ];

    $form['serviceissues_attachment'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Attach a File (pdf, png, jpg, jpeg)'),
      '#upload_validators' => [
        'file_validate_extensions' => [' png jpeg jpg pdf'],
      ],
      '#upload_location' => 'public://service_issues',
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

  public function validateForm(array &$form, FormStateInterface $form_state)
  {

    $first_name = $form_state->getValue('serviceissues_fname');
    $last_name = $form_state->getValue('serviceissues_lname');

    if (empty($first_name) || strlen($first_name) < 2) {
      $form_state->setErrorByName('serviceissues_fname', $this->t('Please enter your first name.'));
    }

    if (empty($last_name) || strlen($last_name) < 2) {
      $form_state->setErrorByName('serviceissues_lname', $this->t('Please enter your last name.'));
    }

    if ($first_name && $last_name && $first_name === $last_name) {
      $form_state->setErrorByName('serviceissues_lname', $this->t('Last name should not be the same as first name.'));
    }

    if ($first_name && $last_name && strlen($last_name) >= 6 && strpos($last_name, $first_name) !== false) {
        $form_state->setErrorByName('serviceissues_lname', $this->t('Last name should not contain first name for 6 or more characters.'));
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


    $Selector = '#serviceissuesform-container';
    $response = new AjaxResponse();

    if ($form_state->hasAnyErrors()) {
      $response = nor_forms_ajax_error($form, $form_state, $Selector);
      return $response;
    } else {

      $response = nor_forms_email_sent_ajax($form, $form_state, $Selector);
    }
    return $response;
  }


  public function submitForm(array &$form, FormStateInterface $form_state){

    $number = $form_state->getValue('serviceissues_PON');
    $application = $form_state->getValue('serviceissues_app');
    $first_name = $form_state->getValue('serviceissues_fname');
    $last_name = $form_state->getValue('serviceissues_lname');
    $email = $form_state->getValue('serviceissues_email');
    $country_code = $form_state->getValue('serviceissues_country');
    $phone = $form_state->getValue('serviceissues_phone');
    $company = $form_state->getValue('serviceissues_company');
    $message = $form_state->getValue('serviceissues_message');
    $date = date("Ymd");
    $form_name = $this->t('Service Issues');
    $output = "";
    $country_name = getCountryNames($country_code);

    if (!isset($first_name)) {
      $first_name = 'NULL';
    }
    if (!isset($email)) {
      $email = 'NULL';
    }

    $moved_file = null;
    $email_dir_name = nor_forms_email_to_directory_name($email);
    $fid = $form_state->getValue('serviceissues_attachment')[0] ?? NULL;
    if($fid){
      $file = File::load($fid);
      $permanent_uri = 'private://service_issues/' .$email_dir_name . '/' . date('Y-m-d'); // uses email and date to store the files. E.g. liam.howes@norgenbiotek.com submitting on May 23 2025 saves to: private://service_issues/liam_howes_norgenbiotek_com_4901bb87/2025-05-23
      $moved_file = nor_forms_file_upload_move_permanent($file, $permanent_uri, ['move_method' => FileSystemInterface::EXISTS_REPLACE]);
      $file_path = $moved_file->getFileUri();  
      $form_state->set('file_path', $file_path);
    }
    // NEED TO ref the moved file and not the initial fid. If the user has already uploaded a duplicate and the move_method is replace, the correct fid will match the old existing file, not the newly uploaded one!
    // inserting the new fid which won't exist will cause database insertion exception.
    $new_fid = null;
    if($moved_file) $new_fid = $moved_file->id() ?? null;

    $query = \Drupal::database()->insert('forms_to_zoho');
    $query->fields(['created_on', 'first_name', 'last_name', 'country', 'company', 'email', 'record', 'timestamp', 'form_name', 'phone', 'lead_application', 'notes', 'so_id', 'doc_fid']); //wrong syntax here breaks entire submit function 
    $query->values([$date, $first_name, $last_name, $country_name, $company, $email, '', time(), $form_name, $phone, $application, $message, $number, $new_fid]);
    $query->execute();

    try { //Zoho upsert

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
    } catch (Exception $e) {
    }


    $output = '<p>Hello,</p>
    <p>A customer submitted their request for Service Issues Form.</p>
    <p>Last name: ' . $last_name . '<br>First name: ' . $first_name . ' <br>Email: ' . $email . '<br>Company: ' . $company . '<br>Country: ' . $country_name . '<br>Primary Phone: ' . $phone . '<br> Sales Order Number: ' . $number . '<br> Application: ' . $application . '<br>Message: ' . $message;

    $time = time();

    if ($form_state->hasAnyErrors()) {
    } else {
      $subject = '[Contact form] - Service Issues ' . date("F j, Y, g:i a", $time);
      // $recipient_email = 'sowmya.movva@norgenbiotek.com';
      $recipient_email = 'services@norgenbiotek.com, alex.white@norgenbiotek.com';// real addresses
      //$recipient_email = 'liam.howes@norgenbiotek.com';
      if ($moved_file) {
        nor_forms_submit_attachment($output, $recipient_email, $moved_file, $subject);
      } else {
        nor_forms_email_redirect($output, $recipient_email, $subject);
      }
    }
  }
}
