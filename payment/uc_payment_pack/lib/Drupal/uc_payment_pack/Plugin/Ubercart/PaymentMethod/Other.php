<?php

/**
 * @file
 * Contains \Drupal\uc_payment_pack\Plugin\Ubercart\PaymentMethod\Other.
 */

namespace Drupal\uc_payment_pack\Plugin\Ubercart\PaymentMethod;

use Drupal\uc_order\UcOrderInterface;
use Drupal\uc_payment\PaymentMethodPluginBase;

/**
 * Defines a generic payment method.
 *
 * @Plugin(
 *   id = "other",
 *   name = @Translation("Other"),
 *   title = @Translation("Other"),
 *   description = @Translation("A generic payment method type."),
 *   checkout = FALSE,
 *   no_gateway = TRUE,
 *   configurable = FALSE,
 *   weight = 10,
 * )
 */
class Other extends PaymentMethodPluginBase {

  /**
   * {@inheritdoc}
   */
  public function orderView(UcOrderInterface $order) {
    if ($description = db_query('SELECT description FROM {uc_payment_other} WHERE order_id = :id', array(':id' => $order->id()))->fetchField()) {
      return array('#markup' => t('Description: @desc', array('@desc' => $description)));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function orderEditDetails(UcOrderInterface $order) {
    $form['description'] = array(
      '#type' => 'textfield',
      '#title' => t('Description'),
      '#default_value' => isset($order->payment_details['description']) ? $order->payment_details['description'] : '',
      '#size' => 32,
      '#maxlength' => 64,
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function orderLoad(UcOrderInterface $order) {
    $description = db_query('SELECT description FROM {uc_payment_other} WHERE order_id = :id', array(':id' => $order->id()))->fetchField();
    if (isset($description)) {
      $order->payment_details['description'] = $description;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function orderSave(UcOrderInterface $order) {
    if (empty($order->payment_details['description'])) {
      db_delete('uc_payment_other')
        ->condition('order_id', $order->id())
        ->execute();
    }
    else {
      db_merge('uc_payment_other')
        ->key(array(
          'order_id' => $order->id(),
        ))
        ->fields(array(
          'description' => $order->payment_details['description'],
        ))
        ->execute();
    }
  }

}