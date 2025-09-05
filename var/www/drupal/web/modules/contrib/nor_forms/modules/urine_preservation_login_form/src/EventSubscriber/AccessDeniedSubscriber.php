<?php 

namespace Drupal\urine_preservation_login_form\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AccessDeniedSubscriber implements EventSubscriberInterface {

  public static function getSubscribedEvents() {
    return [
      KernelEvents::EXCEPTION => ['onAccessDenied', 100],
    ];
  }

  public function onAccessDenied(ExceptionEvent $event) {
    $exception = $event->getThrowable();
    if ($exception instanceof AccessDeniedHttpException) {
      $request = $event->getRequest();
      $node = $request->attributes->get('node');
      
      if ($node && $node->id() == 2920) {
        $url = Url::fromRoute('entity.node.canonical', ['node' => 2922])->toString();
        $event->setResponse(new RedirectResponse($url));
      }
    }
  }
}