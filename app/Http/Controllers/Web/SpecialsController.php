<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SpecialsController extends Controller
{
    /**
     * Display the specials page.
     */
    public function index()
    {
        // Sample specials data - in a real app, this would come from database
        $specials = [
            [
                'id' => 1,
                'name' => 'Seafood Feast',
                'description' => 'Fresh catch of the day with Sri Lankan spices, served with rice and vegetables',
                'original_price' => 2800,
                'special_price' => 2200,
                'category' => 'Main Course',
                'available_until' => '2024-12-31',
                'image' => 'seafood_feast.jpg',
                'badge' => 'Limited Time'
            ],
            [
                'id' => 2,
                'name' => 'Family Combo',
                'description' => 'Perfect for 4 people - 2 mains, 4 sides, and dessert',
                'original_price' => 4500,
                'special_price' => 3500,
                'category' => 'Combo',
                'available_until' => '2024-12-25',
                'image' => 'family_combo.jpg',
                'badge' => 'Best Value'
            ],
            [
                'id' => 3,
                'name' => 'Lunch Special',
                'description' => 'Complete lunch with soup, main, rice, and drink',
                'original_price' => 1200,
                'special_price' => 850,
                'category' => 'Lunch',
                'available_until' => '2024-12-31',
                'image' => 'lunch_special.jpg',
                'badge' => 'Daily'
            ],
            [
                'id' => 4,
                'name' => 'Weekend Brunch',
                'description' => 'Traditional Sri Lankan breakfast with tea/coffee',
                'original_price' => 1800,
                'special_price' => 1400,
                'category' => 'Brunch',
                'available_until' => '2024-12-31',
                'image' => 'weekend_brunch.jpg',
                'badge' => 'Weekend Only'
            ],
            [
                'id' => 5,
                'name' => 'Dessert Special',
                'description' => 'Traditional Sri Lankan sweets platter for two',
                'original_price' => 800,
                'special_price' => 600,
                'category' => 'Dessert',
                'available_until' => '2024-12-31',
                'image' => 'dessert_platter.jpg',
                'badge' => 'Sweet Deal'
            ],
            [
                'id' => 6,
                'name' => 'Happy Hour Drinks',
                'description' => 'Buy 2 get 1 free on selected beverages',
                'original_price' => 600,
                'special_price' => 400,
                'category' => 'Beverages',
                'available_until' => '2024-12-31',
                'image' => 'drinks_special.jpg',
                'badge' => 'Happy Hour'
            ]
        ];

        return view('specials.index', compact('specials'));
    }
}
