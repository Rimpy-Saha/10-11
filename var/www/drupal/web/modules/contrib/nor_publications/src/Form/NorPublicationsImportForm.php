<?php

namespace Drupal\nor_publications\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\nor_publications\Entity\NorPublication;

/**
 * Class NorPublicationsImportForm.
 *
 * Provides a form with a button to import data from Bioz_data.
 */
class NorPublicationsImportForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'nor_publications_import_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Add a submit button to trigger the import.
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Import Publications from Bioz Data'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $from = 14000;
    $limit = 1000;

    // Call the function to import data from Bioz_Data.
    $this->importDataFromBiozData($from, $limit);
    // Display a message after the process is complete.
    \Drupal::messenger()->addStatus($this->t('Publications have been successfully imported from Bioz_Data. From: '.$from.' limit: '.$limit));
  }

  /**
   * Function to import data from Bioz_Data into nor_publications entities.
   */
//   public function importDataFromBiozData($from, $limit) {
//     // Connect to the database.
//     $database = \Drupal::database();
   
//     // Query the Bioz_Data table to fetch all necessary fields.
//     $query = $database->select('Bioz_Data', 'b')
//       ->fields('b', [
//         'alert_month',
//         'alert_year',
//         'application',
//         'authors',
//         'catalogue_number',
//         'citation',
//         'citation_count',
//         'url',
//         'updated',
//         'title',
//         'source',
//         'snippet',
//         'sample_type',
//         'reference_number',
//         'pubmed_doi',
//         'publication_year',
//         'publication_month',
//         'publication',
//         'product_keywords_string',
//         'product',
//         'pmcid',
//         'month',
//         'impact_factor',
//         'day',
//         'date',
//         'contacted_customer',
//         'country',
//         // Add other columns if necessary.
//       ])
//       ->range($from,$limit)
//       ;
//     $result = $query->execute();
//         $cnt = 0;
//     // Iterate through each row in the result set.
//     foreach ($result as $row) {
//         ++$cnt;
//       // Create an array of field values mapping the Bioz_Data columns
//       // to the nor_publications entity fields.
//       $values = [
//         'type' => 'norgen_publications',  // Setting the bundle type as 'norgen_publications'
//         'field_nor_alert_month' => $row->alert_month,
//         'field_nor_alert_year' => $row->alert_year,
//         'field_nor_application' => $row->application,
//         'field_nor_authors' => $row->authors,
//         'field_nor_catalogue_number' => $row->catalogue_number,
//         'field_nor_citation' => $row->citation,
//         'field_nor_citation_count' => $row->citation_count,
//         'field_nor_url' => $row->url,
//         'field_nor_updated' => $row->updated,
//         'field_nor_title' => $row->title,
//         'field_nor_source' => $row->source,
//         'field_nor_snippet' => $row->snippet,
//         'field_nor_sample_type' => $row->sample_type,
//         'field_nor_reference_number' => $row->reference_number,
//         'field_nor_pubmed_doi' => $row->pubmed_doi,
//         'field_nor_publication_year' => $row->publication_year,
//         'field_nor_publication_month' => $row->publication_month,
//         'field_nor_publication' => $row->publication,
//         'field_nor_product_keywords_string' => $row->product_keywords_string,
//         'field_nor_product' => $row->product,
//         'field_nor_pmcid' => $row->pmcid,
//         'field_nor_month' => $row->month,
//         'field_nor_impact_factor' => $row->impact_factor,
//         'field_nor_day' => $row->day,
//         'field_nor_date' => $row->date,
//         'field_nor_contacted_customer' => $row->contacted_customer,
//         'field_nor_country' => $row->country,
//         // Add other fields if necessary.
//       ];

//       try {
//         // Create and save the nor_publications entity.
//         // $entity = NorPublication::create($values);
//         // $entity->setName($row->title);
//         // $entity->save();
//       } catch (\Exception $e) {
//         // Handle any errors and display messages accordingly.
//         // \Drupal::messenger()->addError(t('Failed to import publication with title: @title. Error: @error', [
//         //   '@title' => $row->title,
//         //   '@error' => $e->getMessage(),
//         // ]));
//       }
//     }
//     \Drupal::messenger()->addStatus($this->t('are you sure you wnat to procedd with importing? Count: '.$cnt));
//   }



