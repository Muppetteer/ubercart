<?php

/**
 * @file
 * Contains Drupal\uc_store\Tests\CountryTest.
 */

namespace Drupal\uc_store\Tests;

use Drupal\uc_store\Tests\UbercartTestBase;

class CountryTest extends UbercartTestBase {

  public static function getInfo() {
    return array(
      'name' => 'Country functionality',
      'description' => 'Import, edit, and remove countries and their settings.',
      'group' => 'Ubercart',
    );
  }

  /**
   * Test import/enable/disable/remove of Country information files.
   */
  function testCountries() {
    $import_file = 'belgium_56_3.cif';
    $country_name = 'Belgium';
    $country_code = 'BEL';

    $this->drupalLogin($this->adminUser);

    $this->drupalGet('admin/store/settings/countries');
    $this->assertRaw(
      '<option value="' . $import_file . '">' . $import_file . '</option>',
      t('Ensure country file is not imported yet.')
    );

    $edit = array(
      'import_file[]' => array($import_file => $import_file),
    );
    $this->drupalPostForm(
      'admin/store/settings/countries',
      $edit,
      t('Import')
    );
    $this->assertText(
      t('Country file @file imported.', array('@file' => $import_file)),
      t('Country was imported successfully.')
    );
    $this->assertText(
      $country_code,
      t('Country appears in the imported countries table.')
    );
    $this->assertNoRaw(
      '<option value="' . $import_file . '">' . $import_file . '</option>',
      t('Country does not appear in list of files to be imported.')
    );

    // Have to pick the right one here!
    $this->clickLink(t('disable'));
    $this->assertText(
      t('@name disabled.', array('@name' => $country_name)),
      t('Country was disabled.')
    );

    $this->clickLink(t('enable'));
    $this->assertText(
      t('@name enabled.', array('@name' => $country_name)),
      t('Country was enabled.')
    );

    $this->clickLink(t('remove'));
    $this->assertText(
      t('Are you sure you want to remove @name from the system?', array('@name' => $country_name)),
      t('Confirm form is displayed.')
    );

    $this->drupalPostForm(
      'admin/store/settings/countries/56/remove',
      array(),
      t('Remove')
    );
    $this->assertText(
      t('@name removed.', array('@name' => $country_name)),
      t('Country removed.')
    );
    $this->assertRaw(
      '<option value="' . $import_file . '">' . $import_file . '</option>',
      t('Ensure country file is not imported yet.')
    );
    $this->assertNoText(
      $country_code,
      t('Country does not appear in imported countries table.')
    );
  }
}
