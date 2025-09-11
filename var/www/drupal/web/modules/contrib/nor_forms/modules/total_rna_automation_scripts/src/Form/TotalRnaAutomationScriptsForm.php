<?php

namespace Drupal\total_rna_automation_scripts\Form;

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



class TotalRnaAutomationScriptsForm extends FormBase
{

  public function getFormId()
  {
    return 'total_rna_automation_scripts';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $form['#prefix'] = '<div id="total-rna-automation-scripts-container" class="total-rna-automation-scripts-container">';

    $form['rna_fname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Name'),
      '#default_value' => nor_forms_user_first_name(),
      '#required' => TRUE,
    ];

    $form['rna_lname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last Name'),
      '#default_value' => nor_forms_user_last_name(),
      '#required' => TRUE,
    ];

    $form['rna_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#default_value' => nor_forms_user_email(),
      '#required' => TRUE,
    ];

    $form['rna_country'] = [
      '#type' => 'select',
      '#title' => $this->t('Country'),
      '#required' => TRUE,
      '#empty_option'=>t('- Please Select Country -'),
      '#options' => getCountryOptions(), // Use the global function to get country options
      '#attributes' => [
      'autocomplete'=> "country",
      ],
    ];

    /* $form['rna_company'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Company'),
      '#required' => TRUE,
    ]; */

    $form['sample_type_fieldset'] = [
      '#type' => 'fieldset',
      '#attributes' => array(
        'class' => array('sample-type-fieldset'),
      ),
      '#prefix' => '<div id="sample-type-wrapper">',
      '#suffix' => '</div>',
    ];


    $sample_options = array(
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

    $form['sample_type_fieldset']['sample_type'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('What Sample Types Do You Work With? (check all that apply)'),
      '#options' => array_combine($sample_options, $sample_options),
      '#ajax' => [
        'callback' => '::sampleTypeCallback',
        'disable-refocus' => FALSE, // Or TRUE to prevent re-focusing on the triggering element.
        'event' => 'change',
        'wrapper' => 'sample-type-wrapper', // This element is updated with this AJAX callback.
      ],
      '#required' => TRUE,
    ];

    if ($form_state->getValue('sample_type') !== null && in_array('Other', $form_state->getValue('sample_type'))) {
      // Get the text of the selected option.
      $form['sample_type_fieldset']['sample_type_specify_other'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Please Specify Other Sample Type'),
      ];
    }

    $form['rna_mailing'] = [
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
        
      ]
    ];

    $form['#suffix'] = '</div>';
    return $form;
  }


  public function validateForm(array &$form, FormStateInterface $form_state){

    $first_name = $form_state->getValue('rna_fname');
    $last_name = $form_state->getValue('rna_lname');

    if (empty($first_name) || strlen($first_name) < 2) {
      $form_state->setErrorByName('rna_fname', $this->t('Please enter your first name.'));
    }

    if (empty($last_name) || strlen($last_name) < 2) {
      $form_state->setErrorByName('rna_lname', $this->t('Please enter your last name.'));
    }
    if ($first_name && $last_name && $first_name === $last_name) {
      $form_state->setErrorByName('rna_lname', $this->t('Last name should not be the same as first name.'));
    }
    if ($first_name && $last_name && strlen($last_name) >= 6 && strpos($last_name, $first_name) !== false) {
      $form_state->setErrorByName('rna_lname', $this->t('Last name should not contain first name for 6 or more characters.'));
    }

    if (empty($form_state->getValue('sample_type'))) {
      $form_state->setErrorByName('sample_type', $this->t('Please select your sample types.'));
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
    return $form['sample_type_fieldset'];
  }

  public function submitCallback(array &$form, FormStateInterface $form_state){

    $Selector = '#total-rna-automation-scripts-container';

    if ($form_state->hasAnyErrors()) {
      $response = nor_forms_ajax_error($form, $form_state, $Selector);
      return $response;
   
    } else {
      $filename = 'https://norgenbiotek.com/sites/default/files/gated_content/Total%20RNA%20Automation%20Scripts.zip'; // default
      if($form_state->getBuildInfo()['script_to_attach']){
        switch($form_state->getBuildInfo()['script_to_attach']){
          case 'total-rna':
            $filename = 'https://norgenbiotek.com/sites/default/files/gated_content/Total%20RNA%20Automation%20Scripts.zip';
            break;
          case 'plant':
            $filename = 'https://norgenbiotek.com/sites/default/files/gated_content/Plant%20DNA%20Automation%20Scripts.zip';
            break;
          case 'saliva':
            $filename = 'https://norgenbiotek.com/sites/default/files/gated_content/Saliva%20DNA%20Automation%20Scripts.zip';
            break;
          case 'cells-and-tissue':
            $filename = 'https://norgenbiotek.com/sites/default/files/gated_content/Cells%20and%20Tissue%20DNA%20Automation%20Scripts.zip';
            break;
          case 'blood':
            $filename = 'https://norgenbiotek.com/sites/default/files/gated_content/Blood%20DNA%20Automation%20Scripts.zip';
            break;
          case 'stool':
            $filename = 'https://norgenbiotek.com/sites/default/files/gated_content/Stool%20DNA%20Automation%20Scripts.zip';
            break;
          case 'soil':
            $filename = 'https://norgenbiotek.com/sites/default/files/gated_content/Soil%20DNA%20Automation%20Scripts.zip';
            break;
        }
      }

      $response = catalogue_lead_capture_email_sent_ajax($form, $form_state, $Selector, $filename);

      return $response;
    }
  }


  public function submitForm(array &$form, FormStateInterface $form_state)
  {

    $first_name = $form_state->getValue('rna_fname');
    $last_name = $form_state->getValue('rna_lname');
    $email = $form_state->getValue('rna_email');
    /* $company = $form_state->getValue('rna_company'); */
    $country_code = $form_state->getValue('rna_country');

    $sample_type_specified_other = $form_state->getValue('sample_type_specify_other');
    if($form_state->getValue('sample_type_specify_other') !== null){
      $form_state->getValue('sample_type')['Other'] = $sample_type_specified_other; // replace "Other" value with the specified text value from the textfield
    }
    $sample_type = implode(",", array_filter($form_state->getValue('sample_type')));

    $date = date("Ymd");

    $file_label = 'Total RNA';
    if($form_state->getBuildInfo()['script_to_attach']){
      switch($form_state->getBuildInfo()['script_to_attach']){
        case 'total-rna':
          $file_label = 'Total RNA';
          break;
        case 'plant':
          $file_label = 'Plant DNA';
          break;
        case 'saliva':
          $file_label = 'Saliva DNA';
          break;
        case 'cells-and-tissue':
          $file_label = 'Cells and Tissue DNA';
          break;
        case 'blood':
          $file_label = 'Blood DNA';
          break;
        case 'stool':
          $file_label = 'Stool DNA';
          break;
        case 'soil':
          $file_label = 'Soil DNA';
          break;
      }
    }

    $form_name = $file_label.' Automation Scripts Form';

    $output = "";
    $country_name = getCountryNames($country_code);

    $subscribe = $form_state->getValue('rna_mailing') ? 1 : 0;

    // Get the value of the "Subscribe to Mailing List" checkbox for email
    $subscribe_mail = $form_state->getValue('rna_mailing') ? 'Yes' : 'No';

    if (!isset($first_name)) {
      $first_name = 'NULL';
    }
    if (!isset($email)) {
      $email = 'NULL';
    }


    $query = \Drupal::database()->insert('forms_to_zoho');
    $query->fields(['created_on', 'first_name', 'last_name', 'email', 'lead_sample_type', 'record', 'timestamp', 'form_name', 'opt_in' , 'country']);
    $query->values([$date, $first_name, $last_name, $email, $sample_type, '', time(), $form_name, $subscribe, $country_name]);
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
    <p>Last name: ' . $last_name . '<br>First name: ' . $first_name . '<br>Email: ' . $email . '<br>Sample Type: ' . $sample_type . '<br>Country: ' . $country_name . ' <br>Mailing List: ' . $subscribe_mail;

    $time = time();

    if ($form_state->hasAnyErrors()) {
    } else {
      $subject = '[Automation Scripts Form] - New Submission - '.$file_label.' - ' . date("F j, Y, g:i a", $time);
       //$recipient_email = 'sowmya.movva@norgenbiotek.com';
      $recipient_email = 'info@norgenbiotek.com, sebastian.szopa@norgenbiotek.com, sabah.butt@norgenbiotek.com';// real addresses
      //$recipient_email = 'liam.howes@norgenbiotek.com';
      //$recipient_email = 'huraira.khan@norgenbiotek.com';

      nor_forms_email_redirect($output, $recipient_email, $subject);
    }
  }
}
