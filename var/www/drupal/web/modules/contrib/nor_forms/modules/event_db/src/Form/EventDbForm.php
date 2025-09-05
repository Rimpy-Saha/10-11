<?php

namespace Drupal\event_db\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\file\Entity\File;
use Drupal\nor_forms\Libraries\Zoho\RecordWrapper;

class EventDbForm extends FormBase
{

  public function getFormId()
  {
    return 'event_db_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {

    $form['#prefix'] = '<div id="eventdbform-container" class="eventdbform-container">';

    $form['header'] = [
      '#type' => 'markup',
      '#markup' => '<h1><p style = "color:red;">Update Entry</p></h1><p>Please add new data to the fields you want to change!</p>',
      '#class' => 'work_pls',
    ];
    $form['update_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('ID'),
      '#attributes' => array('readonly' => 'readonly'),
      '#required' => TRUE,
    ];
    $form['update_fname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Name'),
      '#required' => TRUE,
    ];

    $form['update_lname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last Name'),
      '#required' => TRUE,
    ];

    $form['update_email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email'),
      '#required' => TRUE,
    ];

    $form['update_company'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Company / Institution'),
      '#placeholder' => t('Company or Institution Name (Required)'),
      '#required' => TRUE,
    ];

    $form['update_job'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Job Title'),
      '#placeholder' => t('Job Title'),
    ];
    $form['update_event_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Event Name'),
      '#placeholder' => t('E.g. AACR2024'),
    ];

    // $form['update_sample_type_fieldset'] = [
    //   '#type' => 'fieldset',
    //   '#attributes' => array(
    //     'class' => array('sample-type-fieldset'),
    //   ),
    //   '#prefix' => '<div id="sample-type-wrapper">',
    //   '#suffix' => '</div>',
    // ];
    // $sample_options = array(
    //   t('tissue'),
    //   t('plasma/serum'),
    //   t('whole blood'),
    //   t('urine'),
    //   t('stool'),
    //   t('saliva'),
    //   t('exosome'),
    //   t('plant'),
    //   t('soil'),
    //   t('yeast'),
    //   t('fungi'),
    //   t('bacteria'),
    //   t('food'),
    //   t('milk'),
    //   t('cell culture'),
    //   t('FFPE'),
    //   t('CSF'),
    //   t('synovial fluid'),
    //   t('phage'),
    //   t('Other'),
    // );

    // $form['update_sample_type_fieldset']['sample_type'] = [
    //   '#type' => 'checkboxes',
    //   '#title' => $this->t('Sample Type'),
    //   '#options' => array_combine($sample_options, $sample_options),
    //   '#ajax' => [
    //     'callback' => '::sampleTypeCallback',
    //     'disable-refocus' => FALSE, // Or TRUE to prevent re-focusing on the triggering element.
    //     'event' => 'change',
    //     'wrapper' => 'sample-type-wrapper', // This element is updated with this AJAX callback.
    //     'progress' => [
    //       'type' => 'throbber',
    //       'message' => $this->t('Verifying entry...'),
    //     ],
    //   ]
    // ];

    // if ($form_state->getValue('sample_type') !== null && in_array('Other', $form_state->getValue('sample_type'))) {
    //   // Get the text of the selected option.
    //   $form['lead_profile']['sample_type_fieldset']['sample_type_specify_other'] = [
    //     '#type' => 'textfield',
    //     '#title' => $this->t('Please Specify Other Sample Type'),
    //   ];
    // }


    // $form['update_analyte_fieldset'] = [
    //   '#type' => 'fieldset',
    //   '#attributes' => array(
    //     'class' => array('analyte-fieldset'),
    //   ),
    //   '#prefix' => '<div id="analyte-wrapper">',
    //   '#suffix' => '</div>',
    // ];

    // $analyte_options = array(
    //   t('RNA'),
    //   t('DNA'),
    //   t('Protein'),
    //   t('microRNA'),
    //   t('cf-DNA'),
    //   t('cf-RNA'),
    //   t('Other'),
    // );

    //  $form['update_analyte_fieldset']['analyte'] = [
    //   '#type' => 'checkboxes',
    //   '#title' => $this->t('Analyte'),
    //   '#options' => array_combine($analyte_options, $analyte_options),
    //   '#ajax' => [
    //     'callback' => '::analyteTypeCallback',
    //     'disable-refocus' => FALSE, // Or TRUE to prevent re-focusing on the triggering element.
    //     'event' => 'change',
    //     'wrapper' => 'analyte-wrapper', // This element is updated with this AJAX callback.
    //     'progress' => [
    //       'type' => 'throbber',
    //       'message' => $this->t('Verifying entry...'),
    //     ],
    //   ]
    // ];

    // if ($form_state->getValue('analyte') !== null && in_array('Other', $form_state->getValue('analyte'))) {
    //   // Get the text of the selected option.
    //   $form['lead_profile']['analyte_fieldset']['analyte_specify_other'] = [
    //     '#type' => 'textfield',
    //     '#title' => $this->t('Please Specify Other Analyte'),
    //   ];
    // }

    // $form['update_application_fieldset'] = [
    //   '#type' => 'fieldset',
    //   '#attributes' => array(
    //     'class' => array('application-fieldset'),
    //   ),
    //   '#prefix' => '<div id="application-wrapper">',
    //   '#suffix' => '</div>',
    // ];

    // $application_options = array(
    //   t('MDx'),
    //   t('NGS'),
    //   t('Preservation'),
    //   t('RNA-Seq'),
    //   t('microbiome'),
    //   t('PCR/RT-PCR'),
    //   t('Small RNA-Seq'),
    //   t('16s'),
    //   t('ITS'),
    //   t('Shallow Shotgun'),
    //   t('Library Preparation'),
    //   t('Cleanup and Concentration'),
    //   t('Other'),
    // );

    // $form['update_application_fieldset']['application'] = [
    //   '#type' => 'checkboxes',
    //   '#title' => $this->t('Application'),
    //   '#options' => array_combine($application_options, $application_options),
    //   '#ajax' => [
    //     'callback' => '::applicationTypeCallback',
    //     'disable-refocus' => FALSE, // Or TRUE to prevent re-focusing on the triggering element.
    //     'event' => 'change',
    //     'wrapper' => 'application-wrapper', // This element is updated with this AJAX callback.
    //     'progress' => [
    //       'type' => 'throbber',
    //       'message' => $this->t('Verifying entry...'),
    //     ],
    //   ]
    // ];

    // if ($form_state->getValue('application') !== null && in_array('Other', $form_state->getValue('application'))) {
    //   // Get the text of the selected option.
    //   $form['lead_profile']['application_fieldset']['application_specify_other'] = [
    //     '#type' => 'textfield',
    //     '#title' => $this->t('Please Specify Other Application'),
    //   ];
    // }

    // $form['update_newsletter_subscribe'] = [
    //   '#type' => 'checkbox',
    //   '#title' => t('I would like to receive emails from Norgen Biotek regarding webinars, services, and other marketing material.*'),
    //   '#description' => t('*You can unsubscribe at any time by clicking the link in the footer of our emails. For information about our privacy practices, please visit our website.'),
    // ];

    // $first_engagement_options = array(
    //   t('Current Event'),
    //   t('Social Media'),
    //   t('Email'),
    //   t('Website'),
    //   t('Referral'),
    //   t('Advertisement'),
    // );

    // $form['update_first_engagement'] = [
    //   '#type' => 'checkboxes',
    //   '#title' => $this->t('First Engagement'),
    //   '#options' => array_combine($first_engagement_options, $first_engagement_options),
    // ];

    $form['update_conference_message'] = [
      '#title' => $this->t('Additional Notes'),
      '#type' => 'textarea',
      '#required' => false, // Optional feedback field
      '#placeholder' => t('Please provide any adidtional notes or lead information here'),
      '#attributes' => array(
        'rows' => 10,
      )
    ];

    $form['google_recaptcha'] = [
      '#type'=> 'fieldset',
      '#description' => '<div class="g-recaptcha" data-sitekey="6Lcr4u0pAAAAAGj32knXkUzuHAXzj3CoAhtbJ1t5"></div>',
    ];

    $form['actions']['update'] = [
      '#type' => 'submit',
      '#value' => $this->t('Update'),
      '#button_type' => 'primary',
      '#submit' => ['::submitFormUpdate'],
      '#ajax' => [
        'callback' => '::submitCallback',
        'event' => 'click',
        'method' => 'append', 'effect' => 'fade',
      ]
    ];
    

    $form['#suffix'] = '</div>';
    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {

    $first_name = $form_state->getValue('update_fname');
    $last_name = $form_state->getValue('update_lname');

    if (empty($first_name) || strlen($first_name) < 2) {
      $form_state->setErrorByName('update_fname', $this->t('Please enter your first name.'));
    }

    if (empty($last_name) || strlen($last_name) < 2) {
      $form_state->setErrorByName('update_lname', $this->t('Please enter your last name.'));
    }
    if ($first_name && $last_name && $first_name === $last_name) {
      $form_state->setErrorByName('update_lname', $this->t('Last name should not be the same as first name.'));
    }
    if ($first_name && $last_name && strlen($last_name) >= 6 && strpos($last_name, $first_name) !== false) {
      $form_state->setErrorByName('update_lname', $this->t('Last name should not contain first name for 6 or more characters.'));
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

    $Selector = '#eventdbform-container';
    $response = new AjaxResponse();

    if ($form_state->hasAnyErrors()) {
      $response = nor_forms_ajax_error($form, $form_state, $Selector);
    } else {

      $response = event_db_email_sent_ajax($form, $form_state, $Selector);
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
    $first_name = $form_state->getValue('update_fname');
    $last_name = $form_state->getValue('update_lname');
    $id = $form_state->getValue('update_id');
    $email= $form_state->getValue('update_email');

    $date = date("Ymd");
    $form_name = $this->t('Event Db Form');


    if (!isset($first_name)) {
      $first_name = 'NULL';
    }
   
    //$email = 'test@test.ca';

    $query = \Drupal::database()->select('forms_to_zoho', 't');
    $query->fields('t', ['id', 'form_name']);
    $result = $query->execute();

    foreach ($result as $row) {
      $id2 = $row->id;
      $form_name2 = $row->form_name;
      if($form_name2 == $form_name && $id2 == $id){
        $dup = 'true';
        $dup_id = $id;
        $dup_form = $form_name;
      }
      // Do something with the results.
    }
    if($dup == 'true'){
      $query = \Drupal::database()->update('forms_to_zoho');
      $query->fields([
      'first_name' => $first_name,
      ]);
      $query->condition('form_name', $form_name);
      $query->condition('id', $dup_id);
      $query->execute();
    }
    else{
      $query = \Drupal::database()->insert('forms_to_zoho');
      $query->fields(['created_on','form_name', 'first_name', 'last_name','email','record','timestamp']); //wrong syntax here breaks entire submit function 
      $query->values([$date,$form_name, $first_name, $last_name, $email,'',time()]);
      $query->execute();
    }
    // if($dup == 'true'){
    //   $query = \Drupal::database()->update('forms_to_zoho');
    //   $query->fields(['created_on','form_name', 'first_name', 'last_name','email','record','timestamp']); //wrong syntax here breaks entire submit function 
    //   $query->values([$date,$form_name, $first_name, $last_name, $email,'',time()]);
    //   $query->condition('form_name', $form_name);
    //   $query->execute();
    // }
    // $query = \Drupal::database()->insert('forms_to_zoho');
    // $query->fields(['created_on','form_name', 'first_name', 'last_name','email','record','timestamp']); //wrong syntax here breaks entire submit function 
    // $query->values([$date,$form_name, $first_name, $last_name, $email,'',time()]);
    // $query->execute();

    $output = '<p>Hello,</p>
    <p>A customer submitted their request for Covid Workflow.</p>
    <p>Last name: ' .$dup.'';

    $time = time();

    if ($form_state->hasAnyErrors()) {
    } else {
      $subject = '[Contact form] - Covid Workflow ' . date("F j, Y, g:i a", $time);
      // $recipient_email = 'sowmya.movva@norgenbiotek.com';
      $recipient_email = 'sabah.butt@norgenbiotek.com';
      nor_forms_email_redirect($output, $recipient_email, $subject);
    }
  }

  public function submitFormUpdate(array &$form, FormStateInterface $form_state)
  {
    $first_name = $form_state->getValue('update_fname');
    $last_name = $form_state->getValue('update_lname');
    $id = $form_state->getValue('update_id');
    $email= $form_state->getValue('update_email');
    $company= $form_state->getValue('update_company');
    $job= $form_state->getValue('update_job');
    $event_name= $form_state->getValue('update_event_name');
    $message= $form_state->getValue('update_conference_message');

    $date = date("Ymd");
    $form_name = $this->t('Conference Lead');


    if (!isset($first_name)) {
      $first_name = 'NULL';
    }
   
    //$email = 'test@test.ca';

    $query = \Drupal::database()->select('forms_to_zoho', 't');
    $query->fields('t', ['id', 'form_name']);
    $result = $query->execute();

    foreach ($result as $row) {
      $id2 = $row->id;
      $form_name2 = $row->form_name;
      if($form_name2 == $form_name && $id2 == $id){
        $dup = 'true';
        $dup_id = $id;
        $dup_form = $form_name;
      }
      // Do something with the results.
    }
    if($dup == 'true'){
      $query = \Drupal::database()->update('forms_to_zoho');
      $query->fields([
      'first_name' => $first_name,
      'last_name' => $last_name,
      'email' => $email,
      'company' => $company,
      'job_title' => $job,
      'event_name' => $event_name,
      'notes' => $message,
      ]);
      $query->condition('form_name', $form_name);
      $query->condition('id', $dup_id);
      $query->execute();
    }
    else{
      $query = \Drupal::database()->insert('forms_to_zoho');
      $query->fields(['created_on','form_name', 'first_name', 'last_name','email','record','timestamp']); //wrong syntax here breaks entire submit function 
      $query->values([$date,$form_name, $first_name, $last_name, $email,'',time()]);
      $query->execute();
    }
    // if($dup == 'true'){
    //   $query = \Drupal::database()->update('forms_to_zoho');
    //   $query->fields(['created_on','form_name', 'first_name', 'last_name','email','record','timestamp']); //wrong syntax here breaks entire submit function 
    //   $query->values([$date,$form_name, $first_name, $last_name, $email,'',time()]);
    //   $query->condition('form_name', $form_name);
    //   $query->execute();
    // }
    // $query = \Drupal::database()->insert('forms_to_zoho');
    // $query->fields(['created_on','form_name', 'first_name', 'last_name','email','record','timestamp']); //wrong syntax here breaks entire submit function 
    // $query->values([$date,$form_name, $first_name, $last_name, $email,'',time()]);
    // $query->execute();

    $output = '<p>Hello,</p>
    <p>A customer submitted their request for Covid Workflow.</p>
    <p>Last name: ' .$message.'';

    $time = time();

    if ($form_state->hasAnyErrors()) {
    } else {
      $subject = '[Contact form] - Covid Workflow ' . date("F j, Y, g:i a", $time);
      // $recipient_email = 'sowmya.movva@norgenbiotek.com';
      //$recipient_email = 'sabah.butt@norgenbiotek.com';// real addresses
      $recipient_email = 'liam.howes@norgenbiotek.com';
      nor_forms_email_redirect($output, $recipient_email, $subject);
    }
  }

  

}
