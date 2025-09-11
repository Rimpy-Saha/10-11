<?php

namespace Drupal\covid_individual\Form;

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
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\commerce_cart\CartManagerInterface;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_product\Entity\ProductVariationInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CovidIndividualForm extends FormBase {

  public function getFormId() {
    return 'covid_individual';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {


    $form['description'] = [
      '#type' => 'item',
      '#markup' => $this->t('This example shows an add-more and a remove button.'),
    ];

    $form['#prefix'] = '<div id="norgen_form-container" class="norgen_form-container">';
    // $form['step1'] = [
    //   '#type' => 'container',
    //   '#attributes' => ['id' => 'step1'],
    //   'description' => [
    //       '#type' => 'item',
    //       '#markup' => $this->t('This is step 1.'),
    //   ]];
    $points ='<div class="symptom-check">
    <h1>Donor Consent</h1>
    <!-- <p style="margin-bottom: 30px; text-align: left;"><b>For SAME-DAY results, please make an appointment before 2:30 p.m. If your appointment is scheduled after 3:00 p.m., you will receive your results the following business day.</b></p> -->
    <p style="margin-bottom: 30px; text-align: left;color:#be1e2d"><b>If a COVID-19 Test is required for a medical procedure, it is the Donor\'s responsibility to check with their healthcare provider to ensure the saliva sample is collected within the required timeline.</b></p>
    <p style="margin-bottom: 30px; text-align: left;color:#be1e2d"><b>Each Donor must meet the below eligibility criteria to submit biological samples for testing at Norgen Biotek.</b></p>
      <!--<label class="mleft" for="symptoms1">
          <input type="checkbox" id="symptoms1" name="fever" value="1">
          <div class="fake-check"><i class="fas fa-check" aria-hidden="true"></i></div>
          <span>The Donor has <b>not</b> travelled outside of Canada in the last 14 days.</span>
      </label>
      <label class="mleft" for="symptoms2">
          <input type="checkbox" id="symptoms2" name="cough" value="2">
          <div class="fake-check"><i class="fas fa-check" aria-hidden="true"></i></div>
          <span>The Donor has <b>not</b> been identified as a close contact of someone who has tested positive for or is being tested for COVID-19. A close contact is defined as someone who has provided care, lived with, or has otherwise had close, prolonged contact with a probable or confirmed case while the case was ill.</span>
      </label>
      <label class="mleft" for="symptoms3">
          <input type="checkbox" id="symptoms3" name="breathing" value="3">
          <div class="fake-check"><i class="fas fa-check" aria-hidden="true"></i></div>
          <span>The Donor has <b>not</b> been informed that they have tested positive (+) for COVID-19 in the past.</span>
      </label>
      <label class="mleft" for="symptoms4">
          <input type="checkbox" id="symptoms4" name="fatigue" value="4">
          <div class="fake-check"><i class="fas fa-check" aria-hidden="true"></i></div>
          <span>The Donor is <b>not</b> exhibiting any of the following symptoms related to SARS-CoV-2 (COVID-19) infection: Fever, Cough, Shortness of breath, Sore throat, Difficulty swallowing, Decrease or loss of sense of taste or smell, Chills, Headaches, Fatigue, Muscle aches, Nausea/vomiting, Diarrhea, Abdominal pain, Pink eye (conjunctivitis), Runny nose or nasal congestion</span>
      </label>
      <label class="mleft" for="symptoms5">
          <input type="checkbox" id="symptoms5" name="aches" value="5">
          <div class="fake-check"><i class="fas fa-check" aria-hidden="true"></i></div>
          <span>The Donor has <b>not</b> been told to isolate by a doctor, health care provider, or public health unit.</span>
      </label>
      <label class="mleft" for="symptoms6">
          <input type="checkbox" id="symptoms6" name="headache" value="6">
          <div class="fake-check"><i class="fas fa-check" aria-hidden="true"></i></div>
          <span>The Donor has <b>not</b> received a COVID Alert exposure notification on their cell phone in the past 14 days and has NOT gotten a negative test result.</span>
      </label>
      <label class="mleft" for="symptoms7">
          <input type="checkbox" id="symptoms7" name="throat" value="7">
          <div class="fake-check"><i class="fas fa-check" aria-hidden="true"></i></div>
          <span>The Donor is <b>not</b> over the age of 70 and is NOT experiencing any of the following symptoms: delirium, unexplained or increased number of falls, acute functional decline, and/or worsening chronic conditions.</span>
      </label>
      <label class="mleft" for="location">
          <input type="checkbox" id="location" name="location" value="locationPass">
          <div class="fake-check"><i class="fas fa-check" aria-hidden="true"></i></div>
          <span>The Donor <b>is</b> located in the province of Ontario, Canada.</span>
      </label>-->
      <ol align="left">
        <li>I confirm that I am not currently eligible for Molecular Testing for COVID-19 with the Ministry of Health (i.e. testing covered by OHIP) according to the most recent Ministry of Health guidelines.</li>
        <li>If this COVID-19 Diagnostic Test is being conducted as part of a program though my school, employer, health care professional or any other third party organization (pharmacy, clinic etc.), I hereby authorize Norgen Biotek to release my personal information, along with my COVID-19 test result, to the third party as listed above.</li>
        <li>I understand that my personal information, along with my COVID-19 test result, will be reported to the Ontario Provincial Government via OLIS (Ontario Laboratory Information System) as well as the regional health authority.</li>
        <li>I understand that if my test results are positive (+) for the SARS-CoV-2 virus, it is my responsibility to immediately self-isolate and follow the recommended provincial guidelines.</li>
        <li>I understand that an individual must be trained to supervise the saliva collection by reading the instructions and watching the instructional video on proper saliva collection.</li>
        <li>I understand that my saliva collection is to be performed by supervised self-collection.</li>
        <li>I understand that I cannot receive a TravelSafe test unless I have my identity verified by my passport in-person at Norgen Biotek or in-person at the company providing this testing service.</li>
        <li>I understand that Norgen Biotek or any other third party organization providing this test is not acting as my medical provider, and this COVID-19 test does not replace treatment by my medical provider. I assume full responsibility to take appropriate action with regards to my test results. I agree that I will seek medical advice, care, or treatment from my medical provider if I have questions or concerns, or if my condition worsens.</li>
      </ol>
      <hr>
      <div>
        <p>For more information about eligibility requirements please refer to the <a href="https://www.health.gov.on.ca/en/pro/programs/publichealth/coronavirus/docs/contact_mngmt/management_cases_contacts_omicron.pdf" target="_blank"> provided documentation from Ontario Ministry of Health</a> </p>
      </div>
      <hr></div>';
    $form['step1'] = [
      '#type' => 'container',
      '#prefix' => '<div id="step1"> This is Step 1'.$points.' ',
      '#suffix' => '</div>',
      // '#attributes' => ['style' => ['display:flex;']],
    ];
    $form['step1']['accept_terms'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Myself (Donor), Legal Guardian of Donor(s), OR person purchasing the COVID-19 test on behalf of the Donor(s) agrees to have read and accept the terms and conditions as outlined above in this COVID-19 DIAGNOSTIC TESTING AGREEMENT.'),
      '#required' => TRUE,
    ];
    // $form['step1']['continue'] = [
    //   '#type' => 'submit',
    //   '#value' => $this->t('Continue'),
    //   '#button_type' => 'primary',
    //   '#validate' => ['::validateTermsAndContinue'],
    //   '#ajax' => [
    //     'callback' => '::showAjaxForm',
    //     'event' => 'click',
    //     'method' => 'replace']
    // ];
    $form['step1']['actions']['continue'] = [
      '#type' => 'submit',
      '#value' => $this->t('Continue'),
      '#validate' => ['::validateTermsAndContinue'],
      '#ajax' => [
        'callback' => '::showAjaxFormSet',
      ],
    ];
    
    $form['step2'] = [
      '#type' => 'container',
      '#prefix' => '<div class = "visually-hidden" id="step2">',
      '#suffix' => '</div>',
      // '#attributes' => ['style' => ['display:flex;']],
    ];

    $form['step2']['norgen_fname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Name'),
      '#default_value' => 'Test',
      '#required' => TRUE,
    ];

    $form['step2']['norgen_lname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last Name'),
      '#default_value' => 'Test',
      '#required' => TRUE,
    ];

    $form['step2']['norgen_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#default_value' => 'david@norgenbio.com',
      '#required' => TRUE,
    ];




    $form['step2']['quantity'] = [
      '#type' => 'number',
      '#title' => $this->t('How many people wiill be tested? '),
      '#description' => $this->t('If you are purchasing 2 or more COVID-19 Test Kits, all test kits will be shipped to the delivery address provided. If you require the COVID-19 Test Kits to be shipped to multiple delivery addresses, please place a separate order for each.'),
      '#default_value' => '1',
      '#min' => 1,  // Set the minimum value to 1
    ];
    $form['step2']['supervision'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Do you live alone or otherwise require someone to act as supervisor?'),
      
    );
    $form['step2']['google_recaptcha'] = array(
      '#type'=> 'captcha',
      '#captcha_type' => 'recaptcha/reCAPTCHA',
    );
    $form['step2']['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#button_type' => 'primary',
      '#ajax' => ['callback' => '::submitAjaxForm',
        'event' => 'click',
        ]
    ];


       $form['#suffix'] = '</div>';


        return $form;
    }


