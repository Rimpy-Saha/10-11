// Asynchronously load the gtag.js library
(function() {
    var gtag = document.createElement('script');
    gtag.async = true;
    gtag.src = 'https://www.googletagmanager.com/gtag/js?id=G-KWLP9XT01S';
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(gtag, s);
  })();


  // Initialize and configure Google Analytics
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'G-KWLP9XT01S');
  