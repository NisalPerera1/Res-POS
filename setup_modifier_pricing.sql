-- 🍽️ Modifier Pricing Setup Script
-- Run this in your database to configure the new pricing system

-- Step 1: Update base prices for main items
UPDATE menu_items SET price = 1000.00 WHERE name LIKE '%Rice%' AND price IS NULL;
UPDATE menu_items SET price = 800.00 WHERE name LIKE '%Koththu%' AND price IS NULL;

-- Step 2: Create size modifiers (if they don't exist)
INSERT IGNORE INTO modifiers (name, price) VALUES 
('Half Portion', 0),
('Normal Portion', 0), 
('Full Portion', 0);

-- Step 3: Create add-on modifiers (if they don't exist)
INSERT IGNORE INTO modifiers (name, price) VALUES 
('Extra Cheese', 150.00),
('Extra Egg', 80.00),
('Extra Meat', 200.00),
('Extra Vegetables', 60.00);

-- Step 4: Setup rice pricing (increment pricing)
-- Assumes Rice item ID = 1, modifier IDs: Half=1, Normal=2, Full=3
INSERT IGNORE INTO menu_item_modifier (menu_item_id, modifier_id, pricing_type, increment_price) VALUES
((SELECT id FROM menu_items WHERE name LIKE '%Rice%' LIMIT 1), 
 (SELECT id FROM modifiers WHERE name = 'Normal Portion' LIMIT 1), 
 'increment', 200.00),
((SELECT id FROM menu_items WHERE name LIKE '%Rice%' LIMIT 1), 
 (SELECT id FROM modifiers WHERE name = 'Full Portion' LIMIT 1), 
 'increment', 500.00);

-- Step 5: Setup koththu pricing (increment pricing - different amounts)
-- Assumes Koththu item ID = 2
INSERT IGNORE INTO menu_item_modifier (menu_item_id, modifier_id, pricing_type, increment_price) VALUES
((SELECT id FROM menu_items WHERE name LIKE '%Koththu%' LIMIT 1), 
 (SELECT id FROM modifiers WHERE name = 'Normal Portion' LIMIT 1), 
 'increment', 100.00),
((SELECT id FROM menu_items WHERE name LIKE '%Koththu%' LIMIT 1), 
 (SELECT id FROM modifiers WHERE name = 'Full Portion' LIMIT 1), 
 'increment', 200.00);

-- Step 6: Setup add-on pricing (absolute pricing - same for all items)
-- Get all menu items and add cheese pricing
INSERT IGNORE INTO menu_item_modifier (menu_item_id, modifier_id, pricing_type, custom_price)
SELECT mi.id, m.id, 'absolute', 150.00
FROM menu_items mi, modifiers m 
WHERE m.name = 'Extra Cheese';

-- Add egg pricing to all items
INSERT IGNORE INTO menu_item_modifier (menu_item_id, modifier_id, pricing_type, custom_price)
SELECT mi.id, m.id, 'absolute', 80.00
FROM menu_items mi, modifiers m 
WHERE m.name = 'Extra Egg';

-- Add meat pricing to all items
INSERT IGNORE INTO menu_item_modifier (menu_item_id, modifier_id, pricing_type, custom_price)
SELECT mi.id, m.id, 'absolute', 200.00
FROM menu_items mi, modifiers m 
WHERE m.name = 'Extra Meat';

-- Step 7: Verify setup
SELECT 
    mi.name as item_name,
    mi.price as base_price,
    mo.name as modifier_name,
    mip.pricing_type,
    mip.increment_price,
    mip.custom_price,
    CASE 
        WHEN mip.pricing_type = 'increment' THEN mi.price + COALESCE(mip.increment_price, 0)
        ELSE COALESCE(mip.custom_price, mo.price)
    END as final_price
FROM menu_items mi
JOIN menu_item_modifier mip ON mi.id = mip.menu_item_id
JOIN modifiers mo ON mip.modifier_id = mo.id
WHERE mi.name LIKE '%Rice%' OR mi.name LIKE '%Koththu%'
ORDER BY mi.name, mo.name;

-- Expected Results:
-- Rice (1000) + Normal (+200) = 1200
-- Rice (1000) + Full (+500) = 1500  
-- Koththu (800) + Normal (+100) = 900
-- Koththu (800) + Full (+200) = 1000
-- Any item + Extra Cheese = 150 (absolute)
-- Any item + Extra Egg = 80 (absolute)
