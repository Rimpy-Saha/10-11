// // document.addEventListener('DOMContentLoaded', function () {
// //     // Select all pagination links within the pager class
// //     var pagerLinks = document.querySelectorAll('.pagers a');
  
// //     pagerLinks.forEach(function (link) {
// //       link.addEventListener('click', function (e) {
// //         e.preventDefault(); // Prevent the default link behavior
  
// //         var href = this.getAttribute('href'); // Get the href attribute of the clicked link
  
// //         // Update the browser URL using history.pushState
// //         history.pushState(null, '', href);
  
// //         // Load the content via AJAX
// //         // fetch(href)
// //         //   .then(response => response.text())
// //         //   .then(data => {
// //         //     // Parse the HTML response and extract the updated content
// //         //     var parser = new DOMParser();
// //         //     var doc = parser.parseFromString(data, 'text/html');
  
// //         //     // Assuming the AJAX response contains the updated view content
// //         //     var newContent = doc.querySelector('.view-content');
  
// //         //     // Update the current page content with the new content
// //         //     document.querySelector('.view-content').innerHTML = newContent.innerHTML;
// //         //   })
// //         //   .catch(error => console.error('Error loading page:', error));
// //       });
// //     });
// //   });
  


//   /* capture event */

//   document.addEventListener('DOMContentLoaded', function() {
//     // Save filter values in session storage
//     var filterForm = document.querySelector('form[data-drupal-selector^="views-exposed-form-duplicate-of-filterable-product-variations-block-"]');

//     // Save filter values in session storage
//     filterForm.addEventListener('change', function() {
//         var formData = new FormData(filterForm);

//         let filterState = JSON.parse(sessionStorage.getItem('filterState')) || {};
//         formData.forEach((value, key) => {
//             filterState[key] = value;
//         });
//         sessionStorage.setItem('filterState', JSON.stringify(filterState));
//     });

//     // Save pagination state in session storage
//     var paginationLinks = document.querySelectorAll('.pager__item a');
//     paginationLinks.forEach(link => {
//         link.addEventListener('click', function(event) {
//             var urlParams = new URLSearchParams(link.search);
//             var page = urlParams.get('page');
//             sessionStorage.setItem('currentPage', page);
//         });
//     });

//     // Save current filter and page state before submitting the form
//     var addToCartButtons = document.querySelectorAll('.add-to-cart-button'); // Adjust selector as needed
//     addToCartButtons.forEach(button => {
//         button.addEventListener('click', function() {
//             console.log('change');
//             var formData = new FormData(filterForm);
//             let filterState = JSON.parse(sessionStorage.getItem('filterState')) || {};
//             formData.forEach((value, key) => {
//                 filterState[key] = value;
//             });
//             sessionStorage.setItem('filterState', JSON.stringify(filterState));

//             var activePage = document.querySelector('.pager__item.is-active a');
//             if (activePage) {
//                 var urlParams = new URLSearchParams(activePage.search);
//                 var page = urlParams.get('page');
//                 sessionStorage.setItem('currentPage', page-1);
//             }
//         });
//     });
// });


// /* render event */
//   document.addEventListener('DOMContentLoaded', function() {
//     var filterForm = document.querySelector('form[data-drupal-selector^="views-exposed-form-duplicate-of-filterable-product-variations-block-"]');

//     // Restore filter values
//     var filterState = sessionStorage.getItem('filterState');
//     if (filterState) {
//         var filters = JSON.parse(filterState);
//         Object.keys(filters).forEach(key => {
//             var input = filterForm.querySelector(`[name="${key}"]`);
//             if (input) {
//                 if (input.type === 'checkbox' || input.type === 'radio') {
//                     input.checked = input.value === filters[key];
//                 } else {
//                     input.value = filters[key];
//                 }
//             }
//         });
//         // Trigger a change event to refresh the view
//         filterForm.dispatchEvent(new Event('change'));
//     }

//     // Restore pagination state
//     var currentPage = sessionStorage.getItem('currentPage');
//     if (currentPage) {
//         var activePageLink = document.querySelector(`.pager__item a[href*="page=${currentPage}"]`);
//         if (activePageLink) {
//             activePageLink.click();
//         }
//     }
// });

var test;


