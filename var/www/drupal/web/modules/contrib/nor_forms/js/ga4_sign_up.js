values_object = {
  event: "sign_up",
  method: drupalSettings.nor_forms.ga4_sign_up.method,
}

window.dataLayer = window.dataLayer || [];
dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
dataLayer.push(values_object);