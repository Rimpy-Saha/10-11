<?php

namespace Drupal\service_consultation\Form;

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

class ServiceConsultationForm extends FormBase
{

  public function getFormId()
  {
    return 'service_consultation_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
{
    
    $form['utm_source'] = [
      '#type' => 'hidden',
      '#attributes' => ['id' => 'utm_source'],
    ];

    $form['referrer_page'] = [
      '#type' => 'hidden',
      '#attributes' => ['id' => 'referrer_page'],
    ];

    $form['#prefix'] = '<div id="serviceconsultation-container" div class="serviceconsultation-container">';

    $form['header'] = [
      '#type' => 'markup',
      '#markup' => '<h2>We would love to hear about your project! Please fill out this form and a representative will be in touch shortly.</h2>',
    ];

    $form['service_fname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Name'),
      '#default_value' => nor_forms_user_first_name(),
      '#required' => TRUE,
      '#placeholder' => 'First Name (required)',
    ];

    $form['service_lname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last Name'),
      '#default_value' => nor_forms_user_last_name(),
      '#required' => TRUE,
      '#placeholder' => 'Last Name (required)',
    ];


    $form['service_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#default_value' => nor_forms_user_email(),
      '#required' => TRUE,
      '#placeholder' => 'Email Address (required)',
    ];

