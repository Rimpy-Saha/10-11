<?php


namespace Drupal\database_page_files\Form;

use Drupal\Core\Url;
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
use Drupal\Core\Database\Query\PagerSelectExtender;

class DatabasePageFiles extends FormBase{

  public function getFormID(){
    return 'database_page_files';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['#attached']['library'][] = 'core/drupal.ajax';
    $form['#attached']['library'][] = 'core/jquery.form';

    $form['#attached']['library'][] = 'database_page_files/database_page_files';
    $results_per_page = 10; // default 10

    $exceptions_list = [
      'sample_collection_and_preservation_kits'=>'/category/sample-collection-and-preservation',
      'clean_up_concentration'=>'/category/clean-concentration',
      'cf_dna_cf_rna_from_blood'=>'/category/cf-dna-cf-rna-blood',
      'its_library_preparation_kits_for_illumina'=>'/category/its-library-preparation-kits',
      'quantified_dna_standards_for_pathogens'=>'/category/quantified-dna-standards-pathogens',
      'saliva_sample_collection_and_preservation_devices'=>'/category/saliva',
      'stool_sample_preparation_devices'=>'/category/stool',
      'swab_collection_and_preservation'=>'/category/swab-based',
      'urine_sample_collection_and_preservation_devices'=>'/category/urine',
      'cell_free_dna_purification'=>'/category/cell-free-dna-isolation',
      'dna_clean_up_and_concentration'=>'/category/dna-clean-and-concentration',
      'plasmid_dna'=>'/category/plasmid-dna-preparation',
      'protein_clean_up_concentration_and_endotoxin_removal'=>'/category/protein-clean-concentration-and-endotoxin-removal',
      'rna_clean_up_and_concentration_kits'=>'/category/rna-clean-and-concentration',
      'rna_isolation_from_purified_exosomes'=>'/category/rna-isolation-purified-exosomes',
      'total_genomic_dna'=>'/category/total-genomic-dna-isolation',
      'urine_protein'=>'/category/urine-protein-concentration',
      'water_borne_pathogen_detection'=>'/category/waterborne-pathogen-detection',
    ];

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
    
    //$page_num = isset($params['page'])?$params['page']:1;
    //$page_num = $form_state->get('page_num')??1;
    (int) $page_num = $form_state->getValue('page_select') != "" ? intval($form_state->getValue('page_select')) : 1;

    /* $form['testing'] = [
      '#type' => 'item',
      '#markup' => '<pre>'.$form_state->getValue('page_select').'</pre>',
    ]; */
        
    $form['#prefix'] = '<div id="database-page-files-container">';

    $form['top']['title'] = [
      '#type' => 'markup',
      '#markup' => '<h1 class="center">Product Documentation</h1>',
    ];

    /* $form['search'] = [
      '#type' => 'search',
      '#prefix' => '<div class="results-container">',
      '#suffix' => '</div>',
    ]; */

    $form['search-sort'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Search and Sort'),
      '#title_display' => 'invisible',
      /* '#attributes'    => [
        'onChange' => 'this.form.submit();',
      ], */
    ];

    $form['search-sort']['search-products'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Search Products'),
      '#title_display' => 'invisible',
      '#target_type' => 'commerce_product_variation',
      '#tags' => TRUE,
      '#attributes' => [
        'Placeholder' => $this->t('Search for SKU (Cat.) or Product Title'),
        'class' => ['entity-product-request-field'], // Add a custom CSS class
        /* 'onChange' => 'this.form.submit();', */
      ],
      '#selection_handler' => 'nor_product_autocomplete', 
      '#default_value' => isset($variationId) ? \Drupal::entityTypeManager()->getStorage('commerce_product_variation')->load($variationId) : null,
      '#ajax' => [
        'callback' => '::changeSearchProduct',
        'wrapper' => 'database-page-files-container', 
        /* 'event' => 'autocompleteclose change',  */
        'event' => 'autocompleteclose', // remove the change event to prevent users from hitting enter, which would load the dbfiles_form route
        'url' => \Drupal\Core\Url::fromRoute(
          'database_page_files.dbfiles_form',
          [],
          [
            'query' => [
              'ajax_form' => $this->getFormId(),
              '_wrapper_format' => 'drupal_ajax' // Force correct format
            ]
          ]
        ),
        /* 'url' => \Drupal\Core\Url::fromUserInput(
          \Drupal::service('path.current')->getPath(),
          [
            'query' => [
              'ajax_form' => $this->getFormId(),
              '_wrapper_format' => 'drupal_ajax' // Force correct format
            ]
          ]
        ), */
      ],
    ];

    $form['search-sort']['figure-cat-filter'] = [
      '#type' => 'select',
      '#title' => '<i class="fas fa-sort"></i> Product Category',
      '#options' => [
        'all' => 'all',
        'analysis' => 'analysis',
        'lab_components' => 'lab components',
        'ngs_library_preparation_kits' => 'ngs library preparation kits',
        'pcr_based_detection' => 'pcr based detection',
        'purification_and_isolation' => 'purification and isolation',
        'sample_collection_and_preservation_kits'=>'sample collection and preservation kits',
        '16s_rna_library_prep_kits' => '16s rna library prep kits',
        'cf_dna_cf_rna_from_blood'=>'cf-dna/cf-rna from blood',
        'dna_and_rna_ladders' => 'dna and rna ladders',
        'dna_isolation_kits' => 'dna isolation kits',
        'enzymes' => 'enzymes',
        'exosome_purification_and_rna_isolation_kits' => 'exosome purification and rna isolation kits',
        'its_library_preparation_kits_for_illumina'=>'its library preparation kits for illumina',
        'microbiology_kits_and_assays' => 'microbiology kits and assays',
        'multiple_analyte_kits' => 'multiple analyte kits',
        'clean_up_concentration'=>'clean up concentration',
        'normalization_quantification' => 'normalization quantification',
        'nucleic_acid_preservatives' => 'nucleic acid preservatives',
        'pcr_reagents' => 'pcr reagents',
        'protein_purification_kits' => 'protein purification kits',
        'quantified_dna_standards_for_pathogens'=>'quantified dna standards for pathogens',
        'rna_purification_kits' => 'rna purification kits',
        'saliva_sample_collection_and_preservation_devices'=>'saliva sample collection and preservation devices',
        'shipping_accessories' => 'shipping accessories',
        'small_rna_library_prep_kits' => 'small rna library prep kits',
        'stool_sample_preparation_devices'=>'stool sample preparation devices',
        'swab_collection_and_preservation'=>'swab collection and preservation',
        'urine_sample_collection_and_preservation_devices'=>'urine sample collection and preservation devices',
        'virus_purification' => 'virus purification',
        'cell_free_dna_purification'=>'cell free dna purification',
        'dna_clean_up_and_concentration'=>'dna clean up and concentration',
        'dna_ladders' => 'dna ladders',
        'endotoxin_removal' => 'endotoxin removal',
        'exosome_depletion' => 'exosome depletion',
        'food_and_milk_pathogen_detection' => 'food and milk pathogen detection',
        'plasmid_dna'=>'plasmid dna',
        'protein_clean_up_concentration_and_endotoxin_removal'=>'protein clean up concentration and endotoxin removal',
        'rna_clean_up_and_concentration_kits'=>'rna clean up and concentration kits',
        'rna_isolation_from_purified_exosomes'=>'rna isolation from purified exosomes',
        'total_genomic_dna'=>'total genomic dna',
        'urine_protein'=>'urine protein',
        'water_borne_pathogen_detection'=>'water borne pathogen detection',
      ],
      //'#default_value' => 'lab_components',
      '#ajax' => [
        'callback' => '::changeFileCategory',
        'wrapper' => 'database-page-files-container', 
        'event' => 'change', 
        'url' => \Drupal\Core\Url::fromRoute(
          'database_page_files.dbfiles_form',
          [],
          [
            'query' => [
              'ajax_form' => $this->getFormId(),
              '_wrapper_format' => 'drupal_ajax' // Force correct format
            ]
          ]
        ),
      ],
    ];

    $form['search-sort']['file-type-filter'] = [
      '#type' => 'select',
      '#title' => '<i class="fas fa-sort"></i> File Type',
      '#options' => [
        0 => 'All',
        1 => 'Protocol',
        2 => 'Short Protocol',
        3 => 'Appication Note',
        4 => 'Flyer',
        5 => 'Product Information Sheet',
        6 => 'Safety Data Sheet',
        7 => 'Supplementary Protocol',
      ],
      '#ajax' => [
        'callback' => '::changeFileFilter',
        'wrapper' => 'database-page-files-container', 
        'event' => 'change', 
        'url' => \Drupal\Core\Url::fromRoute(
          'database_page_files.dbfiles_form',
          [],
          [
            'query' => [
              'ajax_form' => $this->getFormId(),
              '_wrapper_format' => 'drupal_ajax' // Force correct format
            ]
          ]
        ),
      ],
      /* '#attributes'    => [
        'onChange' => 'this.form.submit();',
      ], */
    ];

    $form['search-sort']['sort'] = [
      '#type' => 'select',
      '#title' => '<i class="fas fa-sort"></i> Sort',
      '#options' => [
        0 => 'Created: Most Recent',
        1 => 'Created: Oldest',
      ],
      '#ajax' => [
        'callback' => '::changeSort',
        'wrapper' => 'database-page-files-container', 
        'event' => 'change', 
        'url' => \Drupal\Core\Url::fromRoute(
          'database_page_files.dbfiles_form',
          [],
          [
            'query' => [
              'ajax_form' => $this->getFormId(),
              '_wrapper_format' => 'drupal_ajax' // Force correct format
            ]
          ]
        ),
      ],
      /* '#attributes'    => [
        'onChange' => 'this.form.submit();',
      ], */
    ];

    $form['submit'] = [
    '#type' => 'submit',
    '#value' => t('Apply'),
    // The submit itself is necessary, but now it can be hidden:
    '#attributes' => [
      'style' => ['display: none;'],
    ],
  ];

  if($form_state->getValue('search-products')){
    $variation_id = $form_state->getValue('search-products')[0]['target_id'];
  }

  if(isset($variation_id)){
    $variation = \Drupal::entityTypeManager()->getStorage('commerce_product_variation')->load($variation_id);
    $product_id = $variation->getProductId();
    $sku = $variation->getSku();
    /* $form['testing'] = [
      '#type' => 'item',
      '#markup' => '<pre>'.$variation->getSku().'</pre>',
    ]; */
  } 

  if($form_state->getValue('figure-cat-filter') && $form_state->getValue('figure-cat-filter') != 'all'){
    \Drupal::logger('databasePageFigures')->notice($form_state->getValue('figure-cat-filter'));
    $selected_category = $form_state->getValue('figure-cat-filter');
  }

  if(isset($selected_category)) \Drupal::logger('databasePageFigures')->notice('selected category: '.$selected_category);
        
   $form['results-info'] = [
    '#type' => 'item',
   ];

    $form['results-info']['num_results'] = [
      '#type' => 'item',
      '#markup' => '',
    ];

    $form['results-info']['num_page_results'] = [
      '#type' => 'item',
      '#markup' => '',
    ];

    $form['results'] = [
      '#type' => 'markup',
      '#prefix' => '<div class="results-container">',
      '#suffix' => '</div>',
    ];

    /* Query to get all the base info */
    $query = \Drupal::database()->select('file_managed', 'fm');
    $query->leftJoin('file_usage', 'fu', 'fm.fid = fu.fid');
    $query->innerJoin('node_field_data', 'nfd', 'fu.id = nfd.nid AND fu.type = "node" AND nfd.status = 1');
    $query->innerJoin('node__field_commerce_product', 'nfcp', 'nfcp.entity_id = nfd.nid AND nfcp.deleted = 0');
    if(isset($product_id)){ // pre-filter the results based on selected product variation - show only results of variation's parent product
      $query->innerJoin('commerce_product_field_data', 'cpfd', 'cpfd.product_id = nfcp.field_commerce_product_target_id AND cpfd.type = "norproduct" AND cpfd.status = 1 AND cpfd.product_id = :product_id', [':product_id' => $product_id]);
    }
    else $query->innerJoin('commerce_product_field_data', 'cpfd', 'cpfd.product_id = nfcp.field_commerce_product_target_id AND cpfd.type = "norproduct" AND cpfd.status = 1');
    $query->innerJoin('commerce_product_variation_field_data', 'cpvfd', 'cpvfd.product_id = cpfd.product_id AND cpvfd.status = 1');
    $query->innerJoin('path_alias', 'pa', 'pa.path = CONCAT("/node/",nfd.nid)');
    switch($form_state->getValue('file-type-filter')){
      case 1:
        $query->innerJoin('node__norproduct_protocol', 'nnp', 'nnp.entity_id = nfd.nid AND nnp.norproduct_protocol_target_id = fu.fid'); // protocols only
        break;
      case 2:
        $query->innerJoin('node__norproduct_shortprotocol', 'nnsp', 'nnsp.entity_id = nfd.nid AND nnsp.norproduct_shortprotocol_target_id = fu.fid');
        break;
      case 3:
        $query->innerJoin('node__norproduct_applicationnote', 'nnan', 'nnan.entity_id = nfd.nid AND nnan.norproduct_applicationnote_target_id = fu.fid');
        break;
      case 4:
        $query->innerJoin('node__field_product_flyer', 'nfpf', 'nfpf.entity_id = nfd.nid AND nfpf.field_product_flyer_target_id = fu.fid');
        break;
      case 5:
        $query->innerJoin('node__norproduct_informationsheet', 'nnis', 'nnis.entity_id = nfd.nid AND nnis.norproduct_informationsheet_target_id = fu.fid');
        break;
      case 6:
        $query->innerJoin('node__norproduct_msds', 'nnmsds', 'nnmsds.entity_id = nfd.nid AND nnmsds.norproduct_msds_target_id = fu.fid');
        break;
      case 7:
        $query->innerJoin('node__norproduct_supplementaryprotocol', 'nnsup', 'nnsup.entity_id = nfd.nid AND nnsup.norproduct_supplementaryprotocol_target_id = fu.fid');
        break;
      case 0:
        $query->leftJoin('node__norproduct_protocol', 'nnp', 'nnp.entity_id = nfd.nid AND nnp.norproduct_protocol_target_id = fu.fid');
        $query->leftJoin('node__norproduct_shortprotocol', 'nnsp', 'nnsp.entity_id = nfd.nid AND nnsp.norproduct_shortprotocol_target_id = fu.fid');
        $query->leftJoin('node__norproduct_applicationnote', 'nnan', 'nnan.entity_id = nfd.nid AND nnan.norproduct_applicationnote_target_id = fu.fid');
        $query->leftJoin('node__field_product_flyer', 'nfpf', 'nfpf.entity_id = nfd.nid AND nfpf.field_product_flyer_target_id = fu.fid');
        $query->leftJoin('node__norproduct_informationsheet', 'nnis', 'nnis.entity_id = nfd.nid AND nnis.norproduct_informationsheet_target_id = fu.fid');
        $query->leftJoin('node__norproduct_msds', 'nnmsds', 'nnmsds.entity_id = nfd.nid AND nnmsds.norproduct_msds_target_id = fu.fid');
        $query->leftJoin('node__norproduct_supplementaryprotocol', 'nnsup', 'nnsup.entity_id = nfd.nid AND nnsup.norproduct_supplementaryprotocol_target_id = fu.fid');
        break;
    }
    $query->fields('fm', ['fid','filename','uri','filemime','filesize','type', 'changed']);
    $query->fields('nfd', ['nid']);
    $query->fields('cpfd', ['product_id']);
    $query->fields('pa', ['alias']);

    switch($form_state->getValue('file-type-filter')){
      case 1:
        $query->fields('nnp', ['norproduct_protocol_description']);
        $query->condition('nnp.norproduct_protocol_description', '', '<>');
        break;
      case 2:
        $query->fields('nnsp', ['norproduct_shortprotocol_description']);
        $query->condition('nnsp.norproduct_shortprotocol_description', '', '<>');
        break;
      case 3:
        $query->fields('nnan', ['norproduct_applicationnote_description']);
        $query->condition('nnan.norproduct_applicationnote_description', '', '<>');
        break;
      case 4:
        $query->fields('nfpf', ['field_product_flyer_description']);
        $query->condition('nfpf.field_product_flyer_description', '', '<>');
        break;
      case 5:
        $query->fields('nnis', ['norproduct_informationsheet_description']);
        $query->condition('nnis.norproduct_informationsheet_description', '', '<>');
        break;
      case 6:
        $query->fields('nnmsds', ['norproduct_msds_description']);
        $query->condition('nnmsds.norproduct_msds_description', '', '<>');
        break;
      case 7:
        $query->fields('nnsup', ['norproduct_supplementaryprotocol_description']);
        $query->condition('nnsup.norproduct_supplementaryprotocol_description', '', '<>');
        break;
      case 0:
        $query->fields('nnp', ['norproduct_protocol_description']);
        $query->fields('nnsp', ['norproduct_shortprotocol_description']);
        $query->fields('nnan', ['norproduct_applicationnote_description']);
        $query->fields('nfpf', ['field_product_flyer_description']);
        $query->fields('nnis', ['norproduct_informationsheet_description']);
        $query->fields('nnmsds', ['norproduct_msds_description']);
        $query->fields('nnsup', ['norproduct_supplementaryprotocol_description']);
        $orGroup1 = $query->orConditionGroup()
        ->condition('nnp.norproduct_protocol_description', '', '<>')
        ->condition('nnsp.norproduct_shortprotocol_description', '', '<>')
        ->condition('nnan.norproduct_applicationnote_description', '', '<>')
        ->condition('nfpf.field_product_flyer_description', '', '<>')
        ->condition('nnis.norproduct_informationsheet_description', '', '<>')
        ->condition('nnmsds.norproduct_msds_description', '', '<>')
        ->condition('nnsup.norproduct_supplementaryprotocol_description', '', '<>');
        $query->condition($orGroup1);// this will ensure the file is one of the fields on a product page, and prevents showing old orphaned files that Drupal fails to update in file_managed/file_usage tables
        break;
    }

    // Add GROUP_CONCAT for variation_ids (MySQL specific)
    $query->addExpression('GROUP_CONCAT(cpvfd.variation_id)', 'variation_ids');

    // Add subquery for getting categories
    $subquery = "
      SELECT GROUP_CONCAT(DISTINCT cpv_categories.field_product_categories_value) AS categories 
      FROM commerce_product_variation__64c051ba81 AS cpv_categories
      WHERE cpv_categories.entity_id = cpvfd.variation_id
      AND cpv_categories.deleted = 0
    ";
    $query->addExpression("($subquery)", 'categories');

    $query->condition('fm.status', 1, '='); // permanent files only
    $query->condition('fm.type', 'document' ,'='); // document (files only)

    $query->groupBy('cpfd.product_id');
    $query->groupBy('fm.fid');

    if(isset($selected_category)) {
        $query->having("FIND_IN_SET(:selected_category, categories) > 0", [
        ':selected_category' => $selected_category // The category you want to filter by
      ]);
    }
    /* else{ 
      $query->having("FIND_IN_SET(:selected_category, categories) > 0", [
        ':selected_category' => 'lab_components' // The category you want to filter by
      ]);
    } */

    switch($form_state->getValue('sort')){
      case 0:
        $query->orderBy('fm.changed', 'DESC'); // newest first
        break;
      case 1:
        $query->orderBy('fm.changed', 'ASC'); // oldest first
        break;
    }
    $total_results = count($query->execute()->fetchAll());

    if((($page_num * $results_per_page) - $results_per_page + 1) > $total_results){
      $page_num = 1;
      $form['page_select']['#value'] = 1;
    }

    $results_offset = ($page_num - 1) * $results_per_page;

    
    \Drupal::logger('databasePageFiles')->notice('results_offset: '.$results_offset);
    \Drupal::logger('databasePageFiles')->notice('results_per_page: '.$results_per_page);

    $query->range($results_offset, $results_per_page);
    //dump($query->__toString());

    //$query = $query->extend(PagerSelectExtender::class)->limit($results_per_page); // Built-in Drupal paginator :)

    $rows = $query->execute()->fetchAll();
    \Drupal::logger('databasePageFiles')->notice('number of rows fetched: '.count($rows));

    //dump(\Drupal::database()->getPagerManager()->getPager());

    /* if ($pager = \Drupal::database()->getPagerManager()->getPager()) {
      $current_page = $pager->getCurrentPage();
      $total_pages = $pager->getTotalPages();
      $items_per_page = $pager->getLimit();
      $total_items = $pager->getTotalItems();
    }
    else {
      throw new \Exception('Pager has not been initialized');
    } */
    $form['pager'] = $this->buildCustomPager($page_num, $results_per_page, $total_results);


    /* $form['pagination'] = [
      '#type' => 'pager',
      //'#route_name' => '<current>',
      '#route_name' => 'database_page_files.dbfiles_form',
      '#ajax' => [
        'callback' => '::changePage',
        'wrapper' => 'database-page-files-container',
        'method' => 'replace',
      ],
      '#attributes' => [
        'class' => ['use-ajax'], // This is crucial
      ],
    ]; */

    //$num_pages = ceil($total_results / $results_per_page);

    $how_many_get_removed = 0;
    foreach ($rows as $row_num => $row){

      /* Get file type */
      $file_type = '';
      $file_type_text = '';
      $file_description = '';
      if(isset($row->norproduct_protocol_description)){
        $file_type = 'protocol';
        $file_description = $row->norproduct_protocol_description;
      }
      elseif(isset($row->norproduct_shortprotocol_description)){
        $file_type = 'short_protocol';
        $file_description = $row->norproduct_shortprotocol_description;
      }
      elseif(isset($row->norproduct_applicationnote_description)){
        $file_type = 'application_note';
        $file_description = $row->norproduct_applicationnote_description;
      }
      elseif(isset($row->field_product_flyer_description)){
        $file_type = 'flyer';
        $file_description = $row->field_product_flyer_description;
      }
      elseif(isset($row->norproduct_informationsheet_description)){
        $file_type = 'product_information_sheet';
        $file_description = $row->norproduct_informationsheet_description;
      }
      elseif(isset($row->norproduct_msds_description)){
        $file_type = 'safety_data_sheet';
        $file_description = $row->norproduct_msds_description;
      }
      elseif(isset($row->norproduct_supplementaryprotocol_description)){
        $file_type = 'supplementary_protocol';
        $file_description = $row->norproduct_supplementaryprotocol_description;
      }

      if($file_type == ''){
        $how_many_get_removed++;
        $total_results--;
        \Drupal::logger('databasePageFiles')->notice('skipped file: '.print_r($row, true));
        \Drupal::logger('databasePageFiles')->notice('total_results: '.$total_results);
        continue; // skip any files that don't exist in one of the node target fields, or don't have a file description
      } 

      // get variations
      $variation_ids = explode(',', $row->variation_ids);
      // Get categories
      $categories = $row->categories!=NULL ? explode(',', $row->categories) : [];

      $row_markup = '<div class="views-row row-'.$row_num.'" file-type="'.$file_type.'">';
      $relative_file_url = \Drupal::service('file_url_generator')->generateAbsoluteString($row->uri);
      $row_markup .= '<div class="file-type">'.str_replace('_',' ',$file_type).'</div>';
      $row_markup .= '<h3><a href="'.$relative_file_url.'"><i class="far fa-file-pdf fa-2x"></i>';
      if($file_description) $row_markup .= $file_description;
      else $row_markup .= $row->filename;
      $row_markup .= '</a></h3>';

      $row_markup .= '<ul class="category-links">';
      $category_reference = [];
      foreach($categories as $category_num => $product_category){
        if(in_array($product_category, $category_reference)) continue;
        /* Convert product_categories to URL */
        /* Get our product_categories value and convert to URL */
        $category_path = '/';
        if(array_key_exists($product_category, $exceptions_list)){
          $category_path = $exceptions_list[$product_category];
        }
        else {
          // Replace dashes with underscores.
          $category_path = '/category/' . str_replace('_', '-', $product_category);
        }
        $row_markup .= '<li>';
        $row_markup .= '<a href="'.$category_path.'">'.str_replace('_', ' ', $product_category).'</a>';
        $row_markup .= '</li>';
        $category_reference[] = $product_category;
      }
      $row_markup .= '</ul>';


      $row_markup .= '<h4>Related Products</h4>';
      $row_markup .= '<ul class="rel-prods">';
      foreach($variation_ids as $variation_num => $variation_id){
        $variation = \Drupal::entityTypeManager()->getStorage('commerce_product_variation')->load($variation_id);
        $row_markup .= '<li>';
        $row_markup .= '<a href="'.$row->alias.'?v='.$variation_id.'">'.$variation->getSKU().' - '.$variation->getTitle().'</a>';
        $row_markup .= '</li>';
      }
      $row_markup .= '</ul>';
      $row_markup .= '</div>';

      $form['results'][$row_num] = array(
        '#type' => 'markup',
        '#markup' => $row_markup,
      );
    }

    $showing_results_floor = min(((($page_num - 1) * $results_per_page) + 1), $total_results);
    
    $form['results-info']['num_results']['#markup'] = $total_results.' files';
    $form['results-info']['num_page_results']['#markup'] = 'Showing files '.$showing_results_floor.' - '.min(($results_per_page * ($page_num)), $total_results);


    $form['#suffix'] = '</div>';
    return $form;
  
  }
  
  
 /*  public function getProductTable(array &$form, FormStateInterface $form_state){
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
  } */
 
  
  public function changeSearchProduct(array &$form, FormStateInterface $form_state) {
    $form['page_select']['#value'] = 1;
    return $form;
  }

