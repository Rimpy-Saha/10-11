<?php

namespace Drupal\commerce_norreferral\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Database\Query\PagerSelectExtender;
use Symfony\Component\DependencyInjection\ContainerInterface;


class ReferralBonusDetailsForm extends FormBase {


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
   *   Description of the return value, which is a string.
   */
  public function getFormId() {
    return 'referral_bonus_points';
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
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Apply Filters'),
    ];
    // Table header.
    $header = [
      'referral_sender' => $this->t('Referral Sender ID'),
      'referral_receiver' => $this->t('Referral Receiver ID'),
      'user_points' => $this->t('Bonus Points for Sender'),
      'referral_bonus_status' => $this->t('Referral Bonus Status'),
      'bonus_date' => $this->t('Bonus Awarded Date'),
    ];

    // Query the database table.
    $query = $this->database->select('referral_bonus_details', 'rbd')
      ->fields('rbd');
      // Apply filters.
      if (!empty($form_state->getValue('referral_sender'))) {
        $query->condition('referral_sender', $form_state->getValue('referral_sender'), 'LIKE');
      }

      if (!empty($form_state->getValue('referral_receiver'))) {
        $query->condition('referral_receiver', $form_state->getValue('referral_receiver'), 'LIKE');
      }
     $total_count_query = clone $query;
     $total_count = $total_count_query->countQuery()->execute()->fetchField();
     $perPageitems = 5;
     $pager = $query->extend(PagerSelectExtender::class)->limit($perPageitems);
     $result = $pager->execute()->fetchAll();
    $rows = [];
    // Add rows to the table.
    foreach ($result as $row) {
      $date = DrupalDateTime::createFromTimestamp($row->bonus_date);
      $formatted_date = $date->format('m-d-Y');
      $rows[] = [
        'referral_sender' => $row->referral_sender,
        'referral_receiver' => $row->referral_receiver,
        'user_points' => $row->user_points,
        'referral_bonus_status' => $row->referral_bonus_status == 0 ? $this->t('Invalid') : $this->t('Valid'),
        'bonus_date' => $formatted_date,
      ];
    }

    $form['table'] = [
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No records to display'),
    ];
    if (!empty($rows) && ( $total_count > $perPageitems)) {
      $form['pager'] = [
        '#type' => 'pager',
      ];
    }

    return $form;
  }


}
