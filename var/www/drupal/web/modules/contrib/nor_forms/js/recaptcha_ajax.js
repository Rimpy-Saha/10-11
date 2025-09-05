// (function($) {
//   Drupal.behaviors.accountIssuesForm = {
//     attach: function(context, settings) {
//       var $form = $('#account-issues-form', 
//         '#business-opportunities-form', 
//         '#contact-distributor-form', 
//         '#conference-inquiries-form', 
//         '#file-complaint-form', 
//         '#web-invoice-form', 
//         '#job-application-form', 
//         '#newsletter-subscription-form', 
//         'order-status-form', 
//         '#product-issues-form', 
//         '#public-relations-form', 
//         '#web-return-form', 
//         '#service-consultation-form', 
//         '#service-issues-form', 
//         '#sponsorship-inquiry-form', 
//         '#technical-support-form', 
//         '#website-issues-form', context);
      

//       if ($form.length) {
//         // Clear any existing content within the container element
//         $('#edit-google-recaptcha').html('');

//         // Initialize reCAPTCHA widget after the API is loaded
//         console.log('Initializing reCAPTCHA...');
//         var captchaResponse = '';
//         var captchaWidget;

//         grecaptcha.ready(function() {
//           // Execute reCAPTCHA with the correct site key
//           captchaWidget = grecaptcha.render('edit-google-recaptcha', {
//             'sitekey': '6LdaR74rAAAAAMzYz3lQ6YnxPU0c1E0-NyeiTVFy',
//             'callback': function(response) {
//               captchaResponse = response;
//               console.log('reCAPTCHA token set:', captchaResponse);
//             }
//           });
//         });

//         // Handle form submission
//         $form.on('submit', function(event) {
//           // Check if the reCAPTCHA widget is filled out
//           if (!captchaResponse) {
//             // If not filled out, prevent the default form submission behavior
//             event.preventDefault();
//             // Display an error message to prompt the user to fill out the reCAPTCHA
//             console.log('reCAPTCHA response is empty. Please complete the reCAPTCHA verification.');
//             // Optionally, you could also focus on the reCAPTCHA widget to draw the user's attention to it
//             grecaptcha.reset(captchaWidget); // Reset reCAPTCHA widget
//             return false;
//           }

//           console.log('Form submitted, checking reCAPTCHA response...');
//           console.log('Form data with reCAPTCHA response:', $form.serialize() + '&g-recaptcha-response=' + captchaResponse);

//           // Perform Ajax submission
//           console.log('Performing Ajax submission...');
//           $.ajax({
//             url: $form.attr('action'),
//             method: 'POST',
//             data: $form.serialize() + '&g-recaptcha-response=' + captchaResponse,
//             success: function(response) {
//               // Handle success response
//               console.log('Form submitted successfully:', response);
//             },
//             error: function(xhr, status, error) {
//               // Handle error response
//               console.error('Form submission error:', error);

//               // Reset reCAPTCHA widget
//               grecaptcha.reset(captchaWidget);
//             }
//           });
//         });
//       }
//     }
//   };
//   window.renderCaptcha = function() { // function we can call to render the captcha as needed
//     var recaptchaElement = document.querySelector('.g-recaptcha');
//     captchaWidget = grecaptcha.render(recaptchaElement, {
//       'sitekey': '6LdaR74rAAAAAMzYz3lQ6YnxPU0c1E0-NyeiTVFy',
//       'callback': function(response) {
//         captchaResponse = response;
//       }
//     });
//   }
// })(jQuery);


(function($) {
  Drupal.behaviors.accountIssuesForm = {
    attach: function(context, settings) {
      var $form = $('#account-issues-form', context);

      if ($form.length) {
        // Ensure there's a hidden input for the token
        if ($('#g-recaptcha-response').length === 0) {
          $('<input>').attr({
            type: 'hidden',
            id: 'g-recaptcha-response',
            name: 'g-recaptcha-response'
          }).appendTo($form);
        }

        $form.on('submit', function(event) {
          event.preventDefault(); // prevent normal submit

          grecaptcha.ready(function() {
            grecaptcha.execute('6LdaR74rAAAAAMzYz3lQ6YnxPU0c1E0-NyeiTVFy', {action: 'account_issues_form'}).then(function(token) {
              $('#g-recaptcha-response').val(token);

              // now submit the form via Ajax
              $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: $form.serialize(),
                success: function(response) {
                  console.log('Form submitted successfully:', response);
                },
                error: function(xhr, status, error) {
                  console.error('Form submission error:', error);
                }
              });
            });
          });
        });
      }
    }
  };
})(jQuery);


// (function($) {
//   Drupal.behaviors.multiFormsRecaptcha = {
//     attach: function(context, settings) {
//       // var formIds = [
//       //   'account-issues-form',
//       //   'business-opportunities-form',
//       //   'contact-distributor-form',
//       //   'conference-inquiries-form',
//       //   'file-complaint-form',
//       //   'web-invoice-form',
//       //   'job-application-form',
//       //   'newsletter-subscription-form',
        // 'order-status-form',
        // 'product-issues-form',
        // 'public-relations-form',
        // 'web-return-form',
        // 'service-consultation-form',
        // 'service-issues-form',
        // 'sponsorship-inquiry-form',
        // 'technical-support-form',
        // 'website-issues-form'
