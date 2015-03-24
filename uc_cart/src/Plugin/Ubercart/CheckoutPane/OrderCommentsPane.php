<?php

/**
 * @file
 * Contains \Drupal\uc_cart\Plugin\Ubercart\CheckoutPane\OrderCommentsPane.
 */

namespace Drupal\uc_cart\Plugin\Ubercart\CheckoutPane;

use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Form\FormStateInterface;
use Drupal\uc_cart\CheckoutPanePluginBase;
use Drupal\uc_order\OrderInterface;

/**
 * Allows a customer to make comments on the order.
 *
 * @Plugin(
 *   id = "comments",
 *   title = @Translation("Order comments"),
 *   weight = 7,
 * )
 */
class OrderCommentsPane extends CheckoutPanePluginBase {

  /**
   * {@inheritdoc}
   */
  public function view(OrderInterface $order, array $form, FormStateInterface $form_state) {
    $build['#description'] = t('Use this area for special instructions or questions regarding your order.');

    if ($order->id()) {
      $default = db_query("SELECT message FROM {uc_order_comments} WHERE order_id = :id", array(':id' => $order->id()))->fetchField();
    }
    else {
      $default = NULL;
    }
    $build['comments'] = array(
      '#type' => 'textarea',
      '#title' => t('Order comments'),
      '#default_value' => $default,
    );

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function process(OrderInterface $order, array $form, FormStateInterface $form_state) {
    db_delete('uc_order_comments')
      ->condition('order_id', $order->id())
      ->execute();

    if (!$form_state->isValueEmpty(['panes', 'comments', 'comments'])) {
      uc_order_comment_save($order->id(), 0, $form_state->getValue(['panes', 'comments', 'comments']), 'order', uc_order_state_default('post_checkout'), TRUE);
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function review(OrderInterface $order) {
    $review = NULL;
    $result = db_query("SELECT message FROM {uc_order_comments} WHERE order_id = :id", array(':id' => $order->id()));
    if ($comment = $result->fetchObject()) {
      $review[] = array('title' => t('Comment'), 'data' => SafeMarkup::checkPlain($comment->message));
    }
    return $review;
  }

}
