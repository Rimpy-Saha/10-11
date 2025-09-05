/**
 * @file
 * Contains the clipboard js.
 */
(function ($, Drupal, drupalSettings) {
  'use strict';
  Drupal.behaviors.commerce_norreferral = {
    attach: function (context, settings) {
      // Referral point copy link.
      $('#copy-referral-link').click(function () {
        var link = $('#user-referral-link-url').text().trim();
        var url = getURLPart(link);
        console.log(url);
        document.getElementById("copy-referral-link").innerHTML = "Copied!";
        if (navigator.clipboard) {
          navigator.clipboard.writeText(url)
            .then(function () {
              $(this).text('Copied!');
            })
            .catch(function (error) {
              unsecuredCopyToClipboard(url);
            });
        } else {
          // Fallback to manual copying
          unsecuredCopyToClipboard(url);
        }
      });

      function getURLPart(text) {
        const urlPattern = /(https?:\/\/[^\s]+)/g; // Regular expression to match URLs
        const matches = text.match(urlPattern);
        return matches ? matches[0] : '';
      }

      function unsecuredCopyToClipboard(text) {
        const textArea = document.createElement("textarea");
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.focus({ preventScroll: true })
        textArea.select();
        try {
          document.execCommand('copy');
        } catch (err) {
        }
        document.body.removeChild(textArea);
      }

      // Redeem User Points checkbox
      $(".user-point-checkbox").click(function () {
        if ($(this).is(":checked")) {
          $('.form-item-coupons-user-points-redemption').hide();
          // Set the "Utilize all available points" radio button as checked
          $('input[name="coupons[user_points_redemption_type]"][value="1"]').prop('checked', true);
        } else {
          $('.form-item-coupons-user-points-redemption').hide();
        }
        // Call the function to handle radio button change event
        handleRadioChange();
      });

      // Trigger change event on radio buttons after page load
      $(document).ready(function () {
        handleRadioChange();
      });

      // Handle change event of radio buttons
      $('input[name="coupons[user_points_redemption_type]"]').change(function () {
        handleRadioChange();
      });

      // Function to handle change event of radio buttons
      function handleRadioChange() {
        if ($('input[name="coupons[user_points]"]').is(":checked")) {
          var selectedValue = $('input[name="coupons[user_points_redemption_type]"]:checked').val();
          if (selectedValue === '2') {
            $('.form-item-coupons-user-points-redemption').show();
          } else {
            $('.form-item-coupons-user-points-redemption').hide();
          }
        } else {
          $('.form-item-coupons-user-points-redemption').hide();
        }
      }
    }

  }
})(jQuery, Drupal, drupalSettings);
