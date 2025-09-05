function buildItems(order_items){
  var items = [];
  order_items.forEach((order_item, index) => {
      items[index] = {
        item_id: order_item.sku,
        item_name: order_item.title,
        price: order_item.price,
        quantity: order_item.quantity,
        discount: order_item.discount,
        promotion_name: order_item.promotion_name,
        index: order_item.index,
      }
  });
  return items;
}

items_array = buildItems(drupalSettings.nor_forms.ga4_purchase.order_items);
values_object = {
  event: "purchase",
  ecommerce: {
    currency: drupalSettings.nor_forms.ga4_purchase.currency,
    value: drupalSettings.nor_forms.ga4_purchase.value,
    transaction_id: drupalSettings.nor_forms.ga4_purchase.order_id,
    shipping: drupalSettings.nor_forms.ga4_purchase.shipping,
    tax: drupalSettings.nor_forms.ga4_purchase.tax,
    discount: drupalSettings.nor_forms.ga4_purchase.discount,
    promotion_name: drupalSettings.nor_forms.ga4_purchase.promotion_name,
    items: items_array,
  }
}

window.dataLayer = window.dataLayer || [];
dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
dataLayer.push(values_object);

console.log(JSON.stringify(values_object));