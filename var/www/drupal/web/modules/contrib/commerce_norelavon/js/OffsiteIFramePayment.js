
(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.elavonForm = {
    attach: function (context) {

      var options = drupalSettings.elavon.transactionData;
      $('#edit-actions-next').on('click', function () {
        // This function will be triggered when the button with ID 'edit-actions-next'("Proceed to Secure Elavon Payment") is clicked

        var token = options.token;
        var returnUrl = options.redirect_url;

        // Function to open a lightbox
        function open_elavon_lightbox() {

          // PaymentFields object || Input to Elavon Converge API
          var paymentFields = { ssl_txn_auth_token: token };

          // callback functions
          var callback = {
            onError: function (error) {
              var response = { elavon_status: 'error', ssl_result_message: error };
              $.post(returnUrl, response, function(){ location.reload(); });
            },
            onCancelled: function () {
              var response = [];
              response['elavon_status'] = 'cancelled';
              $.post(returnUrl, response, function(){ location.reload(); });
            },
            onDeclined: function (response) {
              response['elavon_status'] = 'declined';
              $.post(returnUrl, response, function(){ location.reload(); });
            },
            onApproval: function (response) {
              response['elavon_status'] = 'approval';
              $.post(returnUrl, response, function(){ location.reload(); });
            }
          };

          // Ensure PayWithConverge is defined before calling its methods
          if (typeof PayWithConverge !== 'undefined') {
            PayWithConverge.open(paymentFields, callback);
          } else { console.error('PayWithConverge is not defined.'); }
        }

        // Call the elavon lightbox function
        open_elavon_lightbox();
        return false;
      });

    }
  };

})(jQuery, Drupal, drupalSettings);