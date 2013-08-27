<?php

/**
 * @file
 * Definition of Drupal\uc_order\Tests\UbercartOrderTest.
 */

namespace Drupal\uc_order\Tests;

use Drupal\uc_store\Tests\UbercartTestBase;

/**
 * Tests for Ubercart orders.
 */
class UbercartOrderTest extends UbercartTestBase {

  public static function getInfo() {
    return array(
      'name' => 'Orders',
      'description' => 'Ensure that orders function properly.',
      'group' => 'Ubercart',
    );
  }

  public function testOrderAPI() {
    // Test defaults.
    $order = uc_order_new();
    $this->assertEqual($order->getUserId(), 0, 'New order is anonymous.');
    $this->assertEqual($order->getStatusId(), 'in_checkout', 'New order is in checkout.');

    $order = uc_order_new($this->customer->id(), 'completed');
    $this->assertEqual($order->getUserId(), $this->customer->id(), 'New order has correct uid.');
    $this->assertEqual($order->getStatusId(), 'completed', 'New order is marked completed.');

    // Test deletion.
    uc_order_delete($order->id());
    $deleted_order = uc_order_load($order->id(), TRUE);
    $this->assertFalse($deleted_order, 'Order was successfully deleted');
  }

  public function testOrderEntity() {
    $order = entity_create('uc_order', array());
    $this->assertEqual($order->getUserId(), 0, 'New order is anonymous.');
    $this->assertEqual($order->getStatusId(), 'in_checkout', 'New order is in checkout.');

    $name = $this->randomName();
    $order = entity_create('uc_order', array(
      'uid' => $this->customer->id(),
      'order_status' => 'completed',
      'billing_first_name' => $name,
      'billing_last_name' => $name,
    ));
    $this->assertEqual($order->getUserId(), $this->customer->id(), 'New order has correct uid.');
    $this->assertEqual($order->getStatusId(), 'completed', 'New order is marked completed.');
    $this->assertEqual($order->billing_first_name, $name, 'New order has correct name.');
    $this->assertEqual($order->billing_last_name, $name, 'New order has correct name.');

    // Test deletion.
    $order->save();
    entity_delete_multiple('uc_order', array($order->id()));
    $deleted_order = entity_load('uc_order', $order->id(), TRUE);
    $this->assertFalse($deleted_order, 'Order was successfully deleted');
  }

  public function testEntityHooks() {
    module_enable(array('entity_crud_hook_test'));

    $_SESSION['entity_crud_hook_test'] = array();
    $order = uc_order_new();

    $this->assertHookMessage('entity_crud_hook_test_entity_presave called for type uc_order');
    $this->assertHookMessage('entity_crud_hook_test_entity_insert called for type uc_order');

    $_SESSION['entity_crud_hook_test'] = array();
    $order = uc_order_load($order->id());

    $this->assertHookMessage('entity_crud_hook_test_entity_load called for type uc_order');

    $_SESSION['entity_crud_hook_test'] = array();
    uc_order_save($order);

    $this->assertHookMessage('entity_crud_hook_test_entity_presave called for type uc_order');
    $this->assertHookMessage('entity_crud_hook_test_entity_update called for type uc_order');

    $_SESSION['entity_crud_hook_test'] = array();
    uc_order_delete($order->id());

    $this->assertHookMessage('entity_crud_hook_test_entity_delete called for type uc_order');
  }

