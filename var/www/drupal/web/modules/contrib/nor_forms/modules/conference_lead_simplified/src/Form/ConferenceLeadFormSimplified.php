<?php

namespace Drupal\conference_lead_simplified\Form;

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
use Drupal\nor_forms\Libraries\Zoho\RecordWrapper;

class ConferenceLeadFormSimplified extends FormBase
{

  public function getFormId()
  {
    return 'conference_lead_form_simplified';
  }


  public function buildForm(array $form, FormStateInterface $form_state)
  {

    $form['#prefix'] = '<div id = "conferenceleadformsimplified-container" div class="conferenceleadformsimplified-container">';

    $form['personal_information'] = [
      '#type' => 'fieldset',
      '#title' => t('Personal Information'),
      '#attributes' => [
        'class' => ['js-form-item form-item', 'js-form-wrapper', 'form-wrapper', 'form-group'],
      ],
    ];

    $form['personal_information']['conference_fname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Name'),
      '#required' => TRUE,
      '#placeholder' => t('First Name (Required)'),
    ];

    $form['personal_information']['conference_lname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last Name'),
      '#required' => TRUE,
      '#placeholder' => t('Last Name (Required)'),
    ];

    $form['personal_information']['conference_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#required' => TRUE,
      '#placeholder' => t('email@example.com (Required)'),
    ];

    $form['personal_information']['conference_phone'] = [
      '#type' => 'tel',
      '#attributes' => array(
        'pattern' => '[0-9]+',
      ),
      '#title' => $this->t('Phone Number'),
      '#placeholder' => t('1 866-667-4362'),
    ];

    $form['personal_information']['conference_country'] = [
      '#type' => 'select',
      '#title' => $this->t('Country'),
      '#options' => getCountryOptions(),
      '#required' => TRUE,
    ];

    $form['professional_information'] = [
      '#type' => 'fieldset',
      '#title' => t('Professional Information'),
    ];

    $form['professional_information']['conference_company'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Company / Institution'),
      '#placeholder' => t('Company or Institution Name (Required)'),
      '#required' => TRUE,
    ];

    $form['professional_information']['conference_job'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Job Title'),
      '#placeholder' => t('Job Title'),
    ];

    $form['lead_profile'] = [
      '#type' => 'fieldset',
      '#title' => t('Lead Profile'),
      '#attributes' => array(
        'class' => array('lead-profile-fieldset'),
      ),
    ];

    $form['lead_profile']['event_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Event Name'),
      '#placeholder' => t('E.g. AACR2024'),
    ];

    $form['conference_message'] = [
      '#title' => $this->t('Additional Notes'),
      '#type' => 'textarea',
      '#required' => false, // Optional feedback field
      '#placeholder' => t('Please provide any adidtional notes or lead information here'),
      '#attributes' => array(
        'rows' => 10,
      )
    ];


    $form['newsletter_subscribe'] = [
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



  public function sampleTypeCallback(array &$form, FormStateInterface $form_state)
  {
    return $form['lead_profile']['sample_type_fieldset'];
  }
  public function analyteTypeCallback(array &$form, FormStateInterface $form_state)
  {
    return $form['lead_profile']['analyte_fieldset'];
  }
  public function applicationTypeCallback(array &$form, FormStateInterface $form_state)
  {
    return $form['lead_profile']['application_fieldset'];
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {

    $first_name = $form_state->getValue('conference_fname');
    $last_name = $form_state->getValue('conference_lname');
    $email = $form_state->getValue('conference_email');
    $phone = $form_state->getValue('conference_phone');
    $country = $form_state->getValue('conference_country');

    if (empty($first_name) || strlen($first_name) < 2) {
      $form_state->setErrorByName('conference_fname', $this->t('First Name must be 2 or more characters.'));
    }
    if (empty($last_name) || strlen($last_name) < 2) {
      $form_state->setErrorByName('conference_lname', $this->t('Last Name must be 2 or more characters.'));
    }
    if (empty($email) || strlen($email) < 2) {
      $form_state->setErrorByName('conference_email', $this->t('Email must be 2 or more characters.'));
    }
    if (!empty($phone) && preg_match('/^([0-9]|-|\s|\(|\))+$/', $phone)==false) {
      $form_state->setErrorByName('conference_phone', $this->t('Phone must contain only numbers, hyphens, round brackets, or spaces.'));
    }
    if ($first_name && $last_name && $first_name === $last_name) {
      $form_state->setErrorByName('conference_lname', $this->t('Last name should not be the same as first name.'));
    }
    if ($first_name && $last_name && strlen($last_name) >= 6 && strpos($last_name, $first_name) !== false) {
      $form_state->setErrorByName('conference_lname', $this->t('Last name should not contain first name for 6 or more characters.'));
    }
    if (empty($country)) {
      $form_state->setErrorByName('conference_country', $this->t('Please select your country'));
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

    $Selector = '#conferenceleadformsimplified-container';
    $response = new AjaxResponse();

    if ($form_state->hasAnyErrors()) {
      $response = nor_forms_ajax_error($form, $form_state, $Selector);
      return $response;
    } else {

      $response = nor_forms_email_sent_ajax($form, $form_state, $Selector);
      return $response;
    }
  }

  /**
   * Final submit handler.
   *
   * Reports what values were finally set.
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {

    $first_name = $form_state->getValue('conference_fname');
    $last_name = $form_state->getValue('conference_lname');
    $email = $form_state->getValue('conference_email');
    $phone = $form_state->getValue('conference_phone');
    $country = $form_state->getValue('conference_country');
    $country_name = getCountryNames($country);

    $company = $form_state->getValue('conference_company');
    $job_title = $form_state->getValue('conference_job');

    $event_name = $form_state->getValue('event_name');
    $message = $form_state->getValue('conference_message');
    // attach any "Other" values and stringify checkboxes field arrays
    

    $subscribe = $form_state->getValue('newsletter_subscribe');
   
    $date = date("Ymd");
    $form_name = $this->t('Conference Lead');

    //Get the countries with codes directly from the CountryRepository service
    $output = "";

    if (!isset($first_name)) {
      $first_name = 'NULL';
    }
    if (!isset($email)) {
      $email = 'NULL';
    }

    $subscribe_text = $subscribe == 1 ? 'Yes' : 'No';

    $query = \Drupal::database()->insert('forms_to_zoho');
    $query->fields(['created_on', 'first_name', 'last_name', 'email', 'country', 'phone', 'company', 'job_title', 'record', 'timestamp', 'form_name', 'event_name', 'notes','opt_in']); //wrong syntax here breaks entire submit function 
    $query->values([$date, $first_name, $last_name, $email, $country_name, $phone, $company, $job_title, '', time(), $form_name, $event_name, $message, $subscribe]);
    $query->execute();

    try {
      //Zoho upsert
      $zoho = new RecordWrapper('leads');
      $record = [
        'First_Name' => $first_name,
        'Last_Name' => $last_name,
        'Email' => $email,
        'Phone' => $phone,
        'Country' => $country_name,
        'Company' => $company,
        'Job_Position' => $job_title,
        'Internal_Note' => $message,
        'Lead_Source' => 'Website Form',
        'Web_Forms' => [$form_name],
      ];
      $upsert_result = $zoho->upsert($record);
    } catch (Exception $e) {
    }

    $output = '<p>Hello,</p>
    <p>A new lead has been submitted via the Conference Lead Form.</p>';
    $output .= '<table style="border-spacing:0px;border-bottom:1px solid grey"><tbody><thead><tr><th colspan="2" style="padding:6px;text-align: left;">Personal Information</th></tr></thead>';
    $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px">First Name:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px">'.$first_name.'</td></tr>';
    $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px">Last Name:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px">'.$last_name.'</td></tr>';
    $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px">Email:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px">'.$email.'</td></tr>';
    $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px">Phone:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px">'.$phone.'</td></tr>';
    $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px">Country:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px">'.$country_name.'</td></tr>';
    $output .= '</tbody></table>';

    $output .= '<table style="border-spacing:0px;border-bottom:1px solid grey"><tbody><thead><tr><th colspan="2" style="padding:6px;text-align: left;">Professional Information</th></tr></thead>';
    $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px">Company / Institution:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px">'.$company.'</td></tr>';
    $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px">Job Title:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px">'.$job_title.'</td></tr>';
    $output .= '</tbody></table>';

    $output .= '<table style="border-spacing:0px;border-bottom:1px solid grey"><tbody><thead><tr><th colspan="2" style="padding:6px;text-align: left;">Lead Information</th></tr></thead>';
    $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px">Event Name:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px">'.$event_name.'</td></tr>';
    $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px">Notes:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px">'.$message.'</td></tr>';
    $output .= '</tbody></table>';

    $output .= '<table style="border-spacing:0px;border-bottom:1px solid grey"><tbody><thead><tr><th colspan="2" style="padding:6px;text-align: left;">Additional Information</th></tr></thead>';
    $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px">Newsletter Subscribe:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px">'.$subscribe_text.'</td></tr>';
    
    $output .= '</tbody></table>';

    $time = time();

    if ($form_state->hasAnyErrors()) {
    } else {
      $subject = '[Contact form] - Conference Lead Simplified ' . date("F j, Y, g:i a", $time);
      $recipient_email = 'marketing@norgenbiotek.com,sabah.butt@norgenbiotek.com';// real addresses
      //$recipient_email = 'liam.howes@norgenbiotek.com';
      //$recipient_email = 'sowmya.movva@norgenbiotek.com';
      nor_forms_email_redirect($output, $recipient_email, $subject);
    }
  }
}