/**
     * Callback for both ajax-enabled buttons.
     *
     * Selects and returns the fieldset with the names in it.
     */
  public function showAjaxForm(array &$form, FormStateInterface $form_state) {
     return $form;
  }

  /**
   * Submit handler for the "add-one-more" button.
   *
   * Increments the max counter and causes a rebuild.
   */
  public function showAjaxFormSet(array &$form, FormStateInterface $form_state) {
    // Check if the checkbox is checked
    $acceptTerms = $form_state->getValue('accept_terms');
    $response = new AjaxResponse();

    if ($acceptTerms) {
        // Hide step 1 and show step 2
        $response->addCommand(new InvokeCommand('#step1', 'addClass', ['visually-hidden']));
        $response->addCommand(new InvokeCommand('#step2', 'removeClass', ['visually-hidden']));
    } else {
        // If the checkbox is not checked, set an error
        $form_state->setErrorByName('accept_terms', $this->t('You must agree to the terms and conditions.'));
        // Re-render the form with the error message
        $response->addCommand(new ReplaceCommand(NULL, $form));
    }

    return $response;
  }
    public function validateTermsAndContinue(array &$form, FormStateInterface $form_state) {
      if (!$form_state->getValue('accept_terms')) {
          $form_state->setErrorByName('accept_terms', $this->t('You must agree to the terms and conditions.'));
      }
    }
    public function validateForm(array &$form, FormStateInterface $form_state) {
      
    }

    // public function showAjaxForm(array &$form, FormStateInterface $form_state) 
    // {

    //   $acceptTerms = $form_state->getValue('accept_terms');
    //   $response = new AjaxResponse();

    //   // If terms are accepted, hide step 1 and show step 2
    //   if ($acceptTerms) {
    //       $response->addCommand(new InvokeCommand('#step1', 'addClass', ['visually-hidden']));
    //       $response->addCommand(new InvokeCommand('#step2', 'removeClass', ['visually-hidden']));
    //   } else {
    //       // If terms are not accepted, show an error message
    //       $form_state->setErrorByName('accept_terms', $this->t('You must agree to the terms and conditions.'));
    //       $response->addCommand(new ReplaceCommand(NULL, $form));
    //   }

    //   return $response;

    // }


    //public function submitForm(array &$form, FormStateInterface $form_state) 
    public function submitAjaxForm(array &$form, FormStateInterface $form_state) 
    {

      $first_name = $form_state->getValue('fname');
      $last_name = $form_state->getValue('lname');
      $email = $form_state->getValue('email');
      $output = '<p><span style="color:red">Test Output 2</span></p><p>This includes html from form 1! <br />First Name: ' . $first_name . ' Last Name: ' . $last_name . '</p>';
      // nor_add_cart_create_order(3, 3);
      // $productId = $quantity =26;
      // $sku='COVTEST2';
      // $variationId =getVariationIdBySku($sku);
      $variationId = 1100;//get cov test target id
      $variation = \Drupal::entityTypeManager()->getStorage('commerce_product_variation')->load($variationId);
     
      
      // $isGuestCheckout = FALSE; // Set this to TRUE if you want to allow guest checkout

      // $result = addToCart($variation, $quantity); // this function is within this module

      // Get the service from the container.

      // include_once 'var/www/drupal/web/modules/contrib/nor_add_to_cart_func/nor_add_to_cart_func.module';
      
      $quantity =  $form_state->getValue('quantity');
      $cart_id = addToCart($variation, $quantity);
      $order = \Drupal::entityTypeManager()->getStorage('commerce_order')->load($cart_id);
      $supervisionChecked = $form_state->getValue('supervision');
      if ($supervisionChecked) {
        // Get the current order
        // $order = \Drupal::entityTypeManager()->getStorage('commerce_order')->load($cart_id);

        // Check if the order is an instance of OrderInterface
        if ($order instanceof OrderInterface) {
          $field_name = 'field_field_special_instructions';
          $field_definition = $order->getFieldDefinition($field_name);

          // Check if the field definition exists.
          if ($field_definition) 
          {
            // Update the special instructions field
            $order->set($field_name, 'The user will require a zoom appointment for the test');
            $order->save();
          }
        }
      }

      // $message = json_encode($result);
      // $form_state->setErrorByName('norgen_fname', $this->t($message));
      $Selector = '#norgen_form-container';

      $Content = '<div class="my_top_message">Form Submitted Successfully</div>';
      $response = new AjaxResponse();
      if ($form_state->hasAnyErrors()) {

        $response->addCommand(new ReplaceCommand('#norgen_form-container', $form));
        $messages = \Drupal::messenger()->deleteAll();
        return $response; 
      } 
      
      else 
      {
        // norgen_submit_email($output);
        $response->addCommand(new HtmlCommand($Selector,$Content));
        $response->addCommand(new ReplaceCommand(NULL, $form));

        // $response1 = new RedirectResponse('/cart');
        // $response1->send();
        // return $response1;
        return $response; 
      }

    }
    public function submitForm(array &$form, FormStateInterface $form_state) {




      
    }


    function getColumnFromCSV($filePath, $columnIndex) {
        $columnValues = [];
       
        if (($handle = fopen($filePath, "r")) !== false) {
            // Read the remaining rows and retrieve the column values
            fgetcsv($handle);
            while (($data = fgetcsv($handle)) !== false) {
                if (isset($data[$columnIndex])) {
                    $columnValues[] = $data[$columnIndex];
                }
            }
            
            fclose($handle);
        }
        
        return $columnValues;
    }

    function csvHeader($filePath) {
      $headerTitles = [];
      $columnIndices = [];
    
      if (($handle = fopen($filePath, "r")) !== false) {
        // Read the first row (header)
        $header = fgetcsv($handle);
    
        // Check for each header title in any order
        foreach (['SKU', 'Cat No.', 'Quantity', 'Name'] as $headerTitle) {
          $found = false;
    
          for ($i = 0; $i < count($header); $i++) {
            if (strtolower($headerTitle) === strtolower($header[$i])) {
              $headerTitles[] = $headerTitle;
              $columnIndices[$headerTitle] = $i;
              $found = true;
              break;
            }
          }
    
          // If the header title is not found, set the corresponding index to -1
          if (!$found) {
            $columnIndices[$headerTitle] = -1;
          }
        }
    
        fclose($handle);
      }
    
      return $columnIndices;
    }


}