    //add area code options
    $form['service_phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Phone Number'),
      '#maxlength' => 20, // Limit input to 14 characters
      '#required' => TRUE,
      '#placeholder' => 'Phone Number (required)',
      '#attributes' => [
        'class' => ['form-control input-lg'],
      ],
    ];

    $form['service_company'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Company Name'),
      '#required' => TRUE,
      '#placeholder' => 'Company Name (required)',
    ];

    $form['service_country'] = [
      '#type' => 'select',
      '#title' => $this->t('Country'),
      '#options' => getCountryOptions(),
      '#required' => TRUE,
      '#attributes' => ['class' => ['aligned-country-list']],
    ];

    $form['service_area'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Area of Interest'),
      '#required' => TRUE,
      '#placeholder' => 'Area of Interest (required)',
    ];

    $form['service_interest'] = [
      '#type' => 'select',
      '#title' => $this->t('Service of Interest'),
      '#options' => [
        'option1' => $this->t('16s rRNA Sequencing/ Metagenomics'),
        'option2' => $this->t('Small RNA Seq'),
        'option3' => $this->t('RNA Seq'),
        'option4' => $this->t('SARS-CoV-2 Seq'),
        'option5' => $this->t('qPCR/qRT-PCR'),
        'option6' => $this->t('Extraction(RNA/DNA/Protein/Exosomes'),
        'option7' => $this->t('OEM/Contract Manufacturing'),
        'option8' => $this->t('Other'),
      ],
    ];

    $form['service_analysis'] = [
      '#type' => 'radios',
      '#prefix' => '<p>Does your project require Bioinformatics Analysis? (for NGS Only)</p><div class="bioinformatics-analysis-options">',
      '#title' => $this->t(''),
      '#options' => [
        'yes' => $this->t('Yes'),
        'no' => $this->t('No'),
      ],
      '#suffix' => '</div>',
      '#attributes' => [
        'class' => ['webform-container-inline'],
      ],
    ];

    $form['service_sample'] = [
      '#type' => 'number',
      '#title' => $this->t('Sample Size'),
      '#min' => 0,
      '#required' => true,
      '#attributes' => [
        'class' => ['form-control sample-size-input'],
      ],
    ];


    $form['service_message'] = [
      '#title' => $this->t('Describe the Project'),
      '#type' => 'textarea',
      '#required' => false, // Optional feedback field
    ];

    $form['service_about'] = [
      '#type' => 'select',
      '#title' => $this->t('How did you hear about Norgen Biotek Corp.'),
      '#options' => [
        'option1' => $this->t('Referral'),
        'option2' => $this->t('Research Gate'),
        'option3' => $this->t('Publication'),
        'option4' => $this->t('Search Engine'),
        'option5' => $this->t('Linkedin'),
        'option6' => $this->t('Email'),
        'option7' => $this->t('Radio'),
        'option8' => $this->t('Advertisement'),
      ],
    ];

    $form['service_mailing'] = [
      '#type' => 'radios',
      '#prefix' => '<p>Sign up for our mailing list?</p><div class="mailing-list-options">',
      '#title' => $this->t('Receive free newsletters, promotions, and be the first to learn about new exciting content.'),
      '#options' => [
        'yes' => $this->t('Yes'),
        'no' => $this->t('No'),
      ],
      '#suffix' => '</div>',
      '#attributes' => [
        'class' => ['webform-container-inline'],
      ],
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

    $first_name = $form_state->getValue('service_fname');
    $last_name = $form_state->getValue('service_lname');

    if (empty($first_name) || strlen($first_name) < 2) {
      $form_state->setErrorByName('service_fname', $this->t('Please enter your first name.'));
    }

    if (empty($last_name) || strlen($last_name) < 2) {
      $form_state->setErrorByName('service_lname', $this->t('Please enter your last name.'));
    }

    if ($first_name && $last_name && $first_name === $last_name) {
      $form_state->setErrorByName('service_lname', $this->t('Last name should not be the same as first name.'));
    }
    if ($first_name && $last_name && strlen($last_name) >= 6 && strpos($last_name, $first_name) !== false) {
      $form_state->setErrorByName('service_lname', $this->t('Last name should not contain first name for 6 or more characters.'));
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

    $Selector = '#serviceconsultation-container';
    $response = new AjaxResponse();

    if ($form_state->hasAnyErrors()) {
      $response = nor_forms_ajax_error($form, $form_state, $Selector);
      return $response;
    } else {

      $response = nor_forms_email_sent_ajax($form, $form_state, $Selector);


      $form_file = $form_state->getValue('service_attachment', 0);
      if (isset($form_file[0]) && !empty($form_file[0])) {
        $file = File::load($form_file[0]);
        $file->setPermanent();
        $file->save();

        $file_path = $file->getFileUri();

        $form_state->set('file_path', $file_path);
      }

      return $response;
    }
  }


  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $utm_source = $form_state->getValue('utm_source', 'direct_visited');
    $referrer_page = $form_state->getValue('referrer_page', 'No Referrer (Direct Visit)');

    $first_name = $form_state->getValue('service_fname');
    $last_name = $form_state->getValue('service_lname');
    $email = $form_state->getValue('service_email');
    $country_code = $form_state->getValue('service_country');
    $phone = $form_state->getValue('service_phone');
    $company = $form_state->getValue('service_company');
    $message = $form_state->getValue('service_message');
    $mailing = $form_state->getValue('service_mailing');
    $area = $form_state->getValue('service_area');
    $sample = $form_state->getValue('service_sample');
    $analysis = $form_state->getValue('service_analysis');
    $date = date("Ymd");
    $form_name = $this->t('Service Consultation');

    // Get the selected return issue title
    $selectedServiceInterest = $form_state->getValue('service_interest');
    $returnInterestTitle = '';

    $analysis_bool = $analysis == 'Yes' ? 1 : 0;
    $subscribe = $form_state->getValue('service_mailing') == 'Yes' ? 1 : 0;

    switch ($selectedServiceInterest) {
      case 'option1':
        $returnInterestTitle = $this->t('16s rRNA Sequencing/ Metagenomics');
        break;
      case 'option2':
        $returnInterestTitle = $this->t('Small RNA Seq');
        break;
      case 'option3':
        $returnInterestTitle = $this->t('RNA Seq');
        break;
      case 'option4':
        $returnInterestTitle = $this->t('SARS-CoV-2 Seq');
        break;
      case 'option5':
        $returnInterestTitle = $this->t('qPCR/qRT-PCR');
        break;
      case 'option6':
        $returnInterestTitle = $this->t('Extraction(RNA/DNA/Protein/Exosomes');
        break;
      case 'option7':
        $returnInterestTitle = $this->t('OEM/Contract Manufacturing');
        break;
      case 'option8':
        $returnInterestTitle = $this->t('Other');
        break;
      default:
        $returnInterestTitle = t('16s rRNA Sequencing/ Metagenomics');
    }

    // Get the selected return issue title
    $selectedServiceAbout = $form_state->getValue('service_about');
    $returnAboutTitle = '';

    switch ($selectedServiceInterest) {
      case 'option1':
        $returnAboutTitle = $this->t('Referral');
        break;
      case 'option2':
        $returnAboutTitle = $this->t('Research Gate');
        break;
      case 'option3':
        $returnAboutTitle = $this->t('Publication');
        break;
      case 'option4':
        $returnAboutTitle = $this->t('Search Engine');
        break;
      case 'option5':
        $returnAboutTitle = $this->t('Linkedin');
        break;
      case 'option6':
        $returnAboutTitle = $this->t('Email');
        break;
      case 'option7':
        $returnAboutTitle = $this->t('Radio');
        break;
      case 'option8':
        $returnAboutTitle = $this->t('Advertisement');
        break;
      default:
        $returnAboutTitle = t('Referral');
    }

    //Get the countries with codes directly from the CountryRepository service
    $output = "";
    $country_name = getCountryNames($country_code);

    if (!isset($first_name)) {
      $first_name = 'NULL';
    }
    if (!isset($email)) {
      $email = 'NULL';
    }


    $query = \Drupal::database()->insert('forms_to_zoho');
    $query->fields(['created_on', 'first_name', 'last_name', 'country', 'company', 'email', 'record', 'timestamp', 'form_name', 'phone', 'opt_in', 'lead_first_engagement', 'notes', 'area_interest', 'service_interest', 'bioinfo_analysis', 'sample_size']); //wrong syntax here breaks entire submit function 
    $query->values([$date, $first_name, $last_name, $country_name, $company, $email, '', time(), $form_name, $phone, $subscribe, $returnAboutTitle, $message, $area, $returnInterestTitle, $analysis_bool, $sample]);
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
      <p>A customer submitted their request for Service Consultation Form.</p>
      <p>Last name: ' . $last_name . '<br>First name: ' . $first_name . ' <br>Email: ' . $email . '<br>Primary Phone: ' . $phone . '<br>Company: ' . $company . '<br>Country: ' . $country_name . '<br>Area of interest: ' . $area . ' <br>Service of Interest: ' . $returnInterestTitle . ' <br>Bioinformatics Analysis Required: ' . $analysis . ' <br>Sample Size: ' . $sample . ' <br>Project Details: ' . $message . '<br>How did they hear about us: ' . $returnAboutTitle . '<br>Mailing List: ' . $mailing . '<br>UTM Source: ' . $utm_source . '<br> Referrer Page: ' . $referrer_page;

    $time = time();

    if ($form_state->hasAnyErrors()) {
    } else {
      $subject = '[Contact form] - Service Consultation ' . date("F j, Y, g:i a", $time);
      $recipient_email = 'services@norgenbiotek.com, alex.white@norgenbiotek.com, mohamed.elmogy@norgenbiotek.com, sabah.butt@norgenbiotek.com';// real addresses
      //$recipient_email = 'huraira.khan@norgenbiotek.com';
      //$recipient_email = 'sabah.butt@norgenbiotek.com';
      //$recipient_email = 'liam.howes@norgenbiotek.com';
      nor_forms_email_redirect($output, $recipient_email, $subject);
    }
  }
}