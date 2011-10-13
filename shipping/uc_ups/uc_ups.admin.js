/**
 * @file
 * Utility functions to display settings summaries on vertical tabs.
 */

(function ($) {

Drupal.behaviors.upsAdminFieldsetSummaries = {
  attach: function (context) {
    $('fieldset#edit-uc-ups-credentials', context).drupalSetSummary(function(context) {
      var server = $('#edit-uc-ups-connection-address :selected', context).text().toLowerCase();
      return Drupal.t('Using UPS @role server.', { '@role': server });
    });

    $('fieldset#edit-uc-ups-markups', context).drupalSetSummary(function(context) {
      return Drupal.t('Rate markup') + ': '
        + $('#edit-uc-ups-rate-markup', context).val() + ' '
        + $('#edit-uc-ups-rate-markup-type', context).val() + '<br />'
        + Drupal.t('Weight markup') + ': '
        + $('#edit-uc-ups-weight-markup', context).val() + ' '
        + $('#edit-uc-ups-weight-markup-type', context).val();
    });

    $('fieldset#edit-uc-ups-validation', context).drupalSetSummary(function(context) {
      if ($('#edit-uc-ups-address-validation').is(':checked')) {
        return Drupal.t('Validation is enabled.');
      }
      else {
        return Drupal.t('Validation is disabled.');
      }
    });
  }
};

})(jQuery);
