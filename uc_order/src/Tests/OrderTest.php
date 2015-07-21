<?php

/**
 * @file
 * Contains \Drupal\uc_order\Tests\OrderTest.
 */

namespace Drupal\uc_order\Tests;

use Drupal\Component\Utility\Unicode;
use Drupal\uc_order\Entity\Order;
use Drupal\uc_store\Address;
use Drupal\uc_store\Tests\UbercartTestBase;

/**
 * Tests for Ubercart orders.
 *
 * @group Ubercart
 */
class OrderTest extends UbercartTestBase {

  /** Authenticated but unprivileged user. */
  protected $customer;

  public function setUp() {
    parent::setUp();

    // Create a simple customer user account.
    $this->customer = $this->drupalCreateUser(array('view own orders'));
  }

  public function testOrderAPI() {
    // Test defaults.
    $order = \Drupal\uc_order\Entity\Order::create();
    $order->save();
    $this->assertEqual($order->getUserId(), 0, 'New order is anonymous.');
    $this->assertEqual($order->getStatusId(), 'in_checkout', 'New order is in checkout.');

    $order = \Drupal\uc_order\Entity\Order::create(array(
      'uid' => $this->customer->id(),
      'order_status' => uc_order_state_default('completed'),
    ));
    $order->save();
    $this->assertEqual($order->getUserId(), $this->customer->id(), 'New order has correct uid.');
    $this->assertEqual($order->getStatusId(), 'completed', 'New order is marked completed.');

    // Test deletion.
    $order->delete();
    $deleted_order = Order::load($order->id());
    $this->assertFalse($deleted_order, 'Order was successfully deleted');
  }

  public function testOrderEntity() {
    $order = \Drupal\uc_order\Entity\Order::create();
    $this->assertEqual($order->getUserId(), 0, 'New order is anonymous.');
    $this->assertEqual($order->getStatusId(), 'in_checkout', 'New order is in checkout.');

    $name = $this->randomMachineName();
    $order = \Drupal\uc_order\Entity\Order::create(array(
      'uid' => $this->customer->id(),
      'order_status' => 'completed',
      'billing_first_name' => $name,
      'billing_last_name' => $name,
    ));
    $this->assertEqual($order->getUserId(), $this->customer->id(), 'New order has correct uid.');
    $this->assertEqual($order->getStatusId(), 'completed', 'New order is marked completed.');
    $this->assertEqual($order->getAddress('billing')->first_name, $name, 'New order has correct name.');
    $this->assertEqual($order->getAddress('billing')->last_name, $name, 'New order has correct name.');

    // Test deletion.
    $order->save();
    $storage = \Drupal::entityManager()->getStorage('uc_order');
    $entities = $storage->loadMultiple(array($order->id()));
    $storage->delete($entities);

    $storage->resetCache(array($order->id()));
    $deleted_order = \Drupal\uc_order\Entity\Order::load($order->id());
    $this->assertFalse($deleted_order, 'Order was successfully deleted');
  }

  public function testEntityHooks() {
    \Drupal::service('module_installer')->install(array('entity_crud_hook_test'));

    $GLOBALS['entity_crud_hook_test'] = [];
    $order = \Drupal\uc_order\Entity\Order::create();
    $order->save();

    $this->assertHookMessage('entity_crud_hook_test_entity_presave called for type uc_order');
    $this->assertHookMessage('entity_crud_hook_test_entity_insert called for type uc_order');

    $GLOBALS['entity_crud_hook_test'] = [];
    $order = \Drupal\uc_order\Entity\Order::load($order->id());

    $this->assertHookMessage('entity_crud_hook_test_entity_load called for type uc_order');

    $GLOBALS['entity_crud_hook_test'] = [];
    $order->save();

    $this->assertHookMessage('entity_crud_hook_test_entity_presave called for type uc_order');
    $this->assertHookMessage('entity_crud_hook_test_entity_update called for type uc_order');

    $GLOBALS['entity_crud_hook_test'] = [];
    $order->delete();

    $this->assertHookMessage('entity_crud_hook_test_entity_delete called for type uc_order');
  }

