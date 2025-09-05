<?php

namespace Drupal\nor_abandoned_cart_report\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Symfony\Component\HttpFoundation\Response;

class ReportMakerForm extends FormBase {

  public function getFormId() {
    return 'nor_abandoned_cart_report';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    // Add CSS library.
    $form['#attached']['library'][] = 'nor_abandoned_cart_report/nor_abandoned_cart_report';

    // Set default date to today
    $today = date('Y-m-d');
    
    // State filter.
    $form['state'] = [
      '#type' => 'select',
      '#prefix' => '<div class="filters_container">',
      '#title' => $this->t('State'),
      '#options' => [
        'all' => $this->t('All'),
        'abandoned' => $this->t('Total Abandoned Carts'),
        'cart' => $this->t('Cart'),
        'first_page' => $this->t('First Page of Checkout'),
        'review' => $this->t('Review'),
        'quoted' => $this->t('Quoted'),
        'complete' => $this->t('Complete'),
      ],
      '#default_value' => 'all',
    ];

    // Date fields for selecting the range.
    $form['from_date'] = [
      '#type' => 'date',
      '#prefix' => '<div class="date_container">',
      '#title' => $this->t('From Date'),
      '#default_value' => $today,
      '#required' => TRUE,
    ];

    $form['to_date'] = [
      '#suffix' => '</div>',
      '#type' => 'date',
      '#title' => $this->t('To Date'),
      '#default_value' => $today,
      '#required' => TRUE,
    ];

    // Submit button.
    $form['submit'] = [
      '#prefix' => '<div class="button_container">',
      '#type' => 'submit',
      '#value' => $this->t('Generate Report'),
    ];

    // Export button.
    $form['export'] = [
      '#suffix' => '</div></div>',
      '#type' => 'submit',
      '#value' => $this->t('Export'),
      '#submit' => ['::exportCsv'],
    ];

    // Check if results are available in form state.
    if ($storage = $form_state->getStorage()) {
      $results = $storage['results'];

      $state_filtered_results = array_filter($results, function ($row) use ($storage) {
        $selected_state = $storage['state'];
        $state = '';

        if ($row->placed > 0) {
          $state = 'complete';
        } else if ($row->forms_id > 0) {
          $state = 'quoted';
        } else if ($row->checkout_flow == 'shipping' && $row->checkout_step == 'order_information') {
          $state = 'first_page';
        } else if ($row->checkout_flow == 'shipping' && $row->checkout_step == 'review') {
          $state = 'review';
        } else if (empty($row->checkout_step)) {
          $state = 'cart';
        }

        if ($selected_state === 'all') 
        {
          return true;
        } 
        else if ($selected_state === 'abandoned') 
        {
          return (($state === 'cart' || $state ==='first_page' || $state ==='review') && $row->placed == 0 && (empty($row->forms_id) || $row->forms_id === ''));
        } 
        else 
        {
          return $state === $selected_state;
        }
      });

      
      // $carts_in_request_quote = count(array_filter($results, function($item) {
      //   return  $item->forms_id > 0;
      // }));
      // $test = array_filter($results, function($item) {
      //   return  $item->forms_id > 0;
      // });
      // \Drupal::logger('nor_abandoned_cart_report')->error(json_encode($test));
      // $completed_orders = count(array_filter($results, function($item) {
      //   return $item->status == 'completed';
      // }));
      // $completed_orders_anonymous = count(array_filter($results, function($item) {
      //   return ($item->status == 'completed' && $item->uid == 0);
      // }));
      // $completed_orders_registered = count(array_filter($results, function($item) {
      //   return ($item->status == 'completed' && $item->uid != 0);
      // }));
      // // Summary divs.
      // $total_carts = count($results) - $carts_in_request_quote - $completed_orders;
      $carts_in_request_quote = count(array_filter($state_filtered_results, function ($item) {
        return ($item->forms_id > 0 && $item->status != 'completed');
      }));
      $completed_orders = count(array_filter($state_filtered_results, function ($item) {
        return $item->placed > 0;
      }));
      $completed_orders_anonymous = count(array_filter($state_filtered_results, function ($item) {
        return ($item->placed > 0 && $item->uid == 0);
      }));
      $completed_orders_registered = count(array_filter($state_filtered_results, function ($item) {
        return ($item->placed > 0 && $item->uid != 0);
      }));

      // Summary divs.
      $total_carts = (count($state_filtered_results)!=0)?(count($state_filtered_results) - $carts_in_request_quote - $completed_orders):0;

      $form['summary'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['summary-container']],
      ];

      $form['summary']['total_carts'] = [
        '#type' => 'markup',
        '#markup' => $this->t('<div>Total Abandoned Carts: @count</div>', ['@count' => $total_carts]),
      ];
      $form['summary']['carts_in_request_quote'] = [
        '#type' => 'markup',
        '#markup' => $this->t('<div>Carts in Request Quote: @count</div>', ['@count' => $carts_in_request_quote]),
      ];
      $form['summary']['completed_orders'] = [
        '#type' => 'markup',
        '#markup' => $this->t('<div><div>Total Completed Orders: @count</div><div>Registered User Orders: @count2</div><div>Anonymous Orders: @count3</div></div>', ['@count' => $completed_orders,'@count2' => $completed_orders_registered,'@count3' => $completed_orders_anonymous]),
      ];

      // Table of results.
      $form['results'] = [
        '#type' => 'table',
        '#header' => [
          $this->t('Order ID'),
          $this->t('Order Number'),
          $this->t('User ID'),
          $this->t('State'),
          $this->t('Email'),
          $this->t('Placed'),
          $this->t('Changed'),
          $this->t('IP Address'),
          $this->t('Checkout Flow'),
          $this->t('Checkout Step'),
          $this->t('Changed Date Time'),
          $this->t('Forms ID'),
          // $this->t('Forms Email'),
        ],
        '#rows' => [],
      ];

      // foreach ($results as $row) {
        foreach ($state_filtered_results as $row) {
        $state = '';  // Initialize state to empty string

        // Check conditions in the specified order
        if ($row->placed > 0) {
          $state = 'Complete';
        } else if ($row->forms_id > 0) {
          $state = 'Quoted';
        } else if ($row->checkout_flow == 'shipping' && $row->checkout_step == 'order_information') {
          $state = 'First Page of Checkout';
        } else if ($row->checkout_flow == 'shipping' && $row->checkout_step == 'review') {
          $state = 'Review';
        } else if (empty($row->checkout_step)) {
          $state = 'Cart';
        }

        $form['results']['#rows'][] = [
          'order_id' => $row->order_id,
          'order_number' => $row->order_number,
          'uid' => $row->uid,
          'status' => $state,
          'mail' => $row->mail,
          'placed' => $row->placed,
          'changed' => $row->changed,
          'ip_address' => $row->ip_address,
          'checkout_flow' => $row->checkout_flow,
          'checkout_step' => $row->checkout_step,
          'changed_datetime' => $row->changed_datetime,
          'forms_id' => $row->forms_id,
          // 'forms_email' => $row->forms_email,
        ];
      }
    }

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $from_date = strtotime($form_state->getValue('from_date') . ' 00:00:00');
    $to_date = strtotime($form_state->getValue('to_date') . ' 23:59:59');
    $selected_state = $form_state->getValue('state');
    /**
     *  $query = "SELECT co.order_id, co.order_number, co.uid, co.state AS status, co.mail, co.placed, co.changed, co.ip_address, co.checkout_flow, co.checkout_step, FROM_UNIXTIME(co.changed) AS changed_datetime, min_forms.id AS forms_id, min_forms.email AS forms_email
              FROM commerce_order AS co
              LEFT JOIN (
                SELECT MIN(forms_to_zoho.id) AS id, forms_to_zoho.user_id, forms_to_zoho.form_name, forms_to_zoho.email, forms_to_zoho.products, MIN(forms_to_zoho.timestamp) AS min_timestamp
                FROM forms_to_zoho
                WHERE forms_to_zoho.form_name IN ('Quote Request Form','Request Quote Form', 'Quote Request Form - Cart')
                  AND forms_to_zoho.timestamp < :current_time
                  AND forms_to_zoho.timestamp >= :from_date
                GROUP BY forms_to_zoho.user_id, forms_to_zoho.form_name, forms_to_zoho.email, forms_to_zoho.products
              ) AS min_forms ON min_forms.products = CONCAT('order_id|', co.order_id) 
              AND co.changed < min_forms.min_timestamp
              WHERE co.changed >= :from_date
                AND co.changed <= :to_date
                AND co.mail NOT LIKE '%@norgenbiotek%'
                AND co.ip_address <> '68.71.17.146'
              ORDER BY co.placed ASC , co.checkout_flow, co.checkout_step, co.changed ASC";
     */

