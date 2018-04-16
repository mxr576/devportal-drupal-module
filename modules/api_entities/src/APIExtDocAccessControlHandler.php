<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the APIExtDoc entity.
 *
 * @see \Drupal\devportal_api_entities\Entity\APIExtDoc.
 */
class APIExtDocAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\devportal_api_entities\APIExtDocInterface $entity */
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view api ext docs');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit api ext docs');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete api ext docs');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add api ext docs');
  }

}

