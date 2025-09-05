<?php

namespace Drupal\nor_publications;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\nor_publications\Entity\NorPublicationInterface;

/**
 * Defines the nor_publications storage.
 */
class NorPublicationStorage extends SqlContentEntityStorage implements NorPublicationStorageInterface {
}
