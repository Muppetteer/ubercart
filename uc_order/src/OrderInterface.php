<?php

/**
 * @file
 * Contains \Drupal\uc_order\OrderInterface.
 */

namespace Drupal\uc_order;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\uc_store\Address;

/**
 * Provides an interface defining an Ubercart order entity.
 */
interface OrderInterface extends ContentEntityInterface {

  /**
   * Returns an array containing an order's line items ordered by weight.
   *
   * @return array
   *   An array of line items, which are arrays containing the following keys:
   *   - line_item_id: The line item id.
   *   - type: The line item type.
   *   - title: The line item title.
   *   - amount: The line item amount.
   *   - weight: The line item weight.
   */
  public function getLineItems();

  /**
   * Returns an order's line items ordered by weight, prepared for display.
   *
   * @return array
   *   An array of line items, which are arrays containing the following keys:
   *   - type: The line item type.
   *   - title: The line item title.
   *   - amount: The line item amount.
   *   - weight: The line item weight.
   */
  public function getDisplayLineItems();

  /**
   * Returns the order user entity.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity.
   */
  public function getUser();

  /**
   * Returns the order user ID.
   *
   * @return int
   *   The user ID.
   */
  public function getUserId();

  /**
   * Sets the order user ID.
   *
   * @param int $uid
   *   The user ID.
   *
   * @return \Drupal\uc_order\OrderInterface
   *   The called owner entity.
   */
  public function setUserId($uid);

  /**
   * Returns the order status.
   *
   * @return \Drupal\uc_order\OrderStatusInterface
   *   The order status entity.
   */
  public function getStatus();

  /**
   * Returns the order status ID.
   *
   * @return string
   *   The order status ID.
   */
  public function getStatusId();

  /**
   * Sets the order status ID.
   *
   * @param string $status
   *   The order status ID.
   *
   * @return \Drupal\uc_order\OrderInterface
   *   The called owner entity.
   */
  public function setStatusId($status);

  /**
   * Returns the order state ID.
   *
   * @return string
   *   The order state ID.
   */
  public function getStateId();

  /**
   * Returns the order e-mail address.
   *
   * @return string
   *   The e-mail address.
   */
  public function getEmail();

  /**
   * Sets the order e-mail address.
   *
   * @param string $email
   *   The e-mail address.
   *
   * @return \Drupal\uc_order\OrderInterface
   *   The called owner entity.
   */
  public function setEmail($email);

  /**
   * Returns the order subtotal amount (products only).
   *
   * @return float
   *   The order subtotal.
   */
  public function getSubtotal();

  /**
   * Returns the order total amount (including all line items).
   *
   * @return float
   *   The order total.
   */
  public function getTotal();

  /**
   * Returns the number of products in an order.
   *
   * @return int
   *   The number of products.
   */
  public function getProductCount();

  /**
   * Returns the order currency code.
   *
   * @return string
   *   The order currency code.
   */
  public function getCurrency();

  /**
   * Returns the order payment method.
   *
   * @return string
   *   The payment method.
   */
  public function getPaymentMethodId();

  /**
   * Sets the order payment method.
   *
   * @param string $payment_method
   *   The payment method ID.
   *
   * @return \Drupal\uc_order\OrderInterface
   *   The called owner entity.
   */
  public function setPaymentMethodId($payment_method);

  /**
   * Returns an address attached to the order.
   *
   * @param string $type
   *   The address type, usually 'billing' or 'delivery'.
   *
   * @return \Drupal\uc_store\Address
   *   The address object.
   */
  public function getAddress($type);

  /**
   * Sets an address attached to the order.
   *
   * @param string $type
   *   The address type, usually 'billing' or 'delivery'.
   * @param \Drupal\uc_store\Address $address
   *   The address object.
   *
   * @return \Drupal\uc_order\OrderInterface
   *   The called owner entity.
   */
  public function setAddress($type, Address $address);

  /**
   * Returns whether an order is considered shippable or not.
   *
   * @return bool
   *   TRUE if the order is shippable, FALSE otherwise.
   */
  public function isShippable();

  /**
   * Logs changes made to an order.
   *
   * @param $changes
   *   An array of changes. Two formats are allowed:
   *   - keys: Keys being the name of the field changed and the values being
   *     associative arrays with the keys 'old' and 'new' to represent the old
   *     and new values of the field. These will be converted into a changed
   *     message.
   *   - string: A pre-formatted string describing the change. This is useful for
   *     logging details like payments.
   *
   * @return \Drupal\uc_order\OrderInterface
   *   The called owner entity.
   */
  public function logChanges($changes);

}
