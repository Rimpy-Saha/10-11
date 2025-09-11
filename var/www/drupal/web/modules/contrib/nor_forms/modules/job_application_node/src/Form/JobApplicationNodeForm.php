<?php

namespace Drupal\job_application_node\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\file\Entity\File;
use \Drupal\Core\File\FileSystemInterface;
use Drupal\nor_forms\Libraries\Zoho\RecordWrapper;


class JobApplicationNodeForm extends FormBase
{

  public function getFormId()
  {
    return 'job_application__node_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $form_title_value = NULL, $job_posting_nid = NULL)
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
        'file_validate_extensions' => ['pdf docx'], //remove brackets
      ],
      '#description' => $this->t('Allowed file extensions: pdf, doc'),
      '#upload_location' => 'public://job_applications',
    ];

    $form['account_cv'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Upload Cover Letter'),
      '#upload_validators' => [
        'file_validate_extensions' => ['pdf docx'],
      ],
      '#description' => $this->t('Allowed extensions: pdf, doc'),
      '#upload_location' => 'public://job_applications',
    ];

    $form['job_title'] = [
      '#type' => 'hidden',
      '#title' => $this->t('Job Title'),
      '#value' => $form_title_value,
    ];

    $form['job_nid'] = [
      '#type' => 'hidden',
      '#title' => $this->t('Job Posting Node ID'),
      '#value' => $job_posting_nid,
    ];
    $form['google_recaptcha'] = [
      //'#suffix' => '</div>',
      '#type'=> 'fieldset',
      '#description' => '<div class="g-recaptcha" data-sitekey="6Lcr4u0pAAAAAGj32knXkUzuHAXzj3CoAhtbJ1t5"></div>',
      //'#required' => TRUE,
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

    $first_name = $form_state->getValue('job_fname');
    $last_name = $form_state->getValue('job_lname');

    if (empty($first_name) || strlen($first_name) < 2) {
      $form_state->setErrorByName('job_fname', $this->t('Please enter your first name.'));
    }

    if (empty($last_name) || strlen($last_name) < 2) {
      $form_state->setErrorByName('job_lname', $this->t('Please enter your last name.'));
    }

    if ($first_name && $last_name && $first_name === $last_name) {
      $form_state->setErrorByName('job_lname', $this->t('Last name should not be the same as first name.'));
    }
    if ($first_name && $last_name && strlen($last_name) >= 6 && strpos($last_name, $first_name) !== false) {
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

      // $form_file = $form_state->getValue('account_attachment');
      // if (isset($form_file[0]) && !empty($form_file[0])) {
      //   $file = File::load($form_file[0]);
      //   $file->setPermanent();
      //   $file->save();

      //   $file_path = $file->getFileUri(); // Get the file path

      //   // Store the file path in the form state for later use
      //   $form_state->set('file_path', $file_path);
      // }
      // $form_cv = $form_state->getValue('account_cv');
      // // Process and save the CV file
      // if (isset($form_cv[0]) && !empty($form_cv[0])) {
      //   $cv_file = File::load($form_cv[0]);
      //   $cv_file->setPermanent();
      //   $cv_file->save();

      //   $cv_file_path = $cv_file->getFileUri(); // Get the file path
      //   $form_state->set('cv_file_path', $cv_file_path);
      // }
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

    $first_name = $form_state->getValue('job_fname');
    $last_name = $form_state->getValue('job_lname');
    $email = $form_state->getValue('job_email');
    $phone = $form_state->getValue('job_phone');
    $job_title = $form_state->getValue('job_title');
    $job_nid = $form_state->getValue('job_nid');
    $date = date("Ymd");
    $form_name = $this->t('Job Application');


    // ---- Move files from temporary directory to permanent, private directory
    $resume_file = $cv_file = null;

    $email_dir_name = nor_forms_email_to_directory_name($email);

    // Resume
    $fid = $form_state->getValue('account_attachment')[0] ?? NULL;
    if ($fid) {
      $file = File::load($fid);
      $permanent_uri = 'private://job_applications/' .$job_title. '/' .$email_dir_name . '/' . date('Y-m-d'); // uses email and application date to store the files. E.g. liam.howes@norgenbiotek.com submitting on May 23 2025 saves to: private://job_applications/general/liam_howes_norgenbiotek_com_4901bb87/2025-05-23
      $resume_file = nor_forms_file_upload_move_permanent($file, $permanent_uri, ['move_method' => FileSystemInterface::EXISTS_REPLACE]);
      $file_path = $resume_file->getFileUri(); 
      $form_state->set('res_file_path', $file_path);
    }
    // Cover Letter
    $fid_cv = $form_state->getValue('account_cv')[0] ?? NULL;
    if ($fid_cv) {
      $file = File::load($fid_cv);
      $permanent_uri = 'private://job_applications/' .$job_title. '/' .$email_dir_name . '/' . date('Y-m-d');
      $cv_file = nor_forms_file_upload_move_permanent($file, $permanent_uri, ['move_method' => FileSystemInterface::EXISTS_REPLACE]);
      $file_path = $cv_file->getFileUri(); 
      $form_state->set('cv_file_path', $file_path);
    }
    $resume_fid = $cv_fid = null;
    if($resume_file) $resume_fid = $resume_file->id() ?? null;
    if($cv_file) $cv_fid = $cv_file->id() ?? null;
    // ---- End of moving files code



    if (!isset($first_name)) {
      $first_name = 'NULL';
    }
    if (!isset($email)) {
      $email = 'NULL';
    }

    $query = \Drupal::database()->insert('forms_to_zoho');
    $query->fields(['created_on', 'first_name', 'last_name', 'email', 'record', 'timestamp', 'form_name', 'phone','job_title','job_nid','doc_fid','doc2_fid']); //wrong syntax here breaks entire submit function 
    $query->values([$date, $first_name, $last_name, $email, '', time(), $form_name, $phone, $job_title, $job_nid, $resume_fid, $cv_fid]);
    $query->execute();

    /* Don't need Zoho because these people aren't leads */
    /* try {  //Zoho upsert
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
    } */


    $output = '<p>Hello,</p>
    <p>An applicant submitted their job application for the position of '.$job_title.'.</p>
    <p>First name: ' . $first_name . '<br>Last name: ' . $last_name . '<br>Email: ' . $email . '<br>Phone: ' . $phone . '<br>Job Node ID: ' . $job_nid;

    $time = time();

    if ($form_state->hasAnyErrors()) {
    } else {
      $subject = '[Contact form] - '.$job_title.' Application ' . date("F j, Y, g:i a", $time);
      $recipient_email = 'hr@norgenbiotek.com,mha@norgenbiotek.com,nicholas.wall@norgenbiotek.com';// real addresses
      //$recipient_email = 'liam.howes@norgenbiotek.com';
      // $recipient_email = 'IT@norgenbiotek.com';

      $files = [$resume_file, $cv_file];
      nor_forms_job_application($output, $recipient_email, $files, $subject);
    }
  }
}
