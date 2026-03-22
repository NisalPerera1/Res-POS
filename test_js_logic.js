const testJson = {
    order: {
        id: 1,
        active_items: [
            { id: 10, is_void: false, status: 'confirmed', item_name: 'Burger' }
        ],
        items: [
            { id: 10, is_void: false, status: 'confirmed', item_name: 'Burger' }
        ],
        pending_items: []
    }
};

const order = testJson.order;

let baseItems = [];
if (order?.items && Array.isArray(order.items) && order.items.length > 0) {
  baseItems = order.items;
} else if (order?.active_items && Array.isArray(order.active_items) && order.active_items.length > 0) {
  baseItems = order.active_items;
} else if (order?.activeItems && Array.isArray(order.activeItems) && order.activeItems.length > 0) {
  baseItems = order.activeItems;
}

const items = baseItems.filter(item => {
  return item.is_void === false || item.is_void === 0 || item.is_void === "0" || !item.is_void;
});

console.log("Filtered Length:", items.length);
console.log("First item:", items[0]?.item_name);
