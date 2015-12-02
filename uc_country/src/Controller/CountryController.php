<?php

/**
 * @file
 * Contains \Drupal\uc_country\Controller\CountryController.
 */

namespace Drupal\uc_country\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\uc_country\Entity\Country;


/**
 * Utility functions to enable and disable country configuration entities.
 */
class CountryController extends ControllerBase {

  /**
   * Enables a country.
   *
   * @param \Drupal\uc_country\Entity\Country $uc_country
   *   The country object to enable.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response to the country listing page.
   */
  public function enableConfig(Country $uc_country) {
    $uc_country->enable()->save();

    drupal_set_message($this->t('The country %label has been enabled.', ['%label' => $uc_country->label()]));

    return $this->redirect('entity.uc_country.collection');
  }

  /**
   * Disables a country.
   *
   * @param \Drupal\uc_country\Entity\Country $uc_country
   *   The country object to disable.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response to the country listing page.
   */
  public function disableConfig(Country $uc_country) {
    $uc_country->disable()->save();

    drupal_set_message($this->t('The country %label has been disabled.', ['%label' => $uc_country->label()]));

    return $this->redirect('entity.uc_country.collection');
  }

  /**
   * Returns a list of all installed/available countries.
   *
   * @return array
   *   Associative array keyed by the country's ISO 3166-1 alpha_2 country
   *   code and containing the translated ISO 3166-1 country name.
   */
  public static function countryOptionsCallback() {
    return \Drupal::service('country_manager')->getEnabledList();
  }

  /**
   * Helper function to return zone options, grouped by country.
   */
  public static function zoneOptionsCallback() {
    $options = array();
    $countries = $this->entityManager()->getStorage('uc_country')->loadByProperties(['status' => TRUE]);
    foreach ($countries as $country) {
      if (!empty($country->getZones())) {
        $options[$this->t($country->name)] = $country->getZones();
      }
    }
    uksort($options, 'strnatcasecmp');

    return $options;
  }

}