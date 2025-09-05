<?php

namespace Drupal\catalogue_lead_capture\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\file\Entity\File;
use Drupal\nor_forms\Libraries\Zoho\RecordWrapper;

class CatalogueLeadCapture extends FormBase
{

  public function getFormId()
  {
    return 'catalogue_lead_capture';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {

    $form['#prefix'] = '<div id = "catalogueleadcapture-container" div class="catalogueleadcapture-container"><h2>Access Free Content</h2><p>After entering your information below, you will be granted access to this content!</p>';

    $form['fname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Name'),
      '#required' => TRUE,
      '#placeholder' => $this->t('First Name (Required)'),
    ];

    $form['lname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last Name'),
      '#required' => TRUE,
      '#placeholder' => $this->t('Last Name (Required)'),
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#required' => TRUE,
      '#placeholder' => $this->t('Email Address (Required)'),
    ];

    $form['phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Phone Number'),
      '#required' => TRUE,
      '#placeholder' => $this->t('Phone Number (Required)'),
      '#attributes' => array(
        'pattern' => '[0-9]{7,20}',
      )
    ];

    $form['job'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Job Title'),
      '#required' => TRUE,
      '#placeholder' => $this->t('Job Title (Required)'),
    ];

    $form['company'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Company / Institution'),
      '#required' => TRUE,
      '#placeholder' => $this->t('Company / Institution (Required)'),
    ];

    $form['type'] = [
      '#type' => 'textfield',
      '#title' => $this->t('type'),
      '#required' => TRUE,
      '#placeholder' => $this->t('type'),
    ];

    $form['type-name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('type-name'),
      '#required' => TRUE,
      '#placeholder' => $this->t('type-name'),
    ];
    $form['type-link'] = [
      '#type' => 'textfield',
      '#title' => $this->t('type-link'),
      '#placeholder' => $this->t('type-link'),
    ];

    $form['country'] = [
      '#type' => 'select',
      '#title' => $this->t('Country'),
      '#options' => getCountryOptions(), // Use the global function to get country options
      '#attributes' => ['class' => ['aligned-country-list']],
      '#required' => TRUE,
    ];

    $form['province_state'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Province / State'),
      '#placeholder' => $this->t('Province / State (Required)'),
      '#required' => TRUE,
    ];

    $form[$stepid]['subscribe'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Yes, I would like to receive emails from Norgen Biotek regarding exclusive promotions, scientific conent and industry news.'),
      '#default_value' => 1,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Access Now'),
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

    $first_name = $form_state->getValue('fname');
    $last_name = $form_state->getValue('lname');

    if (empty($first_name) || strlen($first_name) < 2) {
      $form_state->setErrorByName('fname', $this->t('Please enter your first name.'));
    }

    if (empty($last_name) || strlen($last_name) < 2) {
      $form_state->setErrorByName('lname', $this->t('Please enter your last name.'));
    }
  }

  public function submitCallback(array &$form, FormStateInterface $form_state)
  {
   
    $Selector = '#catalogueleadcapture-container';
    $link = $form_state->getValue('type-link');
    $response = new AjaxResponse();

    if ($form_state->hasAnyErrors()) {
      $response = nor_forms_ajax_error($form, $form_state, $Selector);
    } else {
      $response = catalogue_lead_capture_email_sent_ajax($form, $form_state, $Selector, $link);
    }
    return $response;
  }


  public function submitForm(array &$form, FormStateInterface $form_state)
  {

    $first_name = $form_state->getValue('fname');
    $last_name = $form_state->getValue('lname');
    $email = $form_state->getValue('email');
    $phone = $form_state->getValue('phone');
    $job = $form_state->getValue('job');
    $company = $form_state->getValue('company');
    $country = $form_state->getValue('country');
    $country_name = getCountryNames($country);
    $state = $form_state->getValue('province_state');

    $type = $form_state->getValue('type');
    $type_name = $form_state->getValue('type-name');
    setcookie($type_name, "true" , time() + (86400 * 30), "/"); // 86400 = 1 day
    $subscribe = $form_state->getValue('subscribe');
    $date = date("Ymd");
    $form_name = $this->t('Catalogue Lead Capture');


    if (!isset($first_name)) {
      $first_name = 'NULL';
    }
    if (!isset($email)) {
      $email = 'NULL';
    }

    if (!isset($type)) {
      $type = 'NULL';
    }

    if (!isset($type_name)) {
      $type_name = 'NULL';
    }

    $query = \Drupal::database()->insert('forms_to_zoho');
    $query->fields(['created_on', 'first_name', 'last_name', 'email', 'record', 'timestamp', 'form_name', 'phone', 'job_title', 'company', 'country', 'state', 'opt_in', 'catalogue_type','catalogue_type_name']); //wrong syntax here breaks entire submit function 
    $query->values([$date, $first_name, $last_name, $email, '', time(), $form_name, $phone, $job, $company, $country_name, $state, $subscribe, $type, $type_name]);
    $query->execute();

    try { //Zoho upsert
      $zoho = new RecordWrapper('leads');
      $record = [
        'First_Name' => $first_name,
        'Last_Name' => $last_name,
        'Email' => $email,
        'Company' => $company,
        'Job_Position' => $job,
        'Phone' => $phone,
        'Country' => $country_name,
        'State' => $state,
        'Lead_Source' => 'Website Form',
        'Web_Forms' => [$form_name],
      ];
      $upsert_result = $zoho->upsert($record);
    } catch (Exception $e) {
    }


    $output = '<p>Hello,</p>
    <p>A user accessed the catalogue</p>
    <p>First name: ' . $first_name . '<br>Last name: ' . $last_name . '<br>Email: ' . $email . '<br>Phone: ' . $phone . '<br>Job: ' . $job . '<br>Company: ' . $company . '<br>Country: ' . $country_name . '<br>State: ' . $state;

    $time = time();

    if ($form_state->hasAnyErrors()) {
      $subject = '[Catalogue Download error ] - Gated Resource Accessed ' . date("F j, Y, g:i a", $time);
      // $recipient_email = 'sowmya.movva@norgenbiotek.com';
      //$recipient_email = 'support@norgenbiotek.com, sabah.butt@norgenbiotek.com, liam.howes@norgenbiotek.com';
      //$recipient_email = 'sabah.butt@norgenbiotek.com';
      $recipient_email = 'sabah.butt@norgenbiotek.com';
      nor_forms_email_redirect($output, $recipient_email, $subject);
    } else {
      $subject = '[Catalogue Download ] - Gated Resource Accessed ' . date("F j, Y, g:i a", $time);
      // $recipient_email = 'sowmya.movva@norgenbiotek.com';
      //$recipient_email = 'support@norgenbiotek.com, sabah.butt@norgenbiotek.com, liam.howes@norgenbiotek.com';
      //$recipient_email = 'sabah.butt@norgenbiotek.com';
      $recipient_email = 'sabah.butt@norgenbiotek.com';// real addresses
      //$recipient_email = 'liam.howes@norgenbiotek.com';
      nor_forms_email_redirect($output, $recipient_email, $subject);
    }
  }
}

