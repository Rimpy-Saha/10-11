<?php
namespace Drupal\request_sample\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\MessageCommand;
use Drupal\Core\Ajax\AppendCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Ajax\AfterCommand;
use Drupal\nor_forms\Libraries\Zoho\RecordWrapper;

class RequestSampleForm extends FormBase
{
  public function getFormID()
  {
    return 'request_sample_form';
  }

  //public $samples = getSamples();
  private $samples = '';

  public function buildForm(array $form, FormStateInterface $form_state){

    $form['#prefix'] = '<div id="sample-form-container">';

    $form['title'] = [
      '#type' => 'markup',
      '#markup' => '<h2 class="center">Request a Sample</h2>',
    ];

    $form['message'] = [
      '#type' => 'markup',
      '#markup' => '<div id="result-message"></div>',
    ];

    // UTM Parameters

    $current_uri = \Drupal::request()->getRequestUri();
    $url_components = parse_url($current_uri);
    $params = array();
    if(isset($url_components['query'])) parse_str($url_components['query'], $params);

    $form['utm_source'] = [
      '#type' => 'hidden',
      '#title' => $this->t('UTM Source'),
      '#value' => isset($params['utm_source'])?$params['utm_source']:null,
    ];
    $form['utm_medium'] = [
      '#type' => 'hidden',
      '#title' => $this->t('UTM Medium'),
      '#value' => isset($params['utm_medium'])?$params['utm_medium']:null,
    ];
    $form['utm_campaign'] = [
      '#type' => 'hidden',
      '#title' => $this->t('UTM Campaign'),
      '#value' => isset($params['utm_campaign'])?$params['utm_campaign']:null,
    ];
    $form['utm_id'] = [
      '#type' => 'hidden',
      '#title' => $this->t('UTM Id'),
      '#value' => isset($params['utm_id'])?$params['utm_id']:null,
    ];
    $form['utm_term'] = [
      '#type' => 'hidden',
      '#title' => $this->t('UTM Term'),
      '#value' => isset($params['utm_term'])?$params['utm_term']:null,
    ];
    $form['utm_content'] = [
      '#type' => 'hidden',
      '#title' => $this->t('UTM Content'),
      '#value' => isset($params['utm_content'])?$params['utm_content']:null,
    ];

    // End of UTM Parameters 

    $samples = $this->getSamples();

    //$samples = $this->getProductVariations();

    $steps = [1, 2, 3];

    
    $sample_current_step_id = $form_state->get('sample_current_step_id');
    // Set the initial current step ID if not set
    if ($sample_current_step_id == null) {
      $form_state->set('sample_current_step_id', $steps[0]);
      $sample_current_step_id = $form_state->get('sample_current_step_id');
    }
    
    $this->stepid = $sample_current_step_id;

    // Add fields specific to each step here
    foreach ($steps as $step) {
      switch ($step){
        case 1:
          //step 1
          if($sample_current_step_id == $step){
            $form[$step] = [
              '#type' => 'container',
              '#prefix' => '<div id="step1">',
              '#suffix' => '</div>',
            ];
          }
          else{
            $form[$step] = [
              '#type' => 'container',
              '#prefix' => '<div id="step1">',
              '#suffix' => '</div>',
              '#attributes' => [
                'class' => ['hidden'],
              ],
            ];
          }

          $form[$step]['progress'] = [
            '#type' => 'item',
            '#markup' => '<progress value="3" max="10" class="norgen-progress-wrapper center">30%</progress>',
            '#weight' => 0,
          ];

          $form[$step]['hr_separator'] = [
            '#type' => 'item',
            '#markup' => '<hr>',
            '#weight' => 2,
          ];

          // Error message container for Step One
          $form[$step]['erroralert'] = [
            '#type' => 'markup',
            '#prefix' => '<div id="step1-erroralert">',
            '#suffix' => '</div>',
            '#weight' => 2,
          ];

          $form[$step]['samples-list-fieldset'] = [
            '#type' => 'fieldset',
            '#title' => 'Product Samples of Interest (Maximum of 2)&nbsp;<span class="form-required" title="This field is required.">*</span>',
            '#attributes' => [
              'class' => ['samples-list-fieldset'],
              'id' 
            ],
            '#weight' => 2,
          ];

          $form[$step]['samples-list-fieldset']['samples-search'] = [
            '#type' => 'search',
            '#attributes' => [
              'placeholder' => 'Search products by SKU, name, or size',
              'class' => ['filter-samples-search'],
              'onkeyup' => 'filterSamples()',
              'onsearch' => 'filterSamples()',
            ],
            '#weight' => 2,
          ];

          $form[$step]['samples-list-fieldset']['samples'] = [
            '#type' => 'tableselect',
            '#header' => [
              'sku' => t('SKU'),
              'product_name' => t('Product Name'),
              'size' => t('Size'),
            ],
            '#options' => [],
            '#multiple' => TRUE,
            '#js_select' => FALSE, // Disable the "Select all" checkbox
            '#prefix' => '<div class="samples-wrapper">',
            '#suffix' => '</div>',
            '#ajax' => [
              'callback' => '::changeSelectedProducts',
              'wrapper' => 'sample_information', 
              'event' => 'change', 
            ],
            '#weight' => 2,
          ];

          foreach ($samples as $sample) {
            $form[$step]['samples-list-fieldset']['samples']['#options'][$sample->sku] = [
              'sku' => $sample->sku,
              'product_name' => $sample->title,
              'size' => $sample->size,
              '#attributes' => ['class' => ['example-form-checkbox']],
            ];
          };

          // set all the pre-selected samples as default values
          $default_samples = [];
          if(isset($form_state->getBuildInfo()['default_sample']) && is_array($form_state->getBuildInfo()['default_sample'])){
            foreach($form_state->getBuildInfo()['default_sample'] as $sku){
              $default_samples[$sku] = TRUE;
            }
          }

          $form[$step]['samples-list-fieldset']['samples']['#default_value'] = $default_samples;

          $form[$step]['disclaimer'] = [
            '#type' => 'item',
            '#markup' => '<div class="form-msg">Sample kits are free, however, customer must pay for any shipping costs.<br>Sample kits are available only in Canada and the U.S.<br>Internatonal customers, please <a href="/resources/distributors" style="color:#fff;text-decoration:underline;">find your local distributor</a> for sample kit inquiries.</div>',
            '#weight' => 2,
          ];

          break;



        case 2:
          //step 2 
          if($sample_current_step_id == $step){
            $form[$step] = [
              '#type' => 'container',
              '#prefix' => '<div id="step2">',
              '#suffix' => '</div>',
            ];
          }
          else{
            $form[$step] = [
              '#type' => 'container',
              '#prefix' => '<div id="step2">',
              '#suffix' => '</div>',
              '#attributes' => [
                'class' => ['hidden'],
              ],
            ];
          }

          $form[$step]['progress'] = [
            '#type' => 'item',
            '#markup' => '<progress value="6" max="10" class="norgen-progress-wrapper center">60%</progress>',
            '#weight' => 0,
          ];

          $form[$step]['hr_separator'] = [
            '#type' => 'item',
            '#markup' => '<hr>',
            '#weight' => 2,
          ];

          // Error message container for Step Two
          $form[$step]['error_message'] = [
            '#type' => 'markup',
            '#prefix' => '<div id="step2-error-message">',
            '#suffix' => '</div>',
            '#weight' => 2,
          ];

          $form[$step]['step2-validate'] = [
            '#type' => 'markup',
            '#markup' => '<div id="step2-message"></div>',
            '#weight' => 2,
          ];

          $form[$step]['customer_information'] = [
            '#type' => 'fieldset',
            '#title' => 'Contact Information',
            '#attributes' => [
              'class' => ['customer-information'],
            ],
            '#weight' => 2,
          ];
          $form[$step]['customer_information']['sample_fname'] = [
            '#type' => 'textfield',
            '#title' => 'First Name <span class="form-required" title="This field is required.">*</span>',
            '#placeholder' => 'First Name (Required)',
            // '#required' => TRUE,
          ];

          $form[$step]['customer_information']['sample_lname'] = [
            '#type' => 'textfield',
            '#title' => 'Last Name <span class="form-required" title="This field is required.">*</span>',
            '#placeholder' => 'Last Name (Required)',
            // '#required' => TRUE,
          ];

          $form[$step]['customer_information']['sample_email'] = [
            '#type' => 'email',
            '#title' => 'Email Address <span class="form-required" title="This field is required.">*</span>',
            '#placeholder' => 'Email Address (Required)',
            // '#required' => TRUE,
          ];

          $form[$step]['customer_information']['sample_phone'] = [
            '#type' => 'tel',
            '#title' => 'Phone Number <span class="form-required" title="This field is required.">*</span>',
            '#placeholder' => 'Phone Number (Required)',
            // '#required' => TRUE,
          ];

          $form[$step]['customer_information']['sample_company'] = [
            '#type' => 'textfield',
            '#title' => 'Company/ Institution <span class="form-required" title="This field is required.">*</span>',
            '#placeholder' => 'Company/ Institution (Required)',
            // '#required' => TRUE,
          ];

          $form[$step]['customer_information']['job_title'] = [
            '#type' => 'textfield',
            '#title' => 'Job Title <span class="form-required" title="This field is required.">*</span>',
            '#placeholder' => 'Job Title (Required)',
            //'#required' => TRUE,
          ];

          $form[$step]['customer_information']['sample_subscribe'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Subscribe to our mailing list and be the first to hear about offers and news from Norgen Biotek.'),
            '#suffix' => '<div class="disclaimer">It is our responsibility to protect and guarantee that your data will be completely confidential. You can unsubscribe from Norgen emails at any time by clicking the link in the footer of our emails. For more information please view our <a href="/content/privacy-policy">Privacy Policy</a>.</div>',
          ];
          break;
        case 3:

          //step 3
          if($sample_current_step_id == $step){
            $form[$step] = [
              '#type' => 'container',
              '#prefix' => '<div id="step3">',
              '#suffix' => '</div>',
            ];
          }
          else{
            $form[$step] = [
              '#type' => 'container',
              '#prefix' => '<div id="step3">',
              '#suffix' => '</div>',
              '#attributes' => [
                'class' => ['hidden'],
              ],
            ];
          }

          $form[$step]['progress'] = [
            '#type' => 'item',
            '#markup' => '<progress value="9" max="10" class="norgen-progress-wrapper center">90%</progress>',
            '#weight' => 0,
          ];

          $form[$step]['hr_separator'] = [
            '#type' => 'item',
            '#markup' => '<hr>',
            '#weight' => 2,
          ];

          // Error message container for Step Three
          $form[$step]['error_message3'] = [
            '#type' => 'markup',
            '#prefix' => '<div id="step3-error-message3">',
            '#suffix' => '</div>',
            '#weight' => 2,
          ];

          $form[$step]['shipping'] = [
            '#type' => 'fieldset',
            '#title' => t('Shipping Information'),
            '#attributes' => [
              'class' => ['shipping-information'],
            ],
            '#prefix' => '<div id="shipping-fields">',
            '#suffix' => '</div>',
            '#weight' => 2,
          ];

          $form[$step]['shipping']['street_address'] = [
            '#type' => 'textfield',
            '#title' => 'Street Address <span class="form-required" title="This field is required.">*</span>',
            '#placeholder' => 'Street Address (Required)',
          ];

          $form[$step]['shipping']['apt_suite'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Apt., Suite, etc.'),
          ];

          $form[$step]['shipping']['country'] = [
            '#type' => 'select',
            '#title' => 'Country <span class="form-required" title="This field is required.">*</span>',
            '#options' => [
              'CA' => t('Canada'),
              'US' => t('United States'),
            ],
            '#default_value' => 'CA',
            '#ajax' =>[
              'callback' => '::ajax_country_shipping_update_callback',
              'wrapper' => 'shipping-fields',
            ],
          ];

          if($form_state->getValue('country') && $form_state->getValue('country') == 'CA'){
            $form[$step]['shipping']['state_province'] = [
              '#type' => 'select',
              '#title' => 'Province / Territory <span class="form-required" title="This field is required.">*</span>',
              '#options' => [
                'AB' => t('Alberta'),
                'BC' => t('British Columbia'),
                'MB' => t('Manitoba'),
                'NB' => t('New Brunswick'),
                'NL' => t('Newfoundland and Labrador'),
                'NS' => t('Nova Scotia'),
                'ON' => t('Ontario'),
                'PE' => t('Prince Edward Island'),
                'QC' => t('Quebec'),
                'SK' => t('Saskatchewan'),
                'NT' => t('Northwest Territories'),
                'NU' => t('Nunavut'),
                'YT' => t('Yukon'),
              ],
              '#empty_option' => '- Please Select (required) -',
            ];
          }
          else if($form_state->getValue('country') && $form_state->getValue('country') == 'US'){
            $form[$step]['shipping']['state_province'] = [
              '#type' => 'select',
              '#title' => 'State <span class="form-required" title="This field is required.">*</span>',
              '#options' => [
                'AL' => t('Alabama'),
                'AK' => t('Alaska'),
                'AZ' => t('Arizona'),
                'AR' => t('Arkansas'),
                'CA' => t('California'),
                'CO' => t('Colorado'),
                'CT' => t('Connecticut'),
                'DE' => t('Delaware'),
                'DC' => t('District Of Columbia'),
                'FL' => t('Florida'),
                'GA' => t('Georgia'),
                'HI' => t('Hawaii'),
                'ID' => t('Idaho'),
                'IL' => t('Illinois'),
                'IN' => t('Indiana'),
                'IA' => t('Iowa'),
                'KS' => t('Kansas'),
                'KY' => t('Kentucky'),
                'LA' => t('Louisiana'),
                'ME' => t('Maine'),
                'MD' => t('Maryland'),
                'MA' => t('Massachusetts'),
                'MI' => t('Michigan'),
                'MN' => t('Minnesota'),
                'MS' => t('Mississippi'),
                'MO' => t('Missouri'),
                'MT' => t('Montana'),
                'NE' => t('Nebraska'),
                'NV' => t('Nevada'),
                'NH' => t('New Hampshire'),
                'NJ' => t('New Jersey'),
                'NM' => t('New Mexico'),
                'NY' => t('New York'),
                'NC' => t('North Carolina'),
                'ND' => t('North Dakota'),
                'OH' => t('Ohio'),
                'OK' => t('Oklahoma'),
                'OR' => t('Oregon'),
                'PA' => t('Pennsylvania'),
                'RI' => t('Rhode Island'),
                'SC' => t('South Carolina'),
                'SD' => t('South Dakota'),
                'TN' => t('Tennessee'),
                'TX' => t('Texas'),
                'UT' => t('Utah'),
                'VT' => t('Vermont'),
                'VA' => t('Virginia'),
                'WA' => t('Washington'),
                'WV' => t('West Virginia'),
                'WI' => t('Wisconsin'),
                'WY' => t('Wyoming'),
              ],
              '#empty_option' => '- Please Select (required) -',
            ];
          }

          $form[$step]['shipping']['city_town'] = [
            '#type' => 'textfield',
            '#title' => 'City / Town <span class="form-required" title="This field is required.">*</span>',
            '#placeholder' => 'City / Town (Required)',
          ];
          if($form_state->getValue('country') && $form_state->getValue('country') == 'US'){
            $form[$step]['shipping']['zip_code'] = [
              '#type' => 'textfield',
              '#title' => 'Zip Code <span class="form-required" title="This field is required.">*</span>',
              '#placeholder' => 'Zip Code (Required)',
            ];
          }
          else if($form_state->getValue('country') && $form_state->getValue('country') == 'CA'){
            $form[$step]['shipping']['zip_code'] = [
              '#type' => 'textfield',
              '#title' => 'Postal Code <span class="form-required" title="This field is required.">*</span>',
              '#placeholder' => 'Postal Code (Required)',
            ];
          }

          $form[$step]['billing'] = [
            '#type' => 'fieldset',
            '#title' => t('Billing Information'),
            '#attributes' => [
              'class' => ['billing-information'],
            ],
            '#prefix' => '<div id="billing-fields">',
            '#suffix' => '</div>',
            '#weight' => 2,
          ];

          $form[$step]['billing']['billing_same_as_shipping'] = [
            '#type' => 'checkbox',
            '#title' => t('Billing address is the same as shipping address'),
            '#attributes' => [
              'class' => ['billing-same-as-shipping'],
            ],
            '#default_value' => 1,
            '#ajax' => [
              'callback' => '::ajax_country_billing_update_callback',
              'wrapper' => 'billing-fields',
            ],
          ];

          $form[$step]['billing']['billing-toggle-fields'] = [
            '#type' => 'fieldset',
            '#attributes' => [
              'class' => ['billing-toggle-fields'],
            ],
            '#prefix' => '<div id="billing-toggle-fields">',
            '#suffix' => '</div>',
          ];
          if($form_state->getValue('billing_same_as_shipping') == 1){
            $form[$step]['billing']['billing-toggle-fields']['#attributes']['class'] = ['billing-toggle-fields','hide'];
          }
          
            $form[$step]['billing']['billing-toggle-fields']['street_address_billing'] = [
              '#type' => 'textfield',
              '#title' => 'Street Address <span class="form-required" title="This field is required.">*</span>',
              '#placeholder' => 'Street Address (Required)',
            ];
  
            $form[$step]['billing']['billing-toggle-fields']['apt_suite_billing'] = [
              '#type' => 'textfield',
              '#title' => $this->t('Apt., Suite, etc.'),
            ];
  
            $form[$step]['billing']['billing-toggle-fields']['country_billing'] = [
              '#type' => 'select',
              '#title' => 'Country <span class="form-required" title="This field is required.">*</span>',
              '#options' => [
                'CA' => t('Canada'),
                'US' => t('United States'),
              ],
              '#default_value' => 'CA',
              '#ajax' =>[
                'callback' => '::ajax_country_billing_update_callback',
                'wrapper' => 'billing-fields',
              ],
            ];
  
            if($form_state->getValue('country_billing') && $form_state->getValue('country_billing') == 'CA'){
              $form[$step]['billing']['billing-toggle-fields']['state_province_billing'] = [
                '#type' => 'select',
                '#title' => 'Province / Territory <span class="form-required" title="This field is required.">*</span>',
                '#options' => [
                  'AB' => t('Alberta'),
                  'BC' => t('British Columbia'),
                  'MB' => t('Manitoba'),
                  'NB' => t('New Brunswick'),
                  'NL' => t('Newfoundland and Labrador'),
                  'NS' => t('Nova Scotia'),
                  'ON' => t('Ontario'),
                  'PE' => t('Prince Edward Island'),
                  'QC' => t('Quebec'),
                  'SK' => t('Saskatchewan'),
                  'NT' => t('Northwest Territories'),
                  'NU' => t('Nunavut'),
                  'YT' => t('Yukon'),
              ],
                '#empty_option' => '- Please Select (required) -',
              ];
            }
            else if($form_state->getValue('country_billing') && $form_state->getValue('country_billing') == 'US'){
              $form[$step]['billing']['billing-toggle-fields']['state_province_billing'] = [
                '#type' => 'select',
                '#title' => 'State <span class="form-required" title="This field is required.">*</span>',
                '#options' => [
                  'AL' => t('Alabama'),
                  'AK' => t('Alaska'),
                  'AZ' => t('Arizona'),
                  'AR' => t('Arkansas'),
                  'CA' => t('California'),
                  'CO' => t('Colorado'),
                  'CT' => t('Connecticut'),
                  'DE' => t('Delaware'),
                  'DC' => t('District Of Columbia'),
                  'FL' => t('Florida'),
                  'GA' => t('Georgia'),
                  'HI' => t('Hawaii'),
                  'ID' => t('Idaho'),
                  'IL' => t('Illinois'),
                  'IN' => t('Indiana'),
                  'IA' => t('Iowa'),
                  'KS' => t('Kansas'),
                  'KY' => t('Kentucky'),
                  'LA' => t('Louisiana'),
                  'ME' => t('Maine'),
                  'MD' => t('Maryland'),
                  'MA' => t('Massachusetts'),
                  'MI' => t('Michigan'),
                  'MN' => t('Minnesota'),
                  'MS' => t('Mississippi'),
                  'MO' => t('Missouri'),
                  'MT' => t('Montana'),
                  'NE' => t('Nebraska'),
                  'NV' => t('Nevada'),
                  'NH' => t('New Hampshire'),
                  'NJ' => t('New Jersey'),
                  'NM' => t('New Mexico'),
                  'NY' => t('New York'),
                  'NC' => t('North Carolina'),
                  'ND' => t('North Dakota'),
                  'OH' => t('Ohio'),
                  'OK' => t('Oklahoma'),
                  'OR' => t('Oregon'),
                  'PA' => t('Pennsylvania'),
                  'RI' => t('Rhode Island'),
                  'SC' => t('South Carolina'),
                  'SD' => t('South Dakota'),
                  'TN' => t('Tennessee'),
                  'TX' => t('Texas'),
                  'UT' => t('Utah'),
                  'VT' => t('Vermont'),
                  'VA' => t('Virginia'),
                  'WA' => t('Washington'),
                  'WV' => t('West Virginia'),
                  'WI' => t('Wisconsin'),
                  'WY' => t('Wyoming'),
                  '#empty_option' => '- Please Select (required) -',
                ],
              ];
            }
  
            $form[$step]['billing']['billing-toggle-fields']['city_town_billing'] = [
              '#type' => 'textfield',
              '#title' => 'City / Town <span class="form-required" title="This field is required.">*</span>',
              '#placeholder' => 'City / Town (Required)',
              // '#required' => TRUE,
            ];
  
            if($form_state->getValue('country_billing') && $form_state->getValue('country_billing') == 'US'){
              $form[$step]['billing']['billing-toggle-fields']['zip_code_billing'] = [
                '#type' => 'textfield',
                '#title' => 'Zip Code <span class="form-required" title="This field is required.">*</span>',
                '#placeholder' => 'Zip Code (Required)',
                // '#required' => TRUE,
              ];
            }
            else if($form_state->getValue('country_billing') && $form_state->getValue('country_billing') == 'CA'){
              $form[$step]['billing']['billing-toggle-fields']['zip_code_billing'] = [
                '#type' => 'textfield',
                '#title' => 'Postal Code <span class="form-required" title="This field is required.">*</span>',
                '#placeholder' => 'Postal Code (Required)',
                // '#required' => TRUE,
              ];
            }
            

          $form[$step]['google_recaptcha'] = [
            '#type'=> 'fieldset',
            '#description' => '<div class="g-recaptcha" data-sitekey="6Lcr4u0pAAAAAGj32knXkUzuHAXzj3CoAhtbJ1t5"></div>',
            '#weight' => 2,
          ];

          break;
      }
      $form[$sample_current_step_id]['actions'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['sample-step-button-container']], // Add a custom class for styling
        '#weight' => 2,
      ];
      if($sample_current_step_id > 1 && $sample_current_step_id < 4){
        $form[$sample_current_step_id]['actions']['back'] = [
          '#type' => 'submit',
          '#value' => $this->t('Back'),
          '#ajax' => [
            'callback' => '::changePage',
            'wrapper' => 'sample-form-container',
          ],
          '#submit' => ['::submitForm'],
        ];
      }
      if($sample_current_step_id != 3 && $sample_current_step_id != 4){
        $form[$sample_current_step_id]['actions']['submit'] = [
          '#type' => 'submit',
          '#button_type' => 'primary',
          '#value' => $this->t('Next'),
          '#ajax' => [
            'callback' => '::changePage',
            'wrapper' => 'sample-form-container', 
          ],
          '#validate' => ['::validateSampleForm'],
          '#submit' => ['::submitForm'],
        ];
      }
      else if($sample_current_step_id == 3){
        $form[$sample_current_step_id]['actions']['submit'] = [
          '#type' => 'submit',
          '#button_type' => 'primary',
          '#value' => $this->t('Submit'),
          '#ajax' => [
            'callback' => '::changePage',
            'wrapper' => 'sample-form-container', 
          ],
          '#validate' => ['::validateSampleForm'],
          '#submit' => ['::submitForm'],
        ];
      }
    }

