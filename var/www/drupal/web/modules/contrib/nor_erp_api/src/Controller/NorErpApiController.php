<?php
namespace Drupal\nor_erp_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Drupal\commerce_order\Entity\Order;
use Symfony\Component\HttpFoundation\Request;


/**
 * Provides route responses for the Example module.
 */
class NorErpApiController extends ControllerBase {

  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function apiPage() {

   $allowedIps = ['68.71.17.146']; //['192.168.0.1'];
   $userIp = $_SERVER['REMOTE_ADDR'];
   //$expectedpassword= '36357d84aa49e0c3c7002ccd8b4f43a29bb1759eb3b004e71a6dec8f75c49486';
   $expectedpassword = '136dba11f13da916fb64367017c9d5df6e2766fd57ff6563d0362d752c8910ea';
   $receivedToken = $_SERVER["HTTP_AUTHORIZATION"] ?? '';

    $database = \Drupal::database();
    // if (!in_array($userIp, $allowedIps))  {
        $url = \Drupal\Core\Url::fromRoute('<front>');
        // Get the URL string.
        $redirect_url = $url->toString();
        // Redirect to the specified URL.
        $response = new RedirectResponse($redirect_url);
        $response->send();
        return;
      //header("HTTP/1.1 404 Not Authorized");
      //return new Response('Not Authorized', 404);
    // }

    if ($receivedToken !== "Bearer " . $expectedpassword) 
    {
        $url = \Drupal\Core\Url::fromRoute('<front>');
        // Get the URL string.
        $redirect_url = $url->toString();
        // Redirect to the specified URL.
        $response = new RedirectResponse($redirect_url);
        $response->send();
        return;

      //header("HTTP/1.1 403 Unauthorized");
      //exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) 
    // if (true) 
    {
    
    $order_id = $_POST['order_id'];
    $order_type = $_POST['order_type'];

    //if (1 == 1) { //$order_id = 406;
    //$order_type = $_POST['order_type'];
    // $order_type = "sample_order";
    // $order_type = "web_order";
    // $order_type = "quote_order";
    //$order_type = $_POST['order_type'];
    // $order_id = 198538;

    $quote_id = $order_id;
    if($order_type == "quote_order")
    {
      $query = $database->select('forms_to_zoho', 'ftz')
        ->fields('ftz', ['products','timestamp'])
        ->condition('ftz.id', $order_id)
        ->condition('ftz.form_name', 'Request Quote Form');

      // Execute the query and fetch the result
      $quote_result = $query->execute()->fetchAssoc();

      // Check if the result is not empty and get the 'products' field
      if ($quote_result) {
          $products_string = $quote_result['products'];
          $parts = explode('|', $products_string);
          $order_id = $parts[1]; 
          $timestamp_quote = $quote_result['timestamp'];
      } 
      else
      {
        $web_order_result = $query->execute()->fetchAssoc();
        $order_id =-1;
      }
    }
    if(($order_type == 'web_order' || $order_type == "quote_order") && is_numeric($order_id) && $order_id>0 )
    { 

        $query = $database->select('commerce_order', 'co');
        $query->leftJoin('profile__address', 'bill_address', 'co.billing_profile__target_id = bill_address.entity_id');
        $query->leftJoin('profile__address', 'ship_address', 'co.billing_profile__target_id = ship_address.entity_id');
        $query->join('users_field_data', 'u', 'co.uid = u.uid');
        $query->leftJoin('user__field_first_name', 'fname', 'u.uid = fname.entity_id');
        $query->leftJoin('user__field_last_name', 'lname', 'u.uid = lname.entity_id');
        $query->leftJoin('user__field_company_institution', 'company', 'u.uid = company.entity_id');
        $query->leftJoin('user__field_job_title', 'job', 'u.uid = job.entity_id');
        $query->leftJoin('user__field_mail_opt_in', 'opt', 'u.uid = opt.entity_id');
        $query->leftJoin('user__field_phone_number', 'phone', 'u.uid = phone.entity_id');
        $query->leftJoin('commerce_shipment', 'shipping_line_item', 'co.order_id = shipping_line_item.order_id');
        $query->leftJoin('commerce_order_item', 'product_line_item', 'co.order_id = product_line_item.order_id');
        //$query->leftJoin('commerce_product_variation_field_data', 'product_variation', 'product_line_item.purchased_entity = product_variation.variation_id AND product_variation.type = \'product\' AND product_line_item.unit_price__currency_code = \'USD\'');
        $query->leftJoin('commerce_product_variation_field_data', 'product_variation', 'product_line_item.purchased_entity = product_variation.variation_id AND product_variation.type = \'product\'');
        $query->leftJoin('commerce_payment', 'pt', 'co.order_id = pt.order_id');
        $query->leftJoin('commerce_order__coupons', 'c', 'co.order_id = c.entity_id');
        $query->leftJoin('commerce_promotion_coupon', 'pc', 'c.coupons_target_id = pc.id');
        $query->addExpression($quote_id, 'order_id');
        $query->addField('co', 'state', 'status');
        $query->addField('co', 'created', 'created');
        $query->addField('co', 'changed', 'changed');
        $query->addField('co', 'placed', 'placed');  
        if($order_type == "web_order")
        {
            $query->addField('co', 'placed', 'placed');  
        }
        else if($order_type == "quote_order")
        {
            $query->addExpression($timestamp_quote, 'placed');
        }      
        $query->addField('co', 'mail', 'order_email');
        $query->addField('co', 'total_price__number', 'commerce_order_total_amount');
        $query->addField('co', 'total_price__currency_code', 'commerce_order_total_currency_code');
        $query->addField('bill_address', 'address_address_line1', 'billing_address');
        $query->addField('bill_address', 'address_address_line1', 'billing_street1');
        $query->addField('bill_address', 'address_address_line2', 'billing_street2');
        $query->addField('bill_address', 'address_locality', 'billing_city');
        $query->addField('bill_address', 'address_administrative_area', 'billing_state');
        $query->addField('bill_address', 'address_postal_code', 'billing_postal_code');
        $query->addField('bill_address', 'address_country_code', 'billing_country');
        $query->addField('ship_address', 'address_address_line1', 'shipping_address');
        $query->addField('ship_address', 'address_address_line1', 'shipping_street1');
        $query->addField('ship_address', 'address_address_line2', 'shipping_street2');
        $query->addField('ship_address', 'address_locality', 'shipping_city');
        $query->addField('ship_address', 'address_administrative_area', 'shipping_state');
        $query->addField('ship_address', 'address_postal_code', 'shipping_postal_code');
        $query->addField('ship_address', 'address_country_code', 'shipping_country');
        $query->addField('u', 'mail', 'invoice_email');
        $query->addField('u', 'mail', 'shipping_email');
        $query->addField('u', 'mail', 'email');
        $query->addField('company', 'field_company_institution_value', 'institution');
        $query->addField('job', 'field_job_title_value', 'position');
        $query->addField('opt', 'field_mail_opt_in_value', 'optin');
        $query->addField('fname', 'field_first_name_value', 'first_name');
        $query->addField('lname', 'field_last_name_value', 'last_name');
        $query->addField('bill_address', 'address_given_name', 'bill_address_first_name');
        $query->addField('bill_address', 'address_family_name', 'bill_address_last_name');
        $query->addField('ship_address', 'address_given_name', 'ship_address_first_name');
        $query->addField('ship_address', 'address_family_name', 'ship_address_last_name');
        $query->addField('shipping_line_item', 'shipping_service', 'carrier');
        $query->addField('shipping_line_item', 'amount__number', 'shipping_amount');
        $query->addField('pt', 'payment_gateway', 'payment_gateway');
        $query->addField('phone', 'field_phone_number_value', 'phone_number');
        $query->addExpression("GROUP_CONCAT(', ', CONCAT(product_variation.sku, ' _quan_', product_line_item.quantity, ' _price_', IFNULL(product_line_item.unit_price__number, 0)))", 'product_skus');
        $query->addExpression("GROUP_CONCAT(DISTINCT pc.code SEPARATOR ',')", 'all_coupon_names');
        $query->condition('co.order_id', $order_id);
        if($order_type == "web_order")
        {
            $query->condition('co.placed', 0, '<>');
        }
        $query->groupBy('co.order_id');
        $query->groupBy('bill_address.address_address_line1');
        $query->groupBy('bill_address.address_address_line2');
        $query->groupBy('bill_address.address_locality');
        $query->groupBy('bill_address.address_administrative_area');
        $query->groupBy('bill_address.address_postal_code');
        $query->groupBy('bill_address.address_country_code');
        $query->groupBy('ship_address.address_address_line1');
        $query->groupBy('ship_address.address_address_line1');
        $query->groupBy('ship_address.address_address_line2');
        $query->groupBy('ship_address.address_locality');
        $query->groupBy('ship_address.address_administrative_area');
        $query->groupBy('ship_address.address_postal_code');
        $query->groupBy('ship_address.address_country_code');
        $query->groupBy('u.mail');
        $query->groupBy('company.field_company_institution_value');
        $query->groupBy('job.field_job_title_value');
        $query->groupBy('opt.field_mail_opt_in_value');
        $query->groupBy('fname.field_first_name_value');
        $query->groupBy('lname.field_last_name_value');
        $query->groupBy('bill_address.address_given_name');
        $query->groupBy('bill_address.address_family_name');
        $query->groupBy('ship_address.address_given_name');
        $query->groupBy('ship_address.address_family_name');
        $query->groupBy('shipping_line_item.shipping_service');
        $query->groupBy('shipping_line_item.amount__number');
        $query->groupBy('pt.payment_gateway');
        $query->groupBy('phone.field_phone_number_value');

        $web_result = $query->execute()->fetchAll();
        if ($order_id) {
          // Load the order.
          $order = Order::load($order_id);
          
          // $billing_profiles = $order->getBillingProfile();          
          
          $profiles = $order->collectProfiles();
          // var_dump($profiles['shipping']);
            
          $shipping_profile = isset($profiles['shipping']) ? $profiles['shipping'] : NULL;
    
          if ($shipping_profile) {
            // Extract the address field.
            $address = $shipping_profile->get('address')->first()->getValue();
            
            // var_dump($shipping_profile);
            $web_order_result = [];
            foreach ($web_result as $row) {
              $web_order_result[] = (array) $row;
            }
            
            $web_order_result[0]['shipping_country'] = $address['country_code'];
            $web_order_result[0]['shipping_state'] = $address['administrative_area'];
            $web_order_result[0]['shipping_city'] = $address['locality'];
            $web_order_result[0]['shipping_postal_code'] = $address['postal_code'];
            $web_order_result[0]['shipping_address'] = $address['address_line1'];
            $web_order_result[0]['shipping_street1'] = $address['address_line1'];
            $web_order_result[0]['shipping_street2'] = $address['address_line2'];
            $web_order_result[0]['shipping_company'] = $address['organization'];
            $web_order_result[0]['ship_address_first_name'] = $address['given_name'];
            $web_order_result[0]['ship_address_middle_name'] = $address['additional_name'];
            $web_order_result[0]['ship_address_last_name'] = $address['family_name'];
            
          } 
          else
          {
            $web_order_result = 1;

          }
         
        }
        
    }
    else if($order_type == "sample_order")
    {
        $query = $database->select('forms_to_zoho', 'ftz');
        $query->addField('ftz', 'id', 'order_id');
        $query->addExpression('NULL', 'status');
        $query->addField('ftz', 'created_on', 'created');
        $query->addExpression('NULL', 'changed');
        $query->addField('ftz', 'timestamp', 'placed');
        $query->addExpression('NULL', 'commerce_order_total_amount');
        $query->addField('ftz', 'street1', 'billing_address');
        $query->addField('ftz', 'street1', 'billing_street1');
        $query->addField('ftz', 'street2', 'billing_street2');
        $query->addField('ftz', 'city', 'billing_city');
        $query->addField('ftz', 'state', 'billing_state');
        $query->addField('ftz', 'zip', 'billing_postal_code');
        $query->addField('ftz', 'country', 'billing_country');
        $query->addField('ftz', 'street1', 'shipping_address');
        $query->addField('ftz', 'street1', 'shipping_street1');
        $query->addField('ftz', 'street2', 'shipping_street2');
        $query->addField('ftz', 'city', 'shipping_city');
        $query->addField('ftz', 'state', 'shipping_state');
        $query->addField('ftz', 'zip', 'shipping_postal_code');
        $query->addField('ftz', 'country', 'shipping_country');
        $query->addExpression("CASE WHEN ftz.country = 'Canada' THEN 'CAD' ELSE 'USD' END", 'commerce_order_total_currency_code');
        $query->addField('ftz', 'email', 'invoice_email');
        $query->addField('ftz', 'email', 'shipping_email');
        $query->addField('ftz', 'email', 'email');
        $query->addField('ftz', 'company', 'institution');
        $query->addField('ftz', 'title', 'position');
        $query->addField('ftz', 'opt_in', 'optin');
        $query->addField('ftz', 'first_name', 'first_name');
        $query->addField('ftz', 'last_name', 'last_name');
        $query->addField('ftz', 'first_name', 'bill_address_first_name');
        $query->addField('ftz', 'last_name', 'bill_address_last_name');
        $query->addField('ftz', 'first_name', 'ship_address_first_name');
        $query->addField('ftz', 'last_name', 'ship_address_last_name');
        $query->addExpression("'UPS Express Saver'", 'carrier');
        $query->addExpression('NULL', 'shipping_amount');
        $query->addExpression("'sample'", 'payment_method');
        $query->addField('ftz', 'phone', 'phone_number');
        $query->addExpression('NULL', 'coupon_names');
        $query->addExpression('NULL', 'all_discounts');
        $query->addExpression('NULL', 'all_coupons');
        $query->addField('ftz', 'sample', 'product_skus');
        $query->condition('ftz.id', $order_id);
        $query->condition('ftz.form_name', 'Sample Request Form');
    }
    // else if($order_type == "quote_order")
    // {
    //     $query = $database->select('forms_to_zoho', 'ftz');
    //     $query->addField('ftz', 'id', 'order_id');
    //     $query->addExpression('NULL', 'status');
    //     $query->addField('ftz', 'created_on', 'created');
    //     $query->addExpression('NULL', 'changed');
    //     $query->addField('ftz', 'timestamp', 'placed');
    //     $query->addExpression('NULL', 'commerce_order_total_amount');
    //     $query->addExpression('NULL', 'commerce_order_total_currency_code');
    //     $query->addField('ftz', 'invoice_street1', 'billing_address');
    //     $query->addField('ftz', 'street1', 'billing_street1');
    //     $query->addField('ftz', 'street2', 'billing_street2');
    //     $query->addField('ftz', 'invoice_city', 'billing_city');
    //     $query->addField('ftz', 'invoice_state', 'billing_state');
    //     $query->addField('ftz', 'invoice_zip', 'billing_postal_code');
    //     $query->addField('ftz', 'invoice_country', 'billing_country');
    //     $query->addField('ftz', 'street1', 'shipping_address');
    //     $query->addField('ftz', 'street1', 'shipping_street1');
    //     $query->addField('ftz', 'street2', 'shipping_street2');
    //     $query->addField('ftz', 'city', 'shipping_city');
    //     $query->addField('ftz', 'state', 'shipping_state');
    //     $query->addField('ftz', 'zip', 'shipping_postal_code');
    //     $query->addField('ftz', 'country', 'shipping_country');
    //     $query->addExpression("CASE WHEN ftz.country = 'Canada' THEN 'CAD' ELSE 'USD' END", 'commerce_order_total_currency_code');
    //     $query->addField('ftz', 'email', 'invoice_email');
    //     $query->addField('ftz', 'email', 'shipping_email');
    //     $query->addField('ftz', 'email', 'email');
    //     $query->addField('ftz', 'company', 'institution');
    //     $query->addField('ftz', 'title', 'position');
    //     $query->addField('ftz', 'opt_in', 'optin');
    //     $query->addField('ftz', 'first_name', 'first_name');
    //     $query->addField('ftz', 'last_name', 'last_name');
    //     $query->addField('ftz', 'first_name', 'bill_address_first_name');
    //     $query->addField('ftz', 'last_name', 'bill_address_last_name');
    //     $query->addField('ftz', 'first_name', 'ship_address_first_name');
    //     $query->addField('ftz', 'last_name', 'ship_address_last_name');
    //     $query->addExpression('NULL', 'carrier');
    //     $query->addExpression('NULL', 'shipping_amount');
    //     $query->addExpression("'quote'", 'payment_method');
    //     $query->addField('ftz', 'phone', 'phone_number');
    //     $query->addExpression('NULL', 'coupon_names');
    //     $query->addExpression('NULL', 'all_discounts');
    //     $query->addExpression('NULL', 'all_coupons');
    //     $query->addField('ftz', 'prod_quantity', 'product_skus');
    //     $query->condition('ftz.id', $order_id);
    //     $query->condition(
    //       $query->orConditionGroup()
    //         ->condition('ftz.form_name', 'Quote Request Form')
    //         ->condition('ftz.form_name', 'Request Quote Form')
    //         ->condition('ftz.form_name', 'Quote Request Form - Cart')
    //     );
    // }

    $result = $query->execute()->fetchAll();

    if(($order_type == 'web_order' || $order_type == 'quote_order') && $web_order_result!=1)
    { 
        $result = $web_order_result;
    }
    
    //$result = 'web_order: ' + $order_type;
    
    // echo ($quote_id);
    header('Content-Type: application/json');
    return new JsonResponse($result);

    } else {

        $url = \Drupal\Core\Url::fromRoute('<front>');

        // Get the URL string.
        $redirect_url = $url->toString();


        // Redirect to the specified URL.
        $response = new RedirectResponse($redirect_url);
        $response->send();
        return; }
  }

}
