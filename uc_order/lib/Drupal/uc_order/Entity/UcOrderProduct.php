<?php

/**
 * @file
 * Contains \Drupal\uc_order\Entity\UcOrderProduct.
 */

namespace Drupal\uc_order\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldDefinition;
use Drupal\uc_order\UcOrderProductInterface;

/**
 * Defines the order product entity class.
 *
 * @EntityType(
 *   id = "uc_order_product",
 *   label = @Translation("Order product"),
 *   module = "uc_order",
 *   controllers = {
 *     "view_builder" = "Drupal\uc_order\UcOrderProductViewBuilder",
 *     "storage" = "Drupal\uc_order\UcOrderProductStorageController"
 *   },
 *   base_table = "uc_order_products",
 *   fieldable = TRUE,
 *   route_base_path = "admin/store/settings/orders/products",
 *   entity_keys = {
 *     "id" = "order_product_id",
 *   }
 * )
 */
class UcOrderProduct extends ContentEntityBase implements UcOrderProductInterface {

  /**
   * An array of extra data about this product.
   *
   * @var array
   */
  public $data;

  /**
   * Implements Drupal\Core\Entity\EntityInterface::id().
   */
  public function id() {
    return $this->get('order_product_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public static function postLoad(EntityStorageControllerInterface $storage_controller, array &$entities) {
    parent::postLoad($storage_controller, $entities);

    foreach ($entities as $product) {
      // @todo Move unserialize() back to the storage controller.
      $product->data = unserialize($product->data);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['order_product_id'] = FieldDefinition::create('integer')
      ->setLabel(t('Order product ID'))
      ->setDescription(t('The ordered product ID.'))
      ->setReadOnly(TRUE);
    $fields['order_id'] = FieldDefinition::create('entity_reference')
      ->setLabel(t('Order ID'))
      ->setDescription(t('The order ID.'))
      ->setSetting('target_type', 'uc_order');
    $fields['nid'] = FieldDefinition::create('entity_reference')
      ->setLabel(t('Node ID'))
      ->setDescription('The user that placed the order.')
      ->setSetting('target_type', 'node');
    $fields['title'] = FieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription('The product title.');
    $fields['model'] = FieldDefinition::create('string')
      ->setLabel(t('SKU'))
      ->setDescription('The product model/SKU.');
    $fields['qty'] = FieldDefinition::create('integer')
      ->setLabel(t('Quantity'))
      ->setDescription('The number of the product ordered.');
    $fields['cost'] = FieldDefinition::create('float')
      ->setLabel(t('Cost'))
      ->setDescription('The cost to the store for the product.');
    $fields['price'] = FieldDefinition::create('float')
      ->setLabel(t('Price'))
      ->setDescription('The price paid for the ordered product.');
    $fields['weight'] = FieldDefinition::create('float')
      ->setLabel(t('Weight'))
      ->setDescription('The physical weight.');
    $fields['weight_units'] = FieldDefinition::create('string')
      ->setLabel(t('Weight units'))
      ->setDescription('Unit of measure for the weight field.');
//    $fields['data'] = FieldDefinition::create('map')
//      ->setLabel(t('Data'))
//      ->setDescription('A serialized array of extra data.');

    return $fields;
  }

}
