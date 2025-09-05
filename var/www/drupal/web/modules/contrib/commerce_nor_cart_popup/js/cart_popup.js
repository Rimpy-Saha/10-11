(function ($, Drupal) {
    Drupal.behaviors.cartPopup = {
      attach: function (context, settings) {
        if (settings.showCartPopup) {
            console.log(context);
            console.log(settings);
            console.log( $('.c-layout-header .c-mega-menu .c-cart-toggler-wrapper'));
            console.log( document.querySelector('.c-layout-header .c-mega-menu .c-cart-toggler-wrapper'));
            if($('.c-cart-number').text()!=0){
                $('.c-cart-menu.desktop:not(.mobile)').addClass('desktop-rimpy');
                document.getElementsByClassName("dialog-off-canvas-main-canvas")[0].style.marginRight = "390px";
            }
            // Optionally, clear the session flag if needed
            //   $.ajax({
            //     url: '/clear-cart-popup-flag',
            //     method: 'POST',
            //   });
        }
      }
    };
  })(jQuery, Drupal);
  