    $query = "SELECT co.order_id, co.order_number, co.uid, co.state AS status, co.mail, co.placed, co.changed, co.ip_address, co.checkout_flow, co.checkout_step, FROM_UNIXTIME(co.changed) AS changed_datetime, min_forms.id AS forms_id, min_forms.email AS forms_email
              FROM commerce_order AS co
              LEFT JOIN (
                SELECT MAX(forms_to_zoho.id) AS id, forms_to_zoho.user_id, forms_to_zoho.form_name, forms_to_zoho.email, forms_to_zoho.products, MAX(forms_to_zoho.timestamp) AS min_timestamp
                FROM forms_to_zoho
                WHERE forms_to_zoho.form_name IN ('Quote Request Form','Request Quote Form', 'Quote Request Form - Cart')
                  AND forms_to_zoho.timestamp < :current_time
                  AND forms_to_zoho.timestamp >= :from_date
                GROUP BY forms_to_zoho.user_id, forms_to_zoho.form_name, forms_to_zoho.email, forms_to_zoho.products
              ) AS min_forms ON min_forms.email = co.mail 
              WHERE co.changed >= :from_date
                AND co.changed <= :to_date
                AND (co.mail NOT LIKE '%@norgenbiotek%' OR co.mail is NULL)
                AND co.ip_address <> '68.71.17.146'";

// Add state filter to query
if ($selected_state !== 'all') {
  switch ($selected_state) {
    case 'cart':
      $query .= " AND co.checkout_step IS NULL AND min_forms.id IS NULL 
      AND co.cart <> 0";
      break;
    case 'first_page':
      $query .= " AND co.checkout_flow = 'shipping' AND co.checkout_step = 'order_information' AND min_forms.id IS NULL 
      AND co.cart <> 0";
      break;
    case 'review':
      $query .= " AND co.checkout_flow = 'shipping' AND co.checkout_step = 'review' AND min_forms.id IS NULL 
      AND co.cart <> 0";
      break;
    case 'quoted':
      $query .= " AND min_forms.id IS NOT NULL";
      break;
    case 'complete':
      $query .= " AND co.placed > 0";
      break;
    case 'abandoned':
      $query .= " AND (co.state = 'draft' AND min_forms.id IS NULL)";
      break;
  }
}

$query .= " ORDER BY co.placed ASC, co.checkout_flow, co.checkout_step, co.changed ASC";
// \Drupal::logger('nor_abandoned_cart_report')->error(($query));
    $connection = Database::getConnection();
    $results = $connection->query($query, [
      ':current_time' => time(),
      ':from_date' => $from_date,
      ':to_date' => $to_date,
    ])->fetchAll();

