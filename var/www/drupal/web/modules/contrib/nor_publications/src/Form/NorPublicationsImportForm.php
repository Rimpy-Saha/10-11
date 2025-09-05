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
      '#value' => $this->t('Import Publications from Publications Data'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $from = 10056;
    $limit = 500;
    $id = 5548;

    // Call the function to import data from Bioz_Data.
    // $this->importDataFromPublicationsAllData($from, $limit);
    // $this->syncJournalFromCitation($id);
    // Display a message after the process is complete.
    // \Drupal::messenger()->addStatus($this->t('Publications have been successfully imported from Publications_Data. From: '.$from.' limit: '.$limit));
    \Drupal::messenger()->addStatus($this->t('Publications have been successfully Updated '));
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


    // 
    //2025-07-09
    public function importDataFromPublicationsAllData($from, $limit) {
      // Connect to the database.
      $database = \Drupal::database();
    
      // Query the publications_all_data table to fetch all necessary fields.
      $query = $database->select('publications_all_data', 'p')
        ->fields('p', [
          'id',
          'Reference_Number',
          'year',
          'Month',
          'product_name',
          'catalog_number',
          'Sample_Type',
          'Application',
          'title',
          'journal',
          'url',
          'authors',
          'Main_Contact',
          'source',
          'normalized_title',
          'title_internal',
          'title_external',
          'journal_internal',
          'journal_external',
          'authors_internal',
          'authors_external',
          'year_internal',
          'snippet',
          'date',
          'pmcid',
          'url_internal',
          'url_external',
          'product_keywords_string',
          // Add other columns if necessary.
        ])
        ->condition('id', $from, '>')
        ->range(0, $limit);

      
      $result = $query->execute();
    
      $cnt = 0;
      // Iterate through each row in the result set.
      foreach ($result as $row) {
        ++$cnt;
    
        // Set priority fields, split by "||| " and take the first part.
        $title = explode("||| ", $row->title)[0] ?: explode("||| ", $row->title_internal)[0] ?: explode("||| ", $row->title_external)[0];
        $authors = explode("||| ", $row->authors)[0] ?: explode("||| ", $row->authors_internal)[0] ?: explode("||| ", $row->authors_external)[0];
        $journal = explode("||| ", $row->journal)[0] ?: explode("||| ", $row->journal_internal)[0] ?: explode("||| ", $row->journal_external)[0];
        $year = explode("||| ", $row->year)[0] ?: explode("||| ", $row->year_internal)[0];
        $url = explode("||| ", $row->url)[0] ?: explode("||| ", $row->url_internal)[0] ?: explode("||| ", $row->url_external)[0];
        $snippet = explode("||| ", $row->snippet)[0]; // Snippet field
        $product_name = explode("||| ", $row->product_name)[0];
        $catalog_number = explode("||| ", $row->catalog_number)[0];
        $sample_type = explode("||| ", $row->Sample_Type)[0];
        $application = explode("||| ", $row->Application)[0];
        $main_contact = explode("||| ", $row->Main_Contact)[0];
        $pmcid = explode("||| ", $row->pmcid)[0];
        $url_external = explode("||| ", $row->url_external)[0];
        
        // Concatenate product name and catalog number.
        $product_keywords_string = $product_name . ' | ' . $catalog_number;
    
        // Determine field_nor_status based on source content.
        $field_nor_status = (stripos($row->source, 'master') !== false || stripos($row->source, 'internal') !== false);
    
        // Check if an entity with the same reference number already exists in 'norgen_publications' type.
        $existing_entity = \Drupal::entityTypeManager()
          ->getStorage('nor_publications')
          ->loadByProperties([
            'field_nor_reference_number' => $row->id,
            'type' => 'norgen_publications'
          ]);
    
        // If an existing entity is found, skip to the next row.
        if (!empty($existing_entity)) {
          continue;
        }

        // Truncate the title if it exceeds 255 characters to fit the name field.
        $truncated_title = mb_substr($title, 0, 255);
        
        $catalogueNumbers = array_map('trim', explode(',', $row->catalog_number));
        $productReferences = [];

        foreach ($catalogueNumbers as $catalogueNumber) {
          $variation_query = \Drupal::entityTypeManager()
            ->getStorage('commerce_product_variation')
            ->getQuery()
            ->condition('sku', $catalogueNumber)
            ->accessCheck(FALSE)
            ->execute();

          if (!empty($variation_query)) {
            $variation_id = reset($variation_query);
            $variation = \Drupal::entityTypeManager()->getStorage('commerce_product_variation')->load($variation_id);

            if ($variation && $product = $variation->getProduct()) {
              $productReferenceId = $product->id();
              if (!in_array($productReferenceId, $productReferences)) {
                $productReferences[] = $productReferenceId;
              }
            }
          }
        }

    
        // Create an array of field values mapping the publications_all_data columns
        // to the nor_publications entity fields.
        $values = [
          'type' => 'norgen_publications',  // Setting the bundle type as 'norgen_publications'
          'field_nor_reference_number' => $row->id,
          'field_nor_alert_year' => $year,
          'field_nor_month' => $row->Month,
          'field_nor_product_keywords_string' => $product_keywords_string,
          'field_nor_product_reference' => $productReferences,
          'field_nor_sample_type' => $sample_type,
          'field_nor_application' => $application,
          'field_nor_title' => $title,
          'field_nor_citation' => $journal,
          'field_nor_journal' => $journal,
          'field_nor_url' => $url,
          'field_nor_authors' => $authors,
          'field_nor_contacted_customer' => $main_contact,
          'field_nor_source' => $row->source,
          'field_nor_snippet_formatted' => $snippet,
          'field_nor_date' => $row->date,
          'field_nor_pmcid' => $pmcid,
          'field_nor_pubmed_doi' => $url_external,
          'field_nor_status' => $field_nor_status,  // Set status based on source content
        ];
    
        try {
          // Create the nor_publications entity, set its name to the title, and save.
          $entity = NorPublication::create($values);
          $entity->setName($truncated_title);  // Set the name to title.
          $entity->save();
        } catch (\Exception $e) {
          \Drupal::messenger()->addError(t('Failed to import publication with title: @title. Error: @error', [
            '@title' => $title,
            '@error' => $e->getMessage(),
          ]));
        }
      }
      \Drupal::messenger()->addStatus($this->t('Import complete. Total records processed: ' . $cnt));
    }
  //2025-07-09
  public function syncJournalFromCitation($id) 
  {
    $storage = \Drupal::entityTypeManager()->getStorage('nor_publications');

    // Load all entities with ID >= $id.
    $query = $storage->getQuery()
      ->condition('nor_publications_id', $id, '>=')
      ->condition('type', 'norgen_publications')
      ->accessCheck(FALSE);

    $entity_ids = $query->execute();

    if (empty($entity_ids)) {
      \Drupal::messenger()->addWarning('No nor_publications found for ID >= ' . $id);
      return;
    }

    $entities = $storage->loadMultiple($entity_ids);
    $updated = 0;

    foreach ($entities as $entity) {
      $citation = $entity->get('field_nor_citation')->value;
      $journal = $entity->get('field_nor_journal')->value;

      if (!empty($citation) && empty($journal)) {
        $entity->set('field_nor_journal', $citation);
        $entity->save();
        $updated++;
      }
    }

    \Drupal::messenger()->addStatus("Updated $updated nor_publications entities starting from ID >= $id.");
  }
    
  
}


