<?php

namespace Drupal\saliva_dna_automation_scripts_test\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\MessageCommand;
use Drupal\Core\Ajax\AppendCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\nor_forms\Libraries\Zoho\RecordWrapper;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;



class SalivaDnaAutomationScriptsFormTest extends FormBase
{

  public function getFormId()
  {
    return 'saliva_dna_automation_scripts_test';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $form['#prefix'] = '<div id="saliva-dna-automation-scripts-container" class="saliva-dna-automation-scripts-container">';

    $form['saliva_fname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Name'),
      '#default_value' => nor_forms_user_first_name(),
      '#required' => TRUE,
    ];

    $form['saliva_lname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last Name'),
      '#default_value' => nor_forms_user_last_name(),
      '#required' => TRUE,
    ];

    $form['saliva_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#default_value' => nor_forms_user_email(),
      '#required' => TRUE,
    ];

    $form['saliva_country'] = [
      '#type' => 'select',
      '#title' => $this->t('Country'),
      '#required' => TRUE,
      '#empty_option'=>t('- Please Select Country -'),
      '#options' => getCountryOptions(), // Use the global function to get country options
      '#attributes' => [
      'autocomplete'=> "country",
      ],
    ];

    /* $form['saliva_company'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Company'),
      '#required' => TRUE,
    ]; */

    $form['saliva_sample_type_fieldset'] = [
      '#type' => 'fieldset',
      '#attributes' => array(
        'class' => array('sample-type-fieldset'),
      ),
      '#prefix' => '<div id="saliva-sample-type-wrapper">',
      '#suffix' => '</div>',
    ];


    $saliva_sample_options = array(
      t('animal cells'),
      t('animal tissues'),
      t('blood'),
      t('nasal/throat swabs'),
      t('bacteria'),
      t('yeast'),
      t('fungi'),
      t('plant'),
      t('viral supension'),
      t('other'),
    );

    $form['saliva_sample_type_fieldset']['saliva_sample_type'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('What Sample Types Do You Work With? (check all that apply)'),
      '#options' => array_combine($saliva_sample_options, $saliva_sample_options),
      '#ajax' => [
        'callback' => '::sampleTypeCallback',
        'disable-refocus' => FALSE, // Or TRUE to prevent re-focusing on the triggering element.
        'event' => 'change',
        'wrapper' => 'saliva-sample-type-wrapper', // This element is updated with this AJAX callback.
      ],
      '#required' => TRUE,
    ];

    if ($form_state->getValue('saliva_sample_type') !== null && in_array('Other', $form_state->getValue('saliva_sample_type'))) {
      // Get the text of the selected option.
      $form['saliva_sample_type_fieldset']['sample_type_specify_other'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Please Specify Other Sample Type'),
      ];
    }

    $form['saliva_mailing'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Subscribe to our mailing list and be the first to hear about offers and news from Norgen Biotek.'),
      '#suffix' => '<div class="disclaimer">It is our responsibility to protect and guarantee that your data will be completely confidential. You can unsubscribe from Norgen emails at any time by clicking the link in the footer of our emails. For more information please view our <a href="/content/privacy-policy">Privacy Policy</a>.</div>',
    ];

    $form['google_recaptcha'] = [
      '#type'=> 'fieldset',
      '#description' => '<div class="g-recaptcha" data-sitekey="6Lcr4u0pAAAAAGj32knXkUzuHAXzj3CoAhtbJ1t5"></div>',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Access Content'),
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

    $first_name = $form_state->getValue('saliva_fname');
    $last_name = $form_state->getValue('saliva_lname');

    if (empty($first_name) || strlen($first_name) < 2) {
      $form_state->setErrorByName('saliva_fname', $this->t('Please enter your first name.'));
    }

    if (empty($last_name) || strlen($last_name) < 2) {
      $form_state->setErrorByName('saliva_lname', $this->t('Please enter your last name.'));
    }
    if ($first_name && $last_name && $first_name === $last_name) {
      $form_state->setErrorByName('saliva_lname', $this->t('Last name should not be the same as first name.'));
    }
    if ($first_name && $last_name && strlen($last_name) >= 6 && strpos($last_name, $first_name) !== false) {
      $form_state->setErrorByName('saliva_lname', $this->t('Last name should not contain first name for 6 or more characters.'));
    }

    if (empty($form_state->getValue('saliva_sample_type'))) {
      $form_state->setErrorByName('saliva_sample_type', $this->t('Please select your sample types.'));
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

  public function sampleTypeCallback(array &$form, FormStateInterface $form_state)
  {
    return $form['saliva_sample_type_fieldset'];
  }

  public function submitCallback(array &$form, FormStateInterface $form_state){

    $Selector = '#saliva-dna-automation-scripts-container';

    if ($form_state->hasAnyErrors()) {
      $response = nor_forms_ajax_error($form, $form_state, $Selector);
      return $response;
   
    } else {
      $filename = 'https://norgenbiotek.com/sites/default/files/gated_content/Saliva%20DNA%20Automation%20Scripts.zip'; // default
    

      $response = catalogue_lead_capture_email_sent_ajax($form, $form_state, $Selector, $filename);

      return $response;
    }
  }


  public function submitForm(array &$form, FormStateInterface $form_state)
  {

    $first_name = $form_state->getValue('saliva_fname');
    $last_name = $form_state->getValue('saliva_lname');
    $email = $form_state->getValue('saliva_email');
    /* $company = $form_state->getValue('saliva_company'); */
    $country_code = $form_state->getValue('saliva_country');

    $saliva_sample_type_specified_other = $form_state->getValue('sample_type_specify_other');
    if($form_state->getValue('sample_type_specify_other') !== null){
      $form_state->getValue('saliva_sample_type')['Other'] = $saliva_sample_type_specified_other; // replace "Other" value with the specified text value from the textfield
    }
    $saliva_sample_type = implode(",", array_filter($form_state->getValue('saliva_sample_type')));

    $date = date("Ymd");

    $file_label = 'Saliva DNA';

    $form_name = $file_label.' Automation Scripts Form';

    $output = "";
    $country_name = getCountryNames($country_code);

    $subscribe = $form_state->getValue('saliva_mailing') ? 1 : 0;

    // Get the value of the "Subscribe to Mailing List" checkbox for email
    $subscribe_mail = $form_state->getValue('saliva_mailing') ? 'Yes' : 'No';

    if (!isset($first_name)) {
      $first_name = 'NULL';
    }
    if (!isset($email)) {
      $email = 'NULL';
    }


    $query = \Drupal::database()->insert('forms_to_zoho');
    $query->fields(['created_on', 'first_name', 'last_name', 'email', 'lead_sample_type', 'record', 'timestamp', 'form_name', 'opt_in' , 'country']);
    $query->values([$date, $first_name, $last_name, $email, $saliva_sample_type, '', time(), $form_name, $subscribe, $country_name]);
    $query->execute();

    try { 
      $zoho = new RecordWrapper('leads');
      $record = [
        'First_Name' => $first_name,
        'Last_Name' => $last_name,
        'Email' => $email,
        'Country' => $country_name,
        'Lead_Source' => 'Website Form',
        'Web_Forms' => [$form_name],
      ];
      $upsert_result = $zoho->upsert($record);
    } catch (Exception $e) {
    }

    $output = '<p>Hello,</p>
    <p>A customer submitted their request for the gated <b>'.$file_label.'</b> automation scripts.</p>
    <p>Last name: ' . $last_name . '<br>First name: ' . $first_name . '<br>Email: ' . $email . '<br>Sample Type: ' . $saliva_sample_type . '<br>Country: ' . $country_name . ' <br>Mailing List: ' . $subscribe_mail;

    $time = time();

    if ($form_state->hasAnyErrors()) {
    } else {
      $subject = '[Automation Scripts Form] - New Submission - '.$file_label.' - ' . date("F j, Y, g:i a", $time);
       //$recipient_email = 'sowmya.movva@norgenbiotek.com';
      $recipient_email = 'info@norgenbiotek.com,sabah.butt@norgenbiotek.com';// real addresses
      //$recipient_email = 'liam.howes@norgenbiotek.com';
      //$recipient_email = 'huraira.khan@norgenbiotek.com';


      nor_forms_email_redirect($output, $recipient_email, $subject);
    }
  }
}
