(function ($) {
    // Call the function whenever an AJAX request completes
    $(document).ajaxComplete(function(event, xhr, settings) {
        // Check if the AJAX request changed the step
        if (settings.extraData && settings.extraData._triggering_element_name === 'continue') {
            // Assuming sample_current_step_id is defined somewhere in your code
            toggleStepTwoRequired();
        }
    });

    // Assuming sample_current_step_id is defined somewhere in your code
    var sample_current_step_id = 'step2';

    function toggleStepTwoRequired() {
        // Check if the current step is step 2
        if (sample_current_step_id === 'step2') {
            // If it is step 2, set the required attribute for the step 2 fields
            $('#edit-sample-fname, #edit-sample-lname, #edit-sample-email, #edit-sample-phone, #edit-sample-company, #edit-job-title').prop('required', true);
        } else {
            // If it is not step 2, remove the required attribute for the step 2 fields
            $('#edit-sample-fname, #edit-sample-lname, #edit-sample-email, #edit-sample-phone, #edit-sample-company, #edit-job-title').removeAttr('required');
        }
    }
})(jQuery);
