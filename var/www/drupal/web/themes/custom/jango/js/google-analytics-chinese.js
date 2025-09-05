// Asynchronously load the gtag.js library for the Spanish version
(function() {
  var gtag = document.createElement('script');
  gtag.async = true;
  gtag.src = 'https://www.googletagmanager.com/gtag/js?id=G-HM5PV2GE58';
  var s = document.getElementsByTagName('script')[0];
  s.parentNode.insertBefore(gtag, s);
})();

// Initialize and configure Google Analytics for the Spanish version
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', 'G-HM5PV2GE58');