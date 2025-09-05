<?php


namespace Drupal\database_page_form\Form;

use Drupal\Core\Url;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\RemoveCommand;
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
use Drupal\Core\Database\Query\PagerSelectExtender;

use Drupal\vip_data_room\GatedContentController;

class DatabasePageForm extends FormBase{

  public function getFormID(){
    return 'database_page_form';
  }

  private $valid_access_codes = ['68237','57126','34512','31785'];

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attached']['library'][] = 'core/drupal.ajax';
    $form['#attached']['library'][] = 'core/jquery.form';
    $form['#attached']['library'][] = 'database_page_form/database_page_form';

    if (\Drupal::request()->cookies->has('data_room_access_granted')) { // skip logging in + redirect if they've already submitted the form
      return $this->redirect('vip_data_room.content');
    }

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

        
    $form['#prefix'] = '<div id="database-page-form-container" class="accountissuesform-container">';

     /* $form['message'] = [
      '#type' => 'markup',
      '#markup' => '<div id="result-message"></div>',
    ]; */

    $form['top'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['form-top','center'],
      ]
    ];

    $form['top']['title'] = [
      '#type' => 'markup',
      '#markup' => '<h1 class="center">VIP Data Room</h1>',
    ];
    
    $form['top']['vip-crown'] = [
      '#type' => 'markup',
      '#markup' => '<img src="/sites/default/files/vip-crown.png" class="vip-crown">',
    ];

    $form['top']['description'] = [
      '#type' => 'markup',
      '#markup' => "<p>Our VIP Data Room is exclusive to select individuals.</p><p>Wan't access? <a href='/contact'>Contact us</a> to request an access code.</p>",
    ];
    
    $form['form_fields'] = [
      '#type' => 'container',
    ];
    $form['form_fields']['fname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Name'),
      '#required' => TRUE,
      //'#placeholder' => t('First Name (Required)'),
    ];
    $form['form_fields']['lname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last Name'),
      '#required' => TRUE,
      //'#placeholder' => t('Last Name (Required)'),
    ];
    $form['form_fields']['company'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Company / Institution'),
      //'#placeholder' => t('Company or Institution Name (Required)'),
      '#required' => TRUE,
    ];
    $form['form_fields']['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#required' => TRUE,
      //'#placeholder' => t('email@example.com (Required)'),
    ];
    $form['form_fields']['access_code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Access Code'),
      '#required' => TRUE,
      '#description' => 'Case-sensitive',
    ];

    $form['form_actions'] = [
      '#type' => 'container',
    ];
    $form['form_actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Access Content'),
      '#button_type' => 'primary',
      '#submit' => ['::submitForm'],
      '#ajax' => [
        'callback' => '::submitCallback',
        'wrapper' => 'database-page-form-container',
        //'event' => 'click',
        'method' => 'append', 
        'effect' => 'fade',
      ],
    ];

    $form['#suffix'] = '</div>';
    return $form;

  }
    
  public function validateForm(array &$form, FormStateInterface $form_state){
    $first_name = $form_state->getValue('fname');
    $last_name = $form_state->getValue('lname');
    $company = $form_state->getValue('company');
    $email = $form_state->getValue('email');
    $access_code = $form_state->getValue('access_code');

    if (empty($first_name) || strlen($first_name) < 2) {
      $form_state->setErrorByName('fname', $this->t('First Name must be 2 or more characters.'));
    }
    if (empty($last_name) || strlen($last_name) < 2) {
      $form_state->setErrorByName('lname', $this->t('Last Name must be 2 or more characters.'));
    }
    if (empty($company) || strlen($company) < 2) {
      $form_state->setErrorByName('company', $this->t('Company / Institution must be 2 or more characters.'));
    }
    if (empty($email) || strlen($email) < 2) {
      $form_state->setErrorByName('email', $this->t('Email must be 2 or more characters.'));
    }
    /* Access Code */
    if (empty($access_code) || !in_array($access_code, $this->valid_access_codes)) {
      $form_state->setErrorByName('access_code', $this->t('Invalid Access Code'));
    }
  }


  public function submitCallback(array &$form, FormStateInterface $form_state) {
    /* This works! */
    $Selector = '#database-page-form-container';
    /* $content = [
      '#theme' => 'jango_database_portal_content',
      '#description' => 'foo',
      '#attributes' => [],
    ]; */
    $response = new AjaxResponse();

    if ($form_state->hasAnyErrors()) {
      $response = nor_forms_ajax_error($form, $form_state, $Selector);
      return $response;
    } 
    else{
      /* $wrapperSelector = '#vip-wrapper';
      $headerSelector = '#vip-header';
      $loadingCoverSelector = '#vip-loading-cover';
      $welcomeMessageSelector = $loadingCoverSelector.' > .welcome-message';
      $loader_html = '<img src="/sites/default/files/vip-loader.png" id="vip-loader" class="animate">';
      // Add a delay to look like we're loading something LOL
      $new_header = '<header id="vip-header"><a href="/"><img src="/sites/default/files/norgen-logo.svg"></a></header>';
      
      $response->addCommand(new RemoveCommand('[data-drupal-selector="edit-form-actions"]'));
      $response->addCommand(new ReplaceCommand('[data-drupal-selector="edit-form-fields"]', $loader_html));
      $response->addCommand(new InvokeCommand($Selector, 'delay', [2000]));
      $response->addCommand(new InvokeCommand($Selector, 'fadeOut', ["fast"]));

      $welcome_message = 'Welcome, '.$form_state->getValue('fname');
      $response->addCommand(new InvokeCommand($loadingCoverSelector, 'addClass', ['active']));
      $response->addCommand(new AppendCommand($welcomeMessageSelector, $welcome_message));

      $response->addCommand(new AppendCommand('#db-content', $content));
      $response->addCommand(new InvokeCommand('#db-content', 'removeClass', ['no-access']));
      $response->addCommand(new InvokeCommand('#db-content', 'addClass', ['access'])); */

      $loader_html = '<img src="/sites/default/files/vip-loader.png" id="vip-loader" class="animate">';
      $response->addCommand(new RemoveCommand('[data-drupal-selector="edit-form-actions"]'));
      $response->addCommand(new ReplaceCommand('[data-drupal-selector="edit-form-fields"]', $loader_html));
      
      $response->addCommand(new RedirectCommand(Url::fromRoute('vip_data_room.content')->toString()));

      return $response;
    }
  }
  
  public function submitForm(array &$form, FormStateInterface $form_state){
    $first_name = $form_state->getValue('fname');
    $last_name = $form_state->getValue('lname');
    $email = $form_state->getValue('email');
    $company = $form_state->getValue('company');
    $form_name = $this->t('Data Room Access Form');
    $date = date("Ymd");

    setcookie('data_room_access_granted', true, time() + 86400, '/', '', TRUE, TRUE);

    //\Drupal::service('tempstore.private')->get('database_page')->set('form_completed', TRUE);

    $query = \Drupal::database()->insert('forms_to_zoho');
    $query->fields(['created_on', 'first_name', 'last_name', 'email', 'company', 'record', 'timestamp', 'form_name']); //wrong syntax here breaks entire submit function 
    $query->values([$date, $first_name, $last_name, $email, $company, '', time(), $form_name]);
    $query->execute();

    try {
      //Zoho upsert
      $zoho = new RecordWrapper('leads');
      $record = [
        'First_Name' => $first_name,
        'Last_Name' => $last_name,
        'Email' => $email,
        'Company' => $company,
        'Lead_Source' => 'Website Form',
        'Web_Forms' => [$form_name],
      ];
      $upsert_result = $zoho->upsert($record);
    } catch (Exception $e) {}

    $output = '';

    $time = time();

    /* if (!$form_state->hasAnyErrors()) {
      $subject = '[Database Page Form] - New Access ' . date("F j, Y, g:i a", $time);
      //$recipient_email = 'marketing@norgenbiotek.com';// real addresses
      $recipient_email = 'liam.howes@norgenbiotek.com';
      //$recipient_email = 'sowmya.movva@norgenbiotek.com';
      nor_forms_email_redirect($output, $recipient_email, $subject);
    } */
  }
}