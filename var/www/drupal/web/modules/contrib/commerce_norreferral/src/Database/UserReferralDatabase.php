<?php

namespace Drupal\commerce_norreferral\Database;

use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Database CRUD Operations
 */
class UserReferralDatabase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a UserReferralDatabase object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Inject the database service.
    return new static(
      $container->get('database')
    );
  }

  /**
   * Select records from the user_referral_details table with optional conditions.
   *
   * @param array $conditions
   *   (optional) An associative array of conditions to apply to the query.
   *
   * @return array
   *   An array of selected records.
   */
  public function selectRecords(array $conditions = []) {
    $query = $this->database->select('user_referral_details', 'urd');
    $query->fields('urd');
    if (!empty($conditions)) {
        foreach ($conditions as $field => $value) {
            $query->condition($field, $value);
        }
    }
    $result = $query->execute();
    $records = $result->fetchAll();
    $allRecords = [];
    foreach($records as $record) {
        $allRecords[] = [
            'referral_sender' => $record->referral_sender,
            'referral_receiver' => $record->referral_receiver,
            'referral_sender_points_status' => $record->referral_sender_points_status,
            'referral_sender_update_status' => $record->referral_sender_update_status,
            'referral_status' => $record->referral_status,
            'order_id' => $record->order_id,
	          'order_amount' => $record->order_amount,
        ];
    }
    return $allRecords;
  }

  /**
   * Select user points records from the user_points_data table with optional conditions.
   *
   * @param array $conditions
   *   (optional) An associative array of conditions to apply to the query.
   *
   * @return array
   *   An array of selected records.
   */
  public function selectUserPointsRecords(array $conditions = [], $additionalComaparisionCondition = [], $sortCondions = []) {
    $query = $this->database->select('user_points_data', 'urd');
    $query->fields('urd');
    if (!empty($conditions)) {
        foreach ($conditions as $field => $value) {
            // Here we are checking if any additiona comparision param is passed.
            if (isset($additionalComaparisionCondition[$field]) && !empty($additionalComaparisionCondition[$field])) {
              $query->condition($field, $value, $additionalComaparisionCondition[$field]);
            }else{
              $query->condition($field, $value);
            }
        }
    }
    // Add if any sorting condition.
    if (!empty($sortCondions)) {
        foreach ($sortCondions as $field => $value) {
            $value = $value ?? 'ASC';
            $query->orderBy($field, $value);
        }
    }
    $result = $query->execute();
    $records = $result->fetchAll();
    $allRecords = [];
    foreach($records as $record) {
        $allRecords[] = [
            'id' => $record->id,
            'title' => $record->title,
            'earned_points' => $record->earned_points,
            'points_acquisition_date' => $record->points_acquisition_date,
            'point_status' => $record->point_status,
            'user_message' => $record->user_message,
            'point_type' => $record->point_type,
            'used_points' => $record->used_points,
            'uid' => $record->uid,
        ];
    }
    return $allRecords;
  }

/**
 * Delete a user points record from the user_points_data table.
 *
 * @param array $conditions
 *   An associative array of conditions to apply to the deletion query.
 */
public function deleteUserPointsRecord(array $conditions) {
  $query = $this->database->delete('user_points_data');
    if (!empty($conditions)) {
      foreach ($conditions as $field => $value) {
          $query->condition($field, $value);
      }
    }
    $query->execute();
}


  /**
   * Insert a record into the user_referral_details table.
   *
   * @param array $values
   *   An associative array of values to insert.
   */
  public function insertRecord(array $values) {
    $this->database->insert('user_referral_details')
      ->fields($values)
      ->execute();
  }

  /**
   * Insert a user points record into the user_referral_details table.
   *
   * @param array $values
   *   An associative array of values to insert.
   */
  public function insertUserPointsRecord(array $values) {
    $this->database->insert('user_points_data')
      ->fields($values)
      ->execute();
  }

  /**
   * Update a record in the user_referral_details table.
   *
   * @param array $values
   *   An associative array of values to update.
   * @param array $condition
   *   An associative array of conditions for the update.
   */
  public function updateRecord(array $values, array $conditions) {
    $query = $this->database->update('user_referral_details');
    $query->fields($values);
    if (!empty($conditions)) {
      foreach ($conditions as $field => $value) {
          $query->condition($field, $value);
      }
    }
    $query->execute();
  }
  /**
   * Update a user points record in the user_points_data table.
   *
   * @param array $values
   *   An associative array of values to update.
   * @param array $condition
   *   An associative array of conditions for the update.
   */
  public function updateUserPointsRecord(array $values, array $conditions) {
    $query = $this->database->update('user_points_data');
    $query->fields($values);
    if (!empty($conditions)) {
      foreach ($conditions as $field => $value) {
          $query->condition($field, $value);
      }
    }
    $query->execute();
  }

  /**
   * Select referral bonus details based on sender and receiver IDs.
   *
   * @param int $sender
   *   The referral sender ID.
   * @param int $receiver
   *   The referral receiver ID.
   *
   * @return array
   *   An associative array representing the selected referral bonus details.
   */
  public function selectReferralBonus($sender) {
    $query = $this->database->select('referral_bonus_details', 'rbd')
      ->fields('rbd')
      ->condition('referral_sender', $sender)
      ->execute();

    return $query->fetchAssoc();
  }

  /**
 * Select referral bonus details based on sender ID and determine tiered points.
 *
 * @param int $sender
 *   The referral sender ID.
 *
 * @return array
 *   An associative array containing referral count and tiered bonus points.
 */


 public function getReferralCounts($sender) {
  $query = $this->database->select('user_points_data', 'upd')
    ->fields('upd', ['uid'])
    ->condition('upd.uid', $sender)
    ->condition('upd.title', '%Bonus%', 'NOT LIKE')
    ->countQuery();

  // Execute the count query and fetch the count.
  $count = $query->execute()->fetchField();

  return $count;
}