    if($form_state->getValue(['samples']) || $form_state->get('step_1_values') || $form[1]['samples-list-fieldset']['samples']['#default_value']){ // if at least one sample is selected, or a default value has been set, render summary table
      if($form_state->getValue(['samples']) || $form_state->get('step_1_values')){
        $selected_samples = (null !== $form_state->getValue(['samples'])) ? array_filter($form_state->getValue(['samples'])): array_filter($form_state->get('step_1_values')['samples']);
      }
      else $selected_samples = array_keys($form[1]['samples-list-fieldset']['samples']['#default_value']);

      $content = '';

      // check there's at least one matching selected sample in the list of available sample kits
      $at_least_one_match = false;
      foreach($selected_samples as $sku){
        if(array_key_exists($sku, $samples)){
          $at_least_one_match = true;
          break;
        }
      }

      if(count($selected_samples) > 0 && $at_least_one_match){
        $selected_sample_info_output = '<table style="width: 100%; border-collapse: collapse; border: 1px solid #ddd; margin: 0px; padding: 0px;">';
        $selected_sample_info_output .= '<thead><tr><th style="text-align: left; border: 1px solid #ddd; padding: 0.5em;">Product</th><th style="text-align: left; border: 1px solid #ddd; padding: 0.5em;">SKU</th></tr></thead>';
        $selected_sample_info_output .= '<tbody>';
  
        foreach ($selected_samples as $sku) {
          $sample = $samples[$sku];
          if($sample == null) continue; // skip this selected sample if it doesn't exist in the array of samples
          $selected_sample_info_output .= '<tr>';
          $selected_sample_info_output .= '<td style="border: 1px solid #ddd; padding: 0.5em;">' . $sample->title . ' - ' . $sample->size . '</td>';
          $selected_sample_info_output .= '<td style="border: 1px solid #ddd; padding: 0.5em;">' . $sample->sku . '</td>';
          $selected_sample_info_output .= '</tr>';
        }
        $selected_sample_info_output .= '</tbody>';
        $selected_sample_info_output .= '</table>';
  
        $content = '<div class="my_top_message">' . $selected_sample_info_output . '</div>';
      }
      $form['selected_sample_info'] = [
        '#markup' => $this->t('<div id = "sample_information">'.$content.'</div>'),
        '#weight' => 0, // render this field second, after the progress bar
      ];
    }
    else{ // else leave it empty for next steps
      $form['selected_sample_info'] = [
        '#markup' => $this->t('<div id = "sample_information"></div>'),
        '#weight' => 0, // render this field second, after the progress bar
      ];
    }