// Function to attach event listeners to the view
function attachViewListeners() {
    var view = document.querySelector('#rimpy_block');
    console.log(view);
    if (!view) {
      return;
    }
  
    var filterForm = view.querySelector('form[data-drupal-selector^="views-exposed-form-duplicate-of-filterable-product-variations-block-"]');
    if (filterForm) {
      // Save filter values in session storage
      filterForm.addEventListener('change', function() {
        var formData = new FormData(filterForm);
        console.log('change ');
        let filterState = {};
        formData.forEach((value, key) => {
          filterState[key] = value;
        });
        sessionStorage.setItem('filterState', JSON.stringify(filterState));        
        sessionStorage.removeItem('currentPage');
      });
    }
  
    // Save pagination state in session storage
    var paginationLinks = view.querySelectorAll('.pager__item a');
    paginationLinks.forEach(link => {
      link.addEventListener('click', function(event) {
        var urlParams = new URLSearchParams(link.search);
        var page = urlParams.get('page');
        sessionStorage.setItem('currentPage', page);
      });
    });
  
    // Save current filter and page state before submitting the form
    var addToCartButtons = view.querySelectorAll('.add-to-cart-button'); // Adjust selector as needed
    addToCartButtons.forEach(button => {
      button.addEventListener('click', function() {
        var formData = new FormData(filterForm);
        let filterState = JSON.parse(sessionStorage.getItem('filterState')) || {};
        formData.forEach((value, key) => {
          filterState[key] = value;
        });
        sessionStorage.setItem('filterState', JSON.stringify(filterState));
  
        var activePage = view.querySelector('.pager__item.is-active a');
        if (activePage) {
          var urlParams = new URLSearchParams(activePage.search);
          var page = urlParams.get('page');
          sessionStorage.setItem('currentPage', page - 1);
        }
      });
    });
  }
  
  // Function to restore filter and pagination states
  function restoreFilterStates() {
    var filterForm = document.querySelector('form[data-drupal-selector^="views-exposed-form-duplicate-of-filterable-product-variations-block-"]');
  
    // Restore filter values
    var filterState = sessionStorage.getItem('filterState');
    if (filterState && filterForm) {
      var filters = JSON.parse(filterState);
      Object.keys(filters).forEach(key => {
        var input = filterForm.querySelector(`[name="${key}"]`);
        console.log(input);
        if (input) {
          if (input.type === 'checkbox' || input.type === 'radio') {
            input.checked = input.value === filters[key];
          } else {
            input.value = filters[key];
          }
        }
      });
      // Trigger a change event to refresh the view
    //   filterForm.dispatchEvent(new Event('change'));
    sessionStorage.setItem('restorepage', true);
      document.querySelector('#edit-submit-duplicate-of-filterable-product-variations').click();
    //   filterForm.submit();
    }
  

  }

  function restorePaginationStates()
  {
    // Restore pagination state
    var currentPage = sessionStorage.getItem('currentPage');
    if (currentPage) {
        var activePageLink = document.querySelector(`.pager__item a[href*="page=${currentPage}"]`);
        if (activePageLink) {
        activePageLink.click();
        }
    }
  }
  
  // Initial rendering and then clear session items
  document.addEventListener('DOMContentLoaded', function() {
    // Restore states first
    restoreFilterStates();
  
    // Clear session items after rendering
    // sessionStorage.removeItem('filterState');
    // sessionStorage.removeItem('currentPage');
  });
  
  // Bind to Drupal AJAX events to reattach listeners after view is updated
  (function($, Drupal) {
    Drupal.behaviors.attachViewListeners = {
      attach: function(context, settings) {
        console.log( $(context));
        console.log($(context).find('.view-rimpy-fiilter-product-block'));
        // console.log( settings);
        test = context;
        // $(context).find('.view-rimpy-fiilter-product-block').each(function() {
        if(test)
        {
            console.log('here');
            attachViewListeners();
            // restorePaginationStates();
            var storedPage = sessionStorage.getItem('currentPage');
            console.log(storedPage);
            var to_restore_page = sessionStorage.getItem('restorepage');
            if (storedPage && to_restore_page) 
            {
              // Find the active page in the current pagination controls
              var activePageLink = document.querySelector('.pager__item.is-active a');
          
              // Get the current active page from the URL
              var currentPage = activePageLink ? new URLSearchParams(activePageLink.search).get('page') : null;
          
              // Check if the current page differs from the stored page
              console.log('currentPage '+currentPage+"storedPage "+storedPage);
              if (currentPage !== storedPage) {
                var targetPageLink = document.querySelector(`.pager__item a[href*="page=${storedPage}"]`);
                if (targetPageLink) {
                  targetPageLink.click();
                //   sessionStorage.removeItem('currentPage');
                }
              }
              sessionStorage.removeItem('restorepage');
            }
            
        }
            
        // });
      }
    };
  })(jQuery, Drupal);
  