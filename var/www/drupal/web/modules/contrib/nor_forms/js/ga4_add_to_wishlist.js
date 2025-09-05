function buildItems(wishlist_items){
  var items = [];
  wishlist_items.forEach((wishlist_item, index) => {
      items[index] = {
        item_id: wishlist_item.sku,
        item_name: wishlist_item.title,
        price: wishlist_item.price,
        quantity: 1,
      }
  });
  return items
}

items_array = buildItems(drupalSettings.nor_forms.ga4_add_to_wishlist.items);
event_object = {
  event: "add_to_wishlist",
  ecommerce: {
    currency: drupalSettings.nor_forms.ga4_add_to_wishlist.items[0].currency,
    value: drupalSettings.nor_forms.ga4_add_to_wishlist.value,
    items: items_array,
  }
}

window.dataLayer = window.dataLayer || [];
dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
dataLayer.push(event_object);