<?php
namespace Drupal\sample_request\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\MessageCommand;
use Drupal\Core\Ajax\AppendCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\nor_forms\Libraries\Zoho\RecordWrapper;

class SampleRequestForm extends FormBase
{
  public function getFormID()
  {
    return 'sample_request_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state){

    $form['#prefix'] = '<div id="sample-form-container">';

    $form['message'] = [
      '#type' => 'markup',
      '#markup' => '<div id="result-message"></div>',
    ];

    if($form_state->getValue(['samples']) || $form_state->get('step_1_values')){ // if at least one sample is selected, render summary table
      if($form_state->getValue(['samples'])) $selected_samples = array_filter($form_state->getValue(['samples']));
      else if($form_state->get('step_1_values')) $selected_samples = array_filter($form_state->get('step_1_values')['samples']);
      $content = '';
      if(count($selected_samples) > 0){
        $selected_sample_info_output = '<table style="width: 100%; border-collapse: collapse; border: 1px solid #ddd; margin: 0px; padding: 0px;">';
        $selected_sample_info_output .= '<thead><tr><th style="text-align: left; border: 1px solid #ddd; padding: 0.5em;">Product</th><th style="text-align: left; border: 1px solid #ddd; padding: 0.5em;">SKU</th></tr></thead>';
        $selected_sample_info_output .= '<tbody>';
  
        foreach ($selected_samples as $sku) {
          $sample_info = $this->getSampleInfo($sku);
          $selected_sample_info_output .= '<tr>';
          $selected_sample_info_output .= '<td style="border: 1px solid #ddd; padding: 0.5em;">' . $sample_info->title . ' - ' . $sample_info->size . '</td>';
          $selected_sample_info_output .= '<td style="border: 1px solid #ddd; padding: 0.5em;">' . $sample_info->sku . '</td>';
          $selected_sample_info_output .= '</tr>';
        }
        $selected_sample_info_output .= '</tbody>';
        $selected_sample_info_output .= '</table>';
  
        $content = '<div class="my_top_message">' . $selected_sample_info_output . '</div>';
      }
      $form['selected_sample_info'] = [
        '#markup' => $this->t('<div id = "sample_information">'.$content.'</div>'),
      ];
    }
    else{ // else leave it empty for next steps
      $form['selected_sample_info'] = [
        '#markup' => $this->t('<div id = "sample_information"></div>'),
      ];
    }

    $samples = $this->getProductVariations();
    $steps = [1, 2, 3, 4];
    $survey_link_terrible = 'https://survey.zohopublic.com/zs/OcCNqJ?user_satisfaction=Terrible';
    $survey_link_poor = 'https://survey.zohopublic.com/zs/OcCNqJ?user_satisfaction=Poor';
    $survey_link_neutral = 'https://survey.zohopublic.com/zs/OcCNqJ?user_satisfaction=Neutral';
    $survey_link_good = 'https://survey.zohopublic.com/zs/OcCNqJ?user_satisfaction=Good';
    $survey_link_excellent = 'https://survey.zohopublic.com/zs/OcCNqJ?user_satisfaction=Excellent';
    
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
              '#attributes' => array(
                'class' => array('hidden'),
              ),
            ];
          }

          $form[$step]['progress'] = [
            '#type' => 'item',
            '#markup' => '<div class="norgen-progress-wrapper center"><span class="currentPage">1/3</span><div class="progress"><div class="progress-bar" role="progressbar" aria-valuenow="1" ariavaluemin="0" aria-valuemax="3"></div></div><span class="progressLevel">33%</span></div>',
          ];

          $form[$step]['hr_separator'] = [
            '#type' => 'item',
            '#markup' => '<hr>',
          ];

          // Error message container for Step Two
          $form[$step]['erroralert'] = [
            '#type' => 'markup',
            '#prefix' => '<div id="step1-erroralert">',
            '#suffix' => '</div>',
          ];

          $form[$step]['samples-list-fieldset'] = [
            '#type' => 'fieldset',
            '#title' => 'Product Samples of Interest (Maximum of 2)&nbsp;<span class="form-required" title="This field is required.">*</span>',
            '#attributes' => [
              'class' => ['samples-list-fieldset'],
            ],
          ];

          $form[$step]['samples-list-fieldset']['samples-search'] = [
            '#type' => 'search',
            '#attributes' => [
              'placeholder' => 'Search products by SKU, name, or size',
              'class' => ['filter-samples-search'],
              'onkeyup' => 'filterSamples()',
              'onsearch' => 'filterSamples()',
            ],
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
            /* '#limit_validation_errors' => [], */
            /* '#element_validate' => [
              [$this, 'validateSampleForm'],
            ], */
            /* '#attributes' => [
              'class' => ['no-highlight'], // Custom class to hide checkbox highlighting
            ], */
            '#prefix' => '<div class="samples-wrapper">',
            '#suffix' => '</div>',
            '#ajax' => [
              'callback' => '::changeSelectedProducts',
              'wrapper' => 'sample_information', 
              'event' => 'change', 
            ],
          ];

          foreach ($samples as $sample) {
            $form[$step]['samples-list-fieldset']['samples']['#options'][$sample->sku] = [
              'sku' => $sample->sku,
              'product_name' => $sample->title,
              'size' => $sample->size,
              '#attributes' => ['class' => ['example-form-checkbox']],
            ];
          };

          $form[$step]['disclaimer'] = [
            '#type' => 'item',
            '#markup' => '<div class="form-msg">Sample kits are avilable at 50% of the regular product price.<br>Sample kits are available only in Canada and the U.S.<br>Internatonal customers, please <a href="/resources/distributors" style="color:#fff;text-decoration:underline;">find your local distributor</a> for sample kit inquiries.</div>',
          ];
          break;

        case 2:
          //step 2 
          if($sample_current_step_id == $step){
            $form[$step] = [
              '#type' => 'container',
              '#prefix' => '<div id="stepRAWRXD">',
              '#suffix' => '</div>',
            ];
          }
          else{
            $form[$step] = [
              '#type' => 'container',
              '#prefix' => '<div id="stepRAWRXD">',
              '#suffix' => '</div>',
              '#attributes' => array(
                'class' => array('hidden'),
              ),
            ];
          }

          $form[$step]['progress'] = [
            '#type' => 'item',
            '#markup' => '<div class="norgen-progress-wrapper center"><span class="currentPage">2/3</span><div class="progress"><div class="progress-bar" role="progressbar" aria-valuenow="2" ariavaluemin="0" aria-valuemax="3"></div></div><span class="progressLevel">67%</span></div>',
          ];

          $form[$step]['hr_separator'] = [
            '#type' => 'item',
            '#markup' => '<hr>',
          ];

          // Error message container for Step Two
          $form[$step]['error_message'] = [
            '#type' => 'markup',
            '#prefix' => '<div id="step2-error-message">',
            '#suffix' => '</div>',
          ];

          $form[$step]['step2-validate'] = [
            '#type' => 'markup',
            '#markup' => '<div id="step2-message"></div>',
          ];

          $form[$step]['customer_information'] = [
            '#type' => 'fieldset',
            '#title' => 'Personal Information',
            '#attributes' => [
              'class' => ['customer-information'],
            ]
          ];
          $form[$step]['customer_information']['sample_fname'] = [
            '#type' => 'textfield',
            '#title' => [
              '#markup' => $this->t('First Name'),
              '#suffix' => '<span class="form-required" title="This field is required.">*</span>',
            ],
            '#placeholder' => 'First Name (Required)',
            /* '#required' => TRUE, */
          ];

          $form[$step]['customer_information']['sample_lname'] = [
            '#type' => 'textfield',
            '#title' => [
              '#markup' => $this->t('Last Name'),
              
              '#suffix' => '<span class="form-required" title="This field is required.">*</span>',
            ],
            '#placeholder' => 'Last Name (Required)',
            /* '#required' => TRUE, */
          ];

          $form[$step]['customer_information']['sample_email'] = [
            '#type' => 'email',
            '#title' => [
              '#markup' => $this->t('Email Address'),
              
              '#suffix' => '<span class="form-required" title="This field is required.">*</span>',
            ],
            '#placeholder' => 'Email Address (Required)',
            /* '#required' => TRUE, */
          ];

          $form[$step]['customer_information']['sample_phone'] = [
            '#type' => 'tel',
            '#title' => [
              '#markup' => $this->t('Phone Number'),
              
              '#suffix' => '<span class="form-required" title="This field is required.">*</span>',
            ],
            '#placeholder' => 'Phone Number (Required)',
            /* '#required' => TRUE, */
          ];

          $form[$step]['customer_information']['sample_company'] = [
            '#type' => 'textfield',
            '#title' => [
              '#markup' => $this->t('Company/ Institution'),
              '#suffix' => '<span class="form-required" title="This field is required.">*</span>',
            ],
            '#placeholder' => 'Company/ Institution (Required)',
            /* '#required' => TRUE, */
          ];

          $form[$step]['customer_information']['job_title'] = [
            '#type' => 'textfield',
            '#title' => [
              '#markup' => $this->t('Job Title'),
              '#suffix' => '<span class="form-required" title="This field is required.">*</span>',
            ],
            '#placeholder' => 'Job Title (Required)',
            /* '#required' => TRUE, */
          ];
          
          $form[$step]['customer_information']['referred_field_wrapper'] = [
            '#type' => 'container',
            '#prefix' => '<div id="referred-field-wrapper">',
            '#suffix' => '</div>',
          ];
          $form[$step]['customer_information']['referred_field_wrapper']['sample_referred'] = [
            '#type' => 'radios',
            '#title' => $this->t('Were You Referred to This Form by a Norgen Employee?'),
            '#options' => [
              'yes' => $this->t('Yes'),
              'no' => $this->t('No'),
            ],
            /*  '#suffix' => '', // Add an empty suffix to maintain consistency */
            '#attributes' => [
              'class' => ['webform-container-inline'],
            ],
            '#default_value' => 'no', // Set default value to 'No'
            '#ajax' => [
              'callback' => '::toggleReferredFieldVisibility',
              'wrapper' => 'referred-field-wrapper', 
              'event' => 'change', 
            ],
          ];
          if($form_state->getValue('sample_referred') == 'yes'){
            $form[$step]['customer_information']['referred_field_wrapper']['referral_name'] = [
              '#type' => 'textfield',
              '#title' => $this->t('Name of Employee'),
              '#size' => 60,
              '#maxlength' => 128,
            ];
          }

          $form[$step]['customer_information']['sample_subscribe'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Subscribe to Mailing List?'),
          ];

          $form[$step]['customer_application_information'] = [
            '#type' => 'fieldset',
            '#title' => 'Professional Information',
            '#attributes' => [
              'class' => ['customer-application-information'],
            ]
          ];
          $options_downstream_application = [
            '' => $this->t('- Please Select (required) -'),
            'diagnostic' => $this->t('Diagnostic'),
            'nipt' => $this->t('NIPT'),
            'pcr' => $this->t('PCR'),
            'rna_seq' => $this->t('RNA Seq'),
            'small_rna_seq' => $this->t('Small RNA Seq'),
            'automation' => $this->t('Automation'),
            'bioinformatics' => $this->t('Bioinformatics'),
            'genotyping' => $this->t('Genotyping'),
            'epigenetics' => $this->t('Epigenetics'),
            'gene_expression_analysis' => $this->t('Gene Expression Analysis'),
            'metagenomics' => $this->t('Metagenomics'),
            'whole_genome_seq' => $this->t('Whole Genome Seq'),
            'amplicon_seq' => $this->t('Amplicon Seq'),
            'r_and_d' => $this->t('R&D'),
            'dna_sequencing' => $this->t('DNA Sequencing'),
            'other' => $this->t('Other'),
          ];
          $form[$step]['customer_application_information']['downstream_field_wrapper'] = [
            '#type' => 'container',
            '#prefix' => '<div id="downstream-field-wrapper">',
            '#suffix' => '</div>',
          ];
          $form[$step]['customer_application_information']['downstream_field_wrapper']['downstream_application'] = [
            '#type' => 'select',
            '#title' => [
              '#markup' => $this->t('What is your downstream application?'),
              '#suffix' => '<span class="form-required" title="This field is required.">*</span>',
            ],
            '#options' => $options_downstream_application,
            '#ajax' => [
              'callback' => '::toggleDownstreamFieldVisibility',
              'wrapper' => 'downstream-field-wrapper', 
              'event' => 'change', 
            ],
            /* '#required' => TRUE, */
          ];

          if($form_state->getValue('downstream_application') == 'other'){
            $form[$step]['customer_application_information']['downstream_field_wrapper']['downstream_specify'] = [
              '#type' => 'textfield',
              '#title' => $this->t('Please Specify Downstream Application'),
              '#size' => 60,
              '#maxlength' => 128,
            ];
          }

          $options_samples_per_month = [
            '' => $this->t('- Please Select (required) -'),
            '0-50' => $this->t('0-50'),
            '50-200' => $this->t('50-200'),
            '200-500' => $this->t('200-500'),
            '500+' => $this->t('500+'),
          ];

          $form[$step]['customer_application_information']['samples_per_month'] = [
            '#type' => 'select',
            '#title' => [
              '#markup' => $this->t('How many samples do you typically process per month?'),
              '#suffix' => '<span class="form-required" title="This field is required.">*</span>',
            ],
            '#options' => $options_samples_per_month,
            /* '#required' => TRUE, */
          ];

          $options_request_reason = [
            '' => $this->t('- Please Select (required) -'),
            'Starting new test/application' => $this->t('Starting new test/application'),
            'Unhappy with current supplier pricing' => $this->t('Unhappy with current supplier pricing'),
            'Poor results with current products' => $this->t('Poor results with current products'),
            'Evaluate Norgen\'s products' => $this->t('Evaluate Norgen\'s products'),
            'Other' => $this->t('Other'),
          ];
          $form[$step]['customer_application_information']['reason_field_wrapper'] = [
            '#type' => 'container',
            '#prefix' => '<div id="reason-field-wrapper">',
            '#suffix' => '</div>',
          ];
          $form[$step]['customer_application_information']['reason_field_wrapper']['request_reason'] = [
            '#type' => 'select',
            '#title' => [
              '#markup' => $this->t('What is your reason for requesting a sample?'),
              '#suffix' => '<span class="form-required" title="This field is required.">*</span>',
            ],
            '#options' => $options_request_reason,
            '#ajax' => [
              'callback' => '::toggleReasonFieldVisibility',
              'wrapper' => 'reason-field-wrapper', 
              'event' => 'change', 
            ],
            /* '#required' => TRUE, */
          ];
          if($form_state->getValue('request_reason') == 'Other'){
            $form[$step]['customer_application_information']['reason_field_wrapper']['reason_specify'] = [
              '#type' => 'textfield',
              '#title' => $this->t('Please Specify Reason for Requesting Sample'),
              '#size' => 60,
              '#maxlength' => 128,
            ];
          }

          $options_plan_to_use = [
            '' => $this->t('- Please Select (required) -'),
            '2 weeks after receiving' => $this->t('2 weeks after receiving'),
            '1 month after receiving' => $this->t('1 month after receiving'),
            '2 months after receiving' => $this->t('2 months after receiving'),
          ];

          $form[$step]['customer_application_information']['plan_to_use'] = [
            '#type' => 'select',
            '#title' => [
              '#markup' => $this->t('When do you plan to use the sample kit?'),
              '#suffix' => '<span class="form-required" title="This field is required.">*</span>',
            ],
            '#options' => $options_plan_to_use,
            /* '#required' => TRUE, */
          ];

          $options_current_supplier = [
            '' => $this->t('- Please Select -'),
            'Qiagen' => $this->t('Qiagen'),
            'Thermo Fischer' => $this->t('Thermo Fischer'),
            'Sigma-Aldrich' => $this->t('Sigma-Aldrich'),
            'Zymo Research' => $this->t('Zymo Research'),
            'Promega' => $this->t('Promega'),
            'DNA Genotek' => $this->t('DNA Genotek'),
            'Other' => $this->t('Other'),
          ];
          $form[$step]['customer_application_information']['supplier_field_wrapper'] = [
            '#type' => 'container',
            '#prefix' => '<div id="supplier-field-wrapper">',
            '#suffix' => '</div>',
          ];
          $form[$step]['customer_application_information']['supplier_field_wrapper']['current_supplier'] = [
            '#type' => 'select',
            '#title' => [
              '#markup' => $this->t('Who is your current supplier?'),
              '#suffix' => '<span class="form-required" title="This field is required.">*</span>',
            ],
            '#options' => $options_current_supplier,
            '#ajax' => [
              'callback' => '::toggleSupplierFieldVisibility',
              'wrapper' => 'supplier-field-wrapper', 
              'event' => 'change', 
            ],
            /* '#required' => TRUE, */
          ];

          if($form_state->getValue('current_supplier') == 'Other'){
            $form[$step]['customer_application_information']['supplier_field_wrapper']['current_supplier_specify'] = [
              '#type' => 'textfield',
              '#title' => $this->t('Please Specify Your Current Supplier'),
              '#size' => 60,
              '#maxlength' => 128,
            ];
          }

          $form[$step]['customer_application_information']['research_description'] = [
            '#type' => 'textarea',
            '#title' => $this->t('Please tell us about your Research'),
            '#description' => $this->t('Provide additional information about your research.'),
            '#maxlength' => 255, // You can adjust the maximum length as needed.
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
              '#attributes' => array(
                'class' => array('hidden'),
              ),
            ];
          }

          $form[$step]['progress'] = [
            '#type' => 'item',
            '#markup' => '<div class="norgen-progress-wrapper center"><span class="currentPage">3/3</span><div class="progress"><div class="progress-bar" role="progressbar" aria-valuenow="3" ariavaluemin="0" aria-valuemax="3"></div></div><span class="progressLevel">100%</span></div>',
          ];

          $form[$step]['hr_separator'] = [
            '#type' => 'item',
            '#markup' => '<hr>',
          ];

          // Error message container for Step Three
          $form[$step]['error_message3'] = [
            '#type' => 'markup',
            '#prefix' => '<div id="step3-error-message3">',
            '#suffix' => '</div>',
          ];

          $form[$step]['shipping'] = [
            '#type' => 'fieldset',
            '#title' => t('Shipping Information'),
            '#attributes' => [
              'class' => ['shipping-information'],
            ],
            '#prefix' => '<div id="shipping-fields">',
            '#suffix' => '</div>',
          ];

          $form[$step]['shipping']['street_address'] = [
            '#type' => 'textfield',
            '#title' => [
              '#markup' => $this->t('Street Address'),
              '#suffix' => '<span class="form-required" title="This field is required.">*</span>',
            ],
            '#placeholder' => 'Street Address (Required)',
            /* '#required' => TRUE, */
          ];

          $form[$step]['shipping']['apt_suite'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Apt., Suite, etc.'),
          ];

          $form[$step]['shipping']['country'] = [
            '#type' => 'select',
            '#title' => [
              '#markup' => $this->t('Country'),
              '#suffix' => '<span class="form-required" title="This field is required.">*</span>',
            ],
            /* '#options' => getCountryOptions(), */ // Use the global function to get country options
            '#options' => array(
              'CA' => t('Canada'),
              'US' => t('United States'),
            ),
            '#ajax' =>array(
              'callback' => '::ajax_country_shipping_update_callback',
              'wrapper' => 'shipping-fields',
            ),
          /*  '#required' => TRUE, */
          ];

          if($form_state->getValue('country') && $form_state->getValue('country') == 'CA'){
            $form[$step]['shipping']['state_province'] = [
              '#type' => 'select',
              '#title' => [
                '#markup' => $this->t('Province / Territory'),
                '#suffix' => '<span class="form-required" title="This field is required.">*</span>',
              ],
              '#options' => array(
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
              ),
              '#empty_option' => '- Please Select (required) -',
            ];
          }
          if($form_state->getValue('country') && $form_state->getValue('country') == 'US'){
            $form[$step]['shipping']['state_province'] = [
              '#type' => 'select',
              '#title' => [
                '#markup' => $this->t('State'),
                '#suffix' => '<span class="form-required" title="This field is required.">*</span>',
              ],
              '#options' => array(
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
              ),
            ];
          }

          $form[$step]['shipping']['city_town'] = [
            '#type' => 'textfield',
            '#title' => [
              '#markup' => $this->t('City / Town'),
              '#suffix' => '<span class="form-required" title="This field is required.">*</span>',
            ],
            '#placeholder' => 'City / Town (Required)',
            /* '#required' => TRUE, */
          ];
          if($form_state->getValue('country_billing') && $form_state->getValue('country_billing') == 'US'){
            $form[$step]['shipping']['zip_code'] = [
              '#type' => 'textfield',
              '#title' => [
                '#markup' => $this->t('Zip Code'),
                '#suffix' => '<span class="form-required" title="This field is required.">*</span>',
              ],
              '#placeholder' => 'Zip Code (Required)',
              /* '#required' => TRUE, */
            ];
          }
          else if($form_state->getValue('country_billing') && $form_state->getValue('country_billing') == 'CA'){
            $form[$step]['shipping']['zip_code'] = [
              '#type' => 'textfield',
              '#title' => [
                '#markup' => $this->t('Postal Code'),
                '#suffix' => '<span class="form-required" title="This field is required.">*</span>',
              ],
              '#placeholder' => 'Postal Code (Required)',
              /* '#required' => TRUE, */
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
          ];

          $form[$step]['billing']['billing_same_as_shipping'] = [
            '#type' => 'checkbox',
            '#title' => t('Billing address is the same as shipping address'),
            '#attributes' => array(
              'class' => array('billing-same-as-shipping'),
            ),
            '#default_value' => 1,
            '#ajax' => array(
              'callback' => '::ajax_country_billing_update_callback',
              'wrapper' => 'billing-fields',
            ),
          ];

          if($form_state->getValue('billing_same_as_shipping') == 0){
            $form[$step]['billing']['street_address_billing'] = [
              '#type' => 'textfield',
              '#title' => [
                '#markup' => $this->t('Street Address'),
                '#suffix' => '<span class="form-required" title="This field is required.">*</span>',
              ],
              '#placeholder' => 'Street Address (Required)',
              /* '#required' => TRUE, */
            ];
  
            $form[$step]['billing']['apt_suite_billing'] = [
              '#type' => 'textfield',
              '#title' => $this->t('Apt., Suite, etc.'),
            ];
  
            $form[$step]['billing']['country_billing'] = [
              '#type' => 'select',
              '#title' => [
                '#markup' => $this->t('Country'),
                '#suffix' => '<span class="form-required" title="This field is required.">*</span>',
              ],
              '#options' => array(
                'CA' => t('Canada'),
                'US' => t('United States'),
              ),
              '#ajax' =>array(
                'callback' => '::ajax_country_billing_update_callback',
                'wrapper' => 'billing-fields',
              ),
              //  '#required' => TRUE,
            ];
  
            if($form_state->getValue('country_billing') && $form_state->getValue('country_billing') == 'CA'){
              $form[$step]['billing']['state_province_billing'] = [
                '#type' => 'select',
                '#title' => [
                  '#markup' => $this->t('Province / Territory'),
                  '#suffix' => '<span class="form-required" title="This field is required.">*</span>',
                ],
                '#options' => array(
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
                ),
                '#empty_option' => '- Please Select (required) -',
              ];
            }
            if($form_state->getValue('country_billing') && $form_state->getValue('country_billing') == 'US'){
              $form[$step]['billing']['state_province_billing'] = [
                '#type' => 'select',
                '#title' => [
                  '#markup' => $this->t('State'),
                  '#suffix' => '<span class="form-required" title="This field is required.">*</span>',
                ],
                '#options' => array(
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
                ),
              ];
            }
  
            $form[$step]['billing']['city_town_billing'] = [
              '#type' => 'textfield',
              '#title' => [
                '#markup' => $this->t('City / Town'),
                '#suffix' => '<span class="form-required" title="This field is required.">*</span>',
              ],
              '#placeholder' => 'City / Town (Required)',
              // '#required' => TRUE,
            ];
  
            if($form_state->getValue('country_billing') && $form_state->getValue('country_billing') == 'US'){
              $form[$step]['billing']['zip_code_billing'] = [
                '#type' => 'textfield',
                '#title' => [
                  '#markup' => $this->t('Zip Code'),
                  '#suffix' => '<span class="form-required" title="This field is required.">*</span>',
                ],
                '#placeholder' => 'Zip Code (Required)',
                // '#required' => TRUE,
              ];
            }
            else if($form_state->getValue('country_billing') && $form_state->getValue('country_billing') == 'CA'){
              $form[$step]['billing']['zip_code_billing'] = [
                '#type' => 'textfield',
                '#title' => [
                  '#markup' => $this->t('Postal Code'),
                  '#suffix' => '<span class="form-required" title="This field is required.">*</span>',
                ],
                '#placeholder' => 'Postal Code (Required)',
                // '#required' => TRUE,
              ];
            }
            
          }

          $form[$step]['google_recaptcha'] = [
            '#type'=> 'fieldset',
            '#description' => '<div class="g-recaptcha" data-sitekey="6Lcr4u0pAAAAAGj32knXkUzuHAXzj3CoAhtbJ1t5"></div>',
          ];

          break;
        case 4:    
          if($sample_current_step_id == $step){
            $form[$step] = [
              '#type' => 'container',
              '#prefix' => '<div id="step4" class="text-align:center">',
              '#suffix' => '</div>',
            ];
          }
          else{
            $form[$step] = [
              '#type' => 'container',
              '#prefix' => '<div id="step4" class="text-align:center">',
              '#suffix' => '</div>',
              '#attributes' => array(
                'class' => array('hidden'),
              ),
            ];
          }

          // <img src="https://norgenbiotek.com/sites/default/files/images/shipping-truck.png" alt="Shipping truck">
          $success_markup = '<div class="center"><h2>Thank You For Requesting Your Sample Product</h2>
            <p>Your request will be processed and you will be contacted by a sales representative via your provided phone number or email address within one week to confirm your order.</p>
            <dotlottie-player src="https://lottie.host/597363a0-6990-47f6-94b6-eb8ecc0a80dc/2LkEg3gN6I.json" background="transparent" speed="1" style="width: 300px; height: 300px; margin-left: auto; margin-right: auto; display:block;" autoplay></dotlottie-player>
            <p>If you have any questions or concerns please contact us at <a href="mailto:info@norgenbiotek.com" target="_blank">info@norgenbiotek.com</a>, or by phone at <a href="tel:+19052278848" target="_blank">+1 905 227 8848</a></p>
            <hr>
            <table style="margin:auto;">
              <tbody>
                <tr>
                  <td colspan="5" align="center">
                    <h2 style="font-size: 1.2em;">How was your experience requesting a Sample?</h2>
                  </td>
                </tr>
                <tr>
                  <td align="center">
                    <a href="' . $survey_link_terrible . '" style="color:#333333;text-decoration:none;">
                      Terrible
                      <p style="border: 1px solid #AAAAAA;border-radius:100%; width:2em; height:2em; line-height:2em; margin:0.25em;">1</p>
                    </a>
                  </td>
                  <td align="center">
                    <a href="' . $survey_link_poor . '" style="color:#333333;text-decoration:none;">
                      Poor
                      <p style="border: 1px solid #AAAAAA;border-radius:100%; width:2em; height:2em; line-height:2em; margin:0.25em;">2</p>
                    </a>
                  </td>
                  <td align="center">
                    <a href="' . $survey_link_neutral . '" style="color:#333333;text-decoration:none;">
                      Neutral
                      <p style="border: 1px solid #AAAAAA;border-radius:100%; width:2em; height:2em; line-height:2em; margin:0.25em;">3</p>
                    </a>
                  </td>
                  <td align="center">
                    <a href="' . $survey_link_good . '" style="color:#333333;text-decoration:none;">
                      Good
                      <p style="border: 1px solid #AAAAAA;border-radius:100%; width:2em; height:2em; line-height:2em; margin:0.25em;">4</p>
                    </a>
                  </td>
                  <td align="center">
                    <a href="' . $survey_link_excellent . '" style="color:#333333;text-decoration:none;">
                      Excellent
                      <p style="border: 1px solid #AAAAAA;border-radius:100%; width:2em; height:2em; line-height:2em; margin:0.25em;">5</p>
                    </a>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>';

          $form[$step]['markup'] = [
            '#type' => 'item',
            '#children' => $success_markup,
          ];
          break;
      }
      $form[$sample_current_step_id]['actions'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['sample-step-button-container']], // Add a custom class for styling
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

    $form['#suffix'] = '</div>';
    return $form;
  }

  public function changePage(array &$form, FormStateInterface $form_state){
    return $form;
  }
  public function changeSelectedProducts(array &$form, FormStateInterface $form_state) {
    return $form['selected_sample_info'];
  }
  public function toggleReferredFieldVisibility(array &$form, FormStateInterface $form_state) {
    return $form[2]['customer_information']['referred_field_wrapper'];
  }
  public function toggleDownstreamFieldVisibility(array &$form, FormStateInterface $form_state) {
    return $form[2]['customer_application_information']['downstream_field_wrapper'];
  }
  public function toggleReasonFieldVisibility(array &$form, FormStateInterface $form_state) {
    return $form[2]['customer_application_information']['reason_field_wrapper'];
  }
  public function toggleSupplierFieldVisibility(array &$form, FormStateInterface $form_state) {
    return $form[2]['customer_application_information']['supplier_field_wrapper'];
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

  private function getSampleInfo($sku){
    $samples = $this->getProductVariations(); 

    foreach ($samples as $sample) {
      if ($sample->sku === $sku) {
        return $sample;
      }
    }

    return [];
  }

  public function validateSampleForm(array $form, FormStateInterface $form_state){
    switch ($form_state->get('sample_current_step_id')){
      case 1:
        $selected_options = $form_state->getValue(['samples']);
        $selected_options = array_filter($selected_options);
    
        if (count($selected_options) > 2) {
    
          $form_state->setErrorByName('samples', $this->t('Only a maximum of two samples can be selected'));
          //Drupal::messenger()->addError(t('Only a maximum of two samples can be selected'));result-message
    
        } else if (count($selected_options) < 1) {
    
          $form_state->setErrorByName('samples', $this->t('Please select a sample'));
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
        $referred = $form_state->getValue('sample_referred');
        $downstream = $form_state->getValue('downstream_application');
        $samples_month = $form_state->getValue('samples_per_month');
        $request = $form_state->getValue('request_reason');
        $supplier = $form_state->getValue('current_supplier');
        $plan_to_use = $form_state->getValue('plan_to_use');

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
        if(empty($downstream)){
          $form_state->setErrorByName('downstream_application', $this->t('Downstream Application is required.'));
        }
        if(empty($samples_month)){
          $form_state->setErrorByName('samples_per_month', $this->t('Samples per Month is required.'));
        }
        if(empty($request)){
          $form_state->setErrorByName('request_reason', $this->t('Request Reason is required.'));
        }
        if(empty($supplier)){
          $form_state->setErrorByName('current_supplier', $this->t('Current Supplier is required.'));
        }
        if(empty($plan_to_use)){
          $form_state->setErrorByName('plan_to_use', $this->t('When Do You Plan to Use the Sample Kit is required.'));
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

      $referred = $form_state->getValue('sample_referred');
      $street_shipping = '';
      $apartment_shipping = '';
      $country_shipping = '';
      $state_shipping = '';
      $zip_shipping = '';
 
      $output = '<p>Hello,</p>
      <p>A customer submitted their request for Sample Request Form.</p>';

      // Customer Information
      $output .= '<h2 style="margin-bottom:0px;">Customer Information:</h2><table style="border-spacing:0px;border-bottom:1px solid grey;"><tbody>';
      $first_name = $form_state->getValue('sample_fname'); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">First Name:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$first_name.'</td></tr>';
      $last_name = $form_state->getValue('sample_lname'); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Last Name:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$last_name.'</td></tr>';
      $email = $form_state->getValue('sample_email'); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Email:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$email.'</td></tr>';
      $phone = $form_state->getValue('sample_phone'); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Phone:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$phone.'</td></tr>';
      $company = $form_state->getValue('sample_company'); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Company:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$company.'</td></tr>';
      $job = $form_state->getValue('job_title'); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Job:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$job.'</td></tr>';
      if ($form_state->getValue('sample_referred') == 'yes' && $form_state->getValue('referral_name') != null ){ $employee_name = $form_state->getValue('referral_name'); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Referred by Employee:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$employee_name.'</td></tr>'; }
      else if ($form_state->getValue('sample_referred')){ $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Referred by Employee:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$referred.'</td></tr>'; }
      if ($form_state->getValue('sample_subcribe')){ $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Subscribe:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$subscribe_option.'</td></tr>'; }
      $output .= '</tbody></table>';
      
      // Professional Information
      $output .= '<h2 style="margin-bottom:0px;">Professional Information:</h2><table style="border-spacing:0px;border-bottom:1px solid grey;"><tbody>';
      if ($form_state->getValue('downstream_application') == 'other' && $form_state->getValue('downstream_specify') != null){ $downstream_specify = $form_state->getValue('downstream_specify'); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Last Name:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$downstream_specify.'</td></tr>'; }
      
      else if ($form_state->getValue('downstream_application')){ $downstream_application = $form_state->getValue('downstream_application'); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Downstream Application:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$downstream_application.'</td></tr>'; }
      if ($form_state->getValue('samples_per_month')){ $samples_per_month = $form_state->getValue('samples_per_month'); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Samples Per Month:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$samples_per_month.'</td></tr>'; }
      
      if ($form_state->getValue('request_reason') == 'Other' && $form_state->getValue('reason_specify') != null){ $request_reason_specify = $form_state->getValue('request_reason'); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Reason for Request:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$request_reason_specify.'</td></tr>'; }
      else if ($form_state->getValue('request_reason')){ $request_reason = $form_state->getValue('request_reason'); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Reason for Request:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$request_reason.'</td></tr>'; }
      $output .= '</tbody></table>';
         
      // Shipping Information
      $output .= '<h2 style="margin-bottom:0px;">Shipping Information:</h2><table style="border-spacing:0px;border-bottom:1px solid grey;"><tbody>';
      $street_shipping = $form_state->getValue('street_address'); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Street Address:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$street_shipping.'</td></tr>';
      if ($form_state->getValue('apt_suite')){ $apartment_shipping = $form_state->getValue('apt_suite'); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Apt Suite:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$apartment_shipping.'</td></tr>'; }
      $country_shipping = $form_state->getValue('country'); 
      
      //Get the countries with codes directly from the CountryRepository service
      $country_name_shipping = getCountryNames($country_shipping);

      $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Country:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$country_name_shipping.'</td></tr>';
      $state_shipping = $form_state->getValue('state_province'); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">';if($form_state->getValue('country')=='CA'){$output .= 'Province / Territory:';}else{$output .= 'State';} $output .= '</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$state_shipping.'</td></tr>';
      $zip_shipping = $form_state->getValue('zip_code'); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">'; if($form_state->getValue('country_billing')=='CA'){$output .= 'Postal Code:';}else{$output .= 'Zip Code';} $output .= '</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$zip_shipping.'</td></tr>';
      $output .= '</tbody></table>';

      // Billing Information
      $output .= '<h2 style="margin-bottom:0px;">Billing Information:</h2><table style="border-spacing:0px;border-bottom:1px solid grey;"><tbody>';
      if($form_state->getValue('billing_same_as_shipping') == 1){ $billing_same_as_shipping = $form_state->getValue('billing_same_as_shipping');$output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Billing same as Shipping:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">Yes</td></tr>';}
      else{
        if ($form_state->getValue('street_address_billing')){ $street_billing = $form_state->getValue('street_address_billing'); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Street Address:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$street_billing.'</td></tr>'; }
        if ($form_state->getValue('apt_suite_billing')){ $apartment_billing = $form_state->getValue('apt_suite_billing'); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Apt Suite:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$apartment_billing.'</td></tr>'; }
        if ($form_state->getValue('country_billing')){ $country_billing = $form_state->getValue('country_billing'); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Country:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$country_billing.'</td></tr>'; }
        if ($form_state->getValue('state_province_billing')){ $state_billing = $form_state->getValue('state_province_billing'); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">';if($form_state->getValue('country_billing')=='CA'){$output .= 'Province / Territory:';}else{$output .= 'State';} $output .= '</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$state_billing.'</td></tr>'; }
        if ($form_state->getValue('zip_code_billing')){ $zip_billing = $form_state->getValue('zip_code_billing'); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">'; if($form_state->getValue('country_billing')=='CA'){$output .= 'Postal Code:';}else{$output .= 'Zip Code';} $output .= '</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$zip_billing.'</td></tr>'; }
      }
      $output .= '</tbody></table>';
        
      $selected_samples = array_filter($form_state->getValue(['samples']));
      $output .= '<h2 style="margin-bottom:0px;">Sample Product Information:</h2><table style="border-spacing:0px;border-bottom:1px solid grey;"><tbody>';
      foreach ($selected_samples as $sku) {
        $sample_info = $this->getSampleInfo($sku); // Assuming this function retrieves product info
        $output .= '<tr>';
        $output .= '<td style="border:1px solid grey;border-bottom:0px;padding:6px;">' . $sample_info->title . ' - ' . $sample_info->size . '</td>';
        $output .= '<td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">' . $sample_info->sku . '</td>';
        $output .= '</tr>';
      }
      $output .= '</tbody></table>';

      $date = date("Ymd");
      $form_name = $this->t('Sample Request Form');


      // Get the value of the "Referred by Norgen Employee" option
      $referred_option = $this->t('@title', ['@title' => $form['step2']['sample_referred']['#options'][$form_state->getValue('sample_referred')]]);
      $referred_boolean = ($referred == 'yes') ? 0 : 1;

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
      $db_sample_info_output2 = $form_state->getValue('selected_sample_info_output');
      foreach ($selected_samples as $sku) {
      $sample_info = $this->getSampleInfo($sku);
      $selected_sample_info_output2 .= '<p>' . $sample_info->title . ' - SKU: ' . $sample_info->sku . '</p>';
      }

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
      $query->fields(['created_on', 'first_name', 'last_name', 'country', 'email','referred_radios',  'record', 'timestamp', 'form_name', 'company', 'phone', 'street1', 'city', 'state', 'zip', 'opt_in', 'email_opt_out', 'sample', 'num_samples']); //wrong syntax here breaks entire submit function 
      $query->values([$date, $first_name, $last_name, $country_name_shipping, $email, $referred_boolean, '', time(), $form_name, $company, $phone, $street_shipping, $city_shipping, $state_shipping, $zip_shipping, $subscribe_emaildb, $subscribe_optout, $db_sample_info_output2, $selected_samples_count]);
      $query->execute();
      $insert_id = \Drupal::database()->lastInsertId('forms_to_zoho');
      $insert_id = "#" . $insert_id;

      if ($form_state->hasAnyErrors()) {} 
      else {
        $time = time();
        // $recipient_email = 'orders@norgenbiotek.com';  // Angela requested that her and Sebastian be removed form emails, since they receive one through Zoho already
        $recipient_email = 'liam.howes@norgenbiotek.com';
        $customer_email = $email;
        $subject_org = ' [Sample Request] ' . $first_name . ' ' . $last_name . ' (' . $email . ')- ' . date('F j, Y, g:i a', $time) ." ".$insert_id;
        $subject_customer = 'Thank you for Submitting Sample Request';
        nor_forms_email_redirect($output, $recipient_email, $subject_org); 
        // nor_forms_email_redirect($output_customer, $customer_email, $subject_customer); // Don't really need to send an email anymore. A sales rep will just reach out to confirm billing info
      }
    }
    $form_state->setRebuild(TRUE);
    //leave empty else execution will happen on each submit button
  }
}