  public function changeFileFilter(array &$form, FormStateInterface $form_state) {
    $form['page_select']['#value'] = 1;
    return $form;
  }

  public function changeFileCategory(array &$form, FormStateInterface $form_state) {
    $form['page_select']['#value'] = 1;
    return $form;
  }

  public function changeSort(array &$form, FormStateInterface $form_state) {
    $form['page_select']['#value'] = 1;
    return $form;
  }
  
  public function changePage(array &$form, FormStateInterface $form_state) {
    return $form;
  }

 /*  public function ajaxPagerCallback(): AjaxResponse {
    $response = new AjaxResponse();
    $form = $this->formBuilder->getForm('\Drupal\database_page_files\Form\DatabasePageFiles');
    $response->addCommand(new ReplaceCommand('#form-wrapper-for-ajax-pager', $form));
    return $response;
  } */
  protected function buildCustomPager($current_page, $items_per_page, $total_results){
    $total_pages = ceil($total_results / $items_per_page) > 0 ? ceil($total_results / $items_per_page) : 1;
    $max_pagers = 5;

    $pager = [
      '#type' => 'container',
      '#attributes' => ['class' => ['custom-ajax-pager']],
    ];

    // Previous button
    /* if ($current_page > 2) {
      $pager['prev'] = [
        '#type' => 'button',
        '#value' => '← Previous',
        '#ajax' => [
          'callback' => '::updateResults',
          'wrapper' => 'database-page-files-container',
        ],
        '#attributes' => [
          'data-page' => $current_page - 1,
        ],
      ];
    } */

    // Page numbers
    $pager["page_select"] = [
      '#type' => 'select',
      '#title' => 'Page',
      '#options' => [],
      '#ajax' => [
        'callback' => '::changePage',
        'wrapper' => 'database-page-files-container',
        'url' => \Drupal\Core\Url::fromRoute(
          'database_page_files.dbfiles_form',
          [],
          [
            'query' => [
              'ajax_form' => $this->getFormId(),
              '_wrapper_format' => 'drupal_ajax' // Force correct format
            ]
          ]
        ),
      ],
      '#default' => $current_page,
      '#suffix' => "of ".$total_pages,
      /* '#attributes'    => [
        'onChange' => 'this.form.submit();',
      ], */
    ];
    for ($i = 1; $i <= $total_pages; $i++) {
      $pager["page_select"]['#options'][$i] = $i;
    }

    // Next button
    /* if ($current_page < $total_pages) {
      $pager['next'] = [
        '#type' => 'button',
        '#value' => 'Next →',
        '#ajax' => [
          'callback' => '::updateResults',
          'wrapper' => 'database-page-files-container',
        ],
        '#attributes' => [
          'data-page' => $current_page + 1,
        ],
      ];
    } */

    return $pager;
  }

  public function submitForm(array &$form, FormStateInterface $form_state){
   /*  $page_num = $form_state->getValue('page_select') ?? 1;
    $form_state->set('page_num', $page_num);
    \Drupal::logger('databasePageFiles')->notice(print_r($form_state->getTriggeringElement())); */
    $form_state->setRebuild(TRUE);
  }
  
}