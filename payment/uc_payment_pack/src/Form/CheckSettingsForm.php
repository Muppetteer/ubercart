<?php

/**
 * @file
 * Contains \Drupal\uc_payment_pack\Form\CheckSettingsForm.
 */

namespace Drupal\uc_payment_pack\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form for recording a received check and expected clearance date.
 */
class CheckSettingsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'uc_payment_pack_check_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $check_config = \Drupal::config('uc_payment_pack.check.settings');

    $form['check_address_info'] = array(
      '#markup' => '<div>' . t('Set the mailing address to display to customers who choose this payment method during checkout.') . '</div>',
    );
    $form['uc_check_mailing_name'] = array(
      '#type' => 'textfield',
      '#title' => t('Contact'),
      '#description' => t('Direct checks to a person or department.'),
      '#default_value' => $check_config->get('mailing_name'),
    );
    $form['uc_check_address'] = array(
      '#type' => 'uc_address',
      '#default_value' => array(
        'uc_check_mailing_company' => $check_config->get('mailing_company'),
        'uc_check_mailing_street1' => $check_config->get('mailing_street1'),
        'uc_check_mailing_street2' => $check_config->get('mailing_street2'),
        'uc_check_mailing_city' => $check_config->get('mailing_city'),
        'uc_check_mailing_zone' => $check_config->get('mailing_zone'),
        'uc_check_mailing_country' => $form_state->hasValue('uc_check_mailing_country') ? $form_state->getValue('uc_check_mailing_country') : $check_config->get('uc_check_mailing_country'),
        'uc_check_mailing_postal_code' => $check_config->get('mailing_postal_code'),
      ),
      '#required' => FALSE,
      '#key_prefix' => 'uc_check_mailing',
    );
    $form['uc_check_policy'] = array(
      '#type' => 'textarea',
      '#title' => t('Check payment policy', [], ['context' => 'cheque']),
      '#description' => t('Instructions for customers on the checkout page.'),
      '#default_value' => $check_config->get('policy'),
      '#rows' => 3,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $check_config = $this->configFactory()->getEditable('uc_payment_pack.check.settings');
    $check_config
      ->set('mailing_name', $form_state->getValue('uc_check_mailing_name'))
      ->set('mailing_company', $form_state->getValue('uc_check_mailing_company'))
      ->set('mailing_street1', $form_state->getValue('uc_check_mailing_street1'))
      ->set('mailing_street2', $form_state->getValue('uc_check_mailing_street2'))
      ->set('mailing_city', $form_state->getValue('uc_check_mailing_city'))
      ->set('mailing_zone', $form_state->getValue('uc_check_mailing_zone'))
      ->set('mailing_country', $form_state->getValue('uc_check_mailing_country'))
      ->set('mailing_postal_code', $form_state->getValue('uc_check_mailing_postal_code'))
      ->set('policy', $form_state->getValue('uc_check_policy'))
      ->save();
  }
}