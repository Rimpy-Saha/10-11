<?php

namespace Drupal\vip_data_room\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class GatedContentController extends ControllerBase {

  /**
   * Checks if user has access.
   */
  public function hasAccess() {
    return AccessResult::allowedIf(\Drupal::request()->cookies->has('data_room_access_granted'))->addCacheContexts(['cookies:access_granted']);
  }

  /**
   * @return array
   *   Render array for the content page.
   */
  public function content() {
    if (!$this->hasAccess()) {
      throw new AccessDeniedHttpException();
    }
    /* $build = [
        '#theme' => 'jango_database_portal_content',
        '#description' => 'foo',
        '#attributes' => [],
    ]; */
    $build = [
        '#theme' => 'vip_data_room',
        '#description' => 'foo',
        '#attributes' => [],
    ];

    // Force HTML template detection
    //$build['#wrapper_attributes']['data-vip-template'] = 'html';
    return $build;
    /* return [
      '#markup' => $this->t('Your gated content goes here'),
      // Or render a proper Drupal render array
    ]; */
  }
}