/**
 * Function to modify data from Bioz_Data into nor_publications entities.
 */
    // public function importDataFromBiozData($from, $limit) {
    //     // Connect to the database.
    //     // $database = \Drupal::database();
    
    //     // // Query the Bioz_Data table to fetch all necessary fields.
    //     // $query = $database->select('Bioz_Data', 'b')
    //     //   ->fields('b', [
    //     //     'alert_month',
    //     //     'alert_year',
    //     //     'application',
    //     //     'authors',
    //     //     'catalogue_number',
    //     //     'citation',
    //     //     'citation_count',
    //     //     'url',
    //     //     'updated',
    //     //     'title',
    //     //     'source',
    //     //     'snippet',
    //     //     'sample_type',
    //     //     'reference_number',
    //     //     'pubmed_doi',
    //     //     'publication_year',
    //     //     'publication_month',
    //     //     'publication',
    //     //     'product_keywords_string',
    //     //     'product',
    //     //     'pmcid',
    //     //     'month',
    //     //     'impact_factor',
    //     //     'day',
    //     //     'date',
    //     //     'contacted_customer',
    //     //     'country',
    //     //     // Add other columns if necessary.
    //     //   ])
    //     // //   ->condition('reference_number', 13477);
    //     //   ->range($from, $limit);
        
    //     // $result = $query->execute();
    //     // $cnt = 0;
    
    //     // // Iterate through each row in the result set.
    //     // foreach ($result as $row) {
    //     //   ++$cnt;
    
    //     //   // Check if an entity with the matching reference number exists.
    //     //   $entity_query = \Drupal::entityTypeManager()->getStorage('nor_publications')->getQuery()
    //     //     ->condition('field_nor_reference_number', $row->reference_number)
    //     //     ->condition('type', 'norgen_publications')
    //     //     ->accessCheck(FALSE)
    //     //     ->execute();
    
    //     //   if (!empty($entity_query)) {
    //     //     // Load the entity.
    //     //     $entity_id = reset($entity_query);
    //     //     $entity = \Drupal::entityTypeManager()->getStorage('nor_publications')->load($entity_id);
    
    //     //     // Update the snippet field with the value from Bioz_Data.
    //     //     $entity->set('field_nor_snippet_formatted', $row->snippet);
    //     //     \Drupal::messenger()->addStatus($this->t('Publication with reference number @ref updated with snippet.', ['@ref' => $entity_id]));
    //     //     // Save the entity after updating the field.
    //     //     try {
    //     //       $entity->save();
    //     //       \Drupal::messenger()->addStatus($this->t('Publication with reference number @ref updated with snippet.', ['@ref' => $row->reference_number]));
    //     //     } catch (\Exception $e) {
    //     //       \Drupal::messenger()->addError($this->t('Failed to update publication with reference number: @ref. Error: @error', [
    //     //         '@ref' => $row->reference_number,
    //     //         '@error' => $e->getMessage(),
    //     //       ]));
    //     //     }
    //     //   } else {
    //     //     // Handle case where no matching reference number is found.
    //     //     \Drupal::messenger()->addWarning($this->t('No publication found with reference number @ref.', ['@ref' => $row->reference_number]));
    //     //   }
    //     // }
    
    //     // \Drupal::messenger()->addStatus($this->t('Import process completed. Processed count: @cnt', ['@cnt' => $cnt]));
    //     \Drupal::messenger()->addStatus($this->t('Are you sure you want to do this, Pease contact the IT Team to proceed with the Import.'));
    // }


    public function importDataFromBiozData($from, $limit) {
        // Connect to the database.
        $database = \Drupal::database();
      
        // Query the Bioz_Data table to fetch all necessary fields.
        $query = $database->select('Bioz_Data', 'b')
            ->fields('b', [
                'alert_month',
                'alert_year',
                'application',
                'authors',
                'catalogue_number',
                'citation',
                'citation_count',
                'url',
                'updated',
                'title',
                'source',
                'snippet',
                'sample_type',
                'reference_number',
                'pubmed_doi',
                'publication_year',
                'publication_month',
                'publication',
                'product_keywords_string',
                'product',
                'pmcid',
                'month',
                'impact_factor',
                'day',
                'date',
                'contacted_customer',
                'country',
            ])
            ->range($from, $limit);
        
        $result = $query->execute();
        $cnt = 0;
      
        // Iterate through each row in the result set.
        foreach ($result as $row) {
            ++$cnt;
    
            // Split the catalogue_number by comma to handle multiple SKUs.
            $catalogueNumbers = array_map('trim', explode(',', $row->catalogue_number));
            $productReferences = [];
    
            foreach ($catalogueNumbers as $catalogueNumber) {
                // Query to get the product variation by catalogue number.
                $variation_query = \Drupal::entityTypeManager()->getStorage('commerce_product_variation')->getQuery()
                    ->condition('sku', $catalogueNumber)
                    ->accessCheck(FALSE)
                    ->execute();
    
                if (!empty($variation_query)) {
                    // Load the product variation.
                    $variation_id = reset($variation_query);
                    $variation = \Drupal::entityTypeManager()->getStorage('commerce_product_variation')->load($variation_id);
    
                    // Get the parent product.
                    $product = $variation->getProduct();
                    if ($product) {
                        $productReferenceId = $product->id();
    
                        // Avoid duplicates in the references.
                        if (!in_array($productReferenceId, $productReferences)) {
                            $productReferences[] = $productReferenceId;
                        }
                    }
                }
            }
    
            // Check if an entity with the matching reference number exists.
            $entity_query = \Drupal::entityTypeManager()->getStorage('nor_publications')->getQuery()
                ->condition('field_nor_reference_number', $row->reference_number)
                ->condition('type', 'norgen_publications')
                ->accessCheck(FALSE)
                ->execute();
    
            if (!empty($entity_query)) {
                // Load the entity.
                $entity_id = reset($entity_query);
                $entity = \Drupal::entityTypeManager()->getStorage('nor_publications')->load($entity_id);
    
                // Update the snippet field with the value from Bioz_Data.
                // $entity->set('field_nor_snippet_formatted', $row->snippet);
    
                // Set the product references in the field_nor_product_reference.
                $entity->set('field_nor_product_reference', $productReferences);
    
                \Drupal::messenger()->addStatus($this->t('Publication with reference number @ref updated with snippet and product references.', ['@ref' => $entity_id]));
                // Save the entity after updating the field.
                try {
                    $entity->save();
                    \Drupal::messenger()->addStatus($this->t('Publication with reference number @ref updated with snippet and product references.', ['@ref' => $row->reference_number]));
                } catch (\Exception $e) {
                    \Drupal::messenger()->addError($this->t('Failed to update publication with reference number: @ref. Error: @error', [
                        '@ref' => $row->reference_number,
                        '@error' => $e->getMessage(),
                    ]));
                }
            } else {
                // Handle case where no matching reference number is found.
                \Drupal::messenger()->addWarning($this->t('No publication found with reference number @ref.', ['@ref' => $row->reference_number]));
            }
        }
    
        \Drupal::messenger()->addStatus($this->t('Import process completed. Processed count: @cnt', ['@cnt' => $cnt]));
    }
    
  
}


