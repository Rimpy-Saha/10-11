<?php


namespace Drupal\request_quote\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\AfterCommand;
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
use Drupal\nor_forms\Libraries\Zoho\RecordWrapper;

class RequestQuoteForm extends FormBase{

  public function getFormID(){
    return 'request_quote_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $step = $form_state->get('step');
    // Set the initial current step ID if not set
    if ($step == null) {
      $step = 1;
      $form_state->set('step', $step);
    }
    

    $form['#attached']['library'][] = 'request_quote/request_quote';

    /* UTM Parameters */

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

    /* End of UTM Parameters */

    $form['#prefix'] = '<div id="request-quote-form-container">';

    $form['top'] = [
      '#type' => 'container',
      '#prefix' => '<div id="form-header">', // Adjust width as per your design
      '#suffix' => '</div>',
      '#tree' => TRUE,
    ];

    $form['top']['title'] = [
      '#type' => 'markup',
      '#markup' => '<h1 class="center">Request a Quote</h1>',
    ];

    $form['top']['progress'] = [
      '#type' => 'markup',
      '#weight' => 0,
    ];

    $form['top']['requested_product_info'] = [
      '#markup' => $step !== 1 ? $this->getProductTable($form, $form_state) : null,
    ];

    $form['top']['info-message'] = [
      '#type' => 'markup',
      '#prefix' => '<div id="info-message">',
      '#suffix' => '</div>',
    ];

    $form['top']['hr_separator'] = [
      '#type' => 'item',
      '#markup' => '<hr>',
    ];

    // Only add fields we need at each step: this makes browser default form submission error handling better, and we can render exactly what we want when we want. 
    // For example, we want a script snippet to insert and execute on the page when we land on step 2
    switch($step){
      case 1:
        $form['step_'.$step] = [
          '#type' => 'container',
          '#prefix' => '<div id="step1" style="width: 500px; margin: 0 auto; padding-top: 10px; padding-bottom: 10px;">', // Adjust width as per your design
          '#suffix' => '</div>',
          '#tree' => TRUE,
        ];

        $form['top']['progress']['#markup'] = '<progress value="3" max="10" class="norgen-progress-wrapper center">30%</progress>';

        $form['top']['info-message']['#markup'] = '<p>Submit a request and receive a quote with custom pricing within 24 hours. Please select the products and quantities you would like to include in your quote, and then proceed to the next step.</p>';
      
        $product_row_count = $form_state->get('product_row_count');
        // We have to ensure that there is at least one row field.
        if ($product_row_count === NULL) {
          $product_row_count = 1;
          $form_state->set('product_row_count', 1);
        }

        $form['step_'.$step]['products_fieldset'] = [
          '#type' => 'container',
          '#prefix' => '<div class="product-select-wrapper"><table class="table table-striped" id="names-fieldset-wrapper">',
          '#suffix' => '</table></div>',
          '#attributes' => ['class'=>['products-fieldset']],
        ];

              
        $form['step_'.$step]['products_fieldset']['table_header'] = [
          '#type' => 'item',
          '#markup' => '<thead><tr><th>Product</th><th>Quantity</th><th></th></tr></thead>',
        ];

        for ($row_num = 0; $row_num < $product_row_count; $row_num++) {


          $form['step_'.$step]['products_fieldset'][$row_num] = [
            '#type' => 'item',
            '#prefix' => '<tr id="names-fieldset-wrapper-' . $row_num . '">',
            '#suffix' => '</tr>',
            '#attributes' => [
              /* 'style' => 'display: flex; gap: 5px; width: 100%;', */
              'class' => ['product-fieldet-row'],
            ],
          ];

          $variationId = null;
          $quantity = null;
          $product_info = $form_state->get(['step_1_values', 'step_1', 'products_fieldset', $row_num, 'products']);
          if (!empty($product_info[0]['target_id']) && is_numeric($product_info[0]['target_id'])) {
            $variationId = $product_info[0]['target_id'];
            $quantity = $form_state->get(['step_1_values', 'step_1', 'products_fieldset', $row_num, 'quantity']);
          }

          // need to implement form_state storage and default_value for product and quantity fields so that upon re-rendering, the values will be correctly repopulated.
          $form['step_'.$step]['products_fieldset'][$row_num]['products'] = [
            '#type' => 'entity_autocomplete',
            '#title' => $this->t('Product '.($row_num + 1)),
            '#title_display' => 'invisible',
            '#target_type' => 'commerce_product_variation',
            '#tags' => TRUE,
            '#weight' => '0',
            '#attributes' => [
              'Placeholder' => $this->t('Search for SKU (Cat.) or Product Title'),
              'class' => ['entity-product-request-field'], // Add a custom CSS class
            ],
            '#selection_handler' => 'nor_product_autocomplete', 
            '#default_value' => isset($variationId) ? \Drupal::entityTypeManager()->getStorage('commerce_product_variation')->load($variationId) : null,
            '#prefix' => '<td>',
            '#suffix' => '</td>',
          ];


          $form['step_'.$step]['products_fieldset'][$row_num]['quantity'] = [
            '#type' => 'number',
            '#title' => $this->t('Quantity '.($row_num + 1)),
            '#title_display' => 'invisible',
            '#default_value' => isset($quantity) ? $quantity : 1,
            '#min' => 1,  // Set the minimum value to 1
            '#max' => 999,  // Set the minimum value to 999
            '#weight' => '0',
            '#attributes' => ['Placeholder' => $this->t('Enter Quantity'),],
            '#prefix' => '<td>',
            '#suffix' => '</td>',
          ];

          $form['step_'.$step]['products_fieldset'][$row_num]['remove_product'] = [
            '#type' => 'submit',
            '#name' => $row_num,
            '#value' => $this->t('Remove'),
            '#submit' => ['::removeProduct'],
            '#validate' => ['::validateRemove'],
            '#ajax' => [
              'callback' => '::updateProductRowsCallback',
              'wrapper' => 'names-fieldset-wrapper',
              'method' => 'replace',
            ],
            '#prefix' => '<td>',
            '#suffix' => '</td>',
          ];
          if($product_row_count <= 1) $form['step_'.$step]['products_fieldset'][$row_num]['remove_product']['#disabled'] = TRUE; // dont let people remove the last field
        }

        $form['step_'.$step]['actions'] = [
          '#type' => 'actions',
          '#attributes' => ['class' => ['quote-form-button-container']], // Add a custom class for styling
        ];
        
        $form['step_'.$step]['actions']['add_product'] = [
          '#type' => 'submit',
          '#value' => $this->t('Add Product Row'),
          '#submit' => ['::addProduct'],
          '#ajax' => [
            'callback' => '::updateProductRowsCallback',
            'wrapper' => 'names-fieldset-wrapper',
            'method' => 'replace',
          ],
        ];
        
        $form['step_'.$step]['actions']['next'] = [
          '#type' => 'submit',
          '#value' => $this->t('Next'),
          '#ajax' => [
            'callback' => '::nextPageCallback',
            'wrapper' => 'request-quote-form-container',
          ],
          '#validate' => ['::validateQuoteForm'],
          '#submit' => ['::nextSubmit'],
        ];
      break;

      case 2:
        $form['step_'.$step] = [
          '#type' => 'container',
          '#prefix' => '<div id="step2">',
          '#suffix' => '</div>',
        ];

        $form['top']['progress']['#markup'] = '<progress value="6" max="10" class="norgen-progress-wrapper center">60%</progress>';


        $form['step_'.$step]['customer_info'] = [
          '#type' => 'container',
          '#prefix' => '<div id="customer-info">',
          '#suffix' => '</div>',
        ];

        $form['step_'.$step]['customer_info']['sample_fname'] = [
          '#type' => 'textfield',
          '#default_value' => isset($form_state->get("step_".$form_state->get('step')."_values")['sample_fname']) ? $form_state->get("step_".$form_state->get('step')."_values")['sample_fname'] : nor_forms_user_first_name(),
          '#title' => $this->t('First Name'),
          '#placeholder' => t('First Name (Required)'),
          '#required' => TRUE,
        ];


        $form['step_'.$step]['customer_info']['sample_lname'] = [
          '#type' => 'textfield',
          '#default_value' => isset($form_state->get("step_".$form_state->get('step')."_values")['sample_lname']) ? $form_state->get("step_".$form_state->get('step')."_values")['sample_lname'] : nor_forms_user_last_name(),
          '#title' => $this->t('Last Name'),
          '#placeholder' => t('Last Name (Required)'),
          '#required' => TRUE,
        ];

        $form['step_'.$step]['customer_info']['sample_email'] = [
          '#type' => 'email',
          '#default_value' => isset($form_state->get("step_".$form_state->get('step')."_values")['sample_email']) ? $form_state->get("step_".$form_state->get('step')."_values")['sample_email'] : nor_forms_user_email(),
          '#title' => $this->t('Email Address'),
          '#placeholder' => t('Email Address (Required)'),
          '#required' => TRUE,
        ];

        $form['step_'.$step]['customer_info']['sample_phone'] = [
          '#type' => 'tel',
          '#default_value' => isset($form_state->get("step_".$form_state->get('step')."_values")['sample_phone']) ? $form_state->get("step_".$form_state->get('step')."_values")['sample_phone'] : "",
          '#title' => $this->t('Phone Number'),
          '#placeholder' => t('Phone Number (Required)'),
          '#required' => TRUE,
        ];

        $form['step_'.$step]['customer_info']['sample_company'] = [
          '#type' => 'textfield',
          '#default_value' => isset($form_state->get("step_".$form_state->get('step')."_values")['sample_company']) ? $form_state->get("step_".$form_state->get('step')."_values")['sample_company'] : "",
          '#title' => $this->t('Company / Institution'),
          '#placeholder' => t('Company / Institution (Required)'),
          '#required' => TRUE,
        ];

        $form['step_'.$step]['customer_info']['job_title'] = [
          '#type' => 'textfield',
          '#default_value' => isset($form_state->get("step_".$form_state->get('step')."_values")['job_title']) ? $form_state->get("step_".$form_state->get('step')."_values")['job_title'] : "",
          '#title' => $this->t('Job Title'),
          '#placeholder' => t('Job Title (Required)'),
          '#required' => TRUE,
        ];

        $form['step_'.$step]['sample_message'] = [
          '#type' => 'textarea',
          '#default_value' => isset($form_state->get("step_".$form_state->get('step')."_values")['sample_message']) ? $form_state->get("step_".$form_state->get('step')."_values")['sample_message'] : "",
          '#title' => t('Message'),
          '#rows' => 5, 
          '#placeholder' => t('Include any additional information, questions, or requests you would like to send with your quote request.'),
        ];

        $form['step_'.$step]['sample_subscribe'] = [
          '#type' => 'checkbox',
          '#default_value' => isset($form_state->get("step_".$form_state->get('step')."_values")['sample_subscribe']) ? $form_state->get("step_".$form_state->get('step')."_values")['sample_subscribe'] : null,
          '#title' => $this->t('Subscribe to our mailing list and be the first to hear about offers and news from Norgen Biotek.'),
          '#suffix' => '<div class="disclaimer">It is our responsibility to protect and guarantee that your data will be completely confidential. You can unsubscribe from Norgen emails at any time by clicking the link in the footer of our emails. For more information please view our <a href="/content/privacy-policy">Privacy Policy</a>.</div>',
        ];

        $form['step_'.$step]['actions'] = [
          '#type' => 'container',
          '#attributes' => ['class' => ['quote-form-button-container']], // Add a custom class for styling
        ];

        $form['step_'.$step]['actions']['back2'] = [
          '#type' => 'submit',
          '#value' => $this->t('Back'),
          '#name' => 'back2',
          '#submit' => ['::prevSubmit'],
          '#ajax' => [
            'callback' => '::prevPageCallback',
            'wrapper' => 'request-quote-form-container',
          ],
          '#limit_validation_errors' => [['back2']],
        ];

        $form['step_'.$step]['actions']['continue'] = [
          '#type' => 'submit',
          '#value' => $this->t('Continue'),
          '#ajax' => [
            'callback' => '::nextPageCallback',
            'wrapper' => 'request-quote-form-container',
          ],
          '#submit' => ['::nextSubmit'],
        ];
      break;

      case 3:
        $form['step_'.$step] = [
          '#type' => 'container',
          '#prefix' => '<div id="step3">',
          '#suffix' => '</div>',
        ];

        $form['top']['progress']['#markup'] = '<progress value="9" max="10" class="norgen-progress-wrapper center">90%</progress>';


        $form['step_'.$step]['shipping_information'] = [
          '#type' => 'fieldset',
          '#title' => t('Shipping Information'),
          '#attributes'=>array(
            'class'=>array('edit-shipping-information'),
          ),
        ];

        $form['step_'.$step]['shipping_information']['street_address'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Street Address'),
          '#placeholder' => t('Street Address (Required)'),
          '#default_value' => isset($form_state->get("step_".$form_state->get('step')."_values")['street_address']) ? $form_state->get("step_".$form_state->get('step')."_values")['street_address'] : "",
          '#required' => TRUE,
        ];

        $form['step_'.$step]['shipping_information']['apt_suite'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Apt., Suite, etc.'),
          '#placeholder' => t('Apt., Suite, etc.'),
          '#default_value' => isset($form_state->get("step_".$form_state->get('step')."_values")['apt_suite']) ? $form_state->get("step_".$form_state->get('step')."_values")['apt_suite'] : "",
        ];

        $form['step_'.$step]['shipping_information']['country'] = [
          '#type' => 'select',
          '#title' => $this->t('Country'),
          '#options' => getCountryOptions(), // Use the global function to get country options
          '#default_value' => isset($form_state->get("step_".$form_state->get('step')."_values")['country']) ? $form_state->get("step_".$form_state->get('step')."_values")['country'] : "",
          '#required' => TRUE,
        ];

        $form['step_'.$step]['shipping_information']['state_province'] = [
          '#type' => 'textfield',
          '#title' => $this->t('State / Province'),
          '#placeholder' => t('State / Province (Required)'),
          '#default_value' => isset($form_state->get("step_".$form_state->get('step')."_values")['state_province']) ? $form_state->get("step_".$form_state->get('step')."_values")['state_province'] : "",
          '#required' => TRUE,
        ];

        $form['step_'.$step]['shipping_information']['city_town'] = [
          '#type' => 'textfield',
          '#title' => $this->t('City / Town'),
          '#placeholder' => t('City / Town (Required)'),
          '#default_value' => isset($form_state->get("step_".$form_state->get('step')."_values")['city_town']) ? $form_state->get("step_".$form_state->get('step')."_values")['city_town'] : "",
          '#required' => TRUE,
        ];

        $form['step_'.$step]['shipping_information']['zip_code'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Zip / Postal Code'),
          '#placeholder' => t('Zip / Postal Code (Required)'),
          '#default_value' => isset($form_state->get("step_".$form_state->get('step')."_values")['zip_code']) ? $form_state->get("step_".$form_state->get('step')."_values")['zip_code'] : "",
          '#required' => TRUE,
        ];

        // Container for billing information fields
        $form['step_'.$step]['billing_information'] = [
          '#type' => 'fieldset',
          '#title' => t('Billing Information'),
          '#attributes'=>array(
            'class'=>array('edit-billing-information'),
          ),
          '#prefix' => '<div id="billing-info-wrapper">',
          '#suffix' => '</div>',
        ];

        $same_as_shipping_value = isset($form_state->get("step_".$form_state->get('step')."_values")['same_as_shipping']) ? $form_state->get("step_".$form_state->get('step')."_values")['same_as_shipping'] : 1;

        if($form_state->getValue('same_as_shipping')) $same_as_shipping_value = $form_state->getValue('same_as_shipping');


        $form['step_'.$step]['billing_information']['same_as_shipping'] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Same as Shipping Address'),
          '#attributes' => ['class' => ['billing-checkbox']], // Optional: Add class for styling or JavaScript targeting
          '#ajax' => [
            'callback' => '::billingCheckboxAjaxCallback',
            'wrapper' => 'billing-info-wrapper', // ID of the container to be updated via AJAX
            'event' => 'change', // Trigger AJAX on change event
          ],
          '#default_value' => $same_as_shipping_value,
        ];

        $same_as_shipping_checked = true;
        if($form_state->getValue('same_as_shipping') !== null) $same_as_shipping_checked = $form_state->getValue('same_as_shipping'); // get checked value as user updates it
        else $same_as_shipping_checked = $same_as_shipping_value; // user never interacted with the checkbox after step last rendered, use default value

        if(!$same_as_shipping_checked){
          $form['step_'.$step]['billing_information']['billing_information']['billing_street'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Street Address'),
            '#placeholder' => t('Street Address (Required)'),
            '#default_value' => isset($form_state->get("step_".$form_state->get('step')."_values")['street_address']) ? $form_state->get("step_".$form_state->get('step')."_values")['street_address'] : "",
            '#required' => TRUE,
          ];
  
          $form['step_'.$step]['billing_information']['billing_apt'] = [ // Corrected key
            '#type' => 'textfield',
            '#title' => $this->t('Apt., Suite, etc.'),
          ];
  
          $form['step_'.$step]['billing_information']['billing_country'] = [
            '#type' => 'select',
            '#title' => $this->t('Country'),
            '#options' => getCountryOptions(), // Use the global function to get country options
            '#default_value' => isset($form_state->get("step_".$form_state->get('step')."_values")['billing_country']) ? $form_state->get("step_".$form_state->get('step')."_values")['billing_country'] : "",
            '#required' => TRUE,
          ];
  
          $form['step_'.$step]['billing_information']['billing_state'] = [
            '#type' => 'textfield',
            '#title' => $this->t('State / Province'),
            '#placeholder' => t('State / Province (Required)'),
            '#default_value' => isset($form_state->get("step_".$form_state->get('step')."_values")['billing_state']) ? $form_state->get("step_".$form_state->get('step')."_values")['billing_state'] : "",
            '#required' => TRUE,
          ];
  
          $form['step_'.$step]['billing_information']['billing_city'] = [
            '#type' => 'textfield',
            '#title' => $this->t('City / Town'),
            '#placeholder' => t('City / Town (Required)'),
            '#default_value' => isset($form_state->get("step_".$form_state->get('step')."_values")['billing_city']) ? $form_state->get("step_".$form_state->get('step')."_values")['billing_city'] : "",
            '#required' => TRUE,
          ];
  
          $form['step_'.$step]['billing_information']['billing_zip'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Zip / Postal Code'),
            '#placeholder' => t('Zip / Postal Code (Required)'),
            '#default_value' => isset($form_state->get("step_".$form_state->get('step')."_values")['billing_zip']) ? $form_state->get("step_".$form_state->get('step')."_values")['billing_zip'] : "",
            '#required' => TRUE,
          ];
        }
        
        $form['step_'.$step]['google_recaptcha'] = [
          '#type'=> 'fieldset',
          '#description' => '<div class="g-recaptcha" data-sitekey="6Lcr4u0pAAAAAGj32knXkUzuHAXzj3CoAhtbJ1t5"></div>',
        ];

        $form['step_'.$step]['actions'] = [
          '#type' => 'container',
          '#attributes' => ['class' => ['quote-form-button-container']], // Add a custom class for styling
        ];

        $form['step_'.$step]['actions']['back3'] = [
          '#type' => 'submit',
          '#value' => $this->t('Back'),
          '#name' => 'back3',
          '#submit' => ['::prevSubmit'],
          '#ajax' => [
            'callback' => '::prevPageCallback',
            'wrapper' => 'request-quote-form-container',
          ],
          '#limit_validation_errors' => [['back3']],
        ];

        $form['step_'.$step]['actions']['submit'] = [
          '#type' => 'submit',
          '#button_type' => 'primary',
          '#value' => $this->t('Submit'),
          '#ajax' => [
            'callback' => '::submitCallback',
            'wrapper' => 'request-quote-form-container',
          ],
          '#validate' => ['::validateQuoteForm'],
          '#submit' => ['::submitForm'],
        ];

        $form['error_message4'] = [
          '#type' => 'markup',
          '#prefix' => '<div id="step3-error-message4">',
          '#suffix' => '</div>',
        ];
      break;
    }

