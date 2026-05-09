<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Review;
use App\Models\Promotion;
use App\Models\MediaItem;

class HomeController extends Controller
{
   public function index()
{
    return view('home', [
        'featuredItems' => MenuItem::where('is_special', true)
            ->where('is_visible', true)
            ->take(6)
            ->get(),

        'reviews' => Review::where('is_visible', true)
            ->latest()
            ->take(6)
            ->get(),

        'promotions' => Promotion::whereDate('active_from', '<=', now())
            ->whereDate('active_to', '>=', now())
            ->get(),

        'gallery' => MediaItem::orderBy('sort_order')
            ->take(8)
            ->get(),
    ]);
    }
}

