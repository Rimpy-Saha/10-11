<?php

namespace Drupal\commerce_norreferral;

/**
 * Get Referral link.
 *
 * @package Drupal\referrallink
 * Class ReferralLinkService.
 */
class ReferralLinkService
{

  /**
   * Get valid Referral link.
   */
  public function getLink($field, $value = NULL)
  {
    $query = \Drupal::database()->select('user_points_referral', 'upr')
      ->fields('upr', ['referral_link_code'])
      ->condition('upr.' . $field . '', $value);
    $result = $query->execute()->fetchAssoc();
    return $result;
  }

  /**
   * Get valid Referral link.
   */
  public function getLinkUser($value = NULL)
  {
    $query = \Drupal::database()->select('user_points_referral', 'upr')
      ->fields('upr', ['uid'])
      ->condition('upr.referral_link_code', $value);
    $result = $query->execute()->fetchAssoc();
    return $result;
  }

  /**
   * Get add Referral link.
   */
  public function addLink($uid = NULL)
  {
    $chars = "0123456789abcdefghijklmnopqrstuvwxyz";

    do {
      // Generate a random link
      $link = '';
      for ($i = 0; $i < 20; $i++) {
        $link .= $chars[mt_rand(0, strlen($chars) - 1)];
      }

      // Query the table to see if the link already exists
      $existingLink = \Drupal::database()
        ->select('user_points_referral', 'upr')
        ->fields('upr', ['referral_link_code'])
        ->condition('referral_link_code', $link)
        ->range(0, 1)
        ->execute()
        ->fetchField();

    } while ($existingLink !== FALSE);


    // Inesert in database.
    \Drupal::database()->insert('user_points_referral')->fields([
      'uid' => $uid,
      'referral_link_code' => $link,
      'timestamp' => \Drupal::time()->getRequestTime(),
      'status' => 'valid',
    ])->execute();
  }

 /**
   * Get valid Referral link for the current user.
   *
   * @return string|null
   *   The referral link code or NULL if not found.
   */
  public function getReferralLinkForCurrentUser() {
    $uid = \Drupal::currentUser()->id();

    $query = \Drupal::database()->select('user_points_referral', 'upr');
    $query->addField('upr', 'referral_link_code');
    $query->condition('upr.uid', $uid);
    $result = $query->execute()->fetchField();

    return $result ?: NULL;
  }

 /**
   * Get user data.
   *
   * @return array
   *   An array of user data.
   */
  public function getUserData() {
    $uid = \Drupal::currentUser()->id();
    if ($uid != NULL) {
      $query = \Drupal::database()->select('user_points_data', 'upd');
      $query->fields('upd', []);
      $query->condition('uid', $uid);
      $query->condition('point_status', 1);
      $query->addExpression('SUM(earned_points)', 'total_earned_points');
      $query->addExpression('SUM(used_points)', 'total_used_points');
      $result = $query->execute()->fetchAssoc();
      return $result;
    }
    return [];
  }

}
