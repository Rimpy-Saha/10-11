<?php

namespace Drupal\express_order_file\Form;

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

class ExpressOrderFormFile extends FormBase {

  public function getFormId() {
    return 'express_order_file';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['#prefix'] = '<div id="norgen_form-container" class="norgen_form-container col-sm-12 col-lg-6">';

    $form['title'] = [
      'type' => 'item',
      '#markup' => '<h2>Order by File</h2>',
    ];

    $form['description'] = [
      '#type' => 'item',
      '#markup' => '<div id="info-head">
      This form allows you to mass-add products to your cart using a CSV file, please download a sample file here: <a href="/sites/default/files/template_files/order.csv" download>CSV</a>
      
      <hr style="width:50%;margin:2% auto;">
      </div>',
    ];

    // $form['norgen_fname'] = [
    //   '#type' => 'textfield',
    //   '#title' => $this->t('First Name'),
    //   '#default_value' => 'Test',
    //   '#required' => TRUE,
    // ];
    $form['file_upload'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Upload File'),
      '#required' => TRUE,
      '#upload_validators' => [
        'file_validate_extensions' => ['csv xlsx'],
      ],
      '#description' => $this->t('Allowed extensions: csv xlsx.'),
      '#upload_location' => 'public://smart_1',
      '#attributes' => [
        'class' => ['dropzone'],
      ],
      '#dropzone' => [
        // 'url' => file_create_url(file_default_scheme() . '://managed_file/ajax/upload/file_upload'),
      ],
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add to Cart'),
      '#button_type' => 'primary',
      '#validate' => ['::validateSubmit'],
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
    public function validateForm(array &$form, FormStateInterface $form_state){}
    public function validateSubmit (array &$form, FormStateInterface $form_state)
    {
      $errors = [];
    // $form_state->setErrorByName('file_upload', $this->t('Invalid file type. Please upload a CSV or CLS file.'));
    $file_upload = $form_state->getValue('file_upload', 0);
    if (!empty($file_upload[0])) {
      $file = File::load($file_upload[0]);
      if ($file) {
        // Validate file type and size.
        $valid_extensions = ['csv', 'xls', 'cls', 'xlsx']; // Add more if needed.
        $file_info = pathinfo($file->getFileUri());
        $file_extension = customStrToLower($file_info['extension']);
        if (!in_array($file_extension, $valid_extensions)) {
          // $errors['norgen_fname'] = $this->t('Invalid file type. Please upload a CSV or CLS file.');
          $form_state->setErrorByName('file_upload', $this->t('Invalid file type. Please upload a CSV or CLS file.'));
        }

        $max_file_size = 1024 * 1024; // 1 MB (Adjust as needed).
        if ($file->getSize() > $max_file_size) {
          // $errors['norgen_fname'] = ($this->t('File size exceeds the maximum limit. Please upload a smaller file.'));
          $form_state->setErrorByName('file_upload', $this->t('File size exceeds the maximum limit. Please upload a smaller file.'));
        }

        // Validate file structure.
        $file_path = \Drupal::service('file_system')->realpath($file->getFileUri());
        $file_contents = file_get_contents($file_path);
        $lines = explode("\n", $file_contents);
        $header_found = -1;
        $sku_index = -1;
        $quantity_index = -1;
        // print_r($lines); // THIS WORKS
        // Check if the header contains the required titles.
        foreach ($lines as $line) {

          
        
          $header_titles = ['sku', 'quantity', 'name', 'cat no.']; // Add more if needed.
          $columns = str_getcsv($line);
          $columns_lower = array_map('customStrToLower', $columns);
          if($sku_index == -1)
          {
            $sku_index = getColumnIndex($columns_lower, ['sku', 'cat no.']);
          }
          if($quantity_index == -1)
          {
            $quantity_index = getColumnIndex($columns_lower, ['quantity']);
          }
          if (count(array_intersect($columns_lower, $header_titles)) === (count($header_titles)-1)) {
            $header_found = 1;
            break;
          }
        }

        if ($header_found<0) {
          // $errors['file_upload'] = 'Invalid file structure. Please ensure the file contains columns with titles: SKU, Quantity, Name (or Cat No.).';
          $form_state->setErrorByName('file_upload', $this->t('Invalid file structure. Please ensure the file contains columns with titles: SKU (or Cat No.), Quantity, Name.'));
        }

        // Validate if there is at least one pair of SKU and Quantity.
        $sku_quantity_found = FALSE;
        foreach ($lines as $line) {
          $columns = str_getcsv($line);
          // $sku_index = getColumnIndex($columns, ['sku', 'cat no.']);
          // $quantity_index = getColumnIndex($columns, ['quantity']);
          // print_r($columns[$sku_index]." ".$columns[$quantity_index]); // THIS WORKS
          if ($sku_index !== FALSE && $quantity_index !== FALSE && !empty($columns[$sku_index])&& is_numeric($columns[$quantity_index]) &&  $columns[$quantity_index] > 0) {
            $sku_quantity_found = TRUE;
            break;
          }
        }
        // print('HIIIII '.$sku_quantity_found."   ae5y " );
        if (!$sku_quantity_found) {
          // $errors['file_upload'] =($this->t('No valid SKU and Quantity pairs found in the file.'));
          $form_state->setErrorByName('file_upload', $this->t('No valid SKU and Quantity pairs found in the file.'));
        }
      }
    }
    else
    {
      $form_state->setErrorByName('file_upload', $this->t('No file provided for mass upload.'));
    }

    if (!empty($errors)) {
      foreach ($errors as $element => $error) {
          // $form_state->setErrorByName($element, $error);
      }
    }
    $sku_index = -1;
    $quantity_index = -1;
    // $sku_quantity_array = [];
      $header_titles = ['sku', 'quantity', 'name', 'cat no.']; // Adjust as needed.
      foreach ($lines as $line) {
        $columns = str_getcsv($line);
        $columns_lower = array_map('customStrToLower', $columns);

        if (count(array_intersect($columns_lower, $header_titles)) === (count($header_titles)-1)) {
          $header_found = TRUE;
        }

        if($sku_index == -1)
        {
          $sku_index = getColumnIndex($columns_lower, ['sku', 'cat no.']);
        }
        if($quantity_index == -1)
        {
          $quantity_index = getColumnIndex($columns_lower, ['quantity']);
        }
        // print_r($sku_index);
        // print_r($quantity_index);
        // if ($sku_index >=0 && $quantity_index >=0 && !empty($columns[$sku_index]) && is_numeric($columns[$quantity_index]) && $columns[$quantity_index] > 0) {
        //   $sku_quantity_array[] = [
        //     'sku' => $columns[$sku_index],
        //     'quantity' => $columns[$quantity_index],
        //   ];
        // }
      }

      // // Add variation ID to the array based on SKU
      // foreach ($sku_quantity_array as &$item) {
      //   // Assuming you have a function to get variation ID based on SKU
      //   $quantity = $item['quantity'];
      //   $sku = trim($item['sku']);
      //   $variationId = getVariationIdBySku(trim($item['sku']));
      //   print("variation id: ".$variationId." hm ");
      //   $variation = \Drupal::entityTypeManager()->getStorage('commerce_product_variation')->load($variationId);
      //   add_to_cart($variation, $quantity);
      //   // $variationId = getVariationIdBySku($item['sku']);
      //   $item['variation_id'] = $variationId;
      
      // }
      // print_r($sku_quantity_array);
      // print('errors'); // THIS WORKS
      // $form_state->setErrorByName('norgen_fname', $this->t('Please enter a v. '));
      
    }
  

  // public function submitForm(array &$form, FormStateInterface $form_state) 
  public function submitAjaxForm(array &$form, FormStateInterface $form_state) 
  {
    // $variationId = 9;
    // $variation = \Drupal::entityTypeManager()->getStorage('commerce_product_variation')->load($variationId);
    // $quantity = 40;

    // // $isGuestCheckout = FALSE; // Set this to TRUE if you want to allow guest checkout

    // // $result = addToCart($variation, $quantity); // this function is within this module

    // // Get the service from the container.

    // // include_once 'var/www/drupal/web/modules/contrib/nor_add_to_cart_func/nor_add_to_cart_func.module';
    // add_to_cart($variation, $quantity);

     // Load the file
     
    $sku_index = -1;
    $quantity_index = -1;
    
    $form_file = $form_state->getValue('file_upload', 0);
    if (isset($form_file[0]) && !empty($form_file[0])) 
    {
      $file = File::load($form_file[0]);
      $file_path = \Drupal::service('file_system')->realpath($file->getFileUri());
      $file_contents = file_get_contents($file_path);
      $lines = explode("\n", $file_contents);



      $sku_index = -1;
      $quantity_index = -1;
      $sku_quantity_array = [];
      $header_titles = ['sku', 'quantity', 'name', 'cat no.']; // Adjust as needed.
      foreach ($lines as $line) {
        $columns = str_getcsv($line);
        $columns_lower = array_map('customStrToLower', $columns);

        if (count(array_intersect($columns_lower, $header_titles)) === (count($header_titles)-1)) {
          $header_found = TRUE;
        }

        if($sku_index == -1)
        {
          $sku_index = getColumnIndex($columns_lower, ['sku', 'cat no.']);
        }
        if($quantity_index == -1)
        {
          $quantity_index = getColumnIndex($columns_lower, ['quantity']);
        }

        if ($sku_index !== -1 && $quantity_index !== -1 && !empty($columns[$sku_index]) && is_numeric($columns[$quantity_index]) && $columns[$quantity_index] > 0) {
          $sku_quantity_array[] = [
            'sku' => $columns[$sku_index],
            'quantity' => $columns[$quantity_index],
          ];
        }
      }

      //  // Add variation ID to the array based on SKU
      //  foreach ($sku_quantity_array as &$item) {
      //   // Assuming you have a function to get variation ID based on SKU
      //   $quantity = $item['quantity'];
      //   $sku = trim($item['sku']);
      //   $variationId = getVariationIdBySku(trim($item['sku']));
      //   // print("variation id: ".$variationId." hm ");
      //   $variation = \Drupal::entityTypeManager()->getStorage('commerce_product_variation')->load($variationId);
      //   add_to_cart($variation, $quantity_index);
      //   // $item['variation_id'] = $variationId;
      
      // }
      // $count = count($sku_quantity_array);
      for ($i = 0; $i < count($sku_quantity_array); $i++) {
        $item = $sku_quantity_array[$i];
        $quantity = $item['quantity'];
        $sku = trim($item['sku']);
        $variationId = getVariationIdBySku($sku);
        $variation = \Drupal::entityTypeManager()->getStorage('commerce_product_variation')->load($variationId);
        addToCart($variation, $quantity);

      }
    }

    //
    if(count($sku_quantity_array)>0){
      $date = date("Ymd");
      $form_name = $this->t('Express Order File');
  
      /* $variations_string = '';
      $quantities_string = '';
      foreach($sku_quantity_array as $key => $product_line){
        $sku =  $submitted_variation->getSKU();
        if($key == 0){$variations_string .= $product_line['sku']; $quantities_string .= $product_line['quantity'];}
        else if(mb_strlen($variations_string . ','.$product_line['sku']) <= 251 && mb_strlen($quantities_string . ','.$product_line['quantity']) <= 251) {$variations_string .= ','.$product_line['sku']; $quantities_string .= ','.$product_line['quantity'];}
        else {
          $variations_string .= '...';
          $quantities_string .= '...';
          break;
        }
      } */
      $user = \Drupal::currentUser();
      $email = $user->getEmail();
      if($email == null) $email = 'anonymous';
      $query = \Drupal::database()->insert('forms_to_zoho');
      $query->fields(['created_on', 'email', 'record', 'timestamp', 'form_name']);
      $query->values([$date, $email, '', time(), $form_name]);
      $query->execute();
    }
    //

    $Selector = '#norgen_form-container';
    if (!empty($error_message)) {
        $Content = '<div class="error-message my_top_message">' . $error_message . '</div>';
    }
    $Content = '<div class="my_top_message">Form Submitted Successfully'.json_encode($sku_quantity_array).'</div>';
    $response = new AjaxResponse();
    if ($form_state->hasAnyErrors()) {

      $response->addCommand(new ReplaceCommand('#norgen_form-container', $form));
      $messages = \Drupal::messenger()->deleteAll();
      // return $response; 
    } 
    
    else {
      // norgen_submit_email($output,'ruba.inga@milkcreeks.com');
      $response->addCommand(new HtmlCommand($Selector,$Content));
      $response->addCommand(new ReplaceCommand(NULL, $form));
     /*  $current_path = \Drupal::service('path.current')->getPath();
      $current_uri = \Drupal::service('path_alias.manager')->getAliasByPath($current_path);
      $response->addCommand(new RedirectCommand($current_uri)); */
      // return $response; 
    }

    $form_file = $form_state->getValue('file_upload', 0);
    if (isset($form_file[0]) && !empty($form_file[0])) {
      $file = File::load($form_file[0]);
      $file->setPermanent();
      $file->save();
    }
    return $response; 
  }
  public function submitForm(array &$form, FormStateInterface $form_state) {




    
  }


}