document.addEventListener("DOMContentLoaded", function () {

    // if(drupalSettings.nor_forms.user_country != "US" || drupalSettings.nor_forms.user_country != "CA" ){
    //     return;
    // }
    // Function to get URL parameters
    function getQueryParam(param) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param) || ''; // Return empty string if not found
    }

    // Function to get the referrer URL
    function getReferrer() {
        return document.referrer || 'Direct Visit'; // If no referrer, set "Direct Visit"
    }

    // List of social media sources to check
    const socialMediaSources = ["x", "linkedin", "facebook", "instagram", "bluesky", "youtube" , "twitter"];

    // Get the current URL path
    let currentPath = window.location.pathname.toLowerCase();


    if (currentPath.includes("/request-sample-form")) {
        return; //STOP the script here
    }

    if (currentPath.includes("/webinars/")) {
        return; // STOP the script here
    }

    // Function to show the popup
    function showWelcomePopup() {
        let popup = document.getElementById("welcome-bubble");
        if (popup) {
            popup.style.display = "flex"; // Show the popup
        } else {
        }
    }

    // Function to close the popup when clicking the close button only
    function closeWelcomePopup() {
        let popup = document.getElementById("welcome-bubble");
        if (popup) {
            popup.style.display = "none"; // Hide the popup
        }
    }

    // Detect UTM source & show popup if from social media
    let utmSource = getQueryParam("utm_source");

    if (utmSource && socialMediaSources.includes(utmSource.toLowerCase())) {
        showWelcomePopup();
    } else {
    }

    // Attach event listener ONLY for the close button
    let closeButton = document.getElementById("close-bubble");

    if (closeButton) {
        closeButton.addEventListener("click", closeWelcomePopup);
    } else {
    }

    // Insert UTM parameters into hidden form fields (if they exist)
    if (document.getElementById("utm_source")) {
        document.getElementById("utm_source").value = getQueryParam("utm_source");
    }

    if (document.getElementById("utm_medium")) {
        document.getElementById("utm_medium").value = getQueryParam("utm_medium");
    }

    if (document.getElementById("referrer_page")) {
        document.getElementById("referrer_page").value = getReferrer();
    }
});

function copyToClipboard() {
  var promoText = "EMAIL10";
  navigator.clipboard.writeText(promoText).then(function() {
      var promoElement = document.getElementById("promo-code");
      promoElement.innerText = "Copied!";
      setTimeout(function() {
          promoElement.innerText = "EMAIL10";
      }, 2000);
  }).catch(function(err) {
      console.error("Failed to copy: ", err);
  });
}