  public function testOrderCreation() {
    $this->drupalLogin($this->adminUser);

    $edit = array(
      'customer_type' => 'search',
      'customer[email]' => $this->customer->mail->value,
    );
    $this->drupalPost('admin/store/orders/create', $edit, t('Search'));

    $edit['customer[uid]'] = $this->customer->id();
    $this->drupalPost(NULL, $edit, t('Create order'));
    $this->assertText(t('Order created by the administration.'), 'Order created by the administration.');
    $this->assertFieldByName('uid_text', $this->customer->id(), 'The customer UID appears on the page.');

    $order_id = db_query("SELECT order_id FROM {uc_orders} WHERE uid = :uid", array(':uid' => $this->customer->id()))->fetchField();
    $this->assertTrue($order_id, t('Found order ID @order_id', array('@order_id' => $order_id)));

    $this->drupalGet('admin/store/orders');
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
    $this->assertText(drupal_strtoupper($order->billing_first_name . ' ' . $order->billing_last_name), 'Found customer name.');

    $edit = array(
      'billing_first_name' => $this->randomName(8),
      'billing_last_name' => $this->randomName(15),
    );
    $this->drupalPost('admin/store/orders/' . $order->id() . '/edit', $edit, t('Submit changes'));
    $this->assertText(t('Order changes saved.'));
    $this->assertFieldByName('billing_first_name', $edit['billing_first_name'], 'Billing first name changed.');
    $this->assertFieldByName('billing_last_name', $edit['billing_last_name'], 'Billing last name changed.');
  }

  protected function ucCreateOrder($customer) {
    $order = uc_order_new($customer->id());
    uc_order_comment_save($order->id(), 0, t('Order created programmatically.'), 'admin');

    $order_exists = db_query("SELECT 1 FROM {uc_orders} WHERE order_id = :order_id", array(':order_id' => $order->id()))->fetchField();
    $this->assertTrue($order_exists, t('Found order ID @order_id', array('@order_id' => $order->id())));

    $countries = uc_country_option_list();
    $country = array_rand($countries);
    $zones = uc_zone_option_list();

    $order->delivery_first_name = $this->randomName(12);
    $order->delivery_last_name = $this->randomName(12);
    $order->delivery_street1 = $this->randomName(12);
    $order->delivery_street2 = $this->randomName(12);
    $order->delivery_city = $this->randomName(12);
    $order->delivery_zone = array_rand($zones[$countries[$country]]);
    $order->delivery_postal_code = mt_rand(10000, 99999);
    $order->delivery_country = $country;

    $order->billing_first_name = $this->randomName(12);
    $order->billing_last_name = $this->randomName(12);
    $order->billing_street1 = $this->randomName(12);
    $order->billing_street2 = $this->randomName(12);
    $order->billing_city = $this->randomName(12);
    $order->billing_zone = array_rand($zones[$countries[$country]]);
    $order->billing_postal_code = mt_rand(10000, 99999);
    $order->billing_country = $country;

    uc_order_save($order);

    $db_order = db_query("SELECT * FROM {uc_orders} WHERE order_id = :order_id", array(':order_id' => $order->id()))->fetchObject();
    $this->assertEqual($order->delivery_first_name, $db_order->delivery_first_name);
    $this->assertEqual($order->delivery_last_name, $db_order->delivery_last_name);
    $this->assertEqual($order->delivery_street1, $db_order->delivery_street1);
    $this->assertEqual($order->delivery_street2, $db_order->delivery_street2);
    $this->assertEqual($order->delivery_city, $db_order->delivery_city);
    $this->assertEqual($order->delivery_zone, $db_order->delivery_zone);
    $this->assertEqual($order->delivery_postal_code, $db_order->delivery_postal_code);
    $this->assertEqual($order->delivery_country, $db_order->delivery_country);

    $this->assertEqual($order->billing_first_name, $db_order->billing_first_name);
    $this->assertEqual($order->billing_last_name, $db_order->billing_last_name);
    $this->assertEqual($order->billing_street1, $db_order->billing_street1);
    $this->assertEqual($order->billing_street2, $db_order->billing_street2);
    $this->assertEqual($order->billing_city, $db_order->billing_city);
    $this->assertEqual($order->billing_zone, $db_order->billing_zone);
    $this->assertEqual($order->billing_postal_code, $db_order->billing_postal_code);
    $this->assertEqual($order->billing_country, $db_order->billing_country);

    return $order;
  }

  protected function assertHookMessage($text, $message = NULL, $group = 'Other') {
    if (!isset($message)) {
      $message = $text;
    }
    return $this->assertTrue(array_search($text, $_SESSION['entity_crud_hook_test']) !== FALSE, $message, $group);
  }
}
