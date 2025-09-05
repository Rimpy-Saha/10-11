function buildItems(order_items){
  var items = [];
  order_items.forEach((order_item, index) => {
      items[index] = {
        item_id: order_item.sku,
        item_name: order_item.title,
        item_list_id: order_item.list_id,
        item_list_name: order_item.list_name,
        price: order_item.price,
        quantity: order_item.quantity,
      }
  });
  return items
}

items_array = buildItems(drupalSettings.nor_forms.ga4_add_to_cart);
values_object = {
  event: "add_to_cart",
  ecommerce: {
    currency: drupalSettings.nor_forms.ga4_add_to_cart[0].currency, // each ga4_add_to_cart array item gets sent the same currency and value paramters, so we can just grab from the first item
    value: drupalSettings.nor_forms.ga4_add_to_cart[0].value,  
    items: items_array,
  }
}

window.dataLayer = window.dataLayer || [];
dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
dataLayer.push(values_object);