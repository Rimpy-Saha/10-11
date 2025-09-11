(function ($) {
    $(document).ready(function () {
      // Add event listener to the checkbox
      $('#edit-same-as-shipping').change(function () {
        // Check if the checkbox is checked
        if ($(this).prop('checked')) {
          // If checked, remove the required attribute from billing fields
          $('#edit-billing-street, #edit-billing-country, #edit-billing-state, #edit-billing-city, #edit-billing-zip').removeAttr('required');
        } else {
          // If unchecked, add the required attribute to billing fields
          $('#edit-billing-street, #edit-billing-country, #edit-billing-state, #edit-billing-city, #edit-billing-zip').attr('required', 'required');
        }
      });
    });
  })(jQuery);