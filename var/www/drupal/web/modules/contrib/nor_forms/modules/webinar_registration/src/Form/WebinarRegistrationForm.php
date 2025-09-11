<?php
namespace Drupal\webinar_registration\Form;

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

class WebinarRegistrationForm extends FormBase
{
  public function getFormID()
  {
    return 'webinar_registration_form';
  }

  private $samples = '';

  public function buildForm(array $form, FormStateInterface $form_state){

    $form['#prefix'] = '<div id="webinar-registration-container">';

    $form['message'] = [
      '#type' => 'markup',
      '#markup' => '<div id="result-message"></div>',
    ];


    
    /* UTM Parameters */

    $current_request = \Drupal::request();
    $query = $current_request->query;

    $form['utm_source'] = [
      '#type' => 'hidden',
      '#title' => $this->t('UTM Source'),
      '#value' => $query->has('utm_source')?$query->get('utm_source'):null,
    ];
    $form['utm_medium'] = [
      '#type' => 'hidden',
      '#title' => $this->t('UTM Medium'),
      '#value' => $query->has('utm_medium')?$query->get('utm_medium'):null,
    ];
    $form['utm_campaign'] = [
      '#type' => 'hidden',
      '#title' => $this->t('UTM Campaign'),
      '#value' => $query->has('utm_campaign')?$query->get('utm_campaign'):null,
    ];
    $form['utm_id'] = [
      '#type' => 'hidden',
      '#title' => $this->t('UTM Id'),
      '#value' => $query->has('utm_id')?$query->get('utm_id'):null,
    ];
    $form['utm_term'] = [
      '#type' => 'hidden',
      '#title' => $this->t('UTM Term'),
      '#value' => $query->has('utm_term')?$query->get('utm_term'):null,
    ];
    $form['utm_content'] = [
      '#type' => 'hidden',
      '#title' => $this->t('UTM Content'),
      '#value' => $query->has('utm_content')?$query->get('utm_content'):null,
    ];

    /* End of UTM Parameters */


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
            '#markup' => '<progress value="1" max="10" class="norgen-progress-wrapper center">'.$sample_current_step_id.'</progress>',
          ];
          
                
          $form[$step]['customer_information'] = [
            '#type' => 'fieldset',
            '#attributes' => [
              'class' => ['customer-information'],
            ],
          ];

          $form[$step]['customer_information']['name'] = [
            '#type' => 'textfield',
            '#title' => 'Full Name <span class="form-required" title="This field is required.">*</span>',
            '#default_value' => nor_forms_user_full_name(),
            /* '#required' => TRUE, */
          ];


          $form[$step]['customer_information']['email'] = [
            '#type' => 'email',
            '#title' => 'Email <span class="form-required" title="This field is required.">*</span>',
            '#default_value' => nor_forms_user_email(),
            /* '#required' => TRUE, */
          ];
      
          $form[$step]['customer_information']['company'] = [
            '#type' => 'textfield',
            '#title' => 'Company/Institution <span class="form-required" title="This field is required.">*</span>',
            /* '#required' => TRUE, */
          ];
      
          $form[$step]['customer_information']['job'] = [
            '#type' => 'textfield',
            '#title' => 'Job Title <span class="form-required" title="This field is required.">*</span>',
            /* '#required' => TRUE, */
          ];
      
          $form[$step]['customer_information']['country'] = [
            '#type' => 'select',
            '#title' => 'Country <span class="form-required" title="This field is required.">*</span>',
            '#options' => getCountryOptions(),
            /* '#required' => TRUE, */
          ];
      