public function isNewReferral($receiver) {
  $query = $this->database->select('user_points_data', 'upd')
  ->fields('upd', ['uid'])
  ->condition('upd.uid', $receiver) // Specify the table alias for uid
  ->condition('point_type', 'Bonus-First Order');

  // Join with user_referral_details table
  $query->innerJoin('user_referral_details', 'urd', 'upd.uid = urd.referral_receiver');
  $query->condition('urd.referral_receiver', $receiver);

  // Join with commerce_order table to check for completed orders
  $query->leftJoin('commerce_order', 'co', 'upd.uid = co.uid');
  $query->isNull('co.uid'); // Ensure no entry in commerce_order
  $query->condition('co.state', 'completed'); // Exclude completed orders

  // Convert to count query
  $count_query = $query->countQuery();
  $result = $count_query->execute()->fetchField();

  return $result;
}


//^
  // // Build the query
  // $query = $this->database->select('user_points_data', 'upd')
  //     ->fields('upd', ['uid'])
  //     ->condition('upd.point_type', 'Referral')
  //     ->condition('upd.sender_uid', $sender)
  //     ->groupBy('upd.uid')
  //     ->addExpression('COUNT(*)', 'referral_count');

  // // Execute the query and fetch the results
  // $results = $query->execute()->fetchAllAssoc('uid');

  // // Initialize an array to store referral counts per user.
  // $referralCounts = [];

  // foreach ($results as $uid => $result) {
  //     $referralCounts[$uid] = $result->referral_count;
  // }

  // return $referralCounts;

// -



// public function getReferralCounts($sender) {
//   // Query to count referrals for the sender.
//   $query = $this->database->select('user_points_data', 'upd')
//    ->fields('upd', ['uid'])
//    ->condition('upd.point_type', 'Referral')
//    ->groupBy('upd.uid')
//    ->addExpression('COUNT(*)', 'referral_count')
//    ->execute();

//    return $query->fetchAssoc();
// }


// public function selectReferralBonusWithTieredPoints($sender) {
//   // Query to count referrals for the sender.
//   $query = $this->database->select('user_points_data', 'upd')
//    ->fields('upd', ['uid'])
//    ->query->condition('upd.point_type', 'Referral')
//    ->condition('upd.uid', $sender)
//    ->groupBy('upd.uid')
//    ->addExpression('COUNT(*)', 'referral_count')
//    ->execute();

//    return $query->fetchAssoc();

//   // Determine points based on referral count.
//   if (!empty($record)) {
//     $referralCount = $record['referral_count'];

//     if ($referralCount >= 5) {
//       $points = 20000; // 20,000 points for 20 or more referrals.
//     } elseif ($referralCount >= 3) {
//       $points = 10000; // 10,000 points for 10-19 referrals.
//     } elseif ($referralCount >= 2) {
//       $points = 5000; // 5,000 points for 5-9 referrals.
//     } else {
//       $points = 1000; // 1,000 points for less than 5 referrals.
//     }

//     return [
//       'referral_count' => $referralCount,
//       'points' => $points,
//     ];
//   }

//   // return [];
// }


  /**
   * Update referral bonus status.
   *
   * @param int $sender
   *   The referral sender ID.
   * @param bool $status
   *   The new referral bonus status.
   *
   * @return int
   *   The number of rows updated.
   */
  public function updateReferralBonusStatus($sender, $status) {
    return $this->database->update('referral_bonus_details')
      ->fields(['referral_bonus_status' => $status])
      ->condition('referral_sender', $sender)
      ->execute();
  }

  /**
   * Insert a new row into the referral_bonus_details table.
   *
   * @param int $sender
   *   The referral sender ID.
   * @param int $receiver
   *   The referral receiver ID.
   * @param int $user_points
   *   User points for the referral bonus.
   * @param bool $status
   *   Referral bonus status (TRUE/FALSE).
   *
   * @return int|false
   *   The number of rows inserted, or FALSE on failure.
   */
  public function insertReferralBonus($sender, $receiver, $user_points, $status = 0) {
      $this->database->insert('referral_bonus_details')
        ->fields([
          'referral_sender' => $sender,
          'referral_receiver' => $receiver,
          'user_points' => $user_points,
          'referral_bonus_status' => $status,
          'bonus_date' => time(),
        ])
        ->execute();
  }

}

