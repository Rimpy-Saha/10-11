<?php


namespace Drupal\database_page_figures\Form;

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

class DatabasePageFigures extends FormBase{

  public function getFormID(){
    return 'database_page_figures';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['#attached']['library'][] = 'core/drupal.ajax';
    $form['#attached']['library'][] = 'core/jquery.form';

    $form['#attached']['library'][] = 'database_page_figures/database_page_figures';
    $results_per_page = 10; // default 10
    if($form_state->get('results_per_page')) $results_per_page = $form_state->get('results_per_page');

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
    $page_num = $form_state->getValue('page_select') != "" ? intval($form_state->getValue('page_select')) : 1;

    \Drupal::logger('databasePageFigures')->notice('current_page: '.$form_state->getValue('page_select'));

    \Drupal::logger('databasePageFigures')->notice($page_num);
    /* $form['testing'] = [
      '#type' => 'item',
      '#markup' => '<pre>'.$form_state->getValue('page_select').'</pre>',
    ]; */
        
    $form['#prefix'] = '<div id="database-page-figures-container">';

    $form['top']['title'] = [
      '#type' => 'markup',
      '#markup' => '<h1 class="center">Supporting Data Figures</h1>',
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
        'wrapper' => 'database-page-figures-container', 
        /* 'event' => 'autocompleteclose change',  */
        'event' => 'autocompleteclose', // remove the change event to prevent users from hitting enter, which would load the dbfigures_form route
        'url' => \Drupal\Core\Url::fromRoute(
          'database_page_figures.dbfigures_form',
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
        'callback' => '::changeFigureCategory',
        'wrapper' => 'database-page-figures-container', 
        'event' => 'change', 
        'url' => \Drupal\Core\Url::fromRoute(
          'database_page_figures.dbfigures_form',
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

    /* $form['search-sort']['file-type-filter'] = [
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
        'wrapper' => 'database-page-figures-container', 
        'event' => 'change', 
      ],
    ]; */

    $form['search-sort']['sort'] = [
      '#type' => 'select',
      '#title' => '<i class="fas fa-sort"></i> Sort',
      '#options' => [
        0 => 'Created: Most Recent',
        1 => 'Created: Oldest',
      ],
      '#ajax' => [
        'callback' => '::changeSort',
        'wrapper' => 'database-page-figures-container', 
        'event' => 'change', 
        'url' => \Drupal\Core\Url::fromRoute(
          'database_page_figures.dbfigures_form',
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

    //if(isset($selected_category)) dump($selected_category);
        
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
    $query->innerJoin('node__norproduct_image', 'npi', 'npi.entity_id = nfd.nid AND npi.norproduct_image_target_id = fu.fid AND npi.deleted = 0');
    $query->innerJoin('node__field_image_caption', 'nfic', 'nfic.entity_id = nfd.nid AND nfic.deleted = 0 AND nfic.delta = npi.delta');

    $query->fields('fm', ['filename','uri','filemime','filesize','type']);
    $query->fields('nfd', ['nid']);
    $query->fields('cpfd', ['product_id']);
    $query->fields('pa', ['alias']);
    $query->fields('npi', ['norproduct_image_alt','norproduct_image_title','norproduct_image_width','norproduct_image_height']);
    $query->addField('npi','delta','image_delta');
    $query->fields('nfic', ['field_image_caption_value']);
    $query->addField('nfic','delta','image_caption_delta');

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
    $query->condition('fm.type', 'image' ,'='); // image (files only)

    $query->groupBy('cpfd.product_id');
    $query->groupBy('nfic.delta');

    
    if(isset($selected_category)) {
        $query->having("FIND_IN_SET(:selected_category, categories) > 0", [
        ':selected_category' => $selected_category // The category you want to filter by
      ]);
    }
    // testing only
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

    /* Raw SQL Query */
    /* $query = \Drupal::database()->query("
      SELECT fm.filename, fm.uri, fm.filemime, fm.filesize, fm.type, nfd.nid, cpfd.product_id, pa.alias, 
      npi.norproduct_image_alt, npi.norproduct_image_title, npi.norproduct_image_width, npi.norproduct_image_height, npi.delta AS 'image_delta',
      nfic.field_image_caption_value, nfic.delta AS 'image_caption_delta',
      GROUP_CONCAT(cpvfd.variation_id) AS 'variation_ids',
      (
        SELECT GROUP_CONCAT(DISTINCT cpv_categories.field_product_categories_value) AS categories 
        FROM commerce_product_variation__64c051ba81 AS cpv_categories
        WHERE cpv_categories.entity_id = cpvfd.variation_id AND cpv_categories.deleted = 0
      ) AS 'categories'
      FROM file_managed AS fm
      LEFT JOIN file_usage AS fu ON fm.fid = fu.fid
      INNER JOIN node_field_data AS nfd ON fu.id = nfd.nid AND fu.type = 'node' AND nfd.status = 1
      INNER JOIN node__field_commerce_product AS nfcp ON nfcp.entity_id = nfd.nid AND nfcp.deleted = 0
      INNER JOIN commerce_product_field_data AS cpfd ON cpfd.product_id = nfcp.field_commerce_product_target_id AND cpfd.type = 'norproduct' AND cpfd.status = 1
      INNER JOIN commerce_product_variation_field_data AS cpvfd ON cpvfd.product_id = cpfd.product_id AND cpvfd.status = 1
      INNER JOIN path_alias AS pa ON pa.path = CONCAT('/node/',nfd.nid)
      INNER JOIN node__norproduct_image AS npi ON npi.entity_id = nfd.nid AND npi.norproduct_image_target_id = fu.fid AND npi.deleted = 0
      INNER JOIN node__field_image_caption AS nfic ON nfic.entity_id = nfd.nid AND nfic.deleted = 0 AND nfic.delta = npi.delta
      WHERE fm.status = 1
      AND fm.type = 'image'
      GROUP BY cpfd.product_id, nfic.delta
      HAVING FIND_IN_SET('lab_components', categories) > 0
    "); */

    //$total_results = count($query->fetchAll());
    $total_results = count($query->execute()->fetchAll());
    
    \Drupal::logger('databasePageFigures')->notice(gettype($page_num));
    \Drupal::logger('databasePageFigures')->notice(gettype($results_per_page));

    // if page number is out of bounds of the current query
    if((($page_num * $results_per_page) - $results_per_page + 1) > $total_results){
      $page_num = 1;
      $form['page_select']['#value'] = 1;
    }

    $results_offset = ($page_num - 1) * $results_per_page;

    $query->range($results_offset, $results_per_page);

    $rows = $query->execute()->fetchAll();

    $form['pager'] = $this->buildCustomPager($page_num, $results_per_page, $total_results);

    $how_many_get_removed = 0;
    foreach ($rows as $row_num => $row){

      // Get file type
      $file_type = '';
      $file_type_text = '';
      $file_description = '';

      $variation_ids = explode(',', $row->variation_ids);

      // Get categories
      $categories = explode(',', $row->categories);

      $row_markup = '<div class="views-row row-'.$row_num.'">';
      
      if($row->filemime == 'image/svg+xml'){
        $src_url = $og_url = \Drupal::service('file_url_generator')->generateAbsoluteString($row->uri);
      } 
      else{
        $style = \Drupal::entityTypeManager()->getStorage('image_style')->load('medium');
        $src_url = $style->buildUrl($row->uri);
        $og_url = \Drupal::service('file_url_generator')->generateAbsoluteString($row->uri);
      }
      
      $row_markup .= '<div class="image-caption-wrapper">';
      $row_markup .= '<div class="figure-image"><a target="_blank" href="'.$og_url.'">';
      $row_markup .= '<img src="'.$src_url.'" title="'.strip_tags($row->norproduct_image_title).'" alt="'.strip_tags($row->norproduct_image_alt).'" width="220" height="220" filemime="'.$row->filemime.'">';
      $row_markup .= '<div>View Fullsize Image</div>';
      $row_markup .= '</a></div>';
      $row_markup .= '<div class="info">';
      $row_markup .= '<h2>'.$row->field_image_caption_value.'</h2>';
      $row_markup .= '<ul class="category-links">';
      $category_reference = [];
      foreach($categories as $category_num => $product_category){
        if(in_array($product_category, $category_reference)) continue;
        // Convert product_categories to URL
        // Get our product_categories value and convert to URL
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
      $row_markup .= '<h4>Products</h4>';
      $row_markup .= '<ul class="rel-prods">';
      foreach($variation_ids as $variation_num => $variation_id){
        $variation = \Drupal::entityTypeManager()->getStorage('commerce_product_variation')->load($variation_id);
        $row_markup .= '<li>';
        $row_markup .= '<a href="'.$row->alias.'?v='.$variation_id.'">'.$variation->getSKU().' - '.$variation->getTitle().'</a>';
        $row_markup .= '</li>';
      }
      $row_markup .= '</ul>';
      $row_markup .= '</div>';
      $row_markup .= '</div>';
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
  
  public function changeSearchProduct(array &$form, FormStateInterface $form_state) {
    $form['page_select']['#value'] = 1;
    return $form;
  }

  public function changeFigureCategory(array &$form, FormStateInterface $form_state) {
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


  protected function buildCustomPager($current_page, $items_per_page, $total_results){
    $total_pages = ceil($total_results / $items_per_page);
    $max_pagers = 5;

    \Drupal::logger('databasePageFigures')->notice('current_page: '.$current_page);

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
          'wrapper' => 'database-page-figures-container',
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
        'wrapper' => 'database-page-figures-container',
        'url' => \Drupal\Core\Url::fromRoute(
          'database_page_figures.dbfigures_form',
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
          'wrapper' => 'database-page-figures-container',
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
    \Drupal::logger('databasePageFigures')->notice(print_r($form_state->getTriggeringElement())); */
    $form_state->setRebuild(TRUE);
  }
  
}