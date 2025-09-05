<?php

namespace Drupal\nor_erp_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides route responses for the Example module.
 */
class NorProductRankingController extends ControllerBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a NorProductRankingController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Update the field_filterable_views_ranking for product variations based on a given ordered array.
   */
  public function updateProductRankingWithArray() {
    $ordered_skus = [
      '17200','21000','37500','25800','27300','27600','26200','17900','23600','29000',
      '25700','21300','24700','17300','29600','25300','53100','14400','18100','13300',
      '13100','21900','22700','22800','24400','17400','21200','25100','26500','24300',
      '47050','17700','26400','29650','22200','23800','22550','50600','21600','26800',
      '51700','27350','Dx46300','29500','10300','26900','26450','17500','24800','RU35200',
      '34500','23350','Dx29600','25400','18701','30600','29800','31510','29210'
    ];
    
    // Load all product variations.
    $storage = $this->entityTypeManager->getStorage('commerce_product_variation');
    $variations = $storage->loadMultiple();
  
    // Set all variations' field_filterable_views_ranking to 999999.
    foreach ($variations as $variation) {
      if ($variation instanceof ProductVariation) {
        $variation->set('field_filterable_views_ranking', 99999999);
        $variation->save();
      }
    }
  
    // Update rankings based on the ordered SKUs array.
    foreach ($ordered_skus as $position => $sku) {
      // Load the product variation by SKU.
      $variations_by_sku = $storage->loadByProperties(['sku' => $sku]);
  
      if (!empty($variations_by_sku)) {
        // There should be only one variation per SKU.
        $variation = reset($variations_by_sku);
  
        if ($variation instanceof ProductVariation) {
          // Calculate the new ranking value.
          $new_ranking = ($position + 1) * 100;
  
          // Update the field.
          $variation->set('field_filterable_views_ranking', $new_ranking);
          $variation->save();
        }
      }
    }
  
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Product rankings updated successfully.'),
    ];
  }

}

