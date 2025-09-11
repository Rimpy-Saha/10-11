<?php

namespace Drupal\product_issues\Form;

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


class ProductIssuesForm extends FormBase
{

  public function getFormId()
  {
    return 'product_issues_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {

    $form['#prefix'] = '<div id="productissuesform-container" div class="productissuesform-container">';

    $form['header'] = [
      '#type' => 'markup',
      '#markup' => '<h2>Product Issues</h2><p>Any issues with our products? Please submit the following form and a representative will follow up with you shortly.</p>',
    ];

    $form['productissues_fname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Name'),
      '#default_value' => nor_forms_user_first_name(),
      '#required' => TRUE,
      '#placeholder' => 'First Name (Required)',
    ];

    $form['productissues_lname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last Name'),
      '#default_value' => nor_forms_user_last_name(),
      '#required' => TRUE,
      '#placeholder' => 'Last Name (Required)',
    ];


    $form['productissues_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#default_value' => nor_forms_user_email(),
      '#required' => TRUE,
      '#placeholder' => 'Email Address (Required)',
    ];

    $form['productissues_company'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Company Name'),
      '#required' => TRUE,
      '#placeholder' => 'Company Name (Required)',
    ];

    $form['productissues_country'] = [
      '#type' => 'select',
      '#title' => $this->t('Country'),
      '#options' => getCountryOptions(),
      '#required' => TRUE,
      '#attributes' => ['class' => ['aligned-country-list']],
    ];

    //add area code options
    $form['productissues_phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Phone Number'),
      '#maxlength' => 20, // Limit input to 14 characters
      '#required' => TRUE,
      '#placeholder' => 'Phone Number (Required)',
    ];


    $form['productissues_PON'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Sales Order Number or Purchase Order Number'),
      '#pattern' => '^[a-zA-Z0-9]+$', // Regular expression for alphanumeric strings
      '#maxlength' => 255, // Limit input to 255 characters
      '#required' => TRUE,
      '#placeholder' => 'Eg. 12345 (Required)',
    ];

    $form['productissues_cat'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Product SKU (Cat.)'),
      '#required' => TRUE,
      '#placeholder' => 'Eg. 17200 (Required)',
    ];

    $form['productissues_app'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Application'),
      '#required' => TRUE,
      '#placeholder' => 'Application (Required)',
    ];


    $form['productissues_message'] = [
      '#title' => $this->t('Please Describe Your Concern and Attach any Relevant Files Below'),
      '#type' => 'textarea',
      '#required' => TRUE,
    ];


    $form['productissues_attachment'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Attach a File (pdf, png, jpg, jpeg)'),
      '#upload_validators' => [
        'file_validate_extensions' => [' png jpeg jpg pdf'],
      ],
      '#upload_location' => 'public://product_issues',
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

    $first_name = $form_state->getValue('productissues_fname');
    $last_name = $form_state->getValue('productissues_lname');

    if (empty($first_name) || strlen($first_name) < 2) {
      $form_state->setErrorByName('productissues_fname', $this->t('Please enter your first name.'));
    }

    if (empty($last_name) || strlen($last_name) < 2) {
      $form_state->setErrorByName('productissues_lname', $this->t('Please enter your last name.'));
    }

    if ($first_name && $last_name && $first_name === $last_name) {
      $form_state->setErrorByName('productissues_lname', $this->t('Last name should not be the same as first name.'));
    }

    if ($first_name && $last_name && strlen($last_name) >= 6 && strpos($last_name, $first_name) !== false) {
        $form_state->setErrorByName('productissues_lname', $this->t('Last name should not contain first name for 6 or more characters.'));
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


    $Selector = '#productissuesform-container';
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

    $number = $form_state->getValue('productissues_PON');
    $sku = $form_state->getValue('productissues_cat');
    $application = $form_state->getValue('productissues_app');
    $first_name = $form_state->getValue('productissues_fname');
    $last_name = $form_state->getValue('productissues_lname');
    $email = $form_state->getValue('productissues_email');
    $country_code = $form_state->getValue('productissues_country');
    $phone = $form_state->getValue('productissues_phone');
    $company = $form_state->getValue('productissues_company');
    $message = $form_state->getValue('productissues_message');
    $date = date("Ymd");
    $form_name = $this->t('Product Issues');

    //Get the countries with codes directly from the CountryRepository service
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
    $fid = $form_state->getValue('productissues_attachment')[0] ?? NULL;
    if($fid){
      $file = File::load($fid);
      $permanent_uri = 'private://product_issues/' .$email_dir_name . '/' . date('Y-m-d'); // uses email and date to store the files. E.g. liam.howes@norgenbiotek.com submitting on May 23 2025 saves to: private://account_issues/liam_howes_norgenbiotek_com_4901bb87/2025-05-23
      $moved_file = nor_forms_file_upload_move_permanent($file, $permanent_uri, ['move_method' => FileSystemInterface::EXISTS_REPLACE]);
      $file_path = $moved_file->getFileUri();  
      $form_state->set('file_path', $file_path);
    }
    // NEED TO ref the moved file and not the initial fid. If the user has already uploaded a duplicate and the move_method is replace, the correct fid will match the old existing file, not the newly uploaded one!
    // inserting the new fid which won't exist will cause database insertion exception.
    $new_fid = null;
    if($moved_file) $new_fid = $moved_file->id() ?? null;

    $query = \Drupal::database()->insert('forms_to_zoho');
    $query->fields(['created_on', 'first_name', 'last_name', 'country', 'company', 'email', 'record', 'timestamp', 'form_name', 'phone', 'notes', 'products', 'lead_application', 'so_id', 'doc_fid']); //wrong syntax here breaks entire submit function 
    $query->values([$date, $first_name, $last_name, $country_name, $company, $email, '', time(), $form_name, $phone, $message, $sku, $application, $number, $new_fid]);
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
    <p>A customer submitted their request for Product Issues Form.</p>
    <p>Last name: ' . $last_name . '<br>First name: ' . $first_name . '<br> Product Issues Number: ' . $number . '<br> Product SKU: ' . $sku . '<br> Application: ' . $application . '<br> Order Number: ' . $number . '<br>Email: ' . $email . '<br>Primary Phone: ' . $phone . '<br>Company: ' . $company . '<br>Country: ' . $country_name . '<br>Message: ' . $message;

    $time = time();

    if ($form_state->hasAnyErrors()) {
    } else {
      $subject = '[Contact form] - Product Issues ' . date("F j, Y, g:i a", $time);
      $recipient_email = 'support@norgenbiotek.com,lohit.khera@norgenbiotek.com';// real addresses
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
