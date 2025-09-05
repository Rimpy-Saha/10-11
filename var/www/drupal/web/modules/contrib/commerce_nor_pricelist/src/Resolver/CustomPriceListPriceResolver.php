<?php

namespace Drupal\commerce_nor_pricelist\Resolver;

use Drupal\commerce\Context;
use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_price\Resolver\PriceResolverInterface;
use Drupal\commerce_pricelist\PriceListPriceResolver;
use Drupal\commerce_nor_pricelist\Repository\CustomPriceListRepository;

class CustomPriceListPriceResolver extends PriceListPriceResolver implements PriceResolverInterface {

  /**
   * Constructs a new CustomPriceListPriceResolver.
   *
   * @param \Drupal\commerce_nor_pricelist\Repository\CustomPriceListRepository $price_list_repository
   *   The custom price list repository.
   */
  public function __construct(CustomPriceListRepository $price_list_repository) {
    parent::__construct($price_list_repository);
  }

  /**
   * {@inheritdoc}
   */
  public function resolve(PurchasableEntityInterface $entity, $quantity, Context $context) {
    return parent::resolve($entity, $quantity, $context);
  }
}
