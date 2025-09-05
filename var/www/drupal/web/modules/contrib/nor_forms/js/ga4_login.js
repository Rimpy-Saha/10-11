values_object = {
  event: "login",
  method: drupalSettings.nor_forms.ga4_login.method,
}

window.dataLayer = window.dataLayer || [];
dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
dataLayer.push(values_object);