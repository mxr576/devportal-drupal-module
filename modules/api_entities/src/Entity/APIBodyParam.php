<?php

namespace Drupal\devportal_api_entities\Entity;

use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\devportal_api_reference\Traits\APIRefTrait;
use Drupal\devportal_api_entities\Traits\AutoLabelTrait;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\devportal_api_entities\APIBodyParamInterface;
use Drupal\devportal_api_entities\Traits\MethodParameterTrait;
use Drupal\devportal_api_entities\Traits\APIVersionTagRefTrait;
use Drupal\devportal\Traits\URLRouteParametersTrait;;

/**
 * Defines the API Body Param entity class.
 *
 * @ContentEntityType(
 *   id = "api_body_param",
 *   label = @Translation("API HTTP Method Body Parameter"),
 *   handlers = {
 *     "storage" = "Drupal\devportal_api_entities\APIBodyParamStorage",
 *     "list_builder" = "Drupal\devportal_api_entities\APIBodyParamListBuilder",
 *     "view_builder" = "Drupal\devportal\DevportalContentEntityViewBuilder",
 *     "views_data" = "Drupal\devportal_api_entities\APIBodyParamViewsData",
 *     "form" = {
 *       "default" = "Drupal\devportal\Form\DevportalContentEntityForm",
 *       "add" = "Drupal\devportal\Form\DevportalContentEntityForm",
 *       "edit" = "Drupal\devportal\Form\DevportalContentEntityForm",
 *       "delete" = "Drupal\devportal\Form\DevportalContentEntityDeleteForm",
 *     },
 *     "inline_form" = "Drupal\devportal_api_entities\Form\DevportalInlineForm",
 *     "route_provider" = {
 *       "html" = "Drupal\devportal_api_entities\APIBodyParamHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\devportal_api_entities\APIBodyParamAccessControlHandler",
 *     "translation" = "Drupal\devportal_api_entities\APIBodyParamTranslationHandler",
 *   },
 *   admin_permission = "administer api body params",
 *   fieldable = TRUE,
 *   base_table = "api_body_param",
 *   data_table = "api_body_param_field_data",
 *   field_ui_base_route = "entity.api_body_param_type.edit_form",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "uuid" = "uuid",
 *     "revision" = "vid",
 *     "langcode" = "langcode",
 *     "label" = "auto_label",
 *   },
 *   api_extra_info = {
 *     "auto_label" = {
 *       "auto_label" = "auto_label",
 *       "autogenerated_label" = "autogenerated_label",
 *     },
 *     "auto_label_source" = "name",
 *     "method_parameter" = {
 *       "name" = "name",
 *       "description" = "description",
 *       "required" = "required",
 *     },
 *     "api_ref" = "api_ref",
 *     "api_version_tag" = "api_version_tag",
 *   },
 *   bundle_entity_type = "api_body_param_type",
 *   bundle_label = @Translation("API HTTP Method Body Parameter type"),
 *   revision_table = "api_body_param_revision",
 *   revision_data_table = "api_body_param_field_revision",
 *   show_revision_ui = TRUE,
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log",
 *   },
 *   links = {
 *     "canonical" = "/api_body_param/{api_body_param}",
 *     "add-page" = "/api_body_param/add",
 *     "add-form" = "/api_body_param/add/{api_body_param_type}",
 *     "edit-form" = "/api_body_param/{api_body_param}/edit",
 *     "delete-form" = "/api_body_param/{api_body_param}/delete",
 *     "collection" = "/admin/content/api_body_param",
 *     "version-history" = "/api_body_param/{api_body_param}/revisions",
 *     "revision" = "/api_body_param/{api_body_param}/revisions/{api_body_param_revision}/view",
 *     "revision_revert" = "/api_body_param/{api_body_param}/revisions/{api_body_param_revision}/revert",
 *     "revision_delete" = "/api_body_param/{api_body_param}/revisions/{api_body_param_revision}/delete",
 *     "multiple_delete_confirm" = "/admin/content/api_body_param/delete",
 *     "translation_revert" = "/api_body_param/{api_body_param}/revisions/{api_body_param_revision}/revert/{langcode}",
 *   },
 *   translatable = TRUE,
 * )
 */
class APIBodyParam extends RevisionableContentEntityBase implements APIBodyParamInterface {

  use EntityChangedTrait;
  use AutoLabelTrait;
  use MethodParameterTrait;
  use APIRefTrait;
  use APIVersionTagRefTrait;
  use URLRouteParametersTrait;

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    // Generate auto label.
    $this->autoLabelPreSave();
  }

  /**
   * {@inheritdoc}
   */
  public function preSaveRevision(EntityStorageInterface $storage, \stdClass $record) {
    parent::preSaveRevision($storage, $record);

    if (!$this->isNewRevision() && isset($this->original) && (!isset($record->revision_log) || $record->revision_log === '')) {
      // If we are updating an existing APIBodyParam without adding a new
      // revision, we need to make sure $entity->revision_log is reset whenever
      // it is empty. Therefore, this code allows us to avoid clobbering an
      // existing log entry with an empty one.
      $record->revision_log = $this->original->revision_log->value;
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    /** @var \Drupal\Core\Field\BaseFieldDefinition[] $fields */
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the auto label field.
    $fields += static::autoLabelBaseFieldDefinitions($entity_type);

    // Add the method parameter fields.
    $fields += static::methodParameterBaseFieldDefinitions($entity_type);

    // Add the API Reference field.
    $fields += static::apiRefBaseFieldDefinitions($entity_type);
    $fields['api_ref']->setDescription(t('API Reference referenced from API Body Parameter.'));

    // Add the API Version Tag field.
    $fields += static::apiVersionTagBaseFieldDefinitions($entity_type);
    $fields['api_version_tag']->setDescription(t('API Version Tag referenced from API Body Parameter.'));

    $fields['id']->setDescription(t('The API HTTP Method Body Parameter ID.'));

    $fields['uuid']->setDescription(t('The API HTTP Method Body Parameter UUID.'));

    $fields['vid']->setDescription(t('The API HTTP Method Body Parameter revision ID.'));

    $fields['langcode']->setDescription(t('The API HTTP Method Body Parameter language code.'));

    $fields['api_schema'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('API Schema'))
      ->setDescription(t('API Schema referenced from API Body Parameter.'))
      ->setRequired(TRUE)
      ->setRevisionable(TRUE)
      ->setSettings([
        'target_type' => 'api_schema',
        'handler' => 'default',
        'handler_settings' => [
          'sort' => [
            'field' => '_none',
          ],
          'auto_create' => FALSE,
          'auto_create_bundle' => '',
        ],
      ])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'entity_reference_label',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'inline_entity_form_complex',
        'weight' => 5,
        'settings' => [
          'form_mode' => 'default',
          'override_labels' => TRUE,
          // @FIXME Should these use $this->>t()?
          'label_singular' => 'API Schema',
          'label_plural' => 'API Schemas',
          'allow_new' => TRUE,
          'allow_existing' => FALSE,
          'match_operator' => 'CONTAINS',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the API HTTP Method Body Parameter was last edited.'))
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    $label = parent::label();
    if (empty($label)) {
      $label = $this->generateAutoLabel();
    }
    return $label;
  }

}
