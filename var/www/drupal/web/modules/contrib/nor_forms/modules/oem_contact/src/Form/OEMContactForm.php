<?php

namespace Drupal\oem_contact\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\file\Entity\File;
use \Drupal\Core\File\FileSystemInterface;
use Drupal\nor_forms\Libraries\Zoho\RecordWrapper;

class OEMContactForm extends FormBase
{

  public function getFormId() {
    return 'oem_contact_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attached']['library'][] = 'oem_contact/oem_contact';

    $form['#prefix'] = '<div id="oemcontactform-container" class="oemcontactform-container">';

    /* $form['header'] = [
      '#type' => 'markup',
      '#markup' => 'Let us know what you are interested in and our team will reach out to assist you.</p>',
    ]; */

    $form['message'] = [
      '#type' => 'markup',
      '#markup' => '<div id="result-message"></div>',
    ];

    $form['grid_fieldset'] = [
      '#type' => 'fieldset',
      '#attributes' => array(
        'class' => array('grid-fieldset'),
      ),
    ];
    $form['grid_fieldset']['account_fname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Name'),
      '#default_value' => nor_forms_user_first_name(),
      '#required' => TRUE,
      '#placeholder' => 'First Name (Required)',
    ];

    $form['grid_fieldset']['account_lname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last Name'),
      '#default_value' => nor_forms_user_last_name(),
      '#required' => TRUE,
      '#placeholder' => 'Last Name (Required)',
    ];

    $form['grid_fieldset']['account_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#default_value' => nor_forms_user_email(),
      '#required' => TRUE,
      '#placeholder' => 'Email Address (Required)',
    ];

    $form['grid_fieldset']['account_company'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Company/Institution'),
      '#required' => TRUE,
      '#placeholder' => 'Company/Institution (Required)',
    ];

    $form['grid_fieldset']['account_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Job Title'),
      '#required' => TRUE,
      '#placeholder' => 'Job Title (Required)',
    ];

    $form['grid_fieldset']['account_country'] = [
      '#type' => 'select',
      '#title' => $this->t('Country'),
      '#options' => getCountryOptions(),
      '#required' => TRUE,
      '#attributes' => ['class' => ['aligned-country-list'], 'autocomplete' => ['country']],
    ];

    $form['grid_fieldset']['account_phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Phone Number'),
      '#maxlength' => 20,
      '#required' => TRUE,
      '#placeholder' => 'Phone Number (Required)',
    ];


    $form['inquiry_type_fieldset'] = [
      '#type' => 'fieldset',
      '#attributes' => array(
        'class' => array('inquiry-type-fieldset'),
      ),
      '#prefix' => '<div id="inquiry-type-wrapper">',
      '#suffix' => '</div>',
    ];
    $inquiry_type_options = array(
      t('Customized bulk quantities of an existing Norgen product'),
      t('Ordering individual product components of a Norgen kit'),
      t('Product customization to fit my workflow'),
      t('OEM or white-label products'),
      t('Other'),
    );

    $form['inquiry_type_fieldset']['inquiry_type'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('What Solutions Are You Looking For? (Select All That Apply)'),
      '#options' => array_combine($inquiry_type_options, $inquiry_type_options),
      '#ajax' => [
        'callback' => '::inquiryTypeCallback',
        'disable-refocus' => FALSE, // Or TRUE to prevent re-focusing on the triggering element.
        'event' => 'change',
        'wrapper' => 'inquiry-type-wrapper', // This element is updated with this AJAX callback.
      ]
    ];

    if ($form_state->getValue('inquiry_type') !== null && in_array('Other', $form_state->getValue('inquiry_type'))) {
      // Get the text of the selected option.
      $form['inquiry_type_fieldset']['inquiry_type_other'] = [
        '#type' => 'textfield',
        '#placeholder' => t('Please specify other custom solution(s)'),
        '#maxlength' => 90,
      ];
    }

    $form['account_message'] = [
      '#title' => $this->t('Additional Details'),
      '#type' => 'textarea',
      '#description' => 'Please provide any additional details regarding your inquiry and attach any supporting files below.',
    ];

    $form['account_attachment'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Attach a File (pdf, png, jpg, jpeg)'),
      '#upload_validators' => [
        'file_validate_extensions' => [' png jpeg jpg pdf'],
      ],
      '#upload_location' => 'temporary://oem_contact',
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

  public function inquiryTypeCallback(array &$form, FormStateInterface $form_state){
    return $form['inquiry_type_fieldset'];
  }

  public function submitCallback(array &$form, FormStateInterface $form_state) {
    $Selector = '#oemcontactform-container';
    $response = new AjaxResponse();
    
    if ($form_state->hasAnyErrors()) {
      $response = nor_forms_ajax_error($form, $form_state, $Selector);
      return $response;
    } 
    else {
      // Check the path correctly (match only the relative path)
      $response = nor_forms_email_sent_ajax($form, $form_state, $Selector);
    }
    return $response;
  }


  public function submitForm(array &$form, FormStateInterface $form_state){

    $first_name = $form_state->getValue('account_fname');
    $last_name = $form_state->getValue('account_lname');
    $email = $form_state->getValue('account_email');
    $company = $form_state->getValue('account_company');
    $job_title = $form_state->getValue('account_title');
    $country_code = $form_state->getValue('account_country');
    $phone = $form_state->getValue('account_phone');
    // attach any "Other" values and stringify checkboxes field arrays
    $inquiry_type_other = $form_state->getValue('inquiry_type_other');
    if($form_state->getValue('inquiry_type_other') !== null){
      $form_state->getValue('inquiry_type')['Other'] = $inquiry_type_other; // replace "Other" value with the specified text value from the textfield
    }
    $inquiry_type_list = '';
    $inquiry_type = implode(",", array_filter($form_state->getValue('inquiry_type')));
    if(!$inquiry_type) $inquiry_type_list = 'Did not specify';
    else{
      $inquiry_type_list = '<ul>';
      foreach(array_filter($form_state->getValue('inquiry_type')) as $inquiry_type_value){
        $inquiry_type_list .= '<li>'.$inquiry_type_value.'</li>';
      }
      $inquiry_type_list .= '</ul>';
    }

    $message = $form_state->getValue('account_message');

    $date = date("Ymd");
    $form_name = $this->t('OEM Contact Form');

    $output = "";
    $country_name = getCountryNames($country_code);

    $output .= $message;

    if (!isset($first_name)) {
      $first_name = 'NULL';
    }
    if (!isset($email)) {
      $email = 'NULL';
    }

    $moved_file = null;
    $email_dir_name = nor_forms_email_to_directory_name($email);
    $fid = $form_state->getValue('account_attachment')[0] ?? NULL;
    if($fid){
      $file = File::load($fid);
      $permanent_uri = 'private://oem_contact/' .$email_dir_name . '/' . date('Y-m-d'); // uses email and date to store the files. E.g. liam.howes@norgenbiotek.com submitting on May 23 2025 saves to: private://oem_contact/liam_howes_norgenbiotek_com_4901bb87/2025-05-23
      $moved_file = nor_forms_file_upload_move_permanent($file, $permanent_uri, ['move_method' => FileSystemInterface::EXISTS_REPLACE]);
      $file_path = $moved_file->getFileUri();  
      $form_state->set('file_path', $file_path);
    }
    // NEED TO ref the moved file and not the initial fid. If the user has already uploaded a duplicate and the move_method is replace, the correct fid will match the old existing file, not the newly uploaded one!
    // inserting the new fid which won't exist will cause database insertion exception.
    $new_fid = null;
    if($moved_file) $new_fid = $moved_file->id() ?? null;

    $query = \Drupal::database()->insert('forms_to_zoho');
    $query->fields(['created_on', 'first_name', 'last_name', 'country', 'company', 'email', 'record', 'timestamp', 'form_name', 'phone','job_title', 'notes', 'doc_fid']); //wrong syntax here breaks entire submit function 
    $query->values([$date, $first_name, $last_name, $country_name, $company, $email, '', time(), $form_name, $phone, $job_title, $message, $new_fid]);
    $query->execute();

    try {
      $zoho = new RecordWrapper('leads');
      $record = [
        'First_Name' => $first_name,
        'Last_Name' => $last_name,
        'Email' => $email,
        'Phone' => $phone,
        'Company' => $company,
        'Job_title' => $job_title,
        'Country' => $country_name,
        'Lead_Source' => 'Website Form',
        'Web_Forms' => [$form_name],
      ];
      $upsert_result = $zoho->upsert($record);
    } catch (Exception $e) {
    }


    $output = '<p>Hello,</p>
    <p>A customer submitted their request for OEM Contact Form. Please find their submission information below. Any files uploaded by the customer will be attached to this email.</p>
    <p><table style="border-spacing:0px;border-bottom:1px solid grey;">
      <tbody>
        <tr>
          <td style="border:1px solid grey;border-bottom:0px;padding:6px;">First Name</td>
          <td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$first_name.'</td>
        </tr>
        <tr>
          <td style="border:1px solid grey;border-bottom:0px;padding:6px;">Last Name</td>
          <td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$last_name.'</td>
        </tr>
        <tr>
          <td style="border:1px solid grey;border-bottom:0px;padding:6px;">Email</td>
          <td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$email.'</td>
        </tr>
        <tr>
          <td style="border:1px solid grey;border-bottom:0px;padding:6px;">Phone</td>
          <td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$phone.'</td>
        </tr>
        <tr>
          <td style="border:1px solid grey;border-bottom:0px;padding:6px;">Job Title</td>
          <td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$job_title.'</td>
        </tr>
        <tr>
          <td style="border:1px solid grey;border-bottom:0px;padding:6px;">Company/Institution</td>
          <td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$company.'</td>
        </tr>
        <tr>
          <td style="border:1px solid grey;border-bottom:0px;padding:6px;">Country</td>
          <td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$country_name.'</td>
        </tr>
        <tr>
          <td style="border:1px solid grey;border-bottom:0px;padding:6px;">Interest(s)</td>
          <td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$inquiry_type_list.'</td>
        </tr>';
        if($message != null){
          $output .= 
            '<tr>
              <td style="border:1px solid grey;border-bottom:0px;padding:6px;">Additional Info</td>
              <td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$message.'</td>
            </tr>';
        }
      $output .= '</tbody>
    </table></p>';

    $time = time();

    if ($form_state->hasAnyErrors()) {
    } else {
      $subject = '[Contact form] - OEM Contact Form ' . date("F j, Y, g:i a", $time);
      $recipient_email = 'services@norgenbiotek.com, sabah.butt@norgenbiotek.com';// real addresses
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
