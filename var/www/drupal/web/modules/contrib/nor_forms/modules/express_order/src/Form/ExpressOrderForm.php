<?php

namespace Drupal\express_order\Form;

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

class ExpressOrderForm extends FormBase {

  public function getFormId() {
    return 'express_order';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    // $form['title'] = [
    //   'type' => 'string',
    //   '#markup' => '<h2>Order by Product</h2>',
    // ];

    $form['description'] = [
      '#type' => 'item',
      '#markup' => '<h2>Order by Product</h2><div class="field-labels flex"></div>',
    ];

    $form['#prefix'] = '<div id="expressorder_form-container" class="expressorder_form-container col-sm-12 col-lg-6">';


    // Gather the number of rows in the form already.
    $row_count = $form_state->get('row_count');
    // We have to ensure that there is at least one row field.
    if ($row_count === NULL) {
      $form_state->set('row_count', 2);
      $form_state->set("row_0_active", 1);
      $form_state->set("row_1_active", 1);
      $row_count = 2;
    }

    $form['#tree'] = TRUE;
    $form['products_fieldset'] = [
      '#type' => 'container',
      '#prefix' => '<div id="names-fieldset-wrapper">',
      '#suffix' => '</div>',
      '#attributes' => ['class'=>['products-fieldset']],
    ];

    for ($row_no = 0; $row_no < $row_count; $row_no++) {

      $is_active_row = $form_state->get("row_" . $row_no . "_active");

      //We check if the row is active
      if ($is_active_row) {

        $form['products_fieldset'][$row_no] = [
          '#type' => 'container',
          '#prefix' => '<div id="names-fieldset-wrapper-'.$row_no.'">',
          '#suffix' => '</div>',
          '#attributes' => ['style' => ['display:flex;gap:10px;'], 'class'=>['product-fieldet-container']],
        ];

        $form['products_fieldset'][$row_no]['products'] = [
          '#type' => 'entity_autocomplete',
          '#target_type' => 'commerce_product_variation',
          '#tags' => TRUE,
          '#weight' => '0',
          '#attributes' => ['Placeholder' => $this->t('Please Choose A Product'),],
          '#selection_handler' => 'nor_product_autocomplete', 
        ];

        $form['products_fieldset'][$row_no]['quantity'] = [
          '#type' => 'number',
          '#default_value' => '1',
          '#min' => 1,  // Set the minimum value to 1
          '#weight' => '0',
          '#attributes' => ['Placeholder' => $this->t('Please specify a quantity'),]
        ];

        $form['products_fieldset'][$row_no]['remove_product'] = [
          '#type' => 'submit',
          '#name' => $row_no,
          '#value' => $this->t('Remove'),
          '#submit' => ['::removeCallback'],
          '#ajax' => [
            'callback' => '::addmoreCallback',
            'wrapper' => 'names-fieldset-wrapper',
          ],
          '#attributes' => ['style' => ['height: fit-content; margin-top: 7px;}']],
        ];

      }
    }

    $form['products_fieldset']['actions'] = [
            '#type' => 'actions',
    ];
    $form['products_fieldset']['actions']['add_product'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Product Row'),
      '#submit' => ['::addOne'],
      '#ajax' => [
        'callback' => '::addmoreCallback',
        'wrapper' => 'names-fieldset-wrapper',
      ],
    ];

    
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add to Cart'),
      '#button_type' => 'primary',
      '#ajax' => ['callback' => '::submitAjaxForm',
        'event' => 'click',
        'method' => 'append', 'effect' => 'fade',]
    ];


       $form['#suffix'] = '</div>';


