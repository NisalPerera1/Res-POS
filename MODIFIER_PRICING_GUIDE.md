# 🍽️ **Advanced Modifier Pricing System**

## 🎯 **Problem Solved:**

**Different modifiers need different pricing for different base items:**

- **Rice**: Half (Rs.1000), Normal (Rs.1200), Full (Rs.1500)
  - Base rice price: Rs.1000
  - Normal portion: +Rs.200
  - Full portion: +Rs.500

- **Koththu**: Half (Rs.800), Normal (Rs.900), Full (Rs.1000)
  - Base koththu price: Rs.800
  - Normal portion: +Rs.100
  - Full portion: +Rs.200

---

## 🔧 **New Pricing System Features:**

### **1. Two Pricing Types:**
- **`absolute`**: Fixed price regardless of base item
- **`increment`**: Adds amount to base item price

### **2. Database Schema:**
```sql
menu_item_modifier table:
- custom_price: DECIMAL(10,2) NULL     -- Absolute price
- pricing_type: ENUM('absolute', 'increment') DEFAULT 'absolute'
- increment_price: DECIMAL(10,2) NULL  -- Amount to add to base price
```

### **3. Model Methods:**
```php
// Get final price (handles both types)
$finalPrice = $modifier->getFinalPriceForMenuItem($menuItemId, $basePrice);

// Get increment amount only
$increment = $modifier->getIncrementForMenuItem($menuItemId);

// Get absolute price (fallback)
$price = $modifier->getPriceForMenuItem($menuItemId);
```

---

## 📋 **Setup Examples:**

### **Example 1: Rice with Increment Pricing**

**Base Items:**
```sql
-- Base rice (half portion)
INSERT INTO menu_items (name, price) VALUES ('Rice', 1000.00);
-- Base koththu (half portion)  
INSERT INTO menu_items (name, price) VALUES ('Koththu', 800.00);
```

**Modifiers:**
```sql
-- Create size modifiers
INSERT INTO modifiers (name, price) VALUES ('Normal Portion', 0);
INSERT INTO modifiers (name, price) VALUES ('Full Portion', 0);

-- Link to rice with increment pricing
INSERT INTO menu_item_modifier (menu_item_id, modifier_id, pricing_type, increment_price) VALUES
(1, 1, 'increment', 200.00),  -- Rice +200 = 1200
(1, 2, 'increment', 500.00);  -- Rice +500 = 1500

-- Link to koththu with different increment pricing
INSERT INTO menu_item_modifier (menu_item_id, modifier_id, pricing_type, increment_price) VALUES
(2, 1, 'increment', 100.00),  -- Koththu +100 = 900
(2, 2, 'increment', 200.00);  -- Koththu +200 = 1000
```

### **Example 2: Add-ons with Absolute Pricing**

**Add-on modifiers:**
```sql
-- Create add-on modifiers with default prices
INSERT INTO modifiers (name, price) VALUES 
('Extra Cheese', 150.00),
('Extra Egg', 80.00),
('Extra Meat', 200.00);

-- Link to all items with absolute pricing (same price for all)
INSERT INTO menu_item_modifier (menu_item_id, modifier_id, pricing_type, custom_price) VALUES
(1, 3, 'absolute', 150.00),  -- Rice + Cheese = 150
(1, 4, 'absolute', 80.00),   -- Rice + Egg = 80
(1, 5, 'absolute', 200.00),  -- Rice + Meat = 200
(2, 3, 'absolute', 150.00),  -- Koththu + Cheese = 150
(2, 4, 'absolute', 80.00),   -- Koththu + Egg = 80
(2, 5, 'absolute', 200.00);  -- Koththu + Meat = 200
```

---

## 🎯 **How It Works:**

### **Increment Pricing Flow:**
1. **Base Item Price**: Rs.1000 (Rice)
2. **Modifier Selected**: "Full Portion" (+Rs.500)
3. **Final Price**: Rs.1000 + Rs.500 = Rs.1500

### **Absolute Pricing Flow:**
1. **Base Item Price**: Rs.1000 (Rice)
2. **Modifier Selected**: "Extra Cheese" (Rs.150)
3. **Final Price**: Rs.150 (fixed price)

---

## 📊 **Pricing Matrix Examples:**

| Base Item | Base Price | Modifier | Type | Amount | Final Price |
|-----------|------------|----------|------|--------|-------------|
| Rice      | Rs.1000    | Normal   | Increment | +Rs.200 | Rs.1200 |
| Rice      | Rs.1000    | Full     | Increment | +Rs.500 | Rs.1500 |
| Koththu   | Rs.800     | Normal   | Increment | +Rs.100 | Rs.900 |
| Koththu   | Rs.800     | Full     | Increment | +Rs.200 | Rs.1000 |
| Any Item  | -          | Cheese   | Absolute | Rs.150 | Rs.150 |
| Any Item  | -          | Egg      | Absolute | Rs.80  | Rs.80 |

---

## 🛠️ **Implementation Steps:**

### **1. Set Base Prices:**
```sql
UPDATE menu_items SET price = 1000.00 WHERE name = 'Rice';
UPDATE menu_items SET price = 800.00 WHERE name = 'Koththu';
```

### **2. Create Modifiers:**
```sql
-- Size modifiers (increment pricing)
INSERT INTO modifiers (name, price) VALUES 
('Normal Portion', 0),
('Full Portion', 0);

-- Add-on modifiers (absolute pricing)
INSERT INTO modifiers (name, price) VALUES 
('Extra Cheese', 150.00),
('Extra Egg', 80.00);
```

### **3. Link with Pricing Types:**
```sql
-- Rice size increments
INSERT INTO menu_item_modifier (menu_item_id, modifier_id, pricing_type, increment_price) VALUES
(1, 1, 'increment', 200.00),  -- Normal
(1, 2, 'increment', 500.00);  -- Full

-- Koththu size increments (different amounts)
INSERT INTO menu_item_modifier (menu_item_id, modifier_id, pricing_type, increment_price) VALUES
(2, 1, 'increment', 100.00),  -- Normal
(2, 2, 'increment', 200.00);  -- Full
```

---

## 🎉 **Benefits:**

### **✅ Flexible Pricing:**
- Different increments for different base items
- Mix of absolute and incremental pricing
- Backward compatible with existing system

### **✅ Easy Management:**
- Clear pricing structure
- Simple database updates
- Logical pricing flow

### **✅ Business Logic:**
- Rice: Higher increments for larger portions
- Koththu: Smaller increments for larger portions
- Add-ons: Same price regardless of base item

---

## 🔍 **Testing Examples:**

### **Test Case 1: Rice Normal Portion**
- Base: Rs.1000
- Modifier: Normal (+Rs.200)
- Expected: Rs.1200

### **Test Case 2: Koththu Full Portion**
- Base: Rs.800
- Modifier: Full (+Rs.200)
- Expected: Rs.1000

### **Test Case 3: Rice with Extra Cheese**
- Base: Rs.1000
- Modifier: Extra Cheese (Rs.150 absolute)
- Expected: Rs.150

---

## 🚀 **Ready to Use!**

The new system supports:
- ✅ **Increment pricing** for size/portion modifiers
- ✅ **Absolute pricing** for add-on modifiers
- ✅ **Mixed pricing** within same menu
- ✅ **Backward compatibility** with existing data
- ✅ **Flexible setup** for any pricing scenario

**Your modifier pricing is now completely flexible and business-logic driven!** 🎉
