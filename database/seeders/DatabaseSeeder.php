<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\{User, Table, Category, MenuItem, Modifier, Inventory};

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // USERS
        $users = [
            ['name' => 'Admin User',    'pin' => Hash::make('1234'), 'role' => 'admin',    'color' => '#EF4444'],
            ['name' => 'Carlos Rivera', 'pin' => Hash::make('2222'), 'role' => 'cashier',  'color' => '#3B82F6'],
            ['name' => 'Sophie Lee',    'pin' => Hash::make('3333'), 'role' => 'waiter',   'color' => '#10B981'],
            ['name' => 'Ahmed Khan',    'pin' => Hash::make('4444'), 'role' => 'kitchen',  'color' => '#F59E0B'],
            ['name' => 'Maria Santos',  'pin' => Hash::make('5555'), 'role' => 'waiter',   'color' => '#8B5CF6'],
        ];
        foreach ($users as $u) User::create($u);

        // TABLES
        $tables = [
            ['name' => 'T-01', 'section' => 'Main Hall', 'capacity' => 4],
            ['name' => 'T-02', 'section' => 'Main Hall', 'capacity' => 4],
            ['name' => 'T-03', 'section' => 'Main Hall', 'capacity' => 2],
            ['name' => 'T-04', 'section' => 'Main Hall', 'capacity' => 6],
            ['name' => 'T-05', 'section' => 'Main Hall', 'capacity' => 4],
            ['name' => 'T-06', 'section' => 'Main Hall', 'capacity' => 8],
            ['name' => 'B-01', 'section' => 'Bar',       'capacity' => 2],
            ['name' => 'B-02', 'section' => 'Bar',       'capacity' => 2],
            ['name' => 'P-01', 'section' => 'Patio',     'capacity' => 6],
            ['name' => 'P-02', 'section' => 'Patio',     'capacity' => 4],
        ];
        foreach ($tables as $i => $t) Table::create([...$t, 'sort_order' => $i]);

        // CATEGORIES
        $cats = [
            ['name' => 'Mains',    'icon' => '🍔', 'color' => '#F59E0B'],
            ['name' => 'Pizza',    'icon' => '🍕', 'color' => '#EF4444'],
            ['name' => 'Salads',   'icon' => '🥗', 'color' => '#10B981'],
            ['name' => 'Drinks',   'icon' => '🍺', 'color' => '#3B82F6'],
            ['name' => 'Desserts', 'icon' => '🍰', 'color' => '#8B5CF6'],
        ];
        foreach ($cats as $i => $c) Category::create([...$c, 'sort_order' => $i]);

        // MENU ITEMS
        $items = [
            ['category_id'=>1, 'name'=>'Beef Burger',      'price'=>14.99, 'cost_price'=>5.00, 'type'=>'food',     'is_popular'=>true, 'prep_time'=>12],
            ['category_id'=>1, 'name'=>'Grilled Chicken',  'price'=>16.50, 'cost_price'=>6.00, 'type'=>'food',     'prep_time'=>15],
            ['category_id'=>1, 'name'=>'Fish & Chips',     'price'=>13.99, 'cost_price'=>5.50, 'type'=>'food',     'prep_time'=>14],
            ['category_id'=>1, 'name'=>'Pasta Bolognese',  'price'=>13.50, 'cost_price'=>4.00, 'type'=>'food',     'prep_time'=>12],
            ['category_id'=>2, 'name'=>'Margherita Pizza', 'price'=>12.99, 'cost_price'=>3.50, 'type'=>'food',     'is_popular'=>true, 'prep_time'=>15],
            ['category_id'=>2, 'name'=>'Pepperoni Pizza',  'price'=>14.99, 'cost_price'=>4.50, 'type'=>'food',     'prep_time'=>15],
            ['category_id'=>2, 'name'=>'BBQ Chicken Pizza','price'=>15.99, 'cost_price'=>5.00, 'type'=>'food',     'prep_time'=>16],
            ['category_id'=>3, 'name'=>'Caesar Salad',     'price'=> 9.99, 'cost_price'=>2.50, 'type'=>'food',     'prep_time'=> 5],
            ['category_id'=>3, 'name'=>'Greek Salad',      'price'=>10.99, 'cost_price'=>2.80, 'type'=>'food',     'prep_time'=> 5],
            ['category_id'=>4, 'name'=>'Soft Drink',       'price'=> 3.50, 'cost_price'=>0.50, 'type'=>'beverage', 'prep_time'=> 1],
            ['category_id'=>4, 'name'=>'Fresh Juice',      'price'=> 5.50, 'cost_price'=>1.50, 'type'=>'beverage', 'prep_time'=> 3],
            ['category_id'=>4, 'name'=>'Craft Beer',       'price'=> 7.00, 'cost_price'=>2.00, 'type'=>'beverage', 'is_popular'=>true, 'prep_time'=> 2],
            ['category_id'=>5, 'name'=>'Chocolate Cake',   'price'=> 6.99, 'cost_price'=>2.00, 'type'=>'dessert',  'prep_time'=> 3],
            ['category_id'=>5, 'name'=>'Ice Cream',        'price'=> 4.99, 'cost_price'=>1.00, 'type'=>'dessert',  'prep_time'=> 2],
            ['category_id'=>5, 'name'=>'Tiramisu',         'price'=> 7.50, 'cost_price'=>2.50, 'type'=>'dessert',  'prep_time'=> 2],
        ];
        foreach ($items as $i => $item) MenuItem::create([
            ...$item,
            'is_popular'   => $item['is_popular'] ?? false,
            'is_available' => true,
            'sort_order'   => $i,
        ]);

        // MODIFIERS
        $modGroups = [
            ['group_name' => 'Spice Level', 'name' => 'Mild',       'price' => 0],
            ['group_name' => 'Spice Level', 'name' => 'Medium',     'price' => 0],
            ['group_name' => 'Spice Level', 'name' => 'Hot',        'price' => 0],
            ['group_name' => 'Size',        'name' => 'Regular',    'price' => 0],
            ['group_name' => 'Size',        'name' => 'Large',      'price' => 2.00],
            ['group_name' => 'Add-ons',     'name' => 'Extra Cheese','price' => 1.50],
            ['group_name' => 'Add-ons',     'name' => 'Bacon',      'price' => 2.00],
            ['group_name' => 'Add-ons',     'name' => 'Avocado',    'price' => 2.50],
        ];
        foreach ($modGroups as $m) Modifier::create($m);

        // INVENTORY
        $ingredients = [
            ['name' => 'Beef Patty',    'unit' => 'kg',  'quantity' => 10, 'min_quantity' => 2, 'cost_per_unit' => 12.00],
            ['name' => 'Chicken Breast','unit' => 'kg',  'quantity' => 8,  'min_quantity' => 2, 'cost_per_unit' => 8.00],
            ['name' => 'Pizza Dough',   'unit' => 'pcs', 'quantity' => 20, 'min_quantity' => 5, 'cost_per_unit' => 1.50],
            ['name' => 'Mozzarella',    'unit' => 'kg',  'quantity' => 5,  'min_quantity' => 1, 'cost_per_unit' => 10.00],
            ['name' => 'Lettuce',       'unit' => 'kg',  'quantity' => 3,  'min_quantity' => 1, 'cost_per_unit' => 2.50],
            ['name' => 'Tomato Sauce',  'unit' => 'L',   'quantity' => 10, 'min_quantity' => 2, 'cost_per_unit' => 3.00],
            ['name' => 'Craft Beer Keg','unit' => 'L',   'quantity' => 50, 'min_quantity' => 10,'cost_per_unit' => 3.50],
        ];
        foreach ($ingredients as $inv) Inventory::create($inv);
    }
}