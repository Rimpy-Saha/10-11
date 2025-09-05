<?php

namespace Drupal\commerce_norreferral\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_norreferral\Database\UserReferralDatabase;
use Symfony\Component\DependencyInjection\ContainerInterface;


class UserPointsDetailsEditForm extends FormBase
{

  /**
   * The database connection.
   *
   * @var \Drupal\commerce_norreferral\Database\UserReferralDatabase;
   */
  protected $userReferralDatabase;

  /**
   * {@inheritdoc}
   */
  public function __construct(UserReferralDatabase $user_referral_database)
  {
    $this->userReferralDatabase = $user_referral_database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('commerce_norreferral.user_referral_database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'user_referral_point_edit_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL)
  {
    // Get row data.
    $row_data = $this->loadRowData($id);
    $form_state->set('id', $id);

    // Convert integer dates to desired date format.
    $points_acquisition_date = date('Y-m-d', $row_data['points_acquisition_date']);
    //$point_status = $row_data['point_status'] ? $this->t('Redeemed') : $this->t('Active');
    $point_status_options = [
      '1' => $this->t('Active'),
      '0' => $this->t('Redeemed'),
    ];
    // Add form fields for editing.
    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Message'),
      '#default_value' => $row_data['title'],
    ];
    $form['point_type'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Point Type'),
      '#default_value' => $row_data['point_type'],
    ];
    $form['points_acquisition_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Points Acquisition Date'),
      '#default_value' => $points_acquisition_date,
    ];
    $form['earned_points'] = [
      '#type' => 'number',
      '#title' => $this->t('Earned Points'),
      '#default_value' => $row_data['earned_points'],
      '#element_validate' => [
        [$this, 'validateNumeric'],
      ],
    ];
    $form['used_points'] = [
      '#type' => 'number',
      '#title' => $this->t('Used Points'),
      '#default_value' => (int) $row_data['used_points'],
      '#element_validate' => [
        [$this, 'validateNumeric'],
      ],
    ];
    $form['point_status'] = [
      '#type' => 'select',
      '#title' => $this->t('Point Status'),
      '#options' => $point_status_options,
      '#default_value' => $row_data['point_status'],
    ];
    $form['user_message'] = [
      '#type' => 'textfield',
      '#title' => $this->t('User Message'),
      '#default_value' => $row_data['user_message'],
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {

    // Convert the date strings back to integer format.
    $points_acquisition_date = strtotime($form_state->getValue('points_acquisition_date'));

    // Update the data.
    $this->userReferralDatabase->updateUserPointsRecord(
      [
        'title' => $form_state->getValue('title'),
        'earned_points' => $form_state->getValue('earned_points'),
        'point_type' => $form_state->getValue('point_type'),
        'points_acquisition_date' => $points_acquisition_date,
        'used_points' => $form_state->getValue('used_points'),
        'point_status' => $form_state->getValue('point_status'),
        'user_message' => $form_state->getValue('user_message'),
      ],
      ['id' => $form_state->get('id')]
    );
    $this->messenger()->addMessage($this->t('Record updated successfully.'));
    $form_state->setRedirect('commerce_norreferral.admin_user_points_details_form');
  }

  /**
   * Helper method to load row data based on ID.
   */
  private function loadRowData($id)
  {
    $row_data = $this->userReferralDatabase->selectUserPointsRecords(['id' => $id]);
    return $row_data[0];
  }

  /**
   * Custom validation callback to ensure the input is numeric.
   */
  public function validateNumeric($element, FormStateInterface $form_state)
  {
    $earnedPointsValue = $form_state->getValue('earned_points');
    if (!is_numeric($earnedPointsValue)) {
      $form_state->setError($element, $this->t('Earned points must be a numeric value.'));
    }
    $usedPointsValue = $form_state->getValue('used_points');
    if (!is_numeric($usedPointsValue)) {
      $form_state->setError($element, $this->t('Used points must be a numeric value.'));
    }
  }

}
