  document.querySelectorAll('.cart-item__svg-remove').forEach(function (button) {
    button.addEventListener('click', function () {
      // Find the original remove button in the same cart item and trigger a click
      this.closest('.cart-item').querySelector('.delete-order-item').click();
    });
  });


  document.addEventListener("DOMContentLoaded", function () {
    // Select all quantity input fields
    const quantityInputs = document.querySelectorAll('form[data-drupal-selector^="views-form-commerce-cart-form-block-"] input[data-drupal-selector^="edit-edit-quantity-"]');

    // Get the update cart button
    const updateCartButton = document.querySelector('form[data-drupal-selector^="views-form-commerce-cart-form-block-"] input[data-drupal-selector="edit-submit"]');

    // Debounce delay time in milliseconds
    const debounceDelay = 500; // Adjust this value as needed

    let debounceTimeout;

    quantityInputs.forEach((input) => {
      input.addEventListener("change", function () {
        // Clear the previous timeout
        clearTimeout(debounceTimeout);

        // Set a new timeout to trigger the form submit
        debounceTimeout = setTimeout(() => {
          updateCartButton.click();
        }, debounceDelay);
      });
    });


    document.querySelectorAll('.custom-quantity').forEach(function (quantityWrapper) {
      const decreaseBtn = quantityWrapper.querySelector('.quantity-decrease');
      const increaseBtn = quantityWrapper.querySelector('.quantity-increase');
      const customInput = quantityWrapper.querySelector('.custom-quantity-input');
      const originalInput = quantityWrapper.closest('.cart-item__quantity').querySelector('form[data-drupal-selector^="views-form-commerce-cart-form-block-"] input[data-drupal-selector^="edit-edit-quantity-"]');
  
      // Function to update the original field
      const updateOriginalField = () => {
        if(originalInput){ // need this because if product is auto added as part of promo, it will not have an editable quantity input element
          originalInput.value = customInput.value;
          originalInput.dispatchEvent(new Event('change')); // Trigger the change event
        }
      };
  
      // Decrease quantity
      decreaseBtn.addEventListener('click', function () {
        let currentValue = parseInt(customInput.value);
        if (currentValue > 0) {
          customInput.value = currentValue - 1;
          updateOriginalField();
        }
      });
  
      // Increase quantity
      increaseBtn.addEventListener('click', function () {
        let currentValue = parseInt(customInput.value);
        customInput.value = currentValue + 1;
        updateOriginalField();
      });
  
      // Update original field on input change
      customInput.addEventListener('change', function () {
        updateOriginalField();
      });
    });



  });