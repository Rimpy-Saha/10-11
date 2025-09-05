<?php 

namespace Drupal\nor_forms\EventSubscriber;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Utility\Error;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;	
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;


/**
 * Example for catching a DatabaseAccessDeniedException
 */
class NorExceptionSubscriber implements EventSubscriberInterface {

  /**
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  public function __construct(LoggerChannelFactoryInterface $logger) {
    $this->logger = $logger;
  }

  /**
   * Log all exceptions.
   *
   * @param \Symfony\Component\HttpKernel\Event\ExceptionEvent $event
   *   The event to process.
   */
  public function onException(ExceptionEvent $event) {
    $exception = $event->getThrowable();
    $code = $exception->getCode();
    if ($exception instanceof HttpExceptionInterface) {
     $message = $exception->getMessage();
     $code = $exception->getStatusCode();
    }
    \Drupal::logger('NorExceptionSubscriber.php')->info($exception . '<br />' . $code);

    $statusCode = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500;

    // Check if the exception is a HttpException and its status code is 403 or 404.
    if ($exception instanceof HttpExceptionInterface && in_array($statusCode, [403, 404])|| $exception instanceof PaymentGatewayException || $exception instanceof DeclineException) {
      return;

    } elseif ($exception instanceof \Drupal\Core\Entity\EntityStorageException) {
        \Drupal::logger('NorExceptionSubscriber.php')->info($exception . '<br />' . $code . 'Entity Error');
        // Custom error page content for parse error
        $responseContent = $responseContent = file_get_contents('/home/norgen/public_html/web/themes/custom/jango/templates/customError.html');    

        // Create a Response object with the custom HTML content and status code.
        $response = new Response($responseContent, $statusCode);

        // Set the Response object to the event.
        $event->setResponse($response);
    } elseif ($exception instanceof \ParseError) {
        \Drupal::logger('NorExceptionSubscriber.php')->info($exception . '<br />' . $code . 'Parse Error');
        // Custom error page content for parse error
        $responseContent = $responseContent = file_get_contents('/home/norgen/public_html/web/themes/custom/jango/templates/customError.html');    

        // Create a Response object with the custom HTML content and status code.
       $response = new Response($responseContent, $statusCode);

       // Set the Response object to the event.
       $event->setResponse($response);
    } elseif ($exception instanceof HttpExceptionInterface && in_array($statusCode,[500])){
       \Drupal::logger('NorExceptionSubscriber.php')->info($exception . '<br />' . $code . 'Server Error');
       $responseContent = $responseContent = file_get_contents('/home/norgen/public_html/web/themes/custom/jango/templates/customError.html');

       // Create a Response object with the custom HTML content and status code.
       $response = new Response($responseContent, $statusCode);

      // Set the Response object to the event.
       $event->setResponse($response);
    }
  }

  /**
   * {@inheritdoc}
   */

  public static function getSubscribedEvents() {
    $events[KernelEvents::EXCEPTION][] = ['onException', 60];
    return $events;
  }
}