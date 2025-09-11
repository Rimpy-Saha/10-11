window.addEventListener('beforeunload', function () {
    // Get the form using data-drupal-selector attribute
    let form = document.querySelector('[data-drupal-selector="views-exposed-form-ajax-filterable-products-block-1"]');
    if (!form) return; // Stop if the form is not found

    let selectedFilters = {};

    // Iterate over all form elements
    form.querySelectorAll('input, select, textarea').forEach(function (element) {
        let name = element.name;
        
        // Calculate key (if applicable) for both types of inputs
        let key = name.match(/\[([^\]]*)\]/);
        let baseName = name.split('[')[0];

        // Handle numeric, text, select, textarea, and checkboxes
        if ((element.type === 'number' || element.type === 'text' || element.tagName === 'SELECT' || element.tagName === 'TEXTAREA') && element.value) {
            if (key && key[1]) {
                selectedFilters[baseName] = selectedFilters[baseName] || {};
                selectedFilters[baseName][key[1]] = element.value;
            } else {
                selectedFilters[name] = element.value;
            }
        }

        // Handle checkboxes (only save checked ones)
        if (element.type === 'checkbox' && element.checked) {
            if (key && key[1]) {
                selectedFilters[baseName] = selectedFilters[baseName] || {};
                selectedFilters[baseName][key[1]] = element.value;
            } else {
                selectedFilters[name] = element.value;
            }
        }
    });

    // Set cookie expiration time (24 hours from now)
    let date = new Date();
    date.setTime(date.getTime() + (24 * 60 * 60 * 1000)); // 24 hours in milliseconds
    let expires = "expires=" + date.toUTCString();

    // Save the selected filters in a cookie with 24-hour expiration
    document.cookie = "selected_filters=" + JSON.stringify(selectedFilters) + "; " + expires + "; path=/;";
});

window.addEventListener('load', function () {
    // Clear the selected_filters cookie on page load
    document.cookie = "selected_filters=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
});