    $form['#suffix'] = '</div>';
    return $form;
  
  }
  
  
  public function getProductTable(array &$form, FormStateInterface $form_state){
    $product_info_array = [];
    // Retrieve product information from the form fields
    if($form_state->get(['step_1_values', 'step_1', 'products_fieldset']) !== null){
      $product_row_count = $form_state->get('product_row_count');
      for ($row_num = 0; $row_num < $product_row_count; $row_num++) {
        $product_info = $form_state->get(['step_1_values', 'step_1', 'products_fieldset', $row_num, 'products']);
        if (!empty($product_info[0]['target_id']) && is_numeric($product_info[0]['target_id'])) {
          $variationId = $product_info[0]['target_id'];
          $quantity = $form_state->get(['step_1_values', 'step_1', 'products_fieldset', $row_num, 'quantity']);
          $variation = \Drupal::entityTypeManager()->getStorage('commerce_product_variation')->load($variationId);
          $product_info_array[] = [
            'sku' => $variation->getSku(),
            'name' => $variation->getProduct()->getTitle(),
            'quantity' => $quantity,
          ];
        }
      }
      // Append selected sample info to the output
      if (!empty($product_info_array)) {
        $output = '<div class="selected-product-table-wrapper"><table class="table table-striped" style="border-spacing:0px;border-bottom:1px solid grey;">';
        $output .= '<thead><tr><th style="border:1px solid grey;border-bottom:0px;padding:6px;">Selected Product</th><th style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">SKU</th><th style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">Quantity</th></tr></thead>';
        $output .= '<tbody>';
        foreach ($product_info_array as $info) {
          $output .= '<tr>';
          $output .= '<td style="border:1px solid grey;border-bottom:0px;padding:6px;">' . $info['name'] .'</td>';
          $output .= '<td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">' . $info['sku'] . '</td>';
          $output .= '<td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">' . $info['quantity'] . '</td>';
          $output .= '</tr>';
        }
        $output .= '</tbody></table></div>';
      }
    }
    return $output;
  }


  public function updateProductRowsCallback(array &$form, FormStateInterface $form_state) {
    return $form['step_1']['products_fieldset'];
  }

  public function addProduct(array &$form, FormStateInterface $form_state) {
    $cur_rows = $form_state->get('product_row_count');
    $rows = $cur_rows + 1;

    /* for($row = 0; $row < $cur_rows; $row++){
      $form_state->set(['selected_products', $row], $form_state->getValue(['step_1','products_fieldset', $row, 'products'])[0], TRUE);
    } */

    $form_state->set('product_row_count', $rows);

    $form_state->setRebuild();
  }

  public function removeProduct(array &$form, FormStateInterface $form_state) {
    $row_num = $form_state->getTriggeringElement()['#name'];

    $number_of_tags = $form_state->get('product_row_count');

    $form_state->set('product_row_count', $number_of_tags - 1);

    $form_state->unsetValue([1, 'products_fieldset', $row_num]);

    $form_state->setValue('products_fieldset', array_values($form_state->getValue(['step_1', 'products_fieldset']))); // re-index array from 0

    unset($form['step_1']['products_fieldset'][$row_num]);
    $form['step_1']['products_fieldset'] = array_values($form['step_1']['products_fieldset']); // re-index array from 0

    $new_input = $form_state->getUserInput();
    unset($new_input['step_1']['products_fieldset'][$row_num]);

    $new_input['step_1']['products_fieldset'] = array_values($new_input['step_1']['products_fieldset']); // re-index array from 0

    $form_state->setUserInput($new_input);

    $form_state->setRebuild(TRUE);
  }

  public function validateRemove(array $form, FormStateInterface $form_state){
    $number_of_tags = $form_state->get('product_row_count');
    if($number_of_tags <= 1){
      $form_state->setErrorByName('products', $this->t('Please select at least one product.'));
      $form_state->setRebuild(TRUE);
      return;
    }
  }

  public function validateQuoteForm(array $form, FormStateInterface $form_state) {
    switch($form_state->get('step')){
      case 1:
        $first_name = $form_state->getValue('sample_fname');
        $product_row_count = $form_state->get('product_row_count');

        if($product_row_count < 1){
          $form_state->setErrorByName('products', $this->t('Please select at least one product.'));
          break;
        }
        $product_count = 0;
        for ($row_num = 0; $row_num < $product_row_count; $row_num++) {
          if (isset($form_state->getValue(['step_1','products_fieldset', $row_num, 'products'])[0]) && count($form_state->getValue(['step_1','products_fieldset', $row_num, 'products'])[0]) > 0) {
              $product_count++;
          }
        }  
        if($product_count < 1){
          $form_state->setErrorByName('products', $this->t('Please select at least one product.'));
        }
        break;
      case 2: // step 2
        // Get values from relevant form fields
        $first_name = $form_state->getValue('sample_fname');
        $last_name = $form_state->getValue('sample_lname');
        $email = $form_state->getValue('sample_email');
        $phone = $form_state->getValue('sample_phone');
        $company = $form_state->getValue('sample_company');
        $job = $form_state->getValue('job_title');

        // Check if the required fields are empty
        if (empty($first_name ) || empty($last_name) || empty($email) || empty($phone) || empty($company) || empty($job)) {
          // Trigger an error and prevent further processing
          $form_state->setErrorByName('first_name ', $this->t('First Name is required.'));
          $form_state->setErrorByName('last_name', $this->t('Last Name is required.'));
          $form_state->setErrorByName('email', $this->t('Email Address is required.'));
          $form_state->setErrorByName('phone', $this->t('Phone Number is required.'));
          $form_state->setErrorByName('company', $this->t('Company / Institution is required.'));
          $form_state->setErrorByName('job', $this->t('Job Title is required.'));
        }
        break;
      case 3: // step 3
        $street = $form_state->getValue('street_address');
        // $apartment = $form_state->getValue('apt_suite');
        $country = $form_state->getValue('country');
        $state = $form_state->getValue('state_province');
        $city = $form_state->getValue('city_town');
        $zip = $form_state->getValue('zip_code');

        // Shipping Information
        if (empty($street)) { // Trigger an error and prevent further processing
          $form_state->setErrorByName('street ', $this->t('Shipping Street Name is required.'));
        }
        if(empty($country)){
          $form_state->setErrorByName('country', $this->t('Shipping Country is required.'));
        }
        if(empty($state)){
          $form_state->setErrorByName('state', $this->t('Shipping State/ Province is required.'));
        }
        if(empty($city)){
          $form_state->setErrorByName('city', $this->t('Shipping City is required.'));
        }
        if(empty($zip)){
          $form_state->setErrorByName('zip', $this->t('Shipping Zip/ Postal Code is required.'));
        }

        // Billing Information
        if(!$form_state->getValue('same_as_shipping')){ // billing is NOT the same as shipping, and billing fields are shown, validate them
          if (empty($street)) { // Trigger an error and prevent further processing
            $form_state->setErrorByName('billing_street ', $this->t('Billing Street Name is required.'));
          }
          if(empty($country)){
            $form_state->setErrorByName('billing_country', $this->t('Billing Country is required.'));
          }
          if(empty($state)){
            $form_state->setErrorByName('billing_state', $this->t('Billing State/ Province is required.'));
          }
          if(empty($city)){
            $form_state->setErrorByName('billing_city', $this->t('Billing City is required.'));
          }
          if(empty($zip)){
            $form_state->setErrorByName('billing_zip', $this->t('Billing Zip/ Postal Code is required.'));
          }
        }

        if (isset($_POST['g-recaptcha-response']) && $_POST['g-recaptcha-response'] != '') {
          $captcha_response = $_POST['g-recaptcha-response'];
          $remote_ip = $_SERVER['REMOTE_ADDR'];

          $result = $this->verifyGoogleRecaptcha($captcha_response, $remote_ip);

          $data = json_decode($result, true);

          if (!$data['success']) {
            $form_state->setErrorByName('google_recaptcha', t('Please complete the captcha to prove you are human'));
          }
        } 
        else {
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

  /* AJAX Callback: show/hide billing fields */
  public function billingCheckboxAjaxCallback(array &$form, FormStateInterface $form_state) {
    return $form['step_3']['billing_information'];
  }

  /* AJAX Callback: go back a page */
  public function prevPageCallback(array &$form, FormStateInterface $form_state) {
    return $form;
  }
  /* AJAX Callback: go next page */
  public function nextPageCallback(array &$form, FormStateInterface $form_state) {
    if($form_state->get('step') == 3){
      $Selector = '#request-quote-form-container';
      $response = new AjaxResponse();
      $response->addCommand(new ReplaceCommand(NULL, $form));
      $response->addCommand(new AfterCommand($Selector, '<script>renderCaptcha("'.$Selector.'");</script>')); // need this to render the captcha whenever the user lands on step 3 (the page with captcha)
      return $response;
    }
    return $form;
  }

  /* AJAX Callback: go to thank you page */
  public function submitCallback(array &$form, FormStateInterface $form_state){
    $Selector = '#request-quote-form-container > form';
    if ($form_state->hasAnyErrors()) {
      $response = nor_forms_ajax_error($form, $form_state, $Selector);
      return $response;
    } else {

      $response = nor_forms_quote_request_sent_ajax($form, $form_state, $Selector);

      return $response;
    }
  }

  /* Submit Function */
  public function prevSubmit(array &$form, FormStateInterface $form_state) {
    /* Skip validation, suppress required field validation */
    /* Store this step's submission info in the form_state */
    // validation is suppressed so values are not submitted, so get them from user input and store them for when they return to the step!
    $user_input = $form_state->getUserInput();
    if(empty($user_input['same_as_shipping'])) $user_input['same_as_shipping'] = 0;
    $form_state->set("step_".$form_state->get('step')."_values", $user_input); 
    $form_state->set('step', $form_state->get('step') <= 1 ? 1 : $form_state->get('step') - 1);
    $form_state->setRebuild(TRUE);
  }
  /* Submit Function */
  public function nextSubmit(array &$form, FormStateInterface $form_state) {
    /* Store this step's submission info in the form_state */
    $form_state->set("step_".$form_state->get('step')."_values", $form_state->getValues()); 
    $form_state->set('step', $form_state->get('step') + 1);
    $form_state->setRebuild(TRUE);
  }

  /* Final Form Submission Function */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->set("step_".$form_state->get('step')."_values", $form_state->getValues()); // store step values (step_1_values, step_2_values, step_3_values)

    $date = date("Ymd");  
    $form_name = $this->t('Request Quote Form (multistep)');

    $utm_source = $form_state->getValue('utm_source');
    $utm_medium = $form_state->getValue('utm_medium');
    $utm_campaign = $form_state->getValue('utm_campaign');
    $utm_id = $form_state->getValue('utm_id');
    $utm_term = $form_state->getValue('utm_term');
    $utm_content = $form_state->getValue('utm_content');

    $products = '';
    $prod_quantity = '';

    // Once each step is submitted, the submitted values are then stored in the form_state storage arrays: step_1_values, step_2_values, step_3_values
    // Therefore, the data in those storage arrays has already been validated and sanitized by Drupal and should therefore be safe for submitting

    // message to sales team
    $output = '<p>Hello,</p>
    <p>A customer has submitted the Quote Request Form.</p>'; 

    // PRODUCT INFORMATION [STEP 1]
      $product_info_array = [];
      // Retrieve product information from the form fields
      $product_row_count = $form_state->get('product_row_count');
      for ($row_num = 0; $row_num < $product_row_count; $row_num++) {
        $product_info = $form_state->get(['step_1_values', 'step_1', 'products_fieldset', $row_num, 'products']);
        if (!empty($product_info[0]['target_id']) && is_numeric($product_info[0]['target_id'])) {
          $variationId = $product_info[0]['target_id'];
          $quantity = $form_state->get(['step_1_values', 'step_1', 'products_fieldset', $row_num, 'quantity']);
          $variation = \Drupal::entityTypeManager()->getStorage('commerce_product_variation')->load($variationId);
          $product_info_array[] = [
            'sku' => $variation->getSku(),
            'name' => $variation->getProduct()->getTitle(),
            'quantity' => $quantity,
          ];
        }
      }
      $form_state->set('product_info_array', $product_info_array);

      // Append selected sample info to the output
      if (!empty($product_info_array)) {
        $output .= '<h2 style="margin-bottom:0px;">Product Information:</h2><table style="border-spacing:0px;border-bottom:1px solid grey;">';
        $output .= '<thead><tr><th style="border:1px solid grey;border-bottom:0px;padding:6px;">Product Name</th><th style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">SKU</th><th style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">Quantity</th></tr></thead>';
        $output .= '<tbody>';
        foreach ($product_info_array as $key => $info) {
          $output .= '<tr>';
          $output .= '<td style="border:1px solid grey;border-bottom:0px;padding:6px;">' . $info['name'] .'</td>';
          $output .= '<td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">' . $info['sku'] . '</td>';
          $output .= '<td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">' . $info['quantity'] . '</td>';
          $output .= '</tr>';

          if($key !== array_key_last($product_info_array)){
            $products .= $info['sku'].',';
            $prod_quantity .= $info['quantity'].',';
          }
          else {
            $products .= $info['sku'];
            $prod_quantity .= $info['quantity'];
          }

        }
        $output .= '</tbody></table>';
      }
    ////////////////////////////////
    
    // CUSTOMER INFORMATION [STEP 2]
      $subscribe = $form_state->get(['step_2_values','sample_subscribe']);
      $subscribe_text = 'No';
      if($subscribe) $subscribe_text = 'Yes';
      $output .= '<h2 style="margin-bottom:0px;">Customer Information:</h2><table style="border-spacing:0px;border-bottom:1px solid grey;"><tbody>';
      $first_name = $form_state->get(['step_2_values','sample_fname']); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">First Name:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$first_name.'</td></tr>';
      $last_name = $form_state->get(['step_2_values','sample_lname']); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Last Name:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$last_name.'</td></tr>';
      $email = $form_state->get(['step_2_values','sample_email']); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Email:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$email.'</td></tr>';
      $phone = $form_state->get(['step_2_values','sample_phone']); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Phone:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$phone.'</td></tr>';
      $company = $form_state->get(['step_2_values','sample_company']); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Company:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$company.'</td></tr>';
      $job = $form_state->get(['step_2_values','job_title']); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Job:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$job.'</td></tr>';
      //$referred = $form_state->get(['step_2_values','referred']); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Referred by Employee:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$referred.'</td></tr>';
      $message = $form_state->get(['step_2_values','sample_message']); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Message:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$message.'</td></tr>';
      $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Subscribe to Mailing List:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$subscribe_text.'</td></tr>';
      $output .= '</tbody></table>';
    //////////////////////////////////

    // SHIPPING/BILLING INFORMATION [STEP 3]
      //// Shipping
      $output .= '<h2 style="margin-bottom:0px;">Shipping Information:</h2><table style="border-spacing:0px;border-bottom:1px solid grey;"><tbody>';
      $street1 = $form_state->get(['step_3_values','street_address']); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Street Address:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$street1.'</td></tr>';
      $apartment = $form_state->get(['step_3_values','apt_suite']); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Apt., Suite, etc.:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$apartment.'</td></tr>';
      $shipping_country_name = getCountryNames($form_state->get(['step_3_values','country'])); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Country:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$shipping_country_name.'</td></tr>';
      $state = $form_state->get(['step_3_values','state_province']); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Province / State:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$state.'</td></tr>';
      $city = $form_state->get(['step_3_values','city_town']); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">City / Town:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$city.'</td></tr>';
      $zip = $form_state->get(['step_3_values','zip_code']); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Postal / Zip Code:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$zip.'</td></tr>';
      $output .= '</tbody></table>';
      //// Billing
      $output .= '<h2 style="margin-bottom:0px;">Billing Information:</h2><table style="border-spacing:0px;border-bottom:1px solid grey;"><tbody>';
      if($form_state->get(['step_3_values','same_as_shipping'])){
        $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Same as Shipping Address</td></tr>';
      }
      else{
        $billingStreet = $form_state->get(['step_3_values','billing_street']); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Street Address:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$billingStreet.'</td></tr>';
        $billingApt = $form_state->get(['step_3_values','billing_apt']); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Apt., Suite, etc.:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$billingApt.'</td></tr>';
        $billing_country_name = getCountryNames($form_state->get(['step_3_values','billing_country'])); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Country:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$billing_country_name.'</td></tr>';
        $billingState = $form_state->get(['step_3_values','billing_state']); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Province / State:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$billingState.'</td></tr>';
        $billingCity = $form_state->get(['step_3_values','billing_city']); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">City / Town:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$billingCity.'</td></tr>';
        $billingZip = $form_state->get(['step_3_values','billing_zip']); $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Postal / Zip Code:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$billingZip.'</td></tr>';
      }
      $output .= '</tbody></table>';      
    //////////////////////////////////////
  
    if(!isset($first_name)) {
      $first_name = 'NULL';
    }
    if(!isset($email))  {
      $email = 'NULL';
    }

    // Zoho Upsert
    try{
      $zoho = new RecordWrapper('leads');
      $record = [
        'First_Name' => $first_name,
        'Last_Name' => $last_name,
        'Email' => $email,
        'Company' => $company,
        'Job_Position' => $job,
        'Country' => $shipping_country_name,
        'Phone' => $phone,
        'Street' => $street1,
        'City' => $city,
        'State' => $state,
        'Zip_Code' => $zip,
        'Lead_Source' => 'Website Form',
        'Web_Forms' => [$form_name],
      ];
      // Perform the upsert operation
      $upsertResult = $zoho->upsert($record);
    }catch (Exception $e) {}


    // Database Insertion
    $query = \Drupal::database()->insert('forms_to_zoho');
    $query->fields(['created_on','first_name', 'last_name', 'country', 'email','record','timestamp', 'form_name', 'company', 'job_title', 'phone', 'street1', 'street2', 'city', 'state', 'zip', 'products', 'prod_quantity', 'notes', 'opt_in', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_id', 'utm_term', 'utm_content']); //wrong syntax here breaks entire submit function 
    $query->values([$date,$first_name,$last_name, $shipping_country_name, $email,'',time(), $form_name, $company, $job, $phone, $street1, $apartment, $city, $state, $zip, $products, $prod_quantity, $message, $subscribe, $utm_source, $utm_medium, $utm_campaign, $utm_id, $utm_term, $utm_content]);
    $result = $query->execute();
    $id= is_numeric($result)? $result : '00';

    if (!$form_state->hasAnyErrors()){
      $time = time();
      //$recipient_email = 'orders@norgenbiotek.com,sowmya.movva@norgenbiotek.com';
      //$recipient_email = 'liam.howes@norgenbiotek.com';
      $recipient_email = 'orders@norgenbiotek.com,sabah.butt@norgenbiotek.com';// real addresses
      //$recipient_email = 'liam.howes@norgenbiotek.com';
      $customer_email = $email; 
      $subject_org = 'New Quote Request: Action Required #'.$id;
      nor_forms_email_redirect($output, $recipient_email, $subject_org);
    }
  }
}