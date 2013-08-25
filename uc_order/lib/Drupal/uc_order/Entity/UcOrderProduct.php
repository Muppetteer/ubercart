<?php

/**
 * @file
 * Definition of Drupal\uc_order\Entity\UcOrderProduct.
 */

namespace Drupal\uc_order\Entity;

use Drupal\Core\Entity\Entity;
use Drupal\Core\Entity\Annotation\EntityType;
use Drupal\Core\Annotation\Translation;

/**
 * Defines the order product entity class.
 *
 * @EntityType(
 *   id = "uc_order_product",
 *   label = @Translation("Order product"),
 *   module = "uc_order",
 *   controllers = {
 *     "render" = "Drupal\uc_order\UcOrderProductRenderController",
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
class UcOrderProduct extends Entity {

  /**
   * The order product ID.
   *
   * @var integer
   */
  public $order_product_id;

  /**
   * The order ID.
   *
   * @var integer
   */
  public $order_id;

  /**
   * The node ID of this product.
   *
   * @var integer
   */
  public $nid;

  /**
   * The title of this product.
   *
   * @var string
   */
  public $title;

  /**
   * The SKU of this product.
   *
   * @var string
   */
  public $model;

  /**
   * The quantity of this product.
   *
   * @var integer
   */
  public $qty;

  /**
   * The cost of this product.
   *
   * @var float
   */
  public $cost;

  /**
   * The price of this product.
   *
   * @var float
   */
  public $price;

  /**
   * The weight of this product.
   *
   * @var float
   */
  public $weight;

  /**
   * The units of weight of this product.
   *
   * @var string
   */
  public $weight_units;

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
    return $this->order_product_id;
  }

}