          $form[$step]['customer_information']['mailing'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Subscribe to our mailing list and be the first to hear about offers and news from Norgen Biotek.'),
            '#suffix' => '<div class="disclaimer">It is our responsibility to protect and guarantee that your data will be completely confidential. You can unsubscribe from Norgen emails at any time by clicking the link in the footer of our emails. For more information please view our <a href="/content/privacy-policy">Privacy Policy</a>.</div>',
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
              '#attributes' => array(
                'class' => array('hidden'),
              ),
            ];
          }

          $form[$step]['progress'] = [
            '#type' => 'item',
            '#markup' => '<progress value="9" max="10" class="norgen-progress-wrapper center">'.$sample_current_step_id.'</progress>',
          ];

          $form[$step]['other_information'] = [
            '#type' => 'fieldset',
            '#attributes' => [
              'class' => ['other-information'],
            ],
          ];

          $form[$step]['other_information']['referral'] = [
            '#type' => 'fieldset',
            '#attributes' => array(
              'class' => array('referral-fieldset'),
            ),
            '#prefix' => '<div id="referral-wrapper">',
            '#suffix' => '</div>',
          ];
      
          $form[$step]['other_information']['referral']['referral_select'] = [
            '#type' => 'select',
            '#title' => 'Where did you hear about us? <span class="form-required" title="This field is required.">*</span>',
            '#options' => array(
              'Online Search' => $this->t('Online Search'),
              'LinkedIn' => $this->t('LinkedIn'),
              'Instagram' => $this->t('Instagram'),
              'X (Formerly Twitter)' => $this->t('X (Formerly Twitter)'),
              'Referral from a Friend/Colleague' => $this->t('Referral from a Friend/Colleague'),
              'Email Newsletter' => $this->t('Email Newsletter'),
              'Customer Service Interaction' => $this->t('Customer Service Interaction'),
              'Other' => $this->t('Other'),
            ),
            '#ajax' => [
              'callback' => '::referralCallback',
              'disable-refocus' => FALSE, // Or TRUE to prevent re-focusing on the triggering element.
              'event' => 'change',
              'wrapper' => 'referral-wrapper', // This element is updated with this AJAX callback.
            ],
          ];
      
          if ($form_state->getValue('referral_select') !== null && $form_state->getValue('referral_select')=='Other') {
            // Get the text of the selected option.
            $form[$step]['other_information']['referral']['referral_other'] = [
              '#type' => 'textfield',
              '#title' => $this->t('Please specify source'),
            ];
          }
      
          $form[$step]['other_information']['what_info'] = [
            '#type' => 'radios',
            '#title' => t('What information do you hope to gain from this webinar?'),

            '#options' => array(
              'Educational' => t('Educational'),
              'Norgen Solutions' => t('Norgen Solutions'),
              'Both' => t('Both'),
            ),
            /* '#required' => TRUE, */
          ];
      
          $form[$step]['other_information']['sample_type'] = [
            '#type' => 'fieldset',
            '#attributes' => array(
              'class' => array('sample-type-fieldset'),
            ),
            '#prefix' => '<div id="sample-type-wrapper">',
            '#suffix' => '</div>',
          ];
      
          $form[$step]['other_information']['sample_type']['sample_type_select'] = [
            '#type' => 'select',
            '#title' => 'Which sample type best describes what you work with? <span class="form-required" title="This field is required.">*</span>',
            '#options' => array(
              'animal cells' => $this->t('animal cells'),
              'animal tissues' => $this->t('animal tissues'),
              'blood' => $this->t('blood'),
              'nasal/throat swabs' => $this->t('nasal/throat swabs'),
              'bacteria' => $this->t('bacteria'),
              'yeast' => $this->t('yeast'),
              'fungi' => $this->t('fungi'),
              'plant' => $this->t('plant'),
              'viral supension'=> $this->t('viral supension'),
              'other' => $this->t('other'),
            ),
            '#ajax' => [
              'callback' => '::sampleTypeCallback',
              'disable-refocus' => FALSE, // Or TRUE to prevent re-focusing on the triggering element.
              'event' => 'change',
              'wrapper' => 'sample-type-wrapper', // This element is updated with this AJAX callback.
            ],
          ];
      
          if ($form_state->getValue('sample_type_select') !== null && $form_state->getValue('sample_type_select')=='other') {
            // Get the text of the selected option.
            $form[$step]['other_information']['sample_type']['sample_type_select_other'] = [
              '#type' => 'textfield',
              '#title' => $this->t('Please specify source'),
            ];
          }
      
          /* $form[$step]['other_information']['timeslot'] = [
            '#type' => 'radios',
            '#title' => t('What timeslot do you wish to attend?'),
            '#options' => array(
              '9:00am EDT' => t('9:00am EDT'),
              '2:00pm EDT' => t('2:00pm EDT'),
            ),
          ]; */
      
          $form[$step]['other_information']['google_recaptcha'] = [
            '#type'=> 'fieldset',
            '#description' => '<div class="g-recaptcha" data-sitekey="6Lcr4u0pAAAAAGj32knXkUzuHAXzj3CoAhtbJ1t5"></div>',
          ];
          break;
      }
      $form[$sample_current_step_id]['actions'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['sample-step-button-container']], // Add a custom class for styling
        '#weight' => 2,
      ];

      if($sample_current_step_id == 1){
        $form[$sample_current_step_id]['actions']['submit'] = [
          '#type' => 'submit',
          '#button_type' => 'primary',
          '#value' => $this->t('Next'),
          '#ajax' => [
            'callback' => '::changePage',
            'wrapper' => 'webinar-registration-container', 
          ],
          '#validate' => ['::validateSampleForm'],
          '#submit' => ['::submitForm'],
        ];
      }
      else if($sample_current_step_id == 2){
        $form[$sample_current_step_id]['actions']['back'] = [
          '#type' => 'submit',
          '#value' => $this->t('Back'),
          '#ajax' => [
            'callback' => '::changePage',
            'wrapper' => 'webinar-registration-container',
          ],
          '#submit' => ['::submitForm'],
        ];

        $form[$sample_current_step_id]['actions']['submit'] = [
          '#type' => 'submit',
          '#button_type' => 'primary',
          '#value' => $this->t('Submit'),
          '#ajax' => [
            'callback' => '::changePage',
            'wrapper' => 'webinar-registration-container', 
          ],
          '#validate' => ['::validateSampleForm'],
          '#submit' => ['::submitForm'],
        ];
      }
    }

    $form['#suffix'] = '</div>';
    return $form;
  }
  public function referralCallback(array &$form, FormStateInterface $form_state){
    return $form[2]['other_information']['referral'];
  }

  public function sampleTypeCallback(array &$form, FormStateInterface $form_state){
    return $form[2]['other_information']['sample_type'];
  }
  public function changePage(array &$form, FormStateInterface $form_state){
    $messages = \Drupal::messenger()->deleteAll();

    $Selector = '#step2 > div[data-drupal-selector="edit-2"]';
    $response = new AjaxResponse();

    $webinar_link = 'https://youtube.com/live/42hek_n-mP8'; // 9:30am
    /* $timeslot = $form_state->getValue('timeslot');
    if($timeslot == '2:00pm EDT'){
      $webinar_link = 'https://youtube.com/live/19yA-x2VCNY';
    } */

    if($form_state->get('sample_current_step_id') == 2){
      $Selector = '#webinar-registration-container';
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
  
        $response = nor_forms_webinar_registration_sent_ajax($form, $form_state, $Selector, $webinar_link);
  
        return $response;
      }
    }

    return $form;
  }

  public function validateSampleForm(array &$form, FormStateInterface $form_state){
    switch ($form_state->get('sample_current_step_id')){
      case 1:
        // Get values from relevant form fields
        $name = $form_state->getValue('name');
        $email = $form_state->getValue('email');
        $company = $form_state->getValue('company');
        $job = $form_state->getValue('job');
        $country = $form_state->getValue('country'); 
        // Check if the required fields are empty
        if (empty($name)) {
          // Trigger an error and prevent further processing
          $form_state->setErrorByName('name', $this->t('Full Name is required.'));
        }
        if (empty($email)) {
          $form_state->setErrorByName('email', $this->t('Email is required.'));
        }
        if (empty($company)) {
          $form_state->setErrorByName('company', $this->t('Company/Institution is required.'));
        }
        if (empty($job)) {
          $form_state->setErrorByName('job', $this->t('Job Title is required.'));
        }
        if (empty($country)) {
          $form_state->setErrorByName('country', $this->t('Country is required.'));
        }
        break;
      case 2:
        $referral = $form_state->getValue('referral_select');
        $what_info = $form_state->getValue('what_info');
        $sample = $form_state->getValue('sample_type_select');
        /* $timeslot = $form_state->getValue('timeslot'); */
        if (empty($referral)) {
          $form_state->setErrorByName('referral_select', $this->t('Email is required.'));
        }
        if (empty($what_info)) {
          $form_state->setErrorByName('what_info', $this->t('What information do you hope to gain from this webinar? is required.'));
        }
        if (empty($sample)) {
          $form_state->setErrorByName('sample_type_select', $this->t('Which sample type best describes what you work with? is required.'));
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
        
        /* if (empty($timeslot)) {
          $form_state->setErrorByName('timeslot', $this->t('What timeslot do you wish to attend? is required.'));
        } */
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

 /*  public function submitCallback(array &$form, FormStateInterface $form_state){

    $Selector = '#webinar-registration-container';
    $response = new AjaxResponse();

    if ($form_state->hasAnyErrors()) {
      $response = nor_forms_ajax_error($form, $form_state, $Selector);
      return $response;
    } else {


      $timeslot = $form_state->getValue('timeslot');
      $webinar_link = 'https://youtube.com/live/42hek_n-mP8'; // 9:00am
      if($timeslot == '2:00pm EDT'){
        $webinar_link = 'https://youtube.com/live/19yA-x2VCNY';
      }
      //$response = nor_forms_webinar_registration_sent_ajax($form, $form_state, $Selector, $webinar_link);
      $response = nor_forms_email_sent_ajax($form, $form_state, $Selector);
      return $response;
    }
  } */

  public function submitForm(array &$form, FormStateInterface $form_state){

    $form_state->set("step_".$form_state->get('sample_current_step_id')."_values", $form_state->getValues()); // store step values (step_1_values, step_2_values, step_3_values)
    if($form_state->getTriggeringElement()['#value']=='Next') $form_state->set('sample_current_step_id', $form_state->get('sample_current_step_id') + 1);
    else if($form_state->getTriggeringElement()['#value']=='Back') {
      $form_state->set('sample_current_step_id', $form_state->get('sample_current_step_id') - 1);
    }
    else if($form_state->getTriggeringElement()['#value']=='Submit'){
      $form_state->set('sample_current_step_id', $form_state->get('sample_current_step_id') + 1);

      // Customer Information
      $name = $form_state->getValue('name');
      $email = $form_state->getValue('email');
      $company = $form_state->getValue('company');
      $job = $form_state->getValue('job');
      $country = $form_state->getValue('country'); 
      $country_name = getCountryNames($country);
      // Get the value of the "Subscribe to Mailing List" checkbox
      $subscribe_option = $form_state->getValue('mailing') ? 'Yes' : 'No';
      $subscribe_emaildb = $form_state->getValue('mailing') ? 1 : 0;
      $subscribe_optout = $form_state->getValue('mailing') ? 0 : 1;

      $utm_source = $form_state->getValue('utm_source');
      $utm_medium = $form_state->getValue('utm_medium');
      $utm_campaign = $form_state->getValue('utm_campaign');
      $utm_id = $form_state->getValue('utm_id');
      $utm_term = $form_state->getValue('utm_term');
      $utm_content = $form_state->getValue('utm_content');

      // Other Information
      $referral = $form_state->getValue('referral_select');
      if($referral=='Other' && $form_state->getValue('referral_other')) $referral = $form_state->getValue('referral_other');
      $what_info = $form_state->getValue('what_info');
      $sample = $form_state->getValue('sample_type_select');
      if($sample=='other' && $form_state->getValue('sample_type_select_other')) $sample = $form_state->getValue('sample_type_select_other');
      //$timeslot = $form_state->getValue('timeslot');

      $timeslot = '9:30am EDT';

      /* $output = '<p>Hello,</p>
      <p>A customer has registered for the webinar: "Transforming Cancer Care with cfDNA from Liquid Biopsy: A Prostate Cancer Perspective".</p>'; */
      $output = '<p>Hello,</p>
      <p>A customer has registered for the webinar: "4 Factors the Pros Consider Before Isolating Exosomes."</p>';

      // Customer Information
      $output .= '<h2 style="margin-bottom:0px;">Customer Information:</h2><table style="border-spacing:0px;border-bottom:1px solid grey;"><tbody>';
      $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Name:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$name.'</td></tr>';
      $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Email:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$email.'</td></tr>';
      $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Company:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$company.'</td></tr>';
      $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Job:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$job.'</td></tr>';
      $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Country:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$country_name.'</td></tr>';
      $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Subscribe to Mailing List:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$subscribe_option.'</td></tr>';
      $output .= '</tbody></table>';
      // Other Information
      $output .= '<h2 style="margin-bottom:0px;">Additional Information:</h2><table style="border-spacing:0px;border-bottom:1px solid grey;"><tbody>';
      $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">How did you hear about us?:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$referral.'</td></tr>';
      $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">What information do you hope to gain from this webinar?:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$what_info.'</td></tr>';
      $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Sample:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$sample.'</td></tr>';
      $output .= '<tr><td style="border:1px solid grey;border-bottom:0px;padding:6px;">Timeslot:</td><td style="border:1px solid grey;border-bottom:0px;border-left:0px;padding:6px;">'.$timeslot.'</td></tr>';
      $output .= '</tbody></table>';

      // Email to Customer

      /* if($timeslot == '9:00am EDT'){
        $webinar_link = 'https://youtube.com/live/42hek_n-mP8';
        $image_element = '<img align="left" class="zpImage" height="auto" hspace="0" size="B" src="https://stratus.campaign-image.com/images/950380000023551024_zc_v1_1741633275157_2025_03_05_liquid_biopsy_prostate_cancer_thumnail_9am_pr.png" style="width: 600px; max-width: 600px !important; border: 0px; text-align: left" vspace="0" width="600" bis_size="{&quot;x&quot;:330,&quot;y&quot;:188,&quot;w&quot;:600,&quot;h&quot;:337,&quot;abs_x&quot;:330,&quot;abs_y&quot;:188}" bis_id="bn_b4si8rr8rhqzdknv7m1zof">';
      }
      else{
        $webinar_link = 'https://youtube.com/live/19yA-x2VCNY';
        $image_element = '<img align="left" class="zpImage" height="auto" hspace="0" size="B" src="https://stratus.campaign-image.com/images/950380000023616575_zc_v1_1741633400192_2025_03_05_liquid_biopsy_prostate_cancer_thumnail_2pm_pr.png" style="width: 600px; max-width: 600px !important; border: 0px; text-align: left" vspace="0" width="600" bis_size="{&quot;x&quot;:330,&quot;y&quot;:188,&quot;w&quot;:600,&quot;h&quot;:337,&quot;abs_x&quot;:330,&quot;abs_y&quot;:188}" bis_id="bn_5rw5gytegad8srscfha3jd">';
      } */
      $webinar_link = 'https://youtube.com/live/42hek_n-mP8';
      $image_element = '<img align="left" class="zpImage" height="auto" hspace="0" size="B" src="https://norgenbiotek.com/sites/default/files/webinar/exosomes-2025/Exosome_Webinar_Banner_Web_Email_1920x1080.jpg" style="width: 600px; max-width: 600px !important; border: 0px; text-align: left" vspace="0" width="600" bis_size="{&quot;x&quot;:330,&quot;y&quot;:188,&quot;w&quot;:600,&quot;h&quot;:337,&quot;abs_x&quot;:330,&quot;abs_y&quot;:188}" bis_id="bn_b4si8rr8rhqzdknv7m1zof">';

      $customer_output = 
      '<table bgcolor="#f0f0f0" border="0" cellpadding="0" cellspacing="0" class="contentOuter" id="contentOuter" style="background-color: rgb(240, 240, 240); background-color: rgb(240, 240, 240); font-size: 12px; text-align: center; border: 0px; padding: 0px; border-collapse: collapse" width="100%">     <tbody><tr>      <td style="font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: 0px; padding: 0px; border-collapse: collapse">&nbsp;</td>     <td align="center" style="font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: 0px; padding: 0px; border-collapse: collapse">      <table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" class="contentInner" id="contentInner" style="border-collapse: collapse; border: 0px; font-size: 12px; border-collapse: collapse; background-color: rgb(255, 255, 255); background-color: rgb(255, 255, 255); width: 600px; margin: 0px auto; border: 0px" width="600">       <tbody><tr>       <td height="570" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: 0px; padding: 0px" valign="top">        <a name="Top" style="text-decoration: underline" target="_blank"></a>     <div class="zpcontent-wrapper" id="page-container" bis_skin_checked="1">     <table border="0" cellpadding="0" cellspacing="0" id="page-container" style="font-size: 12px; border: 0px; padding: 0px; border-collapse: collapse; text-decoration: none !important" width="100%"> <tbody><tr><td class="txtsize" id="elm_1720534139482" style="font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: 0px; padding: 0px 0px; border-collapse: collapse" valign="top">   <div class="zpelement-wrapper zpcol-layout" id="elm_1720534139482" style="word-wrap: break-word; padding-bottom: 0 !important; overflow: hidden; padding: 0px; margin: 0px" bis_skin_checked="1">  <div class="zpcolumns" style="padding: 0px; margin: 0px" bis_skin_checked="1">        <table bgcolor="transparent" cellpadding="0" cellspacing="0" style="font-size: 12px; border: 0px; padding: 0px; border-collapse: collapse; width: 100%; background-color: transparent" width="100%">      <tbody><tr>   <td class="txtsize" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: 0px; padding: 0px 0px; border-top: none none none; border-bottom: none none none" valign="top">                                             <table align="left" cellpadding="0" cellspacing="0" class="cols" style="font-size: 12px; max-width: 204px; width: 100%; border: 0px; padding: 0px; border-collapse: collapse; float: left" width="100%">     <tbody><tr>                   <td class="txtsize" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: 0px; padding: 0px" valign="top">                                     <div class="zpwrapper col-space" id="pos_1720534139478" style="padding: 0px" bis_skin_checked="1">        <table bgcolor="transparent" border="0" cellpadding="0" cellspacing="0" style="font-size: 12px; border: 0px; padding: 0px; width: 100%; border-collapse: collapse; background-color: transparent">      <tbody><tr>   <td class="txtsize" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: 0px; padding: 0px 0px; border-top: none none none; border-bottom: none none none">              <div class="zpelement-wrapper image" id="elm_1720534139543" style="word-wrap: break-word; overflow: hidden; padding: 0px; background-color: transparent" bis_skin_checked="1">    <div bis_skin_checked="1">           <table align="center" border="0" cellpadding="0" cellspacing="0" class="zpAlignPos" style="font-size: 12px; text-align: left; width: 100%; padding: 0px; border: 0px; border-collapse: collapse; width: 100%; text-align: center">               <tbody><tr><td class="paddingcomp" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: 0px; padding: 7px 15px; text-align: center; padding-top: 7px; padding-bottom: 7px; padding-right: 15px; padding-left: 15px">    <a href="https://norgenbiotek.com/?utm_source=email&amp;utm_medium=email&amp;utm_campaign=rna_promotion" style="text-decoration: underline; border: 0px" target="_blank" rel="noopener noreferrer" bis_size="{&quot;x&quot;:345,&quot;y&quot;:69,&quot;w&quot;:174,&quot;h&quot;:14,&quot;abs_x&quot;:345,&quot;abs_y&quot;:69}">    <img align="center" class="zpImage" height="auto" hspace="0" size="F" src="https://stratus.campaign-image.com/images/950380000023616575_zc_v1_1720534144265_untitled_2_(1).png" style="width: 174px; max-width: 360px !important; border: 0px; text-align: center" vspace="0" width="174" bis_size="{&quot;x&quot;:345,&quot;y&quot;:37,&quot;w&quot;:174,&quot;h&quot;:79,&quot;abs_x&quot;:345,&quot;abs_y&quot;:37}" bis_id="bn_pxv42rceicp9k85qcm03ph">    </a>    </td></tr>    </tbody></table>   </div>             </div>             </td></tr></tbody></table>                       </div>        </td></tr></tbody></table>                                      <table align="left" cellpadding="0" cellspacing="0" class="cols" style="font-size: 12px; max-width: 396px; width: 100%; border: 0px; padding: 0px; border-collapse: collapse; float: left" width="100%">     <tbody><tr>                   <td class="txtsize" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: 0px; padding: 0px" valign="top">                                     <div class="zpwrapper col-space" id="pos_1720534139480" style="padding: 0px" bis_skin_checked="1">            <div class="zpelement-wrapper" id="elm_1720534139481" style="word-wrap: break-word; overflow: hidden; padding-right: 0px" bis_skin_checked="1">       <table border="0" cellpadding="0" cellspacing="0" class="zpAlignPos" style="font-size: 12px; padding: 0px; border: 0px; border-collapse: collapse" width="100%">                  <tbody><tr><td class="paddingcomp" style="border-collapse: collapse; border: 0px; padding: 7px 15px; font-size: 12pt; font-family: Arial, Helvetica; line-height: 19pt; border-top: 0px none; border-bottom: 0px none; padding-top: 7px; padding-bottom: 7px; padding-right: 15px; padding-left: 15px">                     <div style="" bis_skin_checked="1"><p style="font-family: Arial, verdana; font-size: 12px; color: rgb(0, 0, 0); padding: 0px; margin: 0; line-height: 19pt; text-align: left; word-break: normal; overflow-wrap: normal"><b style="font-size: 14pt; font-family: Montserrat, helvetica, arial, sans-serif"><br></b></p><p style="font-family: Arial, verdana; font-size: 12px; color: rgb(0, 0, 0); padding: 0px; margin: 0; line-height: 19pt; text-align: left; word-break: normal; overflow-wrap: normal"><span style="font-family: Montserrat, helvetica, arial, sans-serif"><font style="font-size: 15pt">&nbsp; <a alt="Products" href="https://norgenbiotek.com/products?utm_medium=email" style="text-decoration: underline; color: rgb(0, 0, 0); text-decoration: none" target="_blank" title="Products" rel="noopener noreferrer"><font color="#000000" style="color: rgb(0, 0, 0)">Products</font></a>&nbsp; &nbsp; &nbsp;<a alt="Services" href="https://norgenbiotek.com/services?utm_medium=email" style="text-decoration: underline; color: rgb(0, 0, 0); text-decoration: none" target="_blank" title="Services" rel="noopener noreferrer"><font color="#000000" style="color: rgb(0, 0, 0)">Services</font></a>&nbsp; &nbsp; &nbsp;<a alt="Contact" href="https://norgenbiotek.com/contact?utm_medium=email" style="text-decoration: underline; color: rgb(0, 0, 0); text-decoration: none" target="_blank" title="Contact" rel="noopener noreferrer"><font color="#000000" style="color: rgb(0, 0, 0)">Contact</font></a></font></span><br></p></div>    </td></tr>   </tbody></table>           </div>                       </div>        </td></tr></tbody></table>              </td>      </tr>      </tbody></table>             </div></div> </td></tr> <tr><td class="txtsize" id="elm_1741372369625" style="font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: 0px; padding: 0px 0px; border-collapse: collapse" valign="top">        <table bgcolor="transparent" border="0" cellpadding="0" cellspacing="0" style="font-size: 12px; border: 0px; padding: 0px; width: 100%; border-collapse: collapse; background-color: transparent">      <tbody><tr>   <td class="txtsize" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: 0px; padding: 0px 0px; border-top: none none none; border-bottom: none none none">              <div class="zpelement-wrapper image" id="elm_1741372369625" style="word-wrap: break-word; overflow: hidden; padding: 0px; background-color: transparent" bis_skin_checked="1">    <div bis_skin_checked="1">           <table align="left" border="0" cellpadding="0" cellspacing="0" class="zpAlignPos" style="font-size: 12px; text-align: left; padding: 0px; border: 0px; border-collapse: collapse; width: 100%; text-align: left">                     <tbody><tr><td class="bannerimgpad" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: 0px; padding: 0px; text-align: center; padding-top: 0px; padding-bottom: 0px; padding-right: 0px; padding-left: 0px">    <a href="'.$webinar_link.'" style="text-decoration: underline; border: 0px" target="_blank" rel="noopener noreferrer" bis_size="{&quot;x&quot;:930,&quot;y&quot;:188,&quot;w&quot;:0,&quot;h&quot;:0,&quot;abs_x&quot;:930,&quot;abs_y&quot;:188}">    '.$image_element.'    </a>    </td></tr>    </tbody></table>   </div>             </div>             </td></tr></tbody></table> </td></tr> <tr><td class="txtsize" id="elm_1738768292291" style="font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: 0px; padding: 0px 0px; border-collapse: collapse" valign="top">            <div class="zpelement-wrapper" id="elm_1738768292291" style="word-wrap: break-word; overflow: hidden; padding-right: 0px" bis_skin_checked="1">       <table border="0" cellpadding="0" cellspacing="0" class="zpAlignPos" style="font-size: 12px; padding: 0px; border: 0px; border-collapse: collapse" width="100%">                  <tbody><tr><td class="paddingcomp" style="border-collapse: collapse; border: 0px; padding: 7px 15px; font-size: 12pt; font-family: Arial, Helvetica; line-height: 19pt; border-top: 0px none; border-bottom: 0px none; padding-top: 7px; padding-bottom: 7px; padding-right: 15px; padding-left: 15px">                     <div style="line-height: 19pt" bis_skin_checked="1"><p align="center" style="line-height: 1.7; font-family: Arial, verdana; font-size: 12px; color: rgb(0, 0, 0); padding: 0px; margin: 0; text-align: center; line-height: 19pt"><font style="line-height: 19pt"><font face="Montserrat, helvetica, arial, sans-serif" style="font-size: 11pt; line-height: 19pt"><b>  </b><b></b><b></b><b></b><b></b><b></b><b></b><b><font color="#0a539d"></font></b><b><font color="#0a539d"></font></b></font></font></p><span><div align="left" style="text-align: left" bis_skin_checked="1"><span style="font-size: 11pt; font-family: Montserrat, helvetica, arial, sans-serif; background-color: transparent"><b><font color="#0a539d"></font></b>Thank you for registering for our <b><font color="#d42b2b">4 Factors the Pros Consider Before Isolating Exosomes</font></b> webinar!<b><font color="#0a539d"></font></b></span></div><div align="left" style="text-align: left" bis_skin_checked="1"><span style="font-size: 11pt; font-family: Montserrat, helvetica, arial, sans-serif; background-color: transparent"><span><br></span></span></div><div align="left" style="text-align: left" bis_skin_checked="1"><span style="font-size: 11pt; font-family: Montserrat, helvetica, arial, sans-serif; background-color: transparent"><span>📅 <b>Date: </b>June 17th, 2025</span></span></div><div align="left" style="text-align: left" bis_skin_checked="1"><span style="font-size: 11pt; font-family: Montserrat, helvetica, arial, sans-serif; background-color: transparent"><span>⏰ <b>Time:</b> '.$timeslot.'<br></span></span></div><div align="left" style="text-align: left" bis_skin_checked="1"><span style="font-size: 11pt; font-family: Montserrat, helvetica, arial, sans-serif; background-color: transparent"><span><span><span><br></span></span></span></span></div><div align="left" style="text-align: left" bis_skin_checked="1"><span style="font-size: 11pt; font-family: Montserrat, helvetica, arial, sans-serif; background-color: transparent"><span><span>Use the link below to join the webinar: <font color="#0a539d"><a alt="Join" href="'.$webinar_link.'" style="text-decoration: underline; color: rgb(10, 83, 157)" target="_blank" title="Join" rel="noopener noreferrer"><font color="#0a539d" style="color: rgb(10, 83, 157)"><b>Join</b></font></a></font></span></span></span></div><div align="left" style="text-align: left" bis_skin_checked="1"><span style="font-size: 11pt; font-family: Montserrat, helvetica, arial, sans-serif; background-color: transparent"><span><span><span><br></span></span></span></span></div><div align="left" style="text-align: left" bis_skin_checked="1"><span style="font-size: 11pt; font-family: Montserrat, helvetica, arial, sans-serif; background-color: transparent"><span><span><b><font color="#0a539d">Stay tuned!</font></b> We’ll send a reminder and additional details closer to the event. Thank you, and we look forward to seeing you there! <br></span></span></span></div></span><p align="center" style="line-height: 1.7; font-family: Arial, verdana; font-size: 12px; color: rgb(0, 0, 0); padding: 0px; margin: 0; text-align: center; line-height: 19pt"><b style="font-size: 11pt; font-family: Montserrat, helvetica, arial, sans-serif; background-color: transparent"><font color="#0a539d"></font></b></p></div>    </td></tr>   </tbody></table>           </div> </td></tr> <tr><td class="txtsize" id="elm_1741372594264" style="font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: 0px; padding: 0px 0px; border-collapse: collapse" valign="top">                                              <div class="zpelement-wrapper spacebar" id="elm_1741372594264" style="word-wrap: break-word; overflow: hidden; background-color: transparent" bis_skin_checked="1">             <table bgcolor="transparent" border="0" cellpadding="0" cellspacing="0" class="zpAlignPos" style="padding: 0px; border: 0px; border-collapse: collapse; font-size: 5px; height: 20px" width="100%">                           <tbody><tr><td style="border-collapse: collapse; font-family: Arial, Helvetica, sans-serif; padding: 0px; border: 0px; font-size: 5px; height: 20px; border-top: none none none; border-bottom: none none none">                           &nbsp;&nbsp;&nbsp;                           </td></tr>                   </tbody></table>             </div>  </td></tr> <tr><td class="txtsize" id="elm_1737569376778" style="font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: 0px; padding: 0px 0px; border-collapse: collapse" valign="top">                                              <div class="zpelement-wrapper spacebar" id="elm_1737569376778" style="word-wrap: break-word; overflow: hidden; background-color: rgb(10, 83, 157)" bis_skin_checked="1">             <table bgcolor="#0a539d" border="0" cellpadding="0" cellspacing="0" class="zpAlignPos" style="padding: 0px; border: 0px; border-collapse: collapse; font-size: 5px; height: 20px" width="100%">                           <tbody><tr><td style="border-collapse: collapse; font-family: Arial, Helvetica, sans-serif; padding: 0px; border: 0px; font-size: 5px; height: 20px; border-top: none none none; border-bottom: none none none">                           &nbsp;&nbsp;&nbsp;                           </td></tr>                   </tbody></table>             </div>  </td></tr> <tr><td class="txtsize" id="elm_1737569133457" style="font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: 0px; padding: 0px 0px; border-collapse: collapse" valign="top">            <div class="zpelement-wrapper" id="elm_1737569133457" style="word-wrap: break-word; overflow: hidden; padding-right: 0px; background-color: rgb(10, 83, 157)" bis_skin_checked="1">       <table bgcolor="#0a539d" border="0" cellpadding="0" cellspacing="0" class="zpAlignPos" style="font-size: 12px; padding: 0px; border: 0px; border-collapse: collapse" width="100%">                  <tbody><tr><td class="paddingcomp" style="border-collapse: collapse; border: 0px; padding: 7px 15px; font-size: 12pt; font-family: Arial, Helvetica; line-height: 25pt; border-top: 0px none; border-bottom: 0px none; padding-top: 7px; padding-bottom: 7px; padding-right: 15px; padding-left: 15px">                     <div style="background-color: rgb(10, 83, 157); line-height: 25pt" bis_skin_checked="1"><p align="center" style="line-height: 1.7; font-family: Arial, verdana; font-size: 12px; color: rgb(0, 0, 0); padding: 0px; margin: 0; text-align: center; line-height: 25pt"><font color="#ffffff" face="Montserrat, helvetica, arial, sans-serif" style="font-size: 24pt; line-height: 25pt"><b style=""><span>Follow Us For Updates!</span></b></font></p></div>    </td></tr>   </tbody></table>           </div> </td></tr> <tr><td class="txtsize" id="elm_1699901102024" style="font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: 0px; padding: 0px 0px; border-collapse: collapse" valign="top">                            <div class="zpelement-wrapper wdgts" id="elm_1699901102024" style="overflow: hidden; word-wrap: break-word" bis_skin_checked="1">       <table bgcolor="#0a539d" border="0" cellpadding="0" cellspacing="0" class="zpAlignPos" style="padding: 0px; border: 0px; border-collapse: collapse; font-size: 5px; background-color: rgb(10, 83, 157)" width="100%">        <tbody><tr><td style="border-collapse: collapse; font-family: Arial, Helvetica, sans-serif; padding: 7px 15px; border: 0px; font-size: 5px; border-top: 0px none; border-bottom: 0px none; padding-top: 24px; padding-bottom: 12px; padding-right: 15px; padding-left: 15px">         <table align="center" border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse; border-collapse: collapse; font-size: 12px; min-width: 100%; border: none" width="100%"><tbody><tr><td align="center" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: none; padding: 0px; margin: 0px" valign="top"> <table align="center" border="0" cellpadding="0" cellspacing="0" style="border: 0px; font-size: 12px; border-collapse: collapse; border: none; margin: auto"><tbody><tr><td align="left" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: none; padding: 0px; margin: 0px" valign="top"> <table align="center" border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse; border-collapse: collapse; font-size: 12px; border: none"><tbody><tr><td align="left" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: none; padding: 0px; margin: 0px" valign="top"><table align="left" border="0" cellpadding="0" cellspacing="0" style="font-size: 12px; border-collapse: collapse; border: none"><tbody><tr><td style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; padding-right: 9px; padding-bottom: 9px; border: none; padding: 0px; margin: 0px" valign="top"> <table border="0" cellpadding="0" cellspacing="0" style="font-size: 12px; border-collapse: separate; border: none"><tbody><tr><td align="left" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; padding: 7px; padding-top: 0px; padding-right: 9px; padding-bottom: 0px; padding-left: 9px; border: none" valign="middle"> <table align="left" border="0" cellpadding="0" cellspacing="0" style="font-size: 12px; border-collapse: collapse; border: none"><tbody><tr><td align="center" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: none; padding: 0px; margin: 0px; padding-bottom: 6px" valign="middle"> <a href="https://bsky.app/profile/norgenbiotek.bsky.social" style="text-decoration: underline; display: block; font-size: 1px" target="_blank" rel="noopener noreferrer"><img alt="BlueSky" height="35" src="https://stratus.campaign-image.com/images/bluesky_black_round_circle_outline_logo_24465_zc_v61_950380000023616575.png" style="border: 0px; margin: 0px; outline: none; text-decoration: none; width: 25px; height: 25px" vspace="10" width="35"></a> </td></tr><tr><td align="center" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: none; padding: 0px; margin: 0px" valign="middle"><a href="https://bsky.app/profile/norgenbiotek.bsky.social" style="display: block; font-size: 1px; font-weight: normal; line-height: normal; text-align: center; text-decoration: none" target="_blank" rel="noopener noreferrer"><p style="font-family: Arial, verdana; font-size: 12px; color: rgb(0, 0, 0); padding: 0px; margin: 0; line-height: normal; font-size: 8pt; font-family: Arial, Helvetica, sans-serif; color: rgb(255, 255, 255)">BlueSky</p></a> </td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td><td align="left" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: none; padding: 0px; margin: 0px" valign="top"><table align="left" border="0" cellpadding="0" cellspacing="0" style="font-size: 12px; border-collapse: collapse; border: none"><tbody><tr><td style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; padding-right: 9px; padding-bottom: 9px; border: none; padding: 0px; margin: 0px" valign="top"> <table border="0" cellpadding="0" cellspacing="0" style="font-size: 12px; border-collapse: separate; border: none"><tbody><tr><td align="left" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; padding: 7px; padding-top: 0px; padding-right: 9px; padding-bottom: 0px; padding-left: 9px; border: none" valign="middle"> <table align="left" border="0" cellpadding="0" cellspacing="0" style="font-size: 12px; border-collapse: collapse; border: none"><tbody><tr><td align="center" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: none; padding: 0px; margin: 0px; padding-bottom: 6px" valign="middle"> <a href="https://www.facebook.com/NorgenBiotek/" style="text-decoration: underline; display: block; font-size: 1px" target="_blank" rel="noopener noreferrer"><img alt="Facebook" height="35" src="https://stratus.campaign-image.com/images/950380000023616575_1_1738165027545_zcsclwgtfb2.png" style="border: 0px; margin: 0px; outline: none; text-decoration: none; width: 25px; height: 25px" vspace="10" width="35"></a> </td></tr><tr><td align="center" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: none; padding: 0px; margin: 0px" valign="middle"><a href="https://www.facebook.com/NorgenBiotek/" style="display: block; font-size: 1px; font-weight: normal; line-height: normal; text-align: center; text-decoration: none" target="_blank" rel="noopener noreferrer"><p style="font-family: Arial, verdana; font-size: 12px; color: rgb(0, 0, 0); padding: 0px; margin: 0; line-height: normal; font-size: 8pt; font-family: Arial, Helvetica, sans-serif; color: rgb(255, 255, 255)">Facebook</p></a> </td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td><td align="left" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: none; padding: 0px; margin: 0px" valign="top"><table align="left" border="0" cellpadding="0" cellspacing="0" style="font-size: 12px; border-collapse: collapse; border: none"><tbody><tr><td style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; padding-right: 9px; padding-bottom: 9px; border: none; padding: 0px; margin: 0px" valign="top"> <table border="0" cellpadding="0" cellspacing="0" style="font-size: 12px; border-collapse: separate; border: none"><tbody><tr><td align="left" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; padding: 7px; padding-top: 0px; padding-right: 9px; padding-bottom: 0px; padding-left: 9px; border: none" valign="middle"> <table align="left" border="0" cellpadding="0" cellspacing="0" style="font-size: 12px; border-collapse: collapse; border: none"><tbody><tr><td align="center" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: none; padding: 0px; margin: 0px; padding-bottom: 6px" valign="middle"> <a href="https://twitter.com/norgenbiotek" style="text-decoration: underline; display: block; font-size: 1px" target="_blank" rel="noopener noreferrer"><img alt="Twitter" height="35" src="https://stratus.campaign-image.com/images/950380000023616575_2_1738165027602_zcsclwgttwt2.png" style="border: 0px; margin: 0px; outline: none; text-decoration: none; width: 25px; height: 25px" vspace="10" width="35"></a> </td></tr><tr><td align="center" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: none; padding: 0px; margin: 0px" valign="middle"><a href="https://twitter.com/norgenbiotek" style="display: block; font-size: 1px; font-weight: normal; line-height: normal; text-align: center; text-decoration: none" target="_blank" rel="noopener noreferrer"><p style="font-family: Arial, verdana; font-size: 12px; color: rgb(0, 0, 0); padding: 0px; margin: 0; line-height: normal; font-size: 8pt; font-family: Arial, Helvetica, sans-serif; color: rgb(255, 255, 255)">Twitter</p></a> </td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td><td align="left" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: none; padding: 0px; margin: 0px" valign="top"><table align="left" border="0" cellpadding="0" cellspacing="0" style="font-size: 12px; border-collapse: collapse; border: none"><tbody><tr><td style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; padding-right: 9px; padding-bottom: 9px; border: none; padding: 0px; margin: 0px" valign="top"> <table border="0" cellpadding="0" cellspacing="0" style="font-size: 12px; border-collapse: separate; border: none"><tbody><tr><td align="left" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; padding: 7px; padding-top: 0px; padding-right: 9px; padding-bottom: 0px; padding-left: 9px; border: none" valign="middle"> <table align="left" border="0" cellpadding="0" cellspacing="0" style="font-size: 12px; border-collapse: collapse; border: none"><tbody><tr><td align="center" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: none; padding: 0px; margin: 0px; padding-bottom: 6px" valign="middle"> <a href="https://www.linkedin.com/company/norgenbiotek/" style="text-decoration: underline; display: block; font-size: 1px" target="_blank" rel="noopener noreferrer"><img alt="LinkedIn" height="35" src="https://stratus.campaign-image.com/images/950380000023616575_3_1738165027689_zcsclwgtlin2.png" style="border: 0px; margin: 0px; outline: none; text-decoration: none; width: 25px; height: 25px" vspace="10" width="35"></a> </td></tr><tr><td align="center" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: none; padding: 0px; margin: 0px" valign="middle"><a href="https://www.linkedin.com/company/norgenbiotek/" style="display: block; font-size: 1px; font-weight: normal; line-height: normal; text-align: center; text-decoration: none" target="_blank" rel="noopener noreferrer"><p style="font-family: Arial, verdana; font-size: 12px; color: rgb(0, 0, 0); padding: 0px; margin: 0; line-height: normal; font-size: 8pt; font-family: Arial, Helvetica, sans-serif; color: rgb(255, 255, 255)">LinkedIn</p></a> </td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td><td align="left" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: none; padding: 0px; margin: 0px" valign="top"><table align="left" border="0" cellpadding="0" cellspacing="0" style="font-size: 12px; border-collapse: collapse; border: none"><tbody><tr><td style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; padding-right: 9px; padding-bottom: 9px; border: none; padding: 0px; margin: 0px" valign="top"> <table border="0" cellpadding="0" cellspacing="0" style="font-size: 12px; border-collapse: separate; border: none"><tbody><tr><td align="left" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; padding: 7px; padding-top: 0px; padding-right: 9px; padding-bottom: 0px; padding-left: 9px; border: none" valign="middle"> <table align="left" border="0" cellpadding="0" cellspacing="0" style="font-size: 12px; border-collapse: collapse; border: none"><tbody><tr><td align="center" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: none; padding: 0px; margin: 0px; padding-bottom: 6px" valign="middle"> <a href="https://www.instagram.com/norgen.biotek" style="text-decoration: underline; display: block; font-size: 1px" target="_blank" rel="noopener noreferrer"><img alt="Instagram" height="35" src="https://stratus.campaign-image.com/images/950380000023616575_4_1738165027736_zcsclwgtinsta2.png" style="border: 0px; margin: 0px; outline: none; text-decoration: none; width: 25px; height: 25px" vspace="10" width="35"></a> </td></tr><tr><td align="center" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: none; padding: 0px; margin: 0px" valign="middle"><a href="https://www.instagram.com/norgen.biotek" style="display: block; font-size: 1px; font-weight: normal; line-height: normal; text-align: center; text-decoration: none" target="_blank" rel="noopener noreferrer"><p style="font-family: Arial, verdana; font-size: 12px; color: rgb(0, 0, 0); padding: 0px; margin: 0; line-height: normal; font-size: 8pt; font-family: Arial, Helvetica, sans-serif; color: rgb(255, 255, 255)">Instagram</p></a> </td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td><td align="left" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: none; padding: 0px; margin: 0px" valign="top"><table align="left" border="0" cellpadding="0" cellspacing="0" style="font-size: 12px; border-collapse: collapse; border: none"><tbody><tr><td style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; padding-right: 9px; padding-bottom: 9px; border: none; padding: 0px; margin: 0px" valign="top"> <table border="0" cellpadding="0" cellspacing="0" style="font-size: 12px; border-collapse: separate; border: none"><tbody><tr><td align="left" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; padding: 7px; padding-top: 0px; padding-right: 9px; padding-bottom: 0px; padding-left: 9px; border: none" valign="middle"> <table align="left" border="0" cellpadding="0" cellspacing="0" style="font-size: 12px; border-collapse: collapse; border: none"><tbody><tr><td align="center" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: none; padding: 0px; margin: 0px; padding-bottom: 6px" valign="middle"> <a href="https://www.youtube.com/@norgenbiotek?sub_confirmation=1" style="text-decoration: underline; display: block; font-size: 1px" target="_blank" rel="noopener noreferrer"><img alt="Youtube" height="35" src="https://stratus.campaign-image.com/images/950380000023616575_5_1738165027777_zcsclwgtyt2.png" style="border: 0px; margin: 0px; outline: none; text-decoration: none; width: 25px; height: 25px" vspace="10" width="35"></a> </td></tr><tr><td align="center" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: none; padding: 0px; margin: 0px" valign="middle"><a href="https://www.youtube.com/@norgenbiotek?sub_confirmation=1" style="display: block; font-size: 1px; font-weight: normal; line-height: normal; text-align: center; text-decoration: none" target="_blank" rel="noopener noreferrer"><p style="font-family: Arial, verdana; font-size: 12px; color: rgb(0, 0, 0); padding: 0px; margin: 0; line-height: normal; font-size: 8pt; font-family: Arial, Helvetica, sans-serif; color: rgb(255, 255, 255)">Youtube</p></a> </td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table>         </td></tr>                </tbody></table>                </div> </td></tr> <tr><td class="txtsize" id="elm_1737569970962" style="font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: 0px; padding: 0px 0px; border-collapse: collapse" valign="top">                                              <div class="zpelement-wrapper spacebar" id="elm_1737569970962" style="word-wrap: break-word; overflow: hidden; background-color: rgb(10, 83, 157)" bis_skin_checked="1">             <table bgcolor="#0a539d" border="0" cellpadding="0" cellspacing="0" class="zpAlignPos" style="padding: 0px; border: 0px; border-collapse: collapse; font-size: 5px; height: 20px" width="100%">                           <tbody><tr><td style="border-collapse: collapse; font-family: Arial, Helvetica, sans-serif; padding: 0px; border: 0px; font-size: 5px; height: 20px; border-top: none none none; border-bottom: none none none">                           &nbsp;&nbsp;&nbsp;                           </td></tr>                   </tbody></table>             </div>  </td></tr> <tr><td class="txtsize" id="elm_1740501623574" style="font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: 0px; padding: 0px 0px; border-collapse: collapse" valign="top">   <div class="zpelement-wrapper zpcol-layout" id="elm_1740501623574" style="word-wrap: break-word; padding-bottom: 0 !important; overflow: hidden; padding: 0px; margin: 0px" bis_skin_checked="1">  <div class="zpcolumns" style="padding: 0px; margin: 0px" bis_skin_checked="1">        <table bgcolor="#0a539d" cellpadding="0" cellspacing="0" style="font-size: 12px; border: 0px; padding: 0px; border-collapse: collapse; width: 100%; background-color: rgb(10, 83, 157)" width="100%">      <tbody><tr>   <td class="txtsize" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: 0px; padding: 0px 0px; border-top: none none none; border-bottom: none none none" valign="top">                                             <table align="left" cellpadding="0" cellspacing="0" class="cols" style="font-size: 12px; max-width: 199.98px; width: 100%; border: 0px; padding: 0px; border-collapse: collapse; float: left" width="100%">     <tbody><tr>                   <td class="txtsize" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: 0px; padding: 0px" valign="top">                                     <div class="zpwrapper col-space" id="pos_1740501623575" style="padding: 0px" bis_skin_checked="1">        <table bgcolor="transparent" border="0" cellpadding="0" cellspacing="0" style="font-size: 12px; border: 0px; padding: 0px; width: 100%; border-collapse: collapse; background-color: transparent">      <tbody><tr>   <td class="txtsize" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: 0px; padding: 0px 0px; border-top: none none none; border-bottom: none none none">              <div class="zpelement-wrapper image" id="elm_1740501623578" style="word-wrap: break-word; overflow: hidden; padding: 0px; background-color: transparent" bis_skin_checked="1">    <div bis_skin_checked="1">           <table align="left" border="0" cellpadding="0" cellspacing="0" class="zpAlignPos" style="font-size: 12px; text-align: left; padding: 0px; border: 0px; border-collapse: collapse; width: 100%; text-align: left">               <tbody><tr><td class="paddingcomp" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: 0px; padding: 7px 15px; text-align: center; padding-top: 7px; padding-bottom: 7px; padding-right: 15px; padding-left: 15px">    <img align="left" alt="Speech Bubbles" class="zpImage" height="30" hspace="0" size="C" src="https://stratus.campaign-image.com/images/950380000023616575_1_1740503161565_(1).png" style="width: 30px; height: 30px; max-width: 30px !important; border: 0px; text-align: left" vspace="0" width="30">    </td></tr>    </tbody></table>   </div>             </div>             </td></tr></tbody></table>            <div class="zpelement-wrapper" id="elm_1740501623579" style="word-wrap: break-word; overflow: hidden; padding-right: 0px" bis_skin_checked="1">       <table border="0" cellpadding="0" cellspacing="0" class="zpAlignPos" style="font-size: 12px; padding: 0px; border: 0px; border-collapse: collapse" width="100%">                  <tbody><tr><td class="paddingcomp" style="border-collapse: collapse; border: 0px; padding: 7px 15px; font-size: 12pt; font-family: Arial, Helvetica; line-height: 19pt; border-top: 0px none; border-bottom: 0px none; padding-top: 7px; padding-bottom: 7px; padding-right: 15px; padding-left: 15px">                     <div style="" bis_skin_checked="1"><p align="left" style="line-height: 1.7; font-family: Arial, verdana; font-size: 12px; color: rgb(0, 0, 0); padding: 0px; margin: 0; text-align: left; line-height: 16pt"><font color="#ffffff" face="Arial, Helvetica" style="font-size: 18pt"><b style="">Live Chat</b></font></p></div>    </td></tr>   </tbody></table>           </div>            <div class="zpelement-wrapper" id="elm_1740501623580" style="word-wrap: break-word; overflow: hidden; padding-right: 0px" bis_skin_checked="1">       <table border="0" cellpadding="0" cellspacing="0" class="zpAlignPos" style="font-size: 12px; padding: 0px; border: 0px; border-collapse: collapse" width="100%">                  <tbody><tr><td class="paddingcomp" style="border-collapse: collapse; border: 0px; padding: 7px 15px; font-size: 12pt; font-family: Arial, Helvetica; line-height: 15pt; border-top: 0px none; border-bottom: 0px none; padding-top: 7px; padding-bottom: 7px; padding-right: 15px; padding-left: 15px">                     <div style="line-height: 15pt" bis_skin_checked="1"><p align="justify" style="font-family: Arial, verdana; font-size: 12px; color: rgb(0, 0, 0); padding: 0px; margin: 0; line-height: 15pt; text-align: justify"><font color="#000000" face="Arial, Helvetica" style="color: rgb(0, 0, 0); font-size: 12pt; line-height: 15pt"><span style="line-height: 15pt"></span></font></p><p style="font-family: Arial, verdana; font-size: 12px; color: rgb(0, 0, 0); padding: 0px; margin: 0; line-height: 15pt; text-align: left"><font face="Arial, Helvetica" style="font-size: 12pt; line-height: 15pt"><font face="Arial, Helvetica" style="font-size: 12pt; line-height: 15pt"><font color="#ffffff" style="line-height: 15pt">Use our live Customer service chat.</font></font></font></p><p style="font-family: Arial, verdana; font-size: 12px; color: rgb(0, 0, 0); padding: 0px; margin: 0; line-height: 15pt; text-align: left"><font face="Arial, Helvetica" style="font-size: 12pt; line-height: 15pt"><font face="Arial, Helvetica" style="font-size: 12pt; line-height: 15pt"><font color="#ffffff" style="line-height: 15pt"><br></font></font></font></p><p style="font-family: Arial, verdana; font-size: 12px; color: rgb(0, 0, 0); padding: 0px; margin: 0; line-height: 15pt; text-align: left"><font face="Arial, Helvetica" style="font-size: 12pt; line-height: 15pt"><font face="Arial, Helvetica" style="font-size: 12pt; line-height: 15pt"><font style="line-height: 15pt"><b style=""><a alt="Chat Now" href="https://norgenbiotek.com/contact#help-with-orders" style="text-decoration: underline; color: rgb(255, 255, 255)" target="_blank"><font color="#ffffff" style="color: rgb(255, 255, 255); line-height: 15pt">Chat Now</font></a></b></font></font></font></p><p style="font-family: Arial, verdana; font-size: 12px; color: rgb(0, 0, 0); padding: 0px; margin: 0; line-height: 15pt"></p></div>    </td></tr>   </tbody></table>           </div>                       </div>        </td></tr></tbody></table>                                      <table align="left" cellpadding="0" cellspacing="0" class="cols" style="font-size: 12px; max-width: 199.98px; width: 100%; border: 0px; padding: 0px; border-collapse: collapse; float: left" width="100%">     <tbody><tr>                   <td class="txtsize" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: 0px; padding: 0px" valign="top">                                     <div class="zpwrapper col-space" id="pos_1740501623576" style="padding: 0px" bis_skin_checked="1">        <table bgcolor="#0a539d" border="0" cellpadding="0" cellspacing="0" style="font-size: 12px; border: 0px; padding: 0px; width: 100%; border-collapse: collapse; background-color: rgb(10, 83, 157)">      <tbody><tr>   <td class="txtsize" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: 0px; padding: 0px 0px; border-top: none none none; border-bottom: none none none">              <div class="zpelement-wrapper image" id="elm_1740501623581" style="word-wrap: break-word; overflow: hidden; padding: 0px; background-color: rgb(10, 83, 157)" bis_skin_checked="1">    <div bis_skin_checked="1">           <table align="left" border="0" cellpadding="0" cellspacing="0" class="zpAlignPos" style="font-size: 12px; text-align: left; padding: 0px; border: 0px; border-collapse: collapse; width: 100%; text-align: left">               <tbody><tr><td class="paddingcomp" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: 0px; padding: 7px 15px; text-align: center; padding-top: 7px; padding-bottom: 7px; padding-right: 15px; padding-left: 15px">    <img align="left" alt="Envelope" class="zpImage" height="30" hspace="0" size="C" src="https://stratus.campaign-image.com/images/950380000023616575_2_1740503161659_(1).png" style="width: 30px; height: 30px; max-width: 30px !important; border: 0px; text-align: left" title="Email Us" vspace="0" width="30">    </td></tr>    </tbody></table>   </div>             </div>             </td></tr></tbody></table>            <div class="zpelement-wrapper" id="elm_1740501623582" style="word-wrap: break-word; overflow: hidden; padding-right: 0px" bis_skin_checked="1">       <table border="0" cellpadding="0" cellspacing="0" class="zpAlignPos" style="font-size: 12px; padding: 0px; border: 0px; border-collapse: collapse" width="100%">                  <tbody><tr><td class="paddingcomp" style="border-collapse: collapse; border: 0px; padding: 7px 15px; font-size: 12pt; font-family: Arial, Helvetica; line-height: 19pt; border-top: 0px none; border-bottom: 0px none; padding-top: 7px; padding-bottom: 7px; padding-right: 15px; padding-left: 15px">                     <div style="" bis_skin_checked="1"><p align="left" style="line-height: 1.7; font-family: Arial, verdana; font-size: 12px; color: rgb(0, 0, 0); padding: 0px; margin: 0; text-align: left; line-height: 16pt"><font color="#ffffff" face="Arial, Helvetica" style="font-size: 18pt"><b style="">Email Us</b></font></p></div>    </td></tr>   </tbody></table>           </div>            <div class="zpelement-wrapper" id="elm_1740501623583" style="word-wrap: break-word; overflow: hidden; padding-right: 0px" bis_skin_checked="1">       <table border="0" cellpadding="0" cellspacing="0" class="zpAlignPos" style="font-size: 12px; padding: 0px; border: 0px; border-collapse: collapse" width="100%">                  <tbody><tr><td class="paddingcomp" style="border-collapse: collapse; border: 0px; padding: 7px 15px; font-size: 12pt; font-family: Arial, Helvetica; line-height: 15pt; border-top: 0px none; border-bottom: 0px none; padding-top: 7px; padding-bottom: 7px; padding-right: 15px; padding-left: 15px">                     <div style="line-height: 15pt" bis_skin_checked="1"><p align="left" style="font-family: Arial, verdana; font-size: 12px; color: rgb(0, 0, 0); padding: 0px; margin: 0; line-height: 15pt; text-align: left"><font color="#000000" face="Arial, Helvetica" style="color: rgb(0, 0, 0); font-size: 12pt; line-height: 15pt"><span style="line-height: 15pt"></span></font></p><p align="left" style="line-height: 1.7; font-family: Arial, verdana; font-size: 12px; color: rgb(0, 0, 0); padding: 0px; margin: 0; text-align: left; line-height: 15pt"><font face="Arial, Helvetica" style="font-size: 12pt; line-height: 15pt"><font face="Arial, Helvetica" style="font-size: 12pt; line-height: 15pt"><font color="#ffffff" style="line-height: 15pt">Email us 24 hours a day and we will respond.</font></font></font></p><p align="left" style="line-height: 1.7; font-family: Arial, verdana; font-size: 12px; color: rgb(0, 0, 0); padding: 0px; margin: 0; text-align: left; line-height: 15pt"><font face="Arial, Helvetica" style="font-size: 12pt; line-height: 15pt"><font face="Arial, Helvetica" style="font-size: 12pt; line-height: 15pt"><a alt="Email Now" href="mailto:orders@norgenbiotek.com?subject=" style="text-decoration: underline; color: rgb(255, 255, 255)" target="_blank"><font color="#ffffff" style="color: rgb(255, 255, 255); line-height: 15pt"><b>Email Now</b></font></a></font></font></p><p align="left" style="line-height: 1.7; font-family: Arial, verdana; font-size: 12px; color: rgb(0, 0, 0); padding: 0px; margin: 0; text-align: left; line-height: 15pt"></p></div>    </td></tr>   </tbody></table>           </div>                       </div>        </td></tr></tbody></table>                                      <table align="left" cellpadding="0" cellspacing="0" class="cols" style="font-size: 12px; max-width: 199.98px; width: 100%; border: 0px; padding: 0px; border-collapse: collapse; float: left" width="100%">     <tbody><tr>                   <td class="txtsize" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: 0px; padding: 0px" valign="top">                                     <div class="zpwrapper col-space" id="pos_1740501623577" style="padding: 0px" bis_skin_checked="1">        <table bgcolor="#0a539d" border="0" cellpadding="0" cellspacing="0" style="font-size: 12px; border: 0px; padding: 0px; width: 100%; border-collapse: collapse; background-color: rgb(10, 83, 157)">      <tbody><tr>   <td class="txtsize" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: 0px; padding: 0px 0px; border-top: none none none; border-bottom: none none none">              <div class="zpelement-wrapper image" id="elm_1740501623584" style="word-wrap: break-word; overflow: hidden; padding: 0px; background-color: rgb(10, 83, 157)" bis_skin_checked="1">    <div bis_skin_checked="1">           <table align="left" border="0" cellpadding="0" cellspacing="0" class="zpAlignPos" style="font-size: 12px; text-align: left; padding: 0px; border: 0px; border-collapse: collapse; width: 100%; text-align: left">               <tbody><tr><td class="paddingcomp" style="border-collapse: collapse; font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: 0px; padding: 7px 15px; text-align: center; padding-top: 7px; padding-bottom: 7px; padding-right: 15px; padding-left: 15px">    <img align="left" alt="Cell phone" class="zpImage" height="30" hspace="0" size="C" src="https://stratus.campaign-image.com/images/950380000023616575_3_1740503161716_(1).png" style="width: 30px; height: 30px; max-width: 30px !important; border: 0px; text-align: left" title="Call us" vspace="0" width="30">    </td></tr>    </tbody></table>   </div>             </div>             </td></tr></tbody></table>            <div class="zpelement-wrapper" id="elm_1740501623585" style="word-wrap: break-word; overflow: hidden; padding-right: 0px" bis_skin_checked="1">       <table border="0" cellpadding="0" cellspacing="0" class="zpAlignPos" style="font-size: 12px; padding: 0px; border: 0px; border-collapse: collapse" width="100%">                  <tbody><tr><td class="paddingcomp" style="border-collapse: collapse; border: 0px; padding: 7px 15px; font-size: 12pt; font-family: Arial, Helvetica; line-height: 19pt; border-top: 0px none; border-bottom: 0px none; padding-top: 7px; padding-bottom: 7px; padding-right: 15px; padding-left: 15px">                     <div style="" bis_skin_checked="1"><p align="left" style="line-height: 1.7; font-family: Arial, verdana; font-size: 12px; color: rgb(0, 0, 0); padding: 0px; margin: 0; text-align: left; line-height: 16pt"><font color="#ffffff" face="Arial, Helvetica" style="font-size: 18pt"><b style="">Call Us</b></font></p></div>    </td></tr>   </tbody></table>           </div>            <div class="zpelement-wrapper" id="elm_1740501623586" style="word-wrap: break-word; overflow: hidden; padding-right: 0px; background-color: rgb(10, 83, 157)" bis_skin_checked="1">       <table bgcolor="#0a539d" border="0" cellpadding="0" cellspacing="0" class="zpAlignPos" style="font-size: 12px; padding: 0px; border: 0px; border-collapse: collapse" width="100%">                  <tbody><tr><td class="paddingcomp" style="border-collapse: collapse; border: 0px; padding: 7px 15px; font-size: 12pt; font-family: Arial, Helvetica; line-height: 15pt; border-top: 0px none; border-bottom: 0px none; padding-top: 7px; padding-bottom: 7px; padding-right: 15px; padding-left: 15px">                     <div style="background-color: rgb(10, 83, 157); line-height: 15pt" bis_skin_checked="1"><p align="justify" style="font-family: Arial, verdana; font-size: 12px; color: rgb(0, 0, 0); padding: 0px; margin: 0; line-height: 15pt; text-align: justify"><font color="#000000" face="Arial, Helvetica" style="color: rgb(0, 0, 0); font-size: 12pt; line-height: 15pt"><span style="line-height: 15pt"></span></font></p><p align="left" style="line-height: 1.7; font-family: Arial, verdana; font-size: 12px; color: rgb(0, 0, 0); padding: 0px; margin: 0; text-align: left; line-height: 15pt"><font face="Arial, Helvetica" style="line-height: 15pt"><font face="Arial, Helvetica" style="line-height: 15pt"><a alt="1-866-667-4362" href="tel:1-866-667-4362" style="text-decoration: underline; color: rgb(255, 255, 255)" title="1-866-667-4362" target="_blank"><font color="#ffffff" style="color: rgb(255, 255, 255); font-size: 12pt; line-height: 15pt">1-866-667-4362</font></a></font></font></p><p style="font-family: Arial, verdana; font-size: 12px; color: rgb(0, 0, 0); padding: 0px; margin: 0; line-height: 15pt"></p></div>    </td></tr>   </tbody></table>           </div>                       </div>        </td></tr></tbody></table>              </td>      </tr>      </tbody></table>             </div></div> </td></tr> <tr><td class="txtsize" id="elm_1699901108278" style="font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: 0px; padding: 0px 0px; border-collapse: collapse" valign="top">            <div class="zpelement-wrapper" id="elm_1699901108278" style="word-wrap: break-word; overflow: hidden; padding-right: 0px; background-color: rgb(10, 83, 157)" bis_skin_checked="1">       <table bgcolor="#0a539d" border="0" cellpadding="0" cellspacing="0" class="zpAlignPos" style="font-size: 12px; padding: 0px; border: 0px; border-collapse: collapse" width="100%">                  <tbody><tr><td class="paddingcomp" style="border-collapse: collapse; border: 0px; padding: 7px 15px; font-size: 12pt; font-family: Arial, Helvetica; line-height: 19pt; border-top: 0px none; border-bottom: 0px none; padding-top: 20px; padding-bottom: 20px; padding-right: 15px; padding-left: 15px">                     <div style="background-color: rgb(10, 83, 157)" bis_skin_checked="1"><p align="center" style="font-family: Arial, verdana; font-size: 12px; color: rgb(0, 0, 0); padding: 0px; margin: 0; line-height: 19pt; text-align: center"><font color="#ffffff" face="Montserrat, helvetica, arial, sans-serif" style="font-size: 10pt"></font></p><span><div align="center" style="text-align: center" bis_skin_checked="1"><span style="font-size: 10pt; color: rgb(255, 255, 255); font-family: Montserrat, helvetica, arial, sans-serif">You are receiving this email because you have signed up for our newsletters or expressed interest in receiving emails from us.</span></div></span><div bis_skin_checked="1"><p align="center" style="font-family: Arial, verdana; font-size: 12px; color: rgb(0, 0, 0); padding: 0px; margin: 0; line-height: 19pt; text-align: center"><font color="#ffffff" face="Montserrat, helvetica, arial, sans-serif" style="font-size: 10pt"><span><span>Want to change how you receive these emails?</span><br></span></font></p><p align="center" style="font-family: Arial, verdana; font-size: 12px; color: rgb(0, 0, 0); padding: 0px; margin: 0; line-height: 19pt; text-align: center"><font style="font-size: 10pt"><font color="#ffffff" face="Montserrat, helvetica, arial, sans-serif" style="">You can <a alt="Unsubscribe" href="http://$[LI:UNSUBSCRIBE]$" style="text-decoration: underline; color: rgb(255, 255, 255)" target="_blank" title="Unsubscribe" rel="noopener noreferrer"><font color="#ffffff" style="color: rgb(255, 255, 255)">Unsubscribe</font></a> or <a alt="Update your preferences" href="http://$[LI:SUB_PREF]$" style="text-decoration: underline; color: rgb(255, 255, 255)" target="_blank" title="Update your preferences" rel="noopener noreferrer"><font color="#ffffff" style="color: rgb(255, 255, 255)">Update your preferences</font></a></font><br></font></p></div></div>    </td></tr>   </tbody></table>           </div> </td></tr>     </tbody></table> </div>       </td>       </tr>       </tbody></table>     </td>      <td style="font-size: 12px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: 0px; padding: 0px; border-collapse: collapse">&nbsp;</td>    </tr>    </tbody></table>';


      $date = date("Ymd");
      $form_name = $this->t('Webinar Registration Form');

      $first_name = explode(' ', $name, 2)[0];
      $last_name = array_key_exists(1, explode(' ', $name, 2)) ? explode(' ', $name, 2)[1] : null;

      try {
        $zoho = new RecordWrapper('leads');
        $record = [
          'Last_Name' => $name,
          'Email' => $email,
          'Company' => $company,
          'Job_Position' => $job,
          'Country' => $country_name,
          'Lead_Source' => 'Website Form',
          'Web_Forms' => [$form_name],
        ];

        // Perform the upsert operation
        $upsertResult = $zoho->upsert($record);
      } catch (Exception $e) {
      }

      $query = \Drupal::database()->insert('forms_to_zoho');
      $query->fields(['created_on', 'first_name', 'last_name', 'country', 'email', 'job_title', 'record', 'timestamp', 'form_name', 'company', 'opt_in', 'email_opt_out', 'lead_sample_type', 'lead_first_engagement', 'webinar_reg_reason', 'webinar_timeslot', 'utm_source','utm_medium', 'utm_campaign', 'utm_id', 'utm_term', 'utm_content']); //wrong syntax here breaks entire submit function 
      $query->values([$date, $first_name, $last_name, $country_name, $email, $job, '', time(), $form_name, $company, $subscribe_emaildb, $subscribe_optout, $sample, $referral, $what_info, $timeslot, $utm_source, $utm_medium, $utm_campaign, $utm_id, $utm_term, $utm_content]);
      $query->execute();
      $insert_id = \Drupal::database()->lastInsertId('forms_to_zoho');
      $insert_id = "#" . $insert_id;

      if ($form_state->hasAnyErrors()) {} 
      else {
        $time = time();
        // $recipient_email = 'orders@norgenbiotek.com';  // Angela requested that her and Sebastian be removed from emails, since they receive one through Zoho already
        $recipient_email = 'marketing@norgenbiotek.com,info@norgenbiotek.com'; // real addresses
        //$recipient_email = 'liam.howes@norgenbiotek.com';
        $customer_email = $email;
        $subject_org = ' [Webinar Registration] ' . $fist_name . ' (' . $email . ')- ' . date('F j, Y, g:i a', $time) ." ".$insert_id;
        $subject_customer = 'Thank You '.$fist_name.' for Registering!';
        nor_forms_email_redirect($output, $recipient_email, $subject_org); 
        nor_forms_email_redirect($customer_output, $customer_email, $subject_customer); // Copied from Zoho
     }
    }
    $form_state->setRebuild(TRUE);
    //leave empty else execution will happen on each submit button
  }
}