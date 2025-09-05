<?php

namespace Drupal\nor_publications;

use Drupal\nor_publications\Entity\NorPublicationType;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Defines the list builder for norgen publications items.
 */
class NorPublicationListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['name'] = $this->t('Name');
    $header['type'] = $this->t('Type');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\nor_publications\Entity\NorPublicationInterface $entity */
    $nor_publications_type = NorPublicationType::load($entity->bundle());

    $row['name']['data'] = [
      '#type' => 'link',
      '#title' => $entity->label(),
    ] + $entity->toUrl()->toRenderArray();
    $row['type'] = $nor_publications_type->label();

    return $row + parent::buildRow($entity);
  }

}