        return $form;
    }

    /**
     * Callback for both ajax-enabled buttons.
     *
     * Selects and returns the fieldset with the names in it.
     */
    public function addmoreCallback(array &$form, FormStateInterface $form_state) {
        return $form['products_fieldset'];
    }

    /**
     * Submit handler for the "add-one-more" button.
     *
     * Increments the max counter and causes a rebuild.
     */
    public function addOne(array &$form, FormStateInterface $form_state) {
        $cur_rows = $form_state->get('row_count');
        $rows = $cur_rows + 1;
        $form_state->set('row_count', $rows);
        $form_state->set("row_" . $cur_rows . "_active", 1);
        $form_state->setRebuild();
    }

    /**
     * Submit handler for the "remove one" button.
     *
     * Decrements the max counter and causes a form rebuild.
     */
    public function removeCallback(array &$form, FormStateInterface $form_state) {
        $button_clicked = $form_state->getTriggeringElement()['#name'];
        $form_state->set("row_" . $button_clicked . "_active", 0);
        $form_state->setRebuild();
    }

    public function validateForm(array &$form, FormStateInterface $form_state) {
    }

    //public function submitForm(array &$form, FormStateInterface $form_state) 
    public function submitAjaxForm(array &$form, FormStateInterface $form_state) 
    {
      
      $first_name = $form_state->getValue('fname');
      $last_name = $form_state->getValue('lname');
      $email = $form_state->getValue('email');
      $output = '<p><span style="color:red">Test Output 2</span></p><p>This includes html from form 1! <br />First Name: ' . $first_name . ' Last Name: ' . $last_name . '</p>';
   
      $variationId = 9;
      $variation = \Drupal::entityTypeManager()->getStorage('commerce_product_variation')->load($variationId);
      $quantity = 40;

      $row_count = $form_state->get('row_count');
      // print_r($row_count); // THIS WORKS
      $row_count = $form_state->get('row_count');
      $status_message=" the following products could not be added: ";
      $status_message = '';
      for ($row_no = 0; $row_no < $row_count; $row_no++) 
      {
        // Retrieve product information from the form state for each row
        if (isset($form_state->getValue(['products_fieldset', $row_no, 'products'])[0])  && count($form_state->getValue(['products_fieldset', $row_no, 'products'])[0])> 0)
        {
          $product_info = $form_state->getValue(['products_fieldset', $row_no, 'products'])[0];
          
          if (isset($product_info['target_id']) && is_numeric($product_info['target_id']) && $product_info['target_id'] > 0) 
          {
            $variationId = $product_info['target_id'];
            $quantity = $form_state->getValue(['products_fieldset', $row_no, 'quantity']);
            $variation = \Drupal::entityTypeManager()->getStorage('commerce_product_variation')->load($variationId);
            // Call your add_to_cart function with the retrieved information.

            // Call your add_to_cart function with the retrieved information.
            $variation_id = addToCart($variation, $quantity);
             
           
            
            // add_to_cart($variation, $quantity, $variationId); // put variation_id only if you are getting can't be purchased error
          }   
          // print('The variation id: '.$variationId.' the '.$quantity); // THIS WORKS
        }
                                           
      }
    
      $date = date("Ymd");
      $form_name = $this->t('Express Order');
      $user = \Drupal::currentUser();
      $email = $user->getEmail();
      if($email == null) $email = 'anonymous';
      $query = \Drupal::database()->insert('forms_to_zoho');
      $query->fields(['created_on', 'email', 'record', 'timestamp', 'form_name']);
      $query->values([$date, $email, '', time(), $form_name]);
      $query->execute();

      // $form_state->setErrorByName('norgen_email', $this->t('Please enter a v. '.$quantity));
      // return $form;
      // $message = json_encode($result);
      // $form_state->setErrorByName('norgen_fname', $this->t($message));
      $Selector = '#expressorder_form-container';

      $Content = '<div class="my_top_message">Form Submitted Successfully'.$status_message.'</div>';
      $response = new AjaxResponse();
      if ($form_state->hasAnyErrors()) {

        $response->addCommand(new ReplaceCommand('#expressorder_form-container', $form));
        $messages = \Drupal::messenger()->deleteAll();
        return $response; 
      } 
      
      else {
        /* nor_forms_submit_email($output); */
       /*  $response->addCommand(new HtmlCommand($Selector,$Content)); */
        /* $response->addCommand(new ReplaceCommand(NULL, $form)); */
       /*  $currentURL = Url::fromRoute('https://drupal.norgenbiotek.com/content/express-order'); */
        $current_path = \Drupal::service('path.current')->getPath();
        $current_uri = \Drupal::service('path_alias.manager')->getAliasByPath($current_path);
        $response->addCommand(new RedirectCommand($current_uri));
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

}