//       // ];
//       var $formIds = $('#account-issues-form', '#business-opportunities-form', '#contact-distributor-form', '#conference-inquiries-form', '#file-complaint-form', '#web-invoice-form', '#job-application-form',  context);

//       formIds.forEach(function(formId) {
//         var $form = $('#' + formId, context);

//         if ($form.length) {
//           // Clear any existing content within the container element
//           $('#edit-google-recaptcha').html('');

//           // Initialize reCAPTCHA widget after the API is loaded
//           console.log('Initializing reCAPTCHA for ' + formId + '...');
//           var captchaResponse = '';
//           var captchaWidget;

//           grecaptcha.ready(function() {
//             // Execute reCAPTCHA with the correct site key
//             captchaWidget = grecaptcha.render('edit-google-recaptcha', {
//               'sitekey': '6Lcr4u0pAAAAAGj32knXkUzuHAXzj3CoAhtbJ1t5',
//               'callback': function(response) {
//                 captchaResponse = response;
//                 console.log('reCAPTCHA token set for ' + formId + ':', captchaResponse);
//               }
//             });
//           });

//           // Handle form submission
//           $form.on('submit', function(event) {
//             // Check if the reCAPTCHA widget is filled out
//             if (!captchaResponse) {
//               // If not filled out, prevent the default form submission behavior
//               event.preventDefault();
//               // Display an error message to prompt the user to fill out the reCAPTCHA
//               console.log('reCAPTCHA response is empty for ' + formId + '. Please complete the reCAPTCHA verification.');
//               // Optionally, you could also focus on the reCAPTCHA widget to draw the user's attention to it
//               grecaptcha.reset(captchaWidget); // Reset reCAPTCHA widget
//               return false;
//             }

//             console.log('Form submitted, checking reCAPTCHA response for ' + formId + '...');
//             console.log('Form data with reCAPTCHA response:', $form.serialize() + '&g-recaptcha-response=' + captchaResponse);

//             // Perform Ajax submission
//             console.log('Performing Ajax submission for ' + formId + '...');
//             $.ajax({
//               url: $form.attr('action'),
//               method: 'POST',
//               data: $form.serialize() + '&g-recaptcha-response=' + captchaResponse,
//               success: function(response) {
//                 // Handle success response
//                 console.log('Form submitted successfully for ' + formId + ':', response);
//               },
//               error: function(xhr, status, error) {
//                 // Handle error response
//                 console.error('Form submission error for ' + formId + ':', error);

//                 // Reset reCAPTCHA widget
//                 grecaptcha.reset(captchaWidget);
//               }
//             });
//           });
//         }
//       });
//     }
//   };
// })(jQuery);




// (function($) {
//     Drupal.behaviors.accountIssuesForm = {
//       attach: function(context, settings) {
//         var $form = $('#account-issues-form', context);
  
//         if ($form.length) {
//           // Clear any existing content within the container element
//           $('#edit-google-recaptcha').html('');
  
//           // Initialize reCAPTCHA widget after the API is loaded
//           console.log('Initializing reCAPTCHA...');
//           var captchaResponse = '';
//           var captchaWidget;
  
//           grecaptcha.ready(function() {
//             // Execute reCAPTCHA with the correct site key
//             captchaWidget = grecaptcha.render('edit-google-recaptcha', {
//               'sitekey': '6Lcr4u0pAAAAAGj32knXkUzuHAXzj3CoAhtbJ1t5',
//               'callback': function(response) {
//                 captchaResponse = response;
//                 console.log('reCAPTCHA token set:', captchaResponse);
//               }
//             });
//           });
  
//           // Handle form submission
//           $form.on('submit', function(event) {
//             // Check if the reCAPTCHA widget is filled out
//             if (!captchaResponse) {
//               // If not filled out, prevent the default form submission behavior
//               event.preventDefault();
//               // Display an error message to prompt the user to fill out the reCAPTCHA
//               console.log('reCAPTCHA response is empty. Please complete the reCAPTCHA verification.');
//               // Optionally, you could also focus on the reCAPTCHA widget to draw the user's attention to it
//               grecaptcha.reset(captchaWidget); // Reset reCAPTCHA widget
//               return false;
//             }
  
//             console.log('Form submitted, checking reCAPTCHA response...');
//             console.log('Form data with reCAPTCHA response:', $form.serialize() + '&g-recaptcha-response=' + captchaResponse);
  
//             // Perform Ajax submission
//             console.log('Performing Ajax submission...');
//             $.ajax({
//               url: $form.attr('action'),
//               method: 'POST',
//               data: $form.serialize() + '&g-recaptcha-response=' + captchaResponse,
//               success: function(response) {
//                 // Handle success response
//                 console.log('Form submitted successfully:', response);
//               },
//               error: function(xhr, status, error) {
//                 // Handle error response
//                 console.error('Form submission error:', error);
  
//                 // Reset reCAPTCHA widget
//                 grecaptcha.reset(captchaWidget);
//               }
//             });
//           });
//         }
//       }
//     };
//   })(jQuery);
  
  