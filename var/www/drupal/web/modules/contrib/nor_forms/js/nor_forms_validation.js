(function ($, Drupal, once) {

Drupal.behaviors.clientSideValidationAjax = {
    attach(context) {
      $(context).on("drupalAjaxFormValidate", (event) => {
        const form = $(event.target).closest("form")[0];
        if (!form.checkValidity()) {
          // This is the magic function that displays the validation errors to the user
          form.reportValidity();
          return false;
        }
        return true;
      });
    },
};

})(jQuery, Drupal, once);