<?php

namespace Drupal\job_application\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\file\Entity\File;
use Drupal\nor_forms\Libraries\Zoho\RecordWrapper;
use \Drupal\Core\File\FileSystemInterface;


class JobApplicationForm extends FormBase
{

  public function getFormId()
  {
    return 'job_application_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {

    $form['#prefix'] = '<div id ="job-container" class="job-container">';

    $form['header'] = [
      '#type' => 'markup',
    ];

    $form['job_fname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Name'),
      '#required' => TRUE,
      '#placeholder' => $this->t('First Name (Required)'),
    ];

    $form['job_lname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last Name'),
      '#required' => TRUE,
      '#placeholder' => $this->t('Last Name (Required)'),
    ];

    $form['job_phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Phone Number'),
      '#maxlength' => 20, // Limit input to 14 characters
      '#required' => TRUE,
      '#placeholder' => $this->t('Phone Number (Required)'),
    ];

    $form['job_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#required' => TRUE,
      '#placeholder' => $this->t('Email Address (Required)'),
    ];

    $form['account_attachment'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Upload Resume'),
      '#required' => TRUE, // Make field required
      '#upload_validators' => [
        'file_validate_extensions' => ['pdf doc docx'], //remove brackets
      ],
      '#description' => $this->t('Allowed extensions: pdf, doc, docx'),
      '#upload_location' => 'public://job_applications',
    ];

    $form['account_cv'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Upload Cover Letter'),
      '#required' => FALSE, // Make field required
      '#upload_validators' => [
        'file_validate_extensions' => ['pdf doc docx'],
      ],
      '#description' => $this->t('Allowed file extensions: pdf, doc, docx'),
      '#upload_location' => 'public://job_applications',
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

    // private file uploads require the upload folder to have rwxrwx-w- (0772) directory permissions.
   /*  $private_dir = 'temporary://job_applications';
    $real_path = \Drupal::service('file_system')->realpath($private_dir);

    // Check if directory exists
    $exists = is_dir($real_path);
    \Drupal::messenger()->addMessage("Directory exists: " . ($exists ? 'Yes' : 'No'));

    // Check if writable
    $writable = is_writable($real_path);
    \Drupal::messenger()->addMessage("Directory writable: " . ($writable ? 'Yes' : 'No'));

    // Get current permissions
    $perms = substr(sprintf('%o', fileperms($real_path)), -4);
    \Drupal::messenger()->addMessage("Directory permissions: " . $perms); */


    $first_name = $form_state->getValue('job_fname');
    $last_name = $form_state->getValue('job_lname');

    if (empty($first_name) || strlen($first_name) < 2) {
      $form_state->setErrorByName('job_fname', $this->t('Please enter your first name.'));
    }

    if (empty($last_name) || strlen($last_name) < 2) {
      $form_state->setErrorByName('job_lname', $this->t('Please enter your last name.'));
    }

    if ($first_name && $last_name && $first_name === $last_name) {
      // Add an error to the last name field
      $form_state->setErrorByName('job_lname', $this->t('Last name should not be the same as first name.'));
    }
    if ($first_name && $last_name && strlen($last_name) >= 6 && strpos($last_name, $first_name) !== false) {
      // Add an error if last name contains first name for 6 or more characters
      $form_state->setErrorByName('job_lname', $this->t('Last name should not contain first name for 6 or more characters.'));
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

    $Selector = '#job-container';
    $response = new AjaxResponse();

    if ($form_state->hasAnyErrors()) {
      $response = nor_forms_ajax_error($form, $form_state, $Selector);
    } else {

      $response = nor_forms_email_sent_job_application($form, $form_state, $Selector);
    }
    return $response;
  }


  public function submitForm(array &$form, FormStateInterface $form_state)
  {

    $first_name = $form_state->getValue('job_fname');
    $last_name = $form_state->getValue('job_lname');
    $email = $form_state->getValue('job_email');
    $phone = $form_state->getValue('job_phone');
    $date = date("Ymd");
    $form_name = $this->t('Job Application');

    // ---- Move files from temporary directory to permanent, private directory
    $resume_file = $cv_file = null;

    $email_dir_name = nor_forms_email_to_directory_name($email); // removes special characters, appends first 8 characters hash for uniqueness

    // Resume
    $fid = $form_state->getValue('account_attachment')[0] ?? NULL;
    if ($fid) {
      $file = File::load($fid);
      $permanent_uri = 'private://job_applications/general/' .$email_dir_name . '/' . date('Y-m-d'); // uses email and application date to store the files. E.g. liam.howes@norgenbiotek.com submitting on May 23 2025 saves to: private://job_applications/general/liam_howes_norgenbiotek_com_4901bb87/2025-05-23
      $resume_file = nor_forms_file_upload_move_permanent($file, $permanent_uri, ['move_method' => FileSystemInterface::EXISTS_REPLACE]);
      $file_path = $resume_file->getFileUri(); 
      $form_state->set('res_file_path', $file_path);
    }
    // Cover Letter
    $fid_cv = $form_state->getValue('account_cv')[0] ?? NULL;
    if ($fid_cv) {
      $file = File::load($fid_cv);
      $permanent_uri = 'private://job_applications/general/' .$email_dir_name . '/' . date('Y-m-d');
      $cv_file = nor_forms_file_upload_move_permanent($file, $permanent_uri, ['move_method' => FileSystemInterface::EXISTS_REPLACE]);
      $file_path = $cv_file->getFileUri(); 
      $form_state->set('cv_file_path', $file_path);
    }
    $resume_fid = $cv_fid = null;
    if($resume_file) $resume_fid = $resume_file->id() ?? null;
    if($cv_file) $cv_fid = $cv_file->id() ?? null;

    // ---- End of moving files code

    $query = \Drupal::database()->insert('forms_to_zoho');
    $query->fields(['created_on', 'first_name', 'last_name', 'email', 'record', 'timestamp', 'form_name', 'phone', 'doc_fid', 'doc2_fid']); //wrong syntax here breaks entire submit function 
    $query->values([$date, $first_name, $last_name, $email, '', time(), $form_name, $phone, $resume_fid, $cv_fid]);
    $query->execute();

    
    if (!isset($first_name)) {
      $first_name = 'NULL';
    }
    if (!isset($email)) {
      $email = 'NULL';
    }

    /* Don't need Zoho because these people aren't leads */
    /* try { 
      if (1) {
        $zoho = new RecordWrapper('leads');
        $record = [
          'First_Name' => $first_name,
          'Last_Name' => $last_name,
          'Email' => $email,
          'Phone' => $phone,
        ];
        $upsert_result = $zoho->upsert($record);
      }
    } catch (Exception $e) {
    }
 */

    $output = '<p>Hello,</p>
    <p>A new user submitted the Job Application Form.</p>
    <p>First name: ' . $first_name . '<br>Last name: ' . $last_name . '<br>Email: ' . $email . '<br>Phone: ' . $phone;

    $time = time();

    if ($form_state->hasAnyErrors()) {
    } else {
      $subject = '[Contact form] - Job Application ' . date("F j, Y, g:i a", $time);
      // $recipient_email = 'sowmya.movva@norgenbiotek.com';
      $recipient_email = 'hr@norgenbiotek.com,mha@norgenbiotek.com,nicholas.wall@norgenbiotek.com';// real addresses
      //$recipient_email = 'liam.howes@norgenbiotek.com';

      $files = [$resume_file, $cv_file];
      nor_forms_job_application($output, $recipient_email, $files, $subject);
    }
  }
}
