<?php

/**
 * @file
 * Contains \Drupal\uc_cart\Form\CheckoutReviewForm.
 */

namespace Drupal\uc_cart\Form;

use Drupal\Core\Form\FormBase;

/**
 * Gives customers the option to finish checkout or revise their information.
 */
class CheckoutReviewForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'uc_cart_checkout_review_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state, $order = NULL) {
    if (!isset($form_state['uc_order'])) {
      $form_state['uc_order'] = $order;
    }

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['back'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Back'),
      '#submit' => array(array($this, 'back')),
    );
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Submit order'),
      '#button_type' => 'primary',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    // Invoke hook_uc_order($op = 'submit') to test to make sure the order can
    // be completed... used for auto payment in uc_credit.module.
    $order = $form_state['uc_order'];
    $error = FALSE;

    // Invoke it on a per-module basis instead of all at once.
    $module_handler = \Drupal::moduleHandler();
    foreach ($module_handler->getImplementations('uc_order') as $module) {
      $function = $module . '_uc_order';
      if (function_exists($function)) {
        // $order must be passed by reference.
        $result = $function('submit', $order, NULL);

        $msg_type = 'status';
        if ($result[0]['pass'] === FALSE) {
          $error = TRUE;
          $msg_type = 'error';
        }
        if (!empty($result[0]['message'])) {
          drupal_set_message($result[0]['message'], $msg_type);
        }

        // Stop invoking the hooks if there was an error.
        if ($error) {
          break;
        }
      }
    }

    if ($error) {
      $form_state['redirect_route']['route_name'] = 'uc_cart.checkout_review';
    }
    else {
      unset($_SESSION['uc_checkout'][$order->id()]['do_review']);
      $_SESSION['uc_checkout'][$order->id()]['do_complete'] = TRUE;
      $form_state['redirect_route']['route_name'] = 'uc_cart.checkout_complete';
    }
  }

  /**
   * Returns the customer to the checkout page to edit their information.
   */
  public function back(array &$form, array &$form_state) {
    $form_state['redirect_route']['route_name'] = 'uc_cart.checkout';
  }

}
