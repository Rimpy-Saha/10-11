
(function (Drupal, drupalSettings) {
  Drupal.behaviors.loggedInBehavior = {
    attach: function (context, settings) {
      // Access the current user ID from drupalSettings.
      const userId = drupalSettings.nor_user_rules.currentUserId;
      //console.log('Current logged-in user ID:', userId);

      window.dataLayer = window.dataLayer || [];
    	window.dataLayer.push({
    	'event' : 'user',
    	'userId' : userId //this number must be replaced with an actual User ID
      })
    }
  };
})(Drupal, drupalSettings);