<?php

namespace Drupal\commerce_norreferral\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\PagerSelectExtender;
use Symfony\Component\DependencyInjection\ContainerInterface;


class UserReferralDetailsForm extends FormBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * {@inheritdoc}
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }


  /**
   * {@inheritdoc}
   *
   * Set Form ID.
   *
   * @return string
   *   Description of the return value
   */
  public function getFormId() {
    return 'user_referral_points';
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
    // Redirect to the same page with the applied filters.
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Filter form elements.
    $form['referral_sender'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Referral Sender ID'),
    ];

    $form['referral_receiver'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Referral Receiver ID'),
    ];

    $form['referral_status'] = [
      '#type' => 'select',
      '#title' => $this->t('Referral Status'),
      '#options' => [
        '' => $this->t('- Select -'),
        0 => $this->t('Invalid'),
        1 => $this->t('Valid'),
      ],
      '#default_value' => '',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Apply Filters'),
    ];

    // Table header.
    $header = [
      'referral_sender' => $this->t('Referral Sender ID'),
      'referral_receiver' => $this->t('Referral Receiver ID'),
      'referral_status' => $this->t('Referral status'),
      'order_id' => $this->t('Receiver Order Id'),
      'order_amount' => $this->t('Receiver Order amount'),
      'referral_sender_update_status' => $this->t('Sender notified'),
    ];

    // Query the database table.
    $query = $this->database->select('user_referral_details', 'urd')
      ->fields('urd');

    // Apply filters.
    if (!empty($form_state->getValue('referral_sender'))) {
      $query->condition('referral_sender', $form_state->getValue('referral_sender'), 'LIKE');
    }

    if (!empty($form_state->getValue('referral_receiver'))) {
      $query->condition('referral_receiver', $form_state->getValue('referral_receiver'), 'LIKE');
    }

    if ($form_state->getValue('referral_status') != '') {
      $query->condition('referral_status', $form_state->getValue('referral_status'), 'LIKE');
    }

    // Count the total results before applying the pager.
    $total_count_query = clone $query;
    $total_count = $total_count_query->countQuery()->execute()->fetchField();
    $perPageitems = 30;
    $pager = $query->extend(PagerSelectExtender::class)->limit($perPageitems);
    $result = $pager->execute()->fetchAll();
    // Add rows to the table.
    foreach ($result as $row) {
      $rows[] = [
        'referral_sender' => $row->referral_sender,
        'referral_receiver' => $row->referral_receiver,
        'referral_status' => $row->referral_status == 0 ? $this->t('Invalid') : $this->t('Valid'),
        'order_id' => $row->order_id,
        'order_amount' => $row->order_amount,
        'referral_sender_update_status' => $row->referral_sender_update_status == 0 ? $this->t('No') : $this->t('Yes'),
      ];
    }
     // Display total count.
    $form['total_count'] = [
      '#markup' => $this->t('Total referrals: @count', ['@count' => $total_count]),
      '#prefix' => '<div>',
      '#suffix' => '</div>'
    ];

    $form['table'] = [
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No records to display.'),
    ];
    if (!empty($rows) && ( $total_count > $perPageitems)) {
      $form['pager'] = [
        '#type' => 'pager',
      ];
    }

    return $form;
  }

}
