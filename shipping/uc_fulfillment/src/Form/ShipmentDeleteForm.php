<?php

/**
 * @file
 * Contains \Drupal\uc_fulfillment\Form\ShipmentDeleteForm.
 */

namespace Drupal\uc_fulfillment\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Decides to release packages to be put on another shipment.
 */
class ShipmentDeleteForm extends ConfirmFormBase {

  /**
   * The order id.
   */
  protected $order_id;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'uc_fulfillment_shipment_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'uc_fulfillment.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete this shipment?');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('The shipment will be canceled and the packages it contains will be available for reshipment.');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelText() {
    return $this->t('Cancel');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return Url::fromRoute('uc_fulfillment.shipments', ['uc_order' => $this->order_id]);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $order = NULL, $package = NULL) {
    $this->order_id = $order->id();

    $form['order_id'] = array(
      '#type' => 'value',
      '#value' => $order->id(),
    );
    $form['package_id'] = array(
      '#type' => 'value',
      '#value' => $package->package_id,
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $shipment = uc_fulfillment_shipment_load($form_state->getValue('sid'));
    $methods = \Drupal::moduleHandler()->invokeAll('uc_fulfillment_method');
    if ($shipment->tracking_number &&
        isset($methods[$shipment->shipping_method]['cancel']) &&
        function_exists($methods[$shipment->shipping_method]['cancel'])) {
      $result = call_user_func($methods[$shipment->shipping_method]['cancel'], $shipment->tracking_number);
      if ($result) {
        uc_fulfillment_shipment_delete($form_state->getValue('sid'));
      }
      else {
        drupal_set_message($this->t('The shipment %tracking could not be canceled with %carrier. To delete it anyway, remove the tracking number and try again.', ['%tracking' => $shipment->tracking_number, '%carrier' => $shipment->carrier]));
      }
    }
    else {
      uc_fulfillment_shipment_delete($form_state->getValue('sid'));
    }

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
