<?php

namespace Drupal\nor_publications\Entity;

use Drupal\address\AddressInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Defines the interface for norgen publications items.
 */
interface NorPublicationInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

  /**
   * Gets the nor_publications name.
   *
   * @return string
   *   The nor_publications name.
   */
  public function getName();

  /**
   * Sets the nor_publications name.
   *
   * @param string $name
   *   The nor_publications name.
   *
   * @return $this
   */
  public function setName($name);

  /**
   * Gets the nor_publications creation timestamp.
   *
   * @return int
   *   The nor_publications creation timestamp.
   */
  public function getCreatedTime();

  /**
   * Sets the nor_publications creation timestamp.
   *
   * @param int $timestamp
   *   The nor_publications creation timestamp.
   *
   * @return $this
   */
  public function setCreatedTime($timestamp);

}
