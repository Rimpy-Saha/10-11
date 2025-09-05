<?php

namespace Drupal\commerce_norreferral\Form;

use Drupal\user\Entity\Role;
use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form that configures referral points settings.
 */
class ReferralPointsConfigurationForm extends ConfigFormBase
{

  /**
   * {@inheritdoc}
   *
   * Set Form ID.
   *
   * @return string
   *   Description of the return value
   */
  public function getFormId()
  {
    return 'referral_points_admin_settings';
  }

  /**
   * {@inheritdoc}
   *
   * Get Editable Config Names.
   *
   * @return string
   *   Description of the return value
   */
  protected function getEditableConfigNames() {
    return [
      'commerce_norreferral.user_referral_points_settings',
    ];
  }

  /**
   * {@inheritdoc}
   *
   * Build Config Form.
   *
   * @param array $form
   *   FormStateInterface $form_state, Request $request.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   FormStateInterface $form_state.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request $request.
   *
   * @return array|Object
   *   Description of the return value, which is a array|object.
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL)
  {
    $config = $this->config('commerce_norreferral.user_referral_points_settings');

    // Sender Referral Points.
    $form['sender_referral_points_percentage'] = [
      '#type' => 'number',
      '#title' => $this->t('Referrer Points as Percentage of Order Total'),
      '#description' => t('Referrer Points as a percentage of the total order value awarded to the user who shares the referral link and brings in a new user.'),
      '#attributes' => [
        'type' => 'number',
        'min' => '0',
      ],
      '#default_value' => $config->get('sender_referral_points_percentage'),
      '#field_suffix' => '%', // Addding the percentage symbol as the field suffix
    ];

    // Sender Referral Points.
    $form['points_factor'] = [
      '#type' => 'number',
      '#title' => $this->t('Points Factor'),
      '#description' => t('Configure the points factor. For example, if set to 100, 100 points would be equal to 1 dollar.'),
      '#attributes' => [
        'type' => 'number',
        'min' => '0',
      ],
      '#default_value' => $config->get('points_factor'),
    ];

    // Sender Bonus Referral Points.
    $form['sender_bonus_referral_points'] = [
      '#type' => 'number',
      '#title' => $this->t('Referrer Bonus Points'),
      '#description' => t('Additional points given to the user who shared the link upon successfully referring their first new user.'),
      '#attributes' => [
        'type' => 'number',
        'min' => '0',
      ],
      '#default_value' => $config->get('sender_bonus_referral_points'),
    ];

    //Maximum User Redeemable Points
    $form['user_max_redeemable_points'] = [
      '#type' => 'number',
      '#title' => $this->t('Maximum Redeemable Points by Users'),
      '#description' => t('The maximum points that a user can redeem in a single order.'),
      '#attributes' => [
        'type' => 'number',
        'min' => '0',
      ],
      '#default_value' => $config->get('user_max_redeemable_points'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   *
   * Save Config Form Data.
   *
   * @param array $form
   *   FormStateInterface $form_state.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   FormStateInterface $form_state.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $this->config('commerce_norreferral.user_referral_points_settings')
      ->set('sender_referral_points_percentage', $values['sender_referral_points_percentage'])
      ->set('points_factor', $values['points_factor'])
      ->set('sender_bonus_referral_points', $values['sender_bonus_referral_points'])
      ->set('user_max_redeemable_points', $values['user_max_redeemable_points'])
      ->save();

    // Invalidate user view.
    \Drupal::service('cache_tags.invalidator')->invalidateTags(['user_view']);

  }

}
