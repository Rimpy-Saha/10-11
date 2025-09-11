<?php

namespace Drupal\newsletter_subscription\Form;

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


class NewsletterSubscriptionForm extends FormBase
{

  public function getFormId()
  {
    return 'newsletter_subscription_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {

    $form['#prefix'] = '<div id ="newsletter-container" div class="newsletter-container">';

    $form['header'] = [
      '#type' => 'markup',
      '#markup' => '<h2>Join over 10,000 scientists, bioinformaticians and researchers who receive our exclusive deals, industry updates and more, directly to their inbox!</h2>',
    ];

    $form['newsletter_fname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Name'),
      '#default_value' => nor_forms_user_first_name(),
      '#required' => TRUE,
    ];

    $form['newsletter_lname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last Name'),
      '#default_value' => nor_forms_user_last_name(),
      '#required' => TRUE,
    ];

    $form['newsletter_phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Phone Number'),
      '#maxlength' => 20,
      '#required' => TRUE,
    ];;

    $form['newsletter_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#default_value' => nor_forms_user_email(),
      '#required' => TRUE,
    ];

    $form['newsletter_company'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Company Name'),
    ];

    $form['newsletter_country'] = [
      '#type' => 'select',
      '#title' => $this->t('Country'),
      '#options' => getCountryOptions(),
      '#required' => TRUE,
      '#attributes' => ['class' => ['aligned-country-list']],
    ];


    $form['newsletter_about'] = [
      '#type' => 'select',
      '#title' => $this->t('How Did You Hear About Us'),
      '#options' => [
        'option1' => $this->t('Google'),
        'option2' => $this->t('Social Media'),
        'option3' => $this->t('Company Website'),
        'option4' => $this->t('Blog'),
        'option5' => $this->t('Email'),
        'option6' => $this->t('Referral'),
        'option7' => $this->t('Distributor'),
        'option8' => $this->t('Other'),
      ],
    ];

    $form['newsletter_interests'] = [
      '#type' => 'fieldset',
      '#title' => t('Interests'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    ];

    $form['interests']['myfield'] = [
      '#type' => 'checkboxes',
      '#options' => [
        'Products' => t('Products'),
        'Services' => t('Services'),
        'MasterClass Webinars' => t('MasterClass Webinars'),
        'Blogs' => t('Blogs'),
        'Exclusive Offers' => t('Exclusive Offers'),
        'Other' => t('Other'),
      ],
    ];

    $form['newsletter_recommendation'] = [
      '#type' => 'radios',
      '#options' => [
        1 => 'Not at all',
        2 => 'Rarely',
        3 => 'Sometimes',
        4 => 'Often',
        5 => 'Always',
        6 => 'Definitely',
        7 => 'Highly likely',
        8 => 'Almost certainly',
        9 => 'Almost guaranteed',
        10 => 'Guaranteed',
      ],
      '#title' => t('How likely are you to recommend this product to a colleague?'),
      '#attributes' => [
        'class' => ['recommendation-scale'],
      ],
    ];

    $form['newsletter_agree'] = [
      '#type' => 'checkbox',
      '#title' => t('I agree to the Privacy Policy and Terms and Conditions'),
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

    $first_name = $form_state->getValue('newsletter_fname');
    $last_name = $form_state->getValue('newsletter_lname');

    if (empty($first_name) || strlen($first_name) < 2) {
      $form_state->setErrorByName('newsletter_fname', $this->t('Please enter your first name.'));
    }

    if (empty($last_name) || strlen($last_name) < 2) {
      $form_state->setErrorByName('newsletter_lname', $this->t('Please enter your last name.'));
    }
    if ($first_name && $last_name && $first_name === $last_name) {
      
      $form_state->setErrorByName('newsletter_lname', $this->t('Last name should not be the same as first name.'));
    }
    if ($first_name && $last_name && strlen($last_name) >= 6 && strpos($last_name, $first_name) !== false) {
      
      $form_state->setErrorByName('newsletter_lname', $this->t('Last name should not contain first name for 6 or more characters.'));
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

    $Selector = '#newsletter-container';
    $response = new AjaxResponse();

    if ($form_state->hasAnyErrors()) {
      $response = nor_forms_ajax_error($form, $form_state, $Selector);
      return $response;
    } else {

      $response = nor_forms_email_sent_ajax($form, $form_state, $Selector);

      // $form_file = $form_state->getValue('account_coverletter', 0);
      // if (isset($form_file[0]) && !empty($form_file[0])) {
      //   $file = File::load($form_file[0]);
      //   $file->setPermanent();
      //   $file->save();

      //   $file_path = $file->getFileUri(); 

      //   $form_state->set('file_path', $file_path);
      // }

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

    $first_name = $form_state->getValue('newsletter_fname');
    $last_name = $form_state->getValue('newsletter_lname');
    $email = $form_state->getValue('newsletter_email');
    $country_code = $form_state->getValue('newsletter_country');
    $phone = $form_state->getValue('newsletter_phone');
    $company = $form_state->getValue('newsletter_company');
    $about = $form_state->getValue('newsletter_about');
    $interests = $form_state->getValue('myfield');
    $formatted_interests = implode(', ', array_filter($interests));
    $recommendations = $form_state->getValue('newsletter_recommendation');
    $agree = $form_state->getValue('newsletter_agree');
    $country_code = $form_state->getValue('newsletter_country');

    $date = date("Ymd");
    $form_name = $this->t('Newsletter Subscription');

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
    $query->fields(['created_on', 'first_name', 'last_name', 'email', 'record', 'timestamp', 'form_name', 'phone', 'company', 'country']); //wrong syntax here breaks entire submit function 
    $query->values([$date, $first_name, $last_name, $email, '', time(), $form_name, $phone, $company, $country_name]);
    $query->execute();

    try {  //Zoho upsert

      $zoho = new RecordWrapper('leads');
      $record = [
        'First_Name' => $first_name,
        'Last_Name' => $last_name,
        'Email' => $email,
        'Company' => $company,
        'Phone' => $phone,
        'Country' => $country_name,
        'Lead_Source' => 'Website Form',
        'Web_Forms' => [$form_name],
      ];
      $upsert_result = $zoho->upsert($record);
    } catch (Exception $e) {
    }


    $about_label = $form['newsletter_about']['#options'][$about];
    $recommendations_label = $form['newsletter_recommendation']['#options'][$recommendations];

    $output = '<p>Hello,</p>
    <p>A customer submitted their request for The Newsletter Subscription Form.</p>
    <p>Last name: ' . $last_name . '<br>First name: ' . $first_name . '<br>Email: ' . $email . '<br>Primary Phone: ' . $phone . '<br>Company: ' . $company . '<br>About: ' . $about_label . '<br>Interests: ' . $formatted_interests . '<br>Recommendations: ' . $recommendations_label . '<br>Agree: ' . ($agree ? 'Yes' : 'No');

    $time = time();

    if ($form_state->hasAnyErrors()) {
    } else {
      $subject = '[Contact form] - Newsletter Subscription ' . date("F j, Y, g:i a", $time);
      // $recipient_email = 'sowmya.movva@norgenbiotek.com';
      $recipient_email = 'emarketing@norgenbiotek.com';// real addresses
      //$recipient_email = 'liam.howes@norgenbiotek.com';
      nor_forms_email_redirect($output, $recipient_email, $subject);
    }
  }
}
