<?php

/**
 * @file
 * Contains \Drupal\uc_product\Form\ProductSettingsForm.
 */

namespace Drupal\uc_product\Form;

use Drupal\system\SystemConfigFormBase;

/**
 * Configure product settings for this site.
 */
class ProductSettingsForm extends SystemConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'uc_product_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state) {
    //$config = $this->configFactory->get('uc_product.settings');

    $form['product-settings'] = array('#type' => 'vertical_tabs');

    $form['product'] = array(
      '#type' => 'details',
      '#title' => t('Product settings'),
      '#group' => 'product-settings',
      '#weight' => -10,
    );

    if (module_exists('uc_cart')) {
      $form['product']['uc_product_add_to_cart_qty'] = array(
        '#type' => 'checkbox',
        '#title' => t('Display an optional quantity field in the <em>Add to Cart</em> form.'),
        '#default_value' => variable_get('uc_product_add_to_cart_qty', FALSE),
      );
      $form['product']['uc_product_update_node_view'] = array(
        '#type' => 'checkbox',
        '#title' => t('Update product display based on customer selections'),
        '#default_value' => variable_get('uc_product_update_node_view', FALSE),
        '#description' => t('Check this box to dynamically update the display of product information such as display-price or weight based on customer input on the add-to-cart form (e.g. selecting a particular attribute option).'),
      );
    }

    $form['#submit'][] = array($this, 'submitForm');

    foreach (module_invoke_all('uc_product_feature') as $feature) {
      if (isset($feature['settings']) && function_exists($feature['settings'])) {
        $form[$feature['id']] = array(
          '#type' => 'details',
          '#title' => t('@feature settings', array('@feature' => $feature['title'])),
          '#group' => 'product-settings',
        );
        $form[$feature['id']] += $feature['settings'](array(), $form_state);

        if (function_exists($feature['settings'] . '_validate')) {
          $form['#validate'][] = $feature['settings'] . '_validate';
        }
        if (function_exists($feature['settings'] . '_submit')) {
          $form['#submit'][] = $feature['settings'] . '_submit';
        }
      }
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    variable_set('uc_product_add_to_cart_qty', $form_state['values']['uc_product_add_to_cart_qty']);
    variable_set('uc_product_update_node_view', $form_state['values']['uc_product_update_node_view']);

    parent::submitForm($form, $form_state);
  }

}