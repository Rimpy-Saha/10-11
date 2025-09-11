<?php

namespace Drupal\account_issues\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\file\Entity\File;
use \Drupal\Core\File\FileSystemInterface;
use Drupal\nor_forms\Libraries\Zoho\RecordWrapper;

class AccountIssuesForm extends FormBase
{

  public function getFormId() {
    return 'account_issues_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['#prefix'] = '<div id="accountissuesform-container" class="accountissuesform-container">';

    $form['header'] = [
      '#type' => 'markup',
      '#markup' => '<h2>Account Issues</h2><p>Please submit the following form and a representative will follow up with you shortly.</p>',
      '#class' => 'work_pls',
    ];

    $form['message'] = [
      '#type' => 'markup',
      '#markup' => '<div id="result-message"></div>',
    ];

    $form['account_fname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Name'),
      '#default_value' => nor_forms_user_first_name(),
      '#required' => TRUE,
      '#placeholder' => 'First Name (Required)',
    ];

    $form['account_lname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last Name'),
      '#default_value' => nor_forms_user_last_name(),
      '#required' => TRUE,
      '#placeholder' => 'Last Name (Required)',
    ];

    $form['account_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#default_value' => nor_forms_user_email(),
      '#required' => TRUE,
      '#placeholder' => 'Email Address (Required)',
    ];

    $form['account_company'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Company/Institution'),
      '#required' => TRUE,
      '#placeholder' => 'Company/Institution (Required)',
    ];

    $form['account_country'] = [
      '#type' => 'select',
      '#title' => $this->t('Country'),
      '#options' => getCountryOptions(),
      '#required' => TRUE,
      '#attributes' => ['class' => ['aligned-country-list']],
    ];

    $form['account_phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Phone Number'),
      '#maxlength' => 20,
      '#required' => TRUE,
      '#placeholder' => 'Phone Number (Required)',
    ];

    $form['account_message'] = [
      '#title' => $this->t('Please Provide Inquiry Details'),
      '#type' => 'textarea',
      '#description' => 'Please provide details regarding your inquiry and attach any supporting files below.',
      '#required' => TRUE,
    ];

    $form['account_attachment'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Attach a File (pdf, png, jpg, jpeg)'),
      '#upload_validators' => [
        'file_validate_extensions' => [' png jpeg jpg pdf'],
      ],
      '#upload_location' => 'public://account_issues',
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

  public function validateForm(array &$form, FormStateInterface $form_state){

    $first_name = $form_state->getValue('account_fname');
    $last_name = $form_state->getValue('account_lname');

    if (empty($first_name) || strlen($first_name) < 2) {
      $form_state->setErrorByName('account_fname', $this->t('Please enter your first name.'));
    }

    if (empty($last_name) || strlen($last_name) < 2) {
      $form_state->setErrorByName('account_lname', $this->t('Please enter your last name.'));
    }
    if ($first_name && $last_name && $first_name === $last_name) {
      $form_state->setErrorByName('account_lname', $this->t('Last name should not be the same as first name.'));
    }
    if ($first_name && $last_name && strlen($last_name) >= 6 && strpos($last_name, $first_name) !== false) {
      $form_state->setErrorByName('account_lname', $this->t('Last name should not contain first name for 6 or more characters.'));
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

    $Selector = '#accountissuesform-container';
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

    $first_name = $form_state->getValue('account_fname');
    $last_name = $form_state->getValue('account_lname');
    $email = $form_state->getValue('account_email');
    $phone = $form_state->getValue('account_phone');
    $company = $form_state->getValue('account_company');
    $message = $form_state->getValue('account_message');
    $country_code = $form_state->getValue('account_country');
    $date = date("Ymd");
    $form_name = $this->t('Account Issues Form');
    $output = "";
    $country_name = getCountryNames($country_code);

    $moved_file = null;
    $email_dir_name = nor_forms_email_to_directory_name($email);
    $fid = $form_state->getValue('account_attachment')[0] ?? NULL;
    if($fid){
      $file = File::load($fid);
      $permanent_uri = 'private://account_issues/' .$email_dir_name . '/' . date('Y-m-d'); // uses email and date to store the files. E.g. liam.howes@norgenbiotek.com submitting on May 23 2025 saves to: private://account_issues/liam_howes_norgenbiotek_com_4901bb87/2025-05-23
      $moved_file = nor_forms_file_upload_move_permanent($file, $permanent_uri, ['move_method' => FileSystemInterface::EXISTS_REPLACE]);
      $file_path = $moved_file->getFileUri();  
      $form_state->set('file_path', $file_path);
    }
    // NEED TO ref the moved file and not the initial fid. If the user has already uploaded a duplicate and the move_method is replace, the correct fid will match the old existing file, not the newly uploaded one!
    // inserting the new fid which won't exist will cause database insertion exception.
    $new_fid = null;
    if($moved_file) $new_fid = $moved_file->id() ?? null;

    $query = \Drupal::database()->insert('forms_to_zoho');
    $query->fields(['created_on', 'first_name', 'last_name', 'company', 'country', 'email', 'record', 'timestamp', 'form_name', 'phone', 'notes', 'doc_fid']); //wrong syntax here breaks entire submit function 
    $query->values([$date, $first_name, $last_name, $company, $country_name, $email, '', time(), $form_name, $phone, $message, $new_fid]);
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
        'Phone' => $phone,
        'Company' => $company,
        'Country' => $country_name,
        'Lead_Source' => 'Website Form',
        'Web_Forms' => ['Account Issues'],
      ];
      $upsert_result = $zoho->upsert($record);
    } catch (Exception $e) {
    }


    $output = '<p>Hello,</p>
    <p>A customer submitted their request for Account Issues.</p>
    <p>Last name: ' . $last_name . '<br>First name: ' . $first_name . '<br>Email: ' . $email . '<br>Primary Phone: ' . $phone . '<br>Company: ' . $company . '<br>Country: ' . $country_name . '<br>Message: ' . $message;

    $time = time();

    if ($form_state->hasAnyErrors()) {
    } else {
      $subject = '[Contact form] - Account Issues ' . date("F j, Y, g:i a", $time);
      $recipient_email = 'info@norgenbiotek.com,it@norgenbiotek.com,sabah.butt@norgenbiotek.com,liam.howes@norgenbiotek.com,andrii.omelchuk@norgenbiotek.com';// real addresses
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
