document.addEventListener('DOMContentLoaded', function () {
    const subtractButtons = document.querySelectorAll('.quantity-button.subtract');
    const addButtons = document.querySelectorAll('.quantity-button.add');

    
    const quantityDivs = document.querySelectorAll('div[id^="edit-edit-quantity"]');

    quantityDivs.forEach(function (div) {
        
        div.setAttribute('disabled', 'disabled');

        
        const quantityField = div.closest('.quantity-field');
        if (quantityField) {
    
            const childElements = quantityField.querySelectorAll('*');
            childElements.forEach(function (child) {
                child.setAttribute('disabled', 'disabled');
            });
        }
    });

    // Add event listeners for quantity buttons
    subtractButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const inputField = button.parentNode.querySelector('input.quantity-edit-input');
            if (inputField) {
                let currentValue = parseInt(inputField.value);
                if (currentValue > 1) {
                    // Decrease the value by 1
                    inputField.value = currentValue - 1;
                    console.log("New value after subtraction:", inputField.value);
                }
            } else {
                console.error('Input field not found');
            }
        });
    });

    addButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const inputField = button.parentNode.querySelector('input.quantity-edit-input');
            if (inputField) {
                let currentValue = parseInt(inputField.value);
                // Increase the value by 1
                inputField.value = currentValue + 1;
                console.log("New value after addition:", inputField.value);
            } else {
                console.error('Input field not found');
            }
        });
    });
});
