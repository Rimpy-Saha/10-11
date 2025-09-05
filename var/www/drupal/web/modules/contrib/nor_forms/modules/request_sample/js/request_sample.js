function filterSamples(){
    // Declare variables
    var input, filter, ul, li, a, i, txtValue;
    input = document.getElementsByClassName('filter-samples-search')[0];
    filter = input.value.toUpperCase();
    table_body = document.querySelector(".samples-wrapper table tbody");
    tr = table_body.getElementsByTagName('tr');

    // Loop through all list items, and hide those who don't match the search query
    for (i = 0; i < tr.length; i++) {
      sku = tr[i].getElementsByTagName("td")[1]; // skip [0] which is the checkbox
      title = tr[i].getElementsByTagName("td")[2];
      size = tr[i].getElementsByTagName("td")[3];

      sku_text = sku.textContent || sku.innerText;
      title_text = title.textContent || title.innerText;
      size_text = size.textContent || size.innerText;

      if (
        sku_text.toUpperCase().indexOf(filter) > -1 || 
        title_text.toUpperCase().indexOf(filter) > -1 || 
        size_text.toUpperCase().indexOf(filter) > -1) {
        tr[i].style.display = "";
      } else {
        tr[i].style.display = "none";
      }
    }
  }  

  // select product from URL parameters
  window.addEventListener('load', function(){
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    const url_sku = urlParams.get('sku');
    /* console.log(url_sku); */
    // select checkbox
    matching_sku_checkbox = document.querySelector('.samples-wrapper table tbody tr input[value="'+url_sku+'"]');
    if(matching_sku_checkbox) {
      /* var event = new Event('change'); */
      matching_sku_checkbox.click();
      /* matching_sku_checkbox.dispatchEvent(event); */
    }
  })