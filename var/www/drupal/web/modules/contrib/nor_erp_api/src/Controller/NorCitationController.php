<?php
namespace Drupal\nor_erp_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\commerce_order\Adjustment;
use Drupal\commerce_order\Entity\Order;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides route responses for the Example module.
 */
class NorCitationController extends ControllerBase {


  // public function citePage() {
  //   $url = \Drupal\Core\Url::fromRoute('<front>');
  //   // Get the URL string.
  //   $redirect_url = $url->toString();
  //   // Redirect to the specified URL.
  //   $response = new RedirectResponse($redirect_url);
  //   $response->send();
  //   return;
  // }


  public function citePage() {
    // Path to your CSV file
    $csv_file = '/var/www/drupal/web/modules/contrib/nor_erp_api/publications_update.csv';

    // Open the CSV file.
    if (($handle = fopen($csv_file, "r")) !== FALSE) {
        // Skip the header row (first line).
        fgetcsv($handle);

        $csv_row_num = 10057;   // Row number in the CSV file (starting at 1 after header)

        // Loop through each row of the CSV file.
        while (($row = fgetcsv($handle)) !== FALSE) {
            $csv_row_num++; // Increment the CSV row number for each row (since we already skipped the header)

            // Prepare the data.
            $reference_number = mb_convert_encoding($row[0], 'UTF-8', 'auto');
            $year = mb_convert_encoding($row[1], 'UTF-8', 'auto');
            $month = mb_convert_encoding($row[2], 'UTF-8', 'auto');
            $product_name = mb_convert_encoding($row[3], 'UTF-8', 'auto');
            $catalog_number = mb_convert_encoding($row[4], 'UTF-8', 'auto');
            $sample_type = mb_convert_encoding($row[5], 'UTF-8', 'auto');
            $application = mb_convert_encoding($row[6], 'UTF-8', 'auto');
            $title = mb_convert_encoding($row[7], 'UTF-8', 'auto');
            $journal = mb_convert_encoding($row[8], 'UTF-8', 'auto');
            $url = mb_convert_encoding($row[9], 'UTF-8', 'auto');
            $authors = mb_convert_encoding($row[10], 'UTF-8', 'auto');
            $main_contact = mb_convert_encoding($row[11], 'UTF-8', 'auto');
            $institutes = mb_convert_encoding($row[12], 'UTF-8', 'auto');
            $territory = mb_convert_encoding($row[13], 'UTF-8', 'auto');
            $tags_notes = mb_convert_encoding($row[14], 'UTF-8', 'auto');
            $source = mb_convert_encoding($row[15], 'UTF-8', 'auto');
            $normalized_title = mb_convert_encoding($row[16], 'UTF-8', 'auto');
            $title_internal = mb_convert_encoding($row[17], 'UTF-8', 'auto');
            $url_internal = mb_convert_encoding($row[18], 'UTF-8', 'auto');
            $authors_internal = mb_convert_encoding($row[19], 'UTF-8', 'auto');
            $journal_internal = mb_convert_encoding($row[20], 'UTF-8', 'auto');
            $year_internal = mb_convert_encoding($row[21], 'UTF-8', 'auto');
            $snippet = mb_convert_encoding($row[22], 'UTF-8', 'auto');
            $date = date('Y-m-d', strtotime($row[23])); // Adjust based on your CSV structure
            $title_external = mb_convert_encoding($row[24], 'UTF-8', 'auto');
            $journal_external = mb_convert_encoding($row[25], 'UTF-8', 'auto');
            $pmid = mb_convert_encoding($row[26], 'UTF-8', 'auto');
            $pmcid = mb_convert_encoding($row[27], 'UTF-8', 'auto');
            $url_external = mb_convert_encoding($row[28], 'UTF-8', 'auto');
            $authors_external = mb_convert_encoding($row[29], 'UTF-8', 'auto');
            $product_keywords_string = mb_convert_encoding($row[30], 'UTF-8', 'auto');
            $address = mb_convert_encoding($row[31], 'UTF-8', 'auto');
            $institution = mb_convert_encoding($row[32], 'UTF-8', 'auto');
            $email = mb_convert_encoding($row[33], 'UTF-8', 'auto');

            // Insert a new record into publications_all_data
            \Drupal::database()->insert('publications_all_data')
                ->fields([
                    'Reference_Number' => $reference_number,
                    'year' => $year,
                    'Month' => $month,
                    'product_name' => $product_name,
                    'catalog_number' => $catalog_number,
                    'Sample_Type' => $sample_type,
                    'Application' => $application,
                    'title' => $title,
                    'journal' => $journal,
                    'url' => $url,
                    'authors' => $authors,
                    'Main_Contact' => $main_contact,
                    'Institutes' => $institutes,
                    'Territory' => $territory,
                    'Tags_Notes' => $tags_notes,
                    'source' => $source,
                    'normalized_title' => $normalized_title,
                    'title_internal' => $title_internal,
                    'url_internal' => $url_internal,
                    'authors_internal' => $authors_internal,
                    'journal_internal' => $journal_internal,
                    'year_internal' => $year_internal,
                    'snippet' => $snippet,
                    'date' => $date,
                    'title_external' => $title_external,
                    'journal_external' => $journal_external,
                    'pmid' => $pmid,
                    'pmcid' => $pmcid,
                    'url_external' => $url_external,
                    'authors_external' => $authors_external,
                    'product_keywords_string' => $product_keywords_string,
                    'address' => $address,
                    'institution' => $institution,
                    'email' => $email,
                    'reference' => 'CSV Row: ' . $csv_row_num, // Store the CSV row number in the reference field
                ])
                ->execute();
        }

        // Close the file.
        fclose($handle);

        // Return a success message with the count of imported rows.
        \Drupal::messenger()->addMessage('CSV import successful.');
    } else {
        // Return an error if the file cannot be opened.
        \Drupal::messenger()->addError('Cannot open CSV file.');
    }

    // Return a response indicating the import was completed.
    return new Response('CSV import completed successfully.');
  }
}