<?php
namespace Drupal\nor_erp_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\commerce_order\Entity\Order;

use Symfony\Component\HttpFoundation\RedirectResponse;
/**
 * Provides route responses for the NOR ERP API module.
 */
class NorErpCompareController extends ControllerBase {

  /**
   * Returns a JSON response with completed orders within the specified date range.
   *
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response containing the web order IDs of completed orders.
   */
  public function comparePage() 
  {
    $allowedIps = ['68.71.17.146']; //['192.168.0.1'];
    $userIp = $_SERVER['REMOTE_ADDR'];
    $expectedpassword = '136dba11f13da916fb64367017c9d5df6e2766fd57ff6563d0362d752c8910ea';
    $receivedToken = $_SERVER["HTTP_AUTHORIZATION"] ?? '';
    if (!in_array($userIp, $allowedIps))  {
        $url = \Drupal\Core\Url::fromRoute('<front>');
        // Get the URL string.
        $redirect_url = $url->toString();
        // Redirect to the specified URL.
        $response = new RedirectResponse($redirect_url);
        $response->send();
        return;
        //header("HTTP/1.1 404 Not Authorized");
        //return new Response('Not Authorized', 404);
    }

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
    // $_POST['from'] = '2024-08-01';
    // $_POST['to'] = '2024-08-15';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['from']) && isset($_POST['to'])) 
    // if (isset($_POST['from']) && isset($_POST['to'])) 
    {
        $database = \Drupal::database();
        $from_date = $_POST['from'];
        $to_date = $_POST['to'];
    
      // Validate and format the dates.
        if (!$from_date || !$to_date) {
          return new JsonResponse(['error' => 'Missing or invalid date parameters'], 400);
        }
    
        try {
          $from_datetime = new \DateTime($from_date . ' 00:00:00');
          $to_datetime = new \DateTime($to_date . ' 23:59:59');
        } catch (\Exception $e) {
          return new JsonResponse(['error' => 'Invalid date format'], 400);
        }
    
        // Query the database for completed orders within the date range.
        $query = \Drupal::entityQuery('commerce_order')
          ->condition('state', 'completed')
          ->condition('completed', [$from_datetime->getTimestamp(), $to_datetime->getTimestamp()], 'BETWEEN')
          ->sort('completed', 'ASC')
          ->accessCheck(False); // or FALSE
        $order_ids = $query->execute();
    
        // Load the orders and extract the web order IDs.
        $orders = Order::loadMultiple($order_ids);
        $order_data = [];
    
        foreach ($orders as $order) {
          $order_data[] = [
            'order_id' => $order->id(),
            'order_number' => $order->getOrderNumber(),
          ];
        }
    
        header('Content-Type: application/json');
        return new JsonResponse($order_data);
    }
    else
    {
        return new JsonResponse(['error' => 'Missing or invalid date parameters'], 400);
    }
    
   
  }

}
