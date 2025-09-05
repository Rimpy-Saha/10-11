<?php

namespace Drupal\commerce_norreferral\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_norreferral\Database\UserReferralDatabase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UserPointsDetailsDeleteForm extends FormBase {

  /**
   * The database connection.
   *
   * @var \Drupal\commerce_norreferral\Database\UserReferralDatabase;
   */
  protected $userReferralDatabase;

  /**
   * {@inheritdoc}
   */
  public function __construct(UserReferralDatabase $user_referral_database) {
    $this->userReferralDatabase = $user_referral_database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('commerce_norreferral.user_referral_database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'user_referral_point_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL) {
    // Set the ID for deletion.
    $form_state->set('id', $id);

    // Add a confirmation message.
    $form['confirm_message'] = [
      '#markup' => $this->t('Are you sure you want to delete this record?'),
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Delete'),
    ];

    $form['actions']['cancel'] = [
      '#type' => 'submit',
      '#value' => $this->t('Cancel'),
      '#submit' => ['::cancelForm'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Delete the data.
    $this->userReferralDatabase->deleteUserPointsRecord(['id' => $form_state->get('id')]);
    $this->messenger()->addMessage($this->t('Record deleted successfully.'));
    $form_state->setRedirect('commerce_norreferral.admin_user_points_details_form');
  }

  /**
   * Cancel form submission.
   */
  public function cancelForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('commerce_norreferral.admin_user_points_details_form');
  }
}
