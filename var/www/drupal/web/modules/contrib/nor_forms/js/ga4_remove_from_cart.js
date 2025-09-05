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

items_array = buildItems(drupalSettings.nor_forms.ga4_remove_from_cart);
values_object = {
  event: "remove_from_cart",
  ecommerce: {
    currency: drupalSettings.nor_forms.ga4_remove_from_cart[0].currency,
    value: drupalSettings.nor_forms.ga4_remove_from_cart[0].value,  
    items: items_array,
  }
}

window.dataLayer = window.dataLayer || [];
dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
dataLayer.push(values_object);