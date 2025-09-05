<?php

namespace Drupal\contact_chineese\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\file\Entity\File;
use Drupal\nor_forms\Libraries\Zoho\RecordWrapper;

class ContactChineeseForm extends FormBase
{

  public function getFormId()
  {
    return 'contact_chineese_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {

    $form['#prefix'] = '<div id = "contactchineeseform-container" div class="contactchineeseform-container">';

    $form['contacter_fname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('名'),
      '#required' => TRUE,
    ];

    $form['contacter_lname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('姓'),
      '#required' => TRUE,
    ];

    $form['contacter_company'] = [
      '#type' => 'textfield',
      '#title' => $this->t('公司/机构'),
      '#required' => TRUE,
    ];

    $form['contacter_job'] = [
      '#type' => 'textfield',
      '#title' => $this->t('职称'),
      '#required' => TRUE,
    ];

    $form['contacter_email'] = [
      '#type' => 'email',
      '#title' => $this->t('电子邮件'),
      '#required' => TRUE,
    ];

    $form['contacter_phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('电话'),
      '#required' => TRUE,
    ];

    $form['contacter_message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('信息'),
      '#required' => TRUE,
    ];

    $form['google_recaptcha'] = [
      '#type'=> 'fieldset',
      '#description' => '<div class="g-recaptcha" data-sitekey="6Lcr4u0pAAAAAGj32knXkUzuHAXzj3CoAhtbJ1t5"></div>',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('发送您的电子邮件'),
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

    $first_name = $form_state->getValue('contacter_fname');
    $last_name = $form_state->getValue('contacter_lname');

    if (empty($first_name) || strlen($first_name) < 2) {
      $form_state->setErrorByName('contacter_fname', $this->t('Veuillez entrer votre prénom.'));
    }

    if (empty($last_name) || strlen($last_name) < 2) {
      $form_state->setErrorByName('contacter_lname', $this->t('Veuillez entrer votre nom de famille.'));
    }
    if ($first_name && $last_name && $first_name === $last_name) {
      $form_state->setErrorByName('contacter_lname', $this->t('Le nom de famille ne doit pas être le même que le prénom.'));
    }
    if ($first_name && $last_name && strlen($last_name) >= 6 && strpos($last_name, $first_name) !== false) {
      $form_state->setErrorByName('contacter_lname', $this->t('Le nom de famille ne doit pas contenir le prénom sur 6 caractères ou plus.'));
    }

    if (isset($_POST['g-recaptcha-response']) && $_POST['g-recaptcha-response'] != '') {
      $captcha_response = $_POST['g-recaptcha-response'];
      $remote_ip = $_SERVER['REMOTE_ADDR'];
  
      $result = $this->verifyGoogleRecaptcha($captcha_response, $remote_ip);
  
      $data = json_decode($result, true);
  
      if (!$data['success']) {
          $form_state->setErrorByName('google_recaptcha', t('Veuillez compléter le captcha'));
      }
    } else {
        $form_state->setErrorByName('google_recaptcha', t('Veuillez compléter la vérification reCAPTCHA.'));
    }
    if (empty($_POST['g-recaptcha-response'])) {
        $form_state->setErrorByName('google_recaptcha', t('Veuillez compléter la vérification reCAPTCHA.'));
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

    $Selector = '#contactchineeseform-container';
    $response = new AjaxResponse();

    if ($form_state->hasAnyErrors()) {
      $response = nor_forms_ajax_error($form, $form_state, $Selector);
    } else {

      $response = nor_forms_email_sent_ajax($form, $form_state, $Selector);
    }
    return $response;
  }


  public function submitForm(array &$form, FormStateInterface $form_state)
  {

    $first_name = $form_state->getValue('contacter_fname');
    $last_name = $form_state->getValue('contacter_lname');
    $email = $form_state->getValue('contacter_email');
    $phone = $form_state->getValue('contacter_phone');
    $job = $form_state->getValue('contacter_job');
    $company = $form_state->getValue('contacter_company');
    $message = $form_state->getValue('contacter_message');
    $date = date("Ymd");
    $form_name = $this->t('Contact Us Chinese');


    if (!isset($first_name)) {
      $first_name = 'NULL';
    }
    if (!isset($email)) {
      $email = 'NULL';
    }

    $query = \Drupal::database()->insert('forms_to_zoho');
    $query->fields(['created_on', 'first_name', 'last_name', 'email', 'record', 'timestamp', 'form_name', 'phone', 'company', 'job_title', 'notes']); //wrong syntax here breaks entire submit function 
    $query->values([$date, $first_name, $last_name, $email, '', time(), $form_name, $phone, $company, $job, $message]);
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
        'Lead_Source' => 'Website Form',
        'Web_Forms' => [$form_name],
      ];
      $upsert_result = $zoho->upsert($record);
    } catch (Exception $e) {
    }


    $output = '<p>Hello,</p>
    <p>A customer submitted their request for Contact us Chinese Form.</p>
    <p>Last name: ' . $last_name . '<br>First name: ' . $first_name . '<br>Email: ' . $email . '<br>Primary Phone: ' . $phone . '<br>Company: ' . $company . '<br>Job: ' . $job . '<br>Message: ' . $message;

    $time = time();

    if ($form_state->hasAnyErrors()) {
    } else {
      $subject = '[Contact form] - Contact Us Chinese ' . date("F j, Y, g:i a", $time);
      // $recipient_email = 'andrii.omelchuk@norgenbiotek.com';
      $recipient_email = 'info@norgenbiotek.com,sebastian.szopa@norgenbiotek.com';// real addresses
      //$recipient_email = 'liam.howes@norgenbiotek.com';
      nor_forms_email_redirect($output, $recipient_email, $subject);
    }
  }
}
