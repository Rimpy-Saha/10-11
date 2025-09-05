//numbers for checkout blocks
document.addEventListener("DOMContentLoaded", function () {
    function updateLegendNumbers() {
        let allLegends = document.querySelectorAll('.layout-region.billing-information > fieldset > legend, .layout-region.billing-information #shipping-information-wrapper > fieldset > legend');
        let visibleIndex = 1; 
        allLegends.forEach((legend) => {
            if (legend.offsetParent !== null) {
                legend.setAttribute('data-number', visibleIndex);
                visibleIndex++;
            }
        });
    }

    updateLegendNumbers();

    jQuery(document).ajaxComplete(function () {
        updateLegendNumbers();
    });
});