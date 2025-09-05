<?php

namespace Drupal\covid_workflow\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\file\Entity\File;
use Drupal\nor_forms\Libraries\Zoho\RecordWrapper;

class CovidWorkflowForm extends FormBase
{

  public function getFormId()
  {
    return 'covid_workflow_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {

    $form['#prefix'] = '<div id="covidworkflowform-container" class="covidworkflowform-container">';

    $form['covid_fname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Name'),
      '#required' => TRUE,
    ];

    $form['covid_lname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last Name'),
      '#required' => TRUE,
    ];

    $form['covid_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#required' => TRUE,
    ];

    //add area code options
    $form['covid_phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Phone Number'),
      '#maxlength' => 20, // Limit input to 14 characters
      '#required' => TRUE,
    ];

    $form['covid_billing'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Billing Address'),
      '#required' => TRUE,
    ];

    $form['covid_shipping'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Shipping Address'),
      '#required' => TRUE,
    ];

    $form['covid_company'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Company/Institution'),
      '#required' => TRUE,
    ];

    $form['book_job'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Job Title'),
      '#required' => TRUE,
    ];

    $options_covid_collection = [
      '' => '- Please Select (required) -',
      'Saliva RNA Collection and Preservation Device New Price' => [
        'Saliva RNA Collection and Preservation Devices Dx (53800)' => 'Saliva RNA Collection and Preservation Devices Dx (53800)',
        'Saliva RNA Collection and Preservation Devices (RU53800)' => 'Saliva RNA Collection and Preservation Devices (RU53800)',
      ],
      'Total Nucleic Acid Preservation Tubes' => [
        'Total Nucleic Acid Preservation Tubes Dx (Dx69200)' => 'Total Nucleic Acid Preservation Tubes Dx (Dx69200)',
        'Total Nucleic Acid Preservation Tubes (69200)' => 'Total Nucleic Acid Preservation Tubes (69200)',
      ],
    ];

    $form['covid_collection'] = [
      '#type' => 'select',
      '#title' => $this->t('Collection'),
      '#options' => $options_covid_collection,
    ];


    $options_covid_isolation = [
      '' => '- Please Select (required) -',
      'Total RNA Purification Kits' => [
        'Total RNA Purification Kits Dx (Dx17200)' => 'Total RNA Purification Kits Dx (Dx17200)',
        'Total RNA Purification Kits (17200)' => 'Total RNA Purification Kits (17200)',
        'Total RNA Purification Kits (37500)' => 'Total RNA Purification Kits (37500)',
        'Total RNA Purification Kits (17250)' => 'Total RNA Purification Kits (17250)',
        'Total RNA Purification Kits (17270)' => 'Total RNA Purification Kits (17270)',
        'Total RNA Purification 96-Well Kit Dx (Dx24300)' => 'Total RNA Purification 96-Well Kit Dx (Dx24300)',
        'Total RNA Purification 96-Well Kit Dx (Dx24350)' => 'Total RNA Purification 96-Well Kit Dx (Dx24350)',
        'Total RNA Purification 96-Well Kit Dx (Dx24380)' => 'Total RNA Purification 96-Well Kit Dx (Dx24380)',
        'Total RNA Purification 96-Well Kit Dx (24300)' => 'Total RNA Purification 96-Well Kit Dx (24300)',
        'Total RNA Purification 96-Well Kit Dx (24350)' => 'Total RNA Purification 96-Well Kit Dx (24350)',
        'Total RNA Purification 96-Well Kit Dx (24370)' => 'Total RNA Purification 96-Well Kit Dx (24370)',
        'Total RNA Purification 96-Well Kit Dx (24380)' => 'Total RNA Purification 96-Well Kit Dx (24380)',
      ],
      'Saliva/ Swab RNA Purification Kits' => [
        'Saliva/ Swab RNA Purification Kits Dx (Dx69100)' => 'Saliva/ Swab RNA Purification Kits Dx (Dx69100)',
        'Saliva/ Swab RNA Purification Kits (69100)' => 'Saliva/ Swab RNA Purification Kits (69100)',
        'Saliva/ Swab RNA Purification Kits Dx (Dx69300)' => 'Saliva/ Swab RNA Purification Kits Dx (Dx69300)',
        'Saliva/ Swab RNA Purification Kits (69300)' => 'Saliva/ Swab RNA Purification Kits (69300)',
      ],
    ];


    $form['covid_isolation'] = [
      '#type' => 'select',
      '#title' => $this->t('Isolation'),
      '#options' => $options_covid_isolation,
    ];


    $options_covid_detection = [
      '' => '- Please Select (required) -',
      'COVID-19 TaqMan RT-PCR Kit (N/ORF1ab genes)' => [
        'COVID-19 TaqMan RT-PCR Kit (N/ORF1ab genes) Dx (DxTM67300)' => 'COVID-19 TaqMan RT-PCR Kit (N/ORF1ab genes) Dx (DxTM67300)',
        'COVID-19 TaqMan RT-PCR Kit (N/ORF1ab genes) (TM67300)' => 'COVID-19 TaqMan RT-PCR Kit (N/ORF1ab genes) (TM67300)',
      ],
      'COVID-19 TaqMan RT-PCR Kit (E/RdRP genes)' => [
        'COVID-19 TaqMan RT-PCR Kit (E/RdRP genes) Dx (DxTM67200)' => 'COVID-19 TaqMan RT-PCR Kit (E/RdRP genes) Dx (DxTM67200)',
        'COVID-19 TaqMan RT-PCR Kit (E/RdRP genes) (TM67200)' => 'COVID-19 TaqMan RT-PCR Kit (E/RdRP genes) (TM67200)',
        'COVID-19 TaqMan RT-PCR Kit (E/RdRP genes) (TM67240)' => 'COVID-19 TaqMan RT-PCR Kit (E/RdRP genes) (TM67240)',
        'Saliva/Swab RNA Purification 96-Well Kit (Dx69300)' => 'Saliva/Swab RNA Purification 96-Well Kit (Dx69300)',
        'Saliva/Swab RNA Purification 96-Well Kit (69300)' => 'Saliva/Swab RNA Purification 96-Well Kit (69300)',
      ],
      '2019-nCoV TaqMan RT-PCR Kit' => [
        '2019-nCoV TaqMan RT-PCR Kit Dx (DxTM67100)' => '2019-nCoV TaqMan RT-PCR Kit Dx (DxTM67100)',
        '2019-nCoV TaqMan RT-PCR Kit Dx (DxTM67120)' => '2019-nCoV TaqMan RT-PCR Kit Dx (DxTM67120)',
        '2019-nCoV TaqMan RT-PCR Kit (TM67100)' => '2019-nCoV TaqMan RT-PCR Kit (TM67100)',
        '2019-nCoV TaqMan RT-PCR Kit (TM67120)' => '2019-nCoV TaqMan RT-PCR Kit (TM67120)',
      ],
      'COVID-19/Influenza (A & B) TaqMan RT-PCR Kit' => [
        'COVID-19/Influenza (A & B) TaqMan RT-PCR Kit (TM67400)' => 'COVID-19/Influenza (A & B) TaqMan RT-PCR Kit (TM67400)',
      ],
    ];


    $form['covid_detection'] = [
      '#type' => 'select',
      '#title' => $this->t('Detection'),
      '#options' => $options_covid_detection,
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

    $first_name = $form_state->getValue('covid_fname');
    $last_name = $form_state->getValue('covid_lname');

    if (empty($first_name) || strlen($first_name) < 2) {
      $form_state->setErrorByName('covid_fname', $this->t('Please enter your first name.'));
    }

    if (empty($last_name) || strlen($last_name) < 2) {
      $form_state->setErrorByName('covid_lname', $this->t('Please enter your last name.'));
    }
    if ($first_name && $last_name && $first_name === $last_name) {
      $form_state->setErrorByName('covid_lname', $this->t('Last name should not be the same as first name.'));
    }
    if ($first_name && $last_name && strlen($last_name) >= 6 && strpos($last_name, $first_name) !== false) {
      $form_state->setErrorByName('covid_lname', $this->t('Last name should not contain first name for 6 or more characters.'));
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

    $Selector = '#covidworkflowform-container';
    $response = new AjaxResponse();

    if ($form_state->hasAnyErrors()) {
      $response = nor_forms_ajax_error($form, $form_state, $Selector);
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

    $first_name = $form_state->getValue('covid_fname');
    $last_name = $form_state->getValue('covid_lname');
    $email = $form_state->getValue('covid_email');
    $company = $form_state->getValue('covid_company');
    $phone = $form_state->getValue('covid_phone');
    $billing = $form_state->getValue('covid_billing');
    $shipping = $form_state->getValue('covid_shipping');
    $collection = $form_state->getValue('covid_collection');
    $isolation = $form_state->getValue('covid_isolation');
    $detection = $form_state->getValue('covid_detection');


    $date = date("Ymd");
    $form_name = $this->t('Covid Workflow Form');


    if (!isset($first_name)) {
      $first_name = 'NULL';
    }
    if (!isset($email)) {
      $email = 'NULL';
    }


    try {
      if (1) {

        $zoho = new RecordWrapper('leads');
        $record = [
          'First_Name' => $first_name,
          'Last_Name' => $last_name,
          'Email' => $email,
          'Company' => $company,
          'Phone' => $phone, //potentially not in sandbox
        ];
        $upsert_result = $zoho->upsert($record);

        // Check if the upsert was successful
        if ($upsert_result['success']) {
          \Drupal::logger('zoho')->info('Upsert result for Covid Workflow Form: @result', ['@result' => print_r($upsert_result, TRUE)]);
        } else {
          // Log the error message if the upsert failed
          \Drupal::logger('zoho')->error('Error during Zoho upsert operation for Covid Workflow Form @message', ['@message' => $upsert_result['message']]);
        }
      }
    } catch (Exception $e) {
    }


    $query = \Drupal::database()->insert('forms_to_zoho');
    $query->fields(['created_on', 'first_name', 'last_name', 'email', 'record', 'timestamp', 'form_name', 'phone']); //wrong syntax here breaks entire submit function 
    $query->values([$date, $first_name, $last_name, $email, '', time(), $form_name, $phone]);
    $query->execute();

    $output = '<p>Hello,</p>
    <p>A customer submitted their request for Covid Workflow.</p>
    <p>Last name: ' . $last_name . '<br>First name: ' . $first_name . '<br>Email: ' . $email . '<br>Company: ' . $company . '<br>Phone: ' . $phone . '<br>Billing: ' . $billing . '<br>Collection: ' . $collection . '<br>Isolation: ' . $isolation . '<br>Detection: ' . $detection;

    $time = time();

    if ($form_state->hasAnyErrors()) {
    } else {
      $subject = '[Contact form] - Covid Workflow ' . date("F j, Y, g:i a", $time);
      // $recipient_email = 'andrii.omelchuk@norgenbiotek.com';
      //$recipient_email = 'info@norgenbiotek.com,sebastian.szopa@norgenbiotek.com';// real addresses
      $recipient_email = 'liam.howes@norgenbiotek.com';
      nor_forms_email_redirect($output, $recipient_email, $subject);
    }
  }
}
