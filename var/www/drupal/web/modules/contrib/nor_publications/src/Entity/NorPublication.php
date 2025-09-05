<?php

namespace Drupal\nor_publications\Entity;

use Drupal\address\AddressInterface;
use Drupal\user\EntityOwnerTrait;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityChangedTrait;

/**
 * Defines the nor_publications entity class.
 *
 * @ContentEntityType(
 *   id = "nor_publications",
 *   label = @Translation("Norgen Publications", context = "Custom Entity Modules"),
 *   label_collection = @Translation("Norgen Publications items", context = "Custom Entity Modules"),
 *   label_singular = @Translation("norgen publications item", context = "Custom Entity Modules"),
 *   label_plural = @Translation("norgen publications items", context = "Custom Entity Modules"),
 *   label_count = @PluralTranslation(
 *     singular = "@count norgen publications item",
 *     plural = "@count norgen publications items",
 *     context = "Custom Entity Modules",
 *   ),
 *   bundle_label = @Translation("Norgen Publications type", context = "Custom Entity Modules"),
 *   handlers = {
 *     "event" = "Drupal\nor_publications\Event\NorPublicationEvent",
 *     "storage" = "Drupal\nor_publications\NorPublicationStorage",
 *     "access" = "Drupal\entity\EntityAccessControlHandler",
 *     "query_access" = "Drupal\entity\QueryAccess\QueryAccessHandler",
 *     "permission_provider" = "Drupal\entity\EntityPermissionProvider",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\nor_publications\NorPublicationListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "default" = "Drupal\nor_publications\Form\NorPublicationForm",
 *       "add" = "Drupal\nor_publications\Form\NorPublicationForm",
 *       "edit" = "Drupal\nor_publications\Form\NorPublicationForm",
 *       "duplicate" = "Drupal\nor_publications\Form\NorPublicationForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "local_task_provider" = {
 *       "default" = "Drupal\entity\Menu\DefaultEntityLocalTaskProvider",
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\entity\Routing\AdminHtmlRouteProvider",
 *       "delete-multiple" = "Drupal\entity\Routing\DeleteMultipleRouteProvider",
 *     },
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler"
 *   },
 *   base_table = "nor_publications",
 *   data_table = "nor_publications_field_data",
 *   admin_permission = "administer nor_publications",
 *   permission_granularity = "bundle",
 *   translatable = TRUE,
 *   entity_keys = {
 *     "id" = "nor_publications_id",
 *     "uuid" = "uuid",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "langcode" = "langcode",
 *     "owner" = "uid",
 *     "uid" = "uid",
 *   },
 *   links = {
 *     "canonical" = "/nor_publication/{nor_publications}",
 *     "add-page" = "/nor_publication/add",
 *     "add-form" = "/nor_publication/add/{nor_publications_type}",
 *     "edit-form" = "/nor_publication/{nor_publications}/edit",
 *     "duplicate-form" = "/nor_publication/{nor_publications}/duplicate",
 *     "delete-form" = "/nor_publication/{nor_publications}/delete",
 *     "delete-multiple-form" = "/admin/content/nor_publication-items/delete",
 *     "collection" = "/admin/content/nor_publication-items",
 *   },
 *   bundle_entity_type = "nor_publications_type",
 *   field_ui_base_route = "entity.nor_publications_type.edit_form",
 * )
 */
class NorPublication extends ContentEntityBase implements NorPublicationInterface {

  use EntityOwnerTrait;
  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['type'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel('Type')
      ->setDescription('The nor_publications type.')
      ->setSetting('target_type', 'nor_publications_type')
      ->setReadOnly(TRUE);

    $fields['uid']
      ->setLabel('Owner')
      ->setDescription('The nor_publications owner.')
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel('Name')
      ->setDescription('The nor_publications name.')
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
      ])      
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel('Created')
      ->setDescription('The time when the nor_publications was created.')
      ->setTranslatable(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel('Changed')
      ->setDescription('The time when the nor_publications was last edited.')
      ->setTranslatable(TRUE);

    return $fields;
  }

  /**
   * Default value callback for the 'timezone' base field definition.
   *
   * @see ::baseFieldDefinitions()
   *
   * @return array
   *   An array of default values.
   */
  public static function getSiteTimezone() {
    $site_timezone = \Drupal::config('system.date')->get('timezone.default');
    if (empty($site_timezone)) {
      $site_timezone = @date_default_timezone_get();
    }

    return [$site_timezone];
  }

  /**
   * Gets the allowed values for the 'timezone' base field.
   *
   * @return array
   *   The allowed values.
   */
  public static function getTimezones() {
    return system_time_zones(NULL, TRUE);
  }

  /**
   * Gets the allowed values for the 'billing_countries' base field.
   *
   * @return array
   *   The allowed values.
   */
  public static function getAvailableCountries() {
    return \Drupal::service('address.country_repository')->getList();
  }

}