  public function testOrderCreation() {
    $this->drupalLogin($this->adminUser);

    $edit = array(
      'customer_type' => 'search',
      'customer[email]' => $this->customer->mail->value,
    );
    $this->drupalPostForm('admin/store/orders/create', $edit, t('Search'));

    $edit['customer[uid]'] = $this->customer->id();
    $this->drupalPostForm(NULL, $edit, t('Create order'));
    $this->assertText(t('Order created by the administration.'), 'Order created by the administration.');
    $this->assertFieldByName('uid_text', $this->customer->id(), 'The customer UID appears on the page.');

    $order_ids = \Drupal::entityQuery('uc_order')
      ->condition('uid', $this->customer->id())
      ->execute();
    $order_id = reset($order_ids);
    $this->assertTrue($order_id, t('Found order ID @order_id', ['@order_id' => $order_id]));

    $this->drupalGet('admin/store/orders/view');
    $this->assertLinkByHref('admin/store/orders/' . $order_id, 0, 'View link appears on order list.');
    $this->assertText('Pending', 'New order is "Pending".');
  }

  public function testOrderEditing() {
    $order = $this->ucCreateOrder($this->customer);

    $this->drupalLogin($this->customer);
    $this->drupalGet('user/' . $this->customer->id() . '/orders');
    $this->assertText(t('My order history'));

    $this->drupalGet('user/' . $this->customer->id() . '/orders/' . $order->id());
    $this->assertResponse(200, 'Customer can view their own order.');

    $this->drupalGet('admin/store/orders/' . $order->id());
    $this->assertResponse(403, 'Customer may not edit orders.');

    $this->drupalLogin($this->adminUser);
    $this->drupalGet('user/' . $this->customer->id() . '/orders/' . $order->id());
    $address = $order->getAddress('billing');
    $this->assertText(Unicode::strtoupper($address->first_name . ' ' . $address->last_name), 'Found customer name.');

    $edit = array(
      'bill_to[first_name]' => $this->randomMachineName(8),
      'bill_to[last_name]' => $this->randomMachineName(15),
    );
    $this->drupalPostForm('admin/store/orders/' . $order->id() . '/edit', $edit, t('Save changes'));
    $this->assertText(t('Order changes saved.'));
    $this->assertFieldByName('bill_to[first_name]', $edit['bill_to[first_name]'], 'Billing first name changed.');
    $this->assertFieldByName('bill_to[last_name]', $edit['bill_to[last_name]'], 'Billing last name changed.');
  }

  public function testOrderState() {
    $this->drupalLogin($this->adminUser);

    // Check that the default order state and status is correct.
    $this->drupalGet('admin/store/settings/orders');
    $this->assertFieldByName('order_states[in_checkout][default]', 'in_checkout', 'State defaults to correct default status.');
    $this->assertEqual(uc_order_state_default('in_checkout'), 'in_checkout', 'uc_order_state_default() returns correct default status.');
    $order = $this->ucCreateOrder($this->customer);
    $this->assertEqual($order->getStateId(), 'in_checkout', 'Order has correct default state.');
    $this->assertEqual($order->getStatusId(), 'in_checkout', 'Order has correct default status.');

    // Create a custom "in checkout" order status with a lower weight.
    $this->drupalGet('admin/store/settings/orders');
    $this->clickLink('Create custom order status');
    $edit = array(
      'id' => strtolower($this->randomMachineName()),
      'name' => $this->randomMachineName(),
      'state' => 'in_checkout',
      'weight' => -15,
    );
    $this->drupalPostForm(NULL, $edit, 'Create');
    $this->assertEqual(uc_order_state_default('in_checkout'), $edit['id'], 'uc_order_state_default() returns lowest weight status.');

    // Set "in checkout" state to default to the new status.
    $this->drupalPostForm(NULL, array('order_states[in_checkout][default]' => $edit['id']), 'Save configuration');
    $this->assertFieldByName('order_states[in_checkout][default]', $edit['id'], 'State defaults to custom status.');
    $order = $this->ucCreateOrder($this->customer);
    $this->assertEqual($order->getStatusId(), $edit['id'], 'Order has correct custom status.');
  }

