<?php

namespace Drupal\commerce_norreferral\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;

/**
 * Controller for register user by referral link.
 */
class ReferralLink extends ControllerBase {

  /**
   * Referral Registration Form.
   */
  public function registration() {

    $uid = \Drupal::currentUser()->id();
    // Get current url of referral link page.
    $current_path = \Drupal::request()->getRequestUri();
    $pathParameters = explode("referral/", $current_path);
    if (isset($pathParameters[1]) && !empty($pathParameters[1])) {
      $referralService = \Drupal::service('commerce_norreferral.referral_link');
      $link = $referralService->getLink('referral_link_code', $pathParameters[1]);

      if ($link != NULL && $uid == NULL) {
        // Render registration form.
        $entity = \Drupal::entityTypeManager()
          ->getStorage('user')
          ->create([]);
        $formObject = \Drupal::entityTypeManager()
          ->getFormObject('user', 'register')
          ->setEntity($entity);
        return \Drupal::formBuilder()->getForm($formObject);
      }
      else {
        $frontPage = Url::fromRoute('<front>')->setAbsolute()->toString();
        $register_page = $frontPage . '/user/register';
        $response = new RedirectResponse($register_page);
        $response->send();
        return;
      }
    }
  }

}
