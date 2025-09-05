<?php

namespace Drupal\nor_user_rules\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the routing events and alters routes.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('miniorange_oauth_client.settings') ) 
    {
      $route->setRequirement('_access', 'FALSE');
    }
    if ($route = $collection->get('miniorange_oauth_client.mapping')) 
    {
      $route->setRequirement('_access', 'FALSE');
    }
    if ($route = $collection->get('miniorange_oauth_client.licensing')) 
    {
      $route->setRequirement('_access', 'FALSE');
    }
    if ($route = $collection->get('miniorange_oauth_client.login_reports')) 
    {
      $route->setRequirement('_access', 'FALSE');
    }
  }
}
