<?php

namespace Drupal\nor_publications\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the nor_publications type entity class.
 *
 * @ConfigEntityType(
 *   id = "nor_publications_type",
 *   label = @Translation("Norgen Publications type", context = "Custom Entity Modules"),
 *   label_collection = @Translation("Norgen Publications types", context = "Custom Entity Modules"),
 *   label_singular = @Translation("norgen publications type", context = "Custom Entity Modules"),
 *   label_plural = @Translation("norgen publications types", context = "Custom Entity Modules"),
 *   label_count = @PluralTranslation(
 *     singular = "@count norgen publications type",
 *     plural = "@count norgen publications types",
 *     context = "Custom Entity Modules",
 *   ),
 *   handlers = {
 *     "access" = "Drupal\entity\BundleEntityAccessControlHandler",
 *     "list_builder" = "Drupal\nor_publications\NorPublicationTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\nor_publications\Form\NorPublicationTypeForm",
 *       "edit" = "Drupal\nor_publications\Form\NorPublicationTypeForm",
 *       "duplicate" = "Drupal\nor_publications\Form\NorPublicationTypeForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     },
 *     "local_task_provider" = {
 *       "default" = "Drupal\entity\Menu\DefaultEntityLocalTaskProvider",
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer nor_publications_type",
 *   config_prefix = "nor_publications_type",
 *   bundle_of = "nor_publications",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "uuid",
 *     "description",
 *     "traits",
 *     "locked",
 *   },
 *   links = {
 *     "add-form" = "/admin/content/nor_publication-types/add",
 *     "edit-form" = "/admin/content/nor_publication-types/{nor_publications_type}/edit",
 *     "duplicate-form" = "/admin/content/nor_publication-types/{nor_publications_type}/duplicate",
 *     "delete-form" = "/admin/content/nor_publication-types/{nor_publications_type}/delete",
 *     "collection" = "/admin/content/nor_publication-types",
 *   }
 * )
 */
class NorPublicationType extends ConfigEntityBundleBase implements NorPublicationTypeInterface {

  /**
   * A brief description of this nor_publications type.
   *
   * @var string
   */
  protected $description;

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($description) {
    $this->description = $description;
    return $this;
  }

}
