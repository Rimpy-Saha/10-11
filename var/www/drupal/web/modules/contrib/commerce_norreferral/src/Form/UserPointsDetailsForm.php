<?php

namespace Drupal\commerce_norreferral\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\PagerSelectExtender;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


class UserPointsDetailsForm extends FormBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public function __construct(Connection $database, RendererInterface $renderer) {
    $this->database = $database;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('renderer')
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
    $form['uid'] = [
      '#type' => 'textfield',
      '#title' => $this->t('User ID'),
    ];

    $form['point_type'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Point type'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Apply Filters'),
    ];

    // Table header.
    $header = [
      'uid' => $this->t('User Id'),
      'title' => $this->t('Message'),
      'earned_points' => $this->t('Earned points'),
      'points_acquisition_date' => $this->t('Point acquisition date'),
      'point_status' => $this->t('Point status'),
      'point_type' => $this->t('Point type'),
      'used_points' => $this->t('Used points'),
      'user_message' => $this->t('User Message'),
      'operations' => $this->t('Operations'),
    ];

    // Query the database table.
    $query = $this->database->select('user_points_data', 'urd')
      ->fields('urd');

    // Apply filters.
    if (!empty($form_state->getValue('uid'))) {
      $query->condition('uid', $form_state->getValue('uid'), 'LIKE');
    }

    if (!empty($form_state->getValue('point_type'))) {
      $query->condition('point_type', $form_state->getValue('point_type'), 'LIKE');
    }
    // Latest first.
    $query->orderBy('points_acquisition_date', 'DESC');

    // Count the total results before applying the pager.
    $total_count_query = clone $query;
    $total_count = $total_count_query->countQuery()->execute()->fetchField();
    $perPageitems = 30;
    $pager = $query->extend(PagerSelectExtender::class)->limit($perPageitems);
    $result = $pager->execute()->fetchAll();

    // Add rows to the table.
    foreach ($result as $row) {

      $operations_rendered = $this->renderer->render($this->buildOperations($row->id));
      
      $rows[] = [
        'uid' => $row->uid,
        'title' => $row->title,
        'earned_points' => $row->earned_points,
        'points_acquisition_date' => date('m-d-Y' , $row->points_acquisition_date),
        'point_status' => $row->point_status ? $this->t('Active') : $this->t('Redeemed'),
        'point_type' => $row->point_type,
        'used_points' => $row->used_points,
        'user_message' => $row->user_message,
        'operations' => $operations_rendered,
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

  /**
 * Build operations for each row.
 */
private function buildOperations($id) {
  $operations = [
    'edit' => [
      'title' => $this->t('Edit'),
      'url' => Url::fromRoute('commerce_norreferral.admin_user_points_details_edit_form', ['id' => $id]),
    ],
    'delete' => [
      'title' => $this->t('Delete'),
      'url' => Url::fromRoute('commerce_norreferral.admin_user_points_details_delete_form', ['id' => $id]),
    ],
  ];

  return [
    '#type' => 'operations',
    '#links' => $operations,
  ];
}

}
