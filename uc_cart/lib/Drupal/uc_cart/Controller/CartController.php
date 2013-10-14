<?php

/**
 * @file
 * Contains \Drupal\uc_cart\Controller\CartController.
 */

namespace Drupal\uc_cart\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller routines for the shopping cart.
 */
class CartController extends ControllerBase {

  /**
   * Displays the cart view page.
   *
   * Show the products in the cart with a form to adjust cart contents or go to
   * checkout.
   */
  public function listing() {
    // Load the array of shopping cart items.
    $items = uc_cart_get_contents();

    // Display the empty cart page if there are no items in the cart.
    if (empty($items)) {
      return array(
        '#theme' => 'uc_empty_cart',
      );
    }

    // Add a custom cart breadcrumb if specified.
    if (($text = variable_get('uc_cart_breadcrumb_text', '')) !== '') {
      $link = l($text, variable_get('uc_cart_breadcrumb_url', '<front>'));
      drupal_set_breadcrumb(array($link));
    }

    return drupal_get_form('uc_cart_view_form', $items);
  }

}