  public function testCustomOrderStatus() {
    $order = $this->ucCreateOrder($this->customer);

    $this->drupalLogin($this->adminUser);

    // Update an order status label.
    $this->drupalGet('admin/store/settings/orders');
    $title = $this->randomMachineName();
    $edit = array(
      'order_statuses[in_checkout][name]' => $title,
    );
    $this->drupalPostForm(NULL, $edit, 'Save configuration');
    $this->assertFieldByName('order_statuses[in_checkout][name]', $title, 'Updated status title found.');

    // Confirm the updated label is displayed.
    $this->drupalGet('admin/store/orders/view');
    $this->assertText($title, 'Order displays updated status title.');

    // Create a custom order status.
    $this->drupalGet('admin/store/settings/orders');
    $this->clickLink('Create custom order status');
    $edit = array(
      'id' => strtolower($this->randomMachineName()),
      'name' => $this->randomMachineName(),
      'state' => array_rand(uc_order_state_options_list()),
      'weight' => mt_rand(-10, 10),
    );
    $this->drupalPostForm(NULL, $edit, 'Create');
    $this->assertText($edit['id'], 'Custom status ID found.');
    $this->assertFieldByName('order_statuses[' . $edit['id'] . '][name]', $edit['name'], 'Custom status title found.');
    $this->assertFieldByName('order_statuses[' . $edit['id'] . '][weight]', $edit['weight'], 'Custom status weight found.');

    // Set an order to the custom status.
    $this->drupalPostForm('admin/store/orders/' . $order->id(), array('status' => $edit['id']), 'Update');
    $this->drupalGet('admin/store/orders/view');
    $this->assertText($edit['name'], 'Order displays custom status title.');
  }

  protected function ucCreateOrder($customer) {
    $order = \Drupal\uc_order\Entity\Order::create(array(
      'uid' => $customer->id(),
    ));
    $order->save();
    uc_order_comment_save($order->id(), 0, t('Order created programmatically.'), 'admin');

    $order_ids = \Drupal::entityQuery('uc_order')
      ->condition('order_id', $order->id())
      ->execute();
    $this->assertTrue(in_array($order->id(), $order_ids), t('Found order ID @order_id', ['@order_id' => $order->id()]));

    $country_manager = \Drupal::service('country_manager');
    $country = array_rand($country_manager->getEnabledList());
    $zones = $country_manager->getZoneList($country);

    $delivery_address = new Address();
    $delivery_address->first_name = $this->randomMachineName(12);
    $delivery_address->last_name = $this->randomMachineName(12);
    $delivery_address->street1 = $this->randomMachineName(12);
    $delivery_address->street2 = $this->randomMachineName(12);
    $delivery_address->city = $this->randomMachineName(12);
    $delivery_address->zone = array_rand($zones);
    $delivery_address->postal_code = mt_rand(10000, 99999);
    $delivery_address->country = $country;

    $billing_address = new Address();
    $billing_address->first_name = $this->randomMachineName(12);
    $billing_address->last_name = $this->randomMachineName(12);
    $billing_address->street1 = $this->randomMachineName(12);
    $billing_address->street2 = $this->randomMachineName(12);
    $billing_address->city = $this->randomMachineName(12);
    $billing_address->zone = array_rand($zones);
    $billing_address->postal_code = mt_rand(10000, 99999);
    $billing_address->country = $country;

    $order->setAddress('delivery', $delivery_address)
      ->setAddress('billing', $billing_address)
      ->save();

    // Force the order to load from the DB instead of the entity cache.
    $db_order = \Drupal::entityManager()->getStorage('uc_order')->loadUnchanged($order->id());
    // Compare delivery and billing addresses to those loaded from the database.
    $db_delivery_address = $db_order->getAddress('delivery');
    $db_billing_address = $db_order->getAddress('billing');
    $this->assertEqual($delivery_address, $db_delivery_address, 'Delivery address is equal to delivery address in database.');
    $this->assertEqual($billing_address, $db_billing_address, 'Billing address is equal to billing address in database.');

    return $order;
  }

  protected function assertHookMessage($text, $message = NULL, $group = 'Other') {
    if (!isset($message)) {
      $message = $text;
    }
    return $this->assertTrue(array_search($text, $GLOBALS['entity_crud_hook_test']) !== FALSE, $message, $group);
  }
}