    $form_state->setStorage(['results' => $results, 'from_date' => $from_date, 'to_date' => $to_date, 'state' => $selected_state]);
    $form_state->setRebuild(TRUE);
  }

  public function exportCsv(array &$form, FormStateInterface $form_state) {
    if ($storage = $form_state->getStorage()) {
      $results = $storage['results'];
      $from_date = date('Y-m-d', $storage['from_date']);
      $to_date = date('Y-m-d', $storage['to_date']);
      $state = $storage['state'];

      // CSV header
      $csv_data = [];
      $csv_data[] = [
        'Order ID', 'Order Number', 'User ID', 'Status', 'Email', 'Placed', 'Changed', 'IP Address', 'Checkout Flow', 'Checkout Step', 'Changed Date Time', 'Forms ID', 'Forms Email'
      ];

      // CSV data
      foreach ($results as $row) 
      {
        $state = '';  // Initialize state to empty string

        // Check conditions in the specified order
        if ($row->placed > 0) {
          $state = 'Complete';
        } else if ($row->forms_id > 0) {
          $state = 'Quoted';
          \Drupal::logger('nor_abandoned_cart_report')->debug('Order ID: ' . $row->order_id . ' - Quoted');
        } else if ($row->checkout_flow == 'shipping' && $row->checkout_step == 'order_information') {
          $state = 'First Page of Checkout';
        } else if ($row->checkout_flow == 'shipping' && $row->checkout_step == 'review') {
          $state = 'Review';
        } else if (empty($row->checkout_step)) {
          $state = 'Cart';
        }

        $csv_data[] = 
        [
          $row->order_id, $row->order_number, $row->uid, $state, $row->mail, $row->placed, $row->changed, $row->ip_address, $row->checkout_flow, $row->checkout_step, $row->changed_datetime, $row->forms_id, $row->forms_email
        ];
      }

      
      // $test = array_filter($results, function($item) {
      //   return  $item->forms_id > 0;
      // });
      // \Drupal::logger('nor_abandoned_cart_report')->error(json_encode($test));
      // $completed_orders = count(array_filter($results, function($item) {
      //   return $item->status == 'completed';
      // }));
      // $completed_orders_anonymous = count(array_filter($results, function($item) {
      //   return ($item->status == 'completed' && $item->uid ==0);
      // }));
      // $completed_orders_registered = count(array_filter($results, function($item) {
      //   return($item->status == 'completed' && $item->uid !=0);
      // }));
      // // Summary divs.
      // $total_carts = count($results)- $carts_in_request_quote-$completed_orders;

      // Summary divs.
      $carts_in_request_quote = count(array_filter($results, function ($item) {
        return  ($item->forms_id > 0 && $item->status != 'completed');
      }));
      $completed_orders = count(array_filter($results, function ($item) {
        return $item->status == 'completed';
      }));
      $completed_orders_anonymous = count(array_filter($results, function ($item) {
        return ($item->status == 'completed' && $item->uid == 0);
      }));
      $completed_orders_registered = count(array_filter($results, function ($item) {
        return ($item->status == 'completed' && $item->uid != 0);
      }));

      $total_carts = (!is_null($state_filtered_results) && count($state_filtered_results)!=0)?(count($state_filtered_results) - $carts_in_request_quote - $completed_orders):0;
      $completed_orders;

      $summary_data = [
        ['Total Abandoned Carts', $total_carts],
        ['Carts of Registered Users', $completed_orders_registered],
        ['Guest Carts', $completed_orders_anonymous],
        ['Carts in Request Quote', $carts_in_request_quote],
        ['Total Completed Orders', $completed_orders],
      ];

      // Create CSV file content
      $filename = 'ac_report_' . $from_date . '_to_' . $to_date . '.csv';
      $csv_content = $this->arrayToCsv($csv_data);
      $csv_content .= "\n\nSummary\n";
      $csv_content .= $this->arrayToCsv($summary_data);

      // Create response
      $response = new Response($csv_content);
      $response->headers->set('Content-Type', 'text/csv');
      $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
      $response->send();
      exit();
    }
  }

  private function arrayToCsv(array $data) {
    $csv_output = '';
    foreach ($data as $row) {
      $csv_output .= implode(',', $row) . "\n";
    }
    return $csv_output;
  }
}