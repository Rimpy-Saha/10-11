<?php

namespace Drupal\nor_forms\Plugin\EntityReferenceSelection;

use Drupal\Component\Utility\Html;
use Drupal\commerce_product\Plugin\EntityReferenceSelection\ProductVariationSelection;


/**
 * Provides a Nor autocomplete selection plugin for product variations.
 *
 * 
 * 
 * @EntityReferenceSelection(
 *   id = "nor_product_autocomplete",
 *   label = @Translation("nor_product_autocomplete"), 
 *   entity_types = {"commerce_product_variation"},
 *   group = "nor_product_autocomplete",
 *   weight = 0,
 *   deriver = "Drupal\Core\Entity\Plugin\Derivative\DefaultSelectionDeriver"
 * )
 */

class NorProductAutocomplete extends ProductVariationSelection {

  
    /**
     * {@inheritdoc}
     */
    public function getReferenceableEntities($match = NULL, $match_operator = 'CONTAINS', $limit = 0) {
      $query = $this->buildEntityQuery($match, $match_operator);
      if ($limit > 0) {
        $query->range(0, $limit);
      }
  
      $result = $query->execute();
  
      if (empty($result)) {
        return [];
      }
  
      $options = [];
      $entities = $this->entityTypeManager->getStorage('commerce_product_variation')->loadMultiple($result);
      /** @var \Drupal\commerce_product\Entity\ProductVariationInterface $entity */
      foreach ($entities as $entity_id => $entity) {
        if(is_null($entity->getProductId())) continue; // no parent product, skip it
        $parent_product = \Drupal::entityTypeManager()->getStorage('commerce_product')->load($entity->getProductId());
        if($entity->isActive() && $parent_product && $parent_product->get('type')->first()->get('target_id')->getValue()=='norproduct') // new way to filter out international products: check parent product type
        {
          $bundle = $entity->bundle();
          $options[$bundle][$entity_id] = Html::escape($entity->getSku() . ': ' . $this->entityRepository->getTranslationFromContext($entity)->label());
        }
      }
  
      return $options;
    }
  
  }