    $form['#suffix'] = '</div>';
    return $form;
  }

  public function changePage(array &$form, FormStateInterface $form_state){
    $messages = \Drupal::messenger()->deleteAll();

    $Selector = '#step3 > div[data-drupal-selector="edit-3"]';
    $response = new AjaxResponse();

    if($form_state->get('sample_current_step_id') == 3){
      $Selector = '#sample-form-container';
      $response = new AjaxResponse();
      $response->addCommand(new ReplaceCommand(NULL, $form));
      $response->addCommand(new AfterCommand($Selector, '<script>renderCaptcha();</script>')); // need this to render the captcha whenever the user lands on step 3 (the page with captcha)
      return $response;
    }

    if($form_state->getTriggeringElement()['#value']=='Submit'){
      if ($form_state->hasAnyErrors()) {
        $response = nor_forms_ajax_error($form, $form_state, $Selector);
        return $response;
      } else {
  
        $response = nor_forms_sample_request_sent_ajax($form, $form_state, $Selector);
  
        return $response;
      }
    }

    return $form;
  }
  public function changeSelectedProducts(array &$form, FormStateInterface $form_state) {
    return $form['selected_sample_info'];
  }
  public function ajax_country_shipping_update_callback(array &$form, FormStateInterface $form_state){
    return $form[3]['shipping'];
  }
  public function ajax_country_billing_update_callback(array &$form, FormStateInterface $form_state){
    return $form[3]['billing'];
  }


  private function getProductVariations(){
    $database = \Drupal::database();
    $query = $database->query("SELECT cpvfd.variation_id, cpvfd.sku, cpvfd.title, cpavfd.name AS size FROM commerce_product_variation_field_data AS cpvfd JOIN commerce_product_field_data AS cpfd ON cpvfd.product_id = cpfd.product_id JOIN commerce_product_variation__attribute_size AS cpvas ON cpvas.entity_id = cpvfd.variation_id JOIN commerce_product_attribute_value_field_data AS cpavfd ON cpavfd.attribute_value_id = cpvas.attribute_size_target_id WHERE cpvfd.status = 1 AND cpfd.status = 1 AND cpfd.type = 'norproduct' ORDER BY sku ASC");
    $result = $query->fetchAll();
    return $result;
  }

  private function getSamples(){
    $samples = [
      '49540' => (object) [
        'sku' => '49540',
        'title' => 'Stool Total RNA Purification Kit',
        'size' => '4 Preps',
      ],
      '57740' => (object) [
        'sku' => '57740',
        'title' => 'Urine Exosome Purification Kit',
        'size' => '4 Preps',
      ],
      '63980' => (object) [
        'sku' => '63980',
        'title' => 'cf-DNA/cf-RNA Preservative Tubes',
        'size' => '6 Tubes',
      ],
      '61040' => (object) [
        "sku" => '61040',
        "title" => "RNA Clean-Up and Concentration Micro-Elute Kit",
        "size" => "4 Preps",
      ],
      '23640' => (object) [
        "sku" => '23640',
        "title" => "RNA Clean-Up and Concentration Kit",
        "size" => "4 Preps",
      ],
      '17240' => (object) [
        "sku" => '17240',
        "title" => "Total RNA Purification Kit",
        "size" => "4 Preps",
      ],
      'RU49010' => (object) [
        "sku" => 'RU49010',
        "title" => "Saliva DNA Collection and Preservation Devices",
        "size" => "4 Preps",
      ],
      '55040' => (object) [
        "sku" => '55040',
        "title" => "Plasma/Serum RNA Purification Kit",
        "size" => "4 Preps",
      ],
      '57440' => (object) [
        "sku" => '57440',
        "title" => "Plasma/Serum Exosome Purification Kit",
        "size" => "4 Preps",
      ],
      '25840' => (object) [
        "sku" => '25840',
        "title" => "Plant/Fungi Total RNA Purification Kit",
        "size" => "4 Preps",
      ],
      '63710' => (object) [
        "sku" => '63710',
        "title" => "Stool Nucleic Acid Collection and Preservation System",
        "size" => "1 Device",
      ],
      '45674' => (object) [
        "sku" => '45674',
        "title" => "Fecal Collection and Preservation Tube",
        "size" => "1 Unit",
      ],
      '18124' => (object) [
        "sku" => '18124',
        "title" => "Urine Preservation Solution Single Dose Format",
        "size" => "1 Unit",
      ],
      '18120' => (object) [
        "sku" => '18120',
        "title" => "Urine Collection and Preservation Tube (15 cc)",
        "size" => "1 Unit",
      ],
      'RU53810' => (object) [
        "sku" => 'RU53810',
        "title" => "Clamshell Individual Saliva RNA Collection and Preservation Device",
        "size" => "1 Unit",
      ],
      'CY-93050' => (object) [
        "sku" => 'CY-93050',
        "title" => "Flocked Swab with 80mm breakpoint",
        "size" => "1 Unit",
      ],
      '45683' => (object) [
        "sku" => '45683',
        "title" => "Swab Collection and DNA Preservation Tube",
        "size" => "1 Unit",
      ],
      '68803' => (object) [
        "sku" => '68803',
        "title" => "Total Nucleic Acid Preservation Tube",
        "size" => "1 Unit",
      ],
      '58040' => (object) [
        "sku" => '58040',
        "title" => "Exosomal RNA Isolation Kit",
        "size" => "4 Preps",
      ],
    ];
    //dump($samples[0]->title);
    return $samples;
  }

  private function getSampleInfo($sku){
    $samples = $this->getSamples();
    foreach ($samples as $sample) {
      if ($sample->sku == $sku) {
        return $sample;
      }
    }

    return [];
  }

  public function validateSampleForm(array &$form, FormStateInterface $form_state){
    switch ($form_state->get('sample_current_step_id')){
      case 1:
        $selected_options = $form_state->getValue(['samples']);
        $selected_options = array_filter($selected_options);
    
        if (count($selected_options) > 2) {
          $form_state->setErrorByName('samples-list-fieldset', $this->t('Only a maximum of 2 samples can be selected'));
          $form_state->setErrorByName('samples', $this->t('Only a maximum of 2 samples can be selected'));
          $form[1]['samples-list-fieldset']['#attributes']['class'][] = 'form-fieldset--error';
         /*  $form[1]['erroralert']['#markup'] = '<div class="form-item--error-message">Only a maximum of two samples can be selected</div>'; */
    
        } else if (count($selected_options) < 1) {
          $form_state->setErrorByName('samples', $this->t('Please select a sample'));
          $form[1]['samples-list-fieldset']['#attributes']['class'][] = 'form-fieldset--error';
          /* $form[1]['erroralert']['#markup'] = '<div class="form-item--error-message">Please select a sample</div>'; */
        }
        break;
      case 2:
        // Get values from relevant form fields
        $first_name = $form_state->getValue('sample_fname');
        $last_name = $form_state->getValue('sample_lname');
        $email = $form_state->getValue('sample_email');
        $phone = $form_state->getValue('sample_phone');
        $company = $form_state->getValue('sample_company');
        $job = $form_state->getValue('job_title');

        // Check if the required fields are empty
        if (empty($first_name)) {
          // Trigger an error and prevent further processing
          $form_state->setErrorByName('sample_fname', $this->t('First Name is required.'));
        }
        if(empty($last_name)){
          $form_state->setErrorByName('sample_lname', $this->t('Last Name is required.'));
        }
        if(empty($email)){
          $form_state->setErrorByName('sample_email', $this->t('Email Address is required.'));
        }
        if(\Drupal::service('email.validator')->isValid($email) == false){
          $form_state->setErrorByName('sample_email', $this->t('Please enter a valid email address.'));
        }
        if(empty($phone)){
          $form_state->setErrorByName('sample_phone', $this->t('Phone Number is required.'));
        }
        if(empty($company)){
          $form_state->setErrorByName('sample_company', $this->t('Company / Institution is required.'));
        }
        if(empty($job)){
          $form_state->setErrorByName('job_title', $this->t('Job Title is required.'));
        }
        break;
      case 3:
        // shipping
        $street = $form_state->getValue('street_address');
        $country = $form_state->getValue('country');
        $state = $form_state->getValue('state_province');
        $city = $form_state->getValue('city_town');
        $zip = $form_state->getValue('zip_code');
        if (empty($street)) {
          $form_state->setErrorByName('street_address', $this->t('Street Name is required.'));
        }
        if(empty($country)){
          $form_state->setErrorByName('country', $this->t('Country is required.'));
        }
        if(empty($state)){
          $form_state->setErrorByName('state_province', $this->t('State/ Province is required.'));
        }
        if(empty($city)){
          $form_state->setErrorByName('city_town', $this->t('City is required.'));
        }
        if(empty($zip)){
          $form_state->setErrorByName('zip_code', $this->t('Zip/ Postal Code is required.'));
        }
        // billing
        $billing_same_as_shipping = $form_state->getValue('billing_same_as_shipping');
        if($billing_same_as_shipping == 0){
          $street_billing = $form_state->getValue('street_address_billing');
          $country_billing = $form_state->getValue('country_billing');
          $state_billing = $form_state->getValue('state_province_billing');
          $city_billing = $form_state->getValue('city_town_billing');
          $zip_billing = $form_state->getValue('zip_code_billing');
          if (empty($street_billing)) {
            $form_state->setErrorByName('street_address_billing', $this->t('Street Name is required.'));
          }
          if(empty($country_billing)){
            $form_state->setErrorByName('country_billing', $this->t('Country is required.'));
          }
          if(empty($state_billing)){
            $form_state->setErrorByName('state_province_billing', $this->t('State/ Province is required.'));
          }
          if(empty($city_billing)){
            $form_state->setErrorByName('city_town_billing', $this->t('City is required.'));
          }
          if(empty($zip_billing)){
            $form_state->setErrorByName('zip_code_billing', $this->t('Zip/ Postal Code is required.'));
          }
        }
        // Captcha
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
        break;
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

    $Selector = '#sample-form-container';
    $response = new AjaxResponse();

    if ($form_state->hasAnyErrors()) {
      $response = nor_forms_ajax_error($form, $form_state, $Selector);
      return $response;
    } else {

      $response = nor_forms_sample_request_sent_ajax($form, $form_state, $Selector);

      return $response;
    }
  }


  public function submitForm(array &$form, FormStateInterface $form_state){
    $form_state->set("step_".$form_state->get('sample_current_step_id')."_values", $form_state->getValues()); // store step values (step_1_values, step_2_values, step_3_values)
    if($form_state->getTriggeringElement()['#value']=='Next') $form_state->set('sample_current_step_id', $form_state->get('sample_current_step_id') + 1);
    else if($form_state->getTriggeringElement()['#value']=='Back') {
      $form_state->set('sample_current_step_id', $form_state->get('sample_current_step_id') - 1);
      /* $form_state->set('testing', $form_state->getButtons()); */
    }
    else if($form_state->getTriggeringElement()['#value']=='Submit'){

      $form_state->set('sample_current_step_id', $form_state->get('sample_current_step_id') + 1);

      // Get the value of the "Subscribe to Mailing List" checkbox
      $subscribe_option = $form_state->getValue('sample_subscribe') ? 'Yes' : 'No';
      $subscribe_emaildb = $form_state->getValue('sample_subscribe') ? 1 : 0;
      $subscribe_optout = $form_state->getValue('sample_subscribe') ? 0 : 1;

      $street_shipping = '';
      $apartment_shipping = '';
      $country_shipping = '';
      $state_shipping = '';
      $city_shipping = '';
      $zip_shipping = '';

      $utm_source = $form_state->getValue('utm_source');
      $utm_medium = $form_state->getValue('utm_medium');
      $utm_campaign = $form_state->getValue('utm_campaign');
      $utm_id = $form_state->getValue('utm_id');
      $utm_term = $form_state->getValue('utm_term');
      $utm_content = $form_state->getValue('utm_content');
 
      $output = '<p>Hello,</p>
      <p>A customer has requested a sample product.</p>';

      // Customer Information
      $output .= '<h2 style="margin-bottom:0px;">Customer Information:</h2><table style="border-spacing:0px;border-bottom:1px solid grey;"><tbody>';
      $first_name = $form_state->getValue('sample_fname'); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">First Name:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$first_name.'</td></tr>';
      $last_name = $form_state->getValue('sample_lname'); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Last Name:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$last_name.'</td></tr>';
      $email = $form_state->getValue('sample_email'); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Email:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$email.'</td></tr>';
      $phone = $form_state->getValue('sample_phone'); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Phone:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$phone.'</td></tr>';
      $company = $form_state->getValue('sample_company'); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Company:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$company.'</td></tr>';
      $job = $form_state->getValue('job_title'); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Job:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$job.'</td></tr>';
      $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Subscribe to Mailing List:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$subscribe_option.'</td></tr>';
      $output .= '</tbody></table>';
      
      $output .= '</tbody></table>';
      
      // Shipping Information
      $output .= '<h2 style="margin-bottom:0px;">Shipping Information:</h2><table style="border-spacing:0px;border-bottom:1px solid grey;"><tbody>';
      $street_shipping = $form_state->getValue('street_address'); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Street Address:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$street_shipping.'</td></tr>';
      if ($form_state->getValue('apt_suite')){ $apartment_shipping = $form_state->getValue('apt_suite'); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Apt Suite:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$apartment_shipping.'</td></tr>'; }
      $country_shipping = $form_state->getValue('country'); 
      
      //Get the countries with codes directly from the CountryRepository service
      $country_name_shipping = getCountryNames($country_shipping);

      $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Country:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$country_name_shipping.'</td></tr>';
      $state_shipping = $form_state->getValue('state_province'); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">';if($country_shipping=='CA'){$output .= 'Province / Territory:';}else{$output .= 'State';} $output .= '</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$state_shipping.'</td></tr>';
      $city_shipping = $form_state->getValue('city_town'); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">'; $output .= 'City / Town:'; $output .= '</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$city_shipping.'</td></tr>';
      $zip_shipping = $form_state->getValue('zip_code'); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">'; if($country_shipping=='CA'){$output .= 'Postal Code:';}else{$output .= 'Zip Code';} $output .= '</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$zip_shipping.'</td></tr>';
      $output .= '</tbody></table>';

      // Billing Information
      $output .= '<h2 style="margin-bottom:0px;">Billing Information:</h2><table style="border-spacing:0px;border-bottom:1px solid grey;"><tbody>';
      if($form_state->getValue('billing_same_as_shipping') == 1){ $billing_same_as_shipping = $form_state->getValue('billing_same_as_shipping');$output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Billing same as Shipping:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">Yes</td></tr>';}
      else{
        if ($form_state->getValue('street_address_billing')){ $street_billing = $form_state->getValue('street_address_billing'); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Street Address:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$street_billing.'</td></tr>'; }
        if ($form_state->getValue('apt_suite_billing')){ $apartment_billing = $form_state->getValue('apt_suite_billing'); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Apt Suite:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$apartment_billing.'</td></tr>'; }
        if ($form_state->getValue('country_billing')){ $country_billing = $form_state->getValue('country_billing'); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Country:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$country_billing.'</td></tr>'; }
        if ($form_state->getValue('state_province_billing')){ $state_billing = $form_state->getValue('state_province_billing'); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">';if($form_state->getValue('country_billing')=='CA'){$output .= 'Province / Territory:';}else{$output .= 'State';} $output .= '</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$state_billing.'</td></tr>'; }
        if ($form_state->getValue('city_town_billing')){ $city_billing = $form_state->getValue('city_town_billing'); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">'; $output .= 'City / Town:'; $output .= '</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$city_billing.'</td></tr>'; }
        if ($form_state->getValue('zip_code_billing')){ $zip_billing = $form_state->getValue('zip_code_billing'); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">'; if($form_state->getValue('country_billing')=='CA'){$output .= 'Postal Code:';}else{$output .= 'Zip Code';} $output .= '</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$zip_billing.'</td></tr>'; }
      }
      $output .= '</tbody></table>';
      $selected_samples = (null !== $form_state->getValue(['samples'])) ? array_filter($form_state->getValue(['samples'])): array_filter($form_state->get('step_1_values')['samples']);
      $output .= '<h2 style="margin-bottom:0px;">Sample Product Information:</h2><table style="border-spacing:0px;border-bottom:1px solid grey;"><thead><tr><th style="border:1px solid grey;border-bottom:0px;padding:6px;">Ttitle - Size</th><th style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">SKU</th></tr></thead><tbody>';
      /* dump($selected_samples);
      dump($samples); */
      $samples = $this->getSamples();
      foreach ($selected_samples as $sku) {
        $sample_info = $samples[$sku];
        $output .= '<tr>';
       /*  $output .= print_r($samples[]); */
        $output .= '<td style="border:1px solid grey;border-bottom:0px;padding:6px;">' . $sample_info->title . ' - ' . $sample_info->size . '</td>';
        $output .= '<td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">' . $sample_info->sku . '</td>';
        $output .= '</tr>';
      }
      $output .= '</tbody></table>';

      $date = date("Ymd");
      $form_name = $this->t('Sample Request Form');

      if (!isset($first_name)) {
        $first_name = 'NULL';
      }
      if (!isset($email)) {
        $email = 'NULL';
      }

      try {
        $zoho = new RecordWrapper('leads');
        $record = [
          'First_Name' => $first_name,
          'Last_Name' => $last_name,
          'Email' => $email,
          'Company' => $company,
          'Job_Position' => $job,
          'Country' => $country_name_shipping,
          'Phone' => $phone,
          'Street' => $street_shipping,
          'City' => $city_shipping,
          'State' => $state_shipping,
          'Zip_Code' => $zip_shipping,
          'Lead_Source' => 'Website Form',
          'Web_Forms' => [$form_name],
        ];

        // Perform the upsert operation
        $upsertResult = $zoho->upsert($record);
      } catch (Exception $e) {
      }


      $selected_optionsDB = $form_state->getValue(['samples']);

      // Filter out any empty values
      $selected_optionsDB = array_filter($selected_optionsDB);
      
      // Count the number of selected samples
      $selected_samples_count = count($selected_optionsDB);
      $selected_sample_info_output2 = $form_state->getValue('selected_sample_info_output');

      // Initialize a variable to store all SKUs
      $sku_output = '';

      $selected_samples_count = count($selected_samples);

      // If more than one sample is selected
      if ($selected_samples_count > 1) {
          // Replace the existing code with the new logic
          $sku_output = implode(',', $selected_samples);
      } else {
          // If only one sample is selected, set the SKU output directly
          $sku_output = reset($selected_samples);
      }

      // Append the SKU output to the database sample info output
      $db_sample_info_output2 .= $sku_output;

      $query = \Drupal::database()->insert('forms_to_zoho');
      $query->fields(['created_on', 'first_name', 'last_name', 'country', 'email', 'job_title', 'record', 'timestamp', 'form_name', 'company', 'phone', 'street1', 'city', 'state', 'zip', 'opt_in', 'email_opt_out', 'sample', 'num_samples', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_id', 'utm_term', 'utm_content']); //wrong syntax here breaks entire submit function 
      $query->values([$date, $first_name, $last_name, $country_name_shipping, $email, $job, '', time(), $form_name, $company, $phone, $street_shipping, $city_shipping, $state_shipping, $zip_shipping, $subscribe_emaildb, $subscribe_optout, $db_sample_info_output2, $selected_samples_count, $utm_source, $utm_medium, $utm_campaign, $utm_id, $utm_term, $utm_content]);
      $query->execute();
      $insert_id = \Drupal::database()->lastInsertId('forms_to_zoho');
      $insert_id = "#" . $insert_id;

      if ($form_state->hasAnyErrors()) {} 
      else {
        $time = time();
        $recipient_email = 'orders@norgenbiotek.com,sabah.butt@norgenbiotek.com';  // Angela requested that her and Sebastian be removed from emails, since they receive one through Zoho already// real addresses
        //$recipient_email = 'liam.howes@norgenbiotek.com';
        $customer_email = $email;
        $subject_org = ' [Sample Request] ' . $first_name . ' ' . $last_name . ' (' . $email . ')- ' . date('F j, Y, g:i a', $time) ." ".$insert_id;
        $subject_customer = 'Thank you for Submitting Sample Request';
        nor_forms_email_redirect($output, $recipient_email, $subject_org); 
      }
    }
    $form_state->setRebuild(TRUE);
    //leave empty else execution will happen on each submit button
  }
}