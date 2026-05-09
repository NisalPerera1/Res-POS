@extends('layouts.app')

@section('content')

<!-- HERO -->
<section class="relative h-screen flex items-center justify-center text-white text-center overflow-hidden">
    <img src="{{ asset('images/hero.jpg') }}" 
         class="absolute w-full h-full object-cover scale-110">

    <!-- Overlay -->
    <div class="absolute inset-0 bg-black/70"></div>

    <div class="relative z-10 px-6 max-w-3xl">
        <h1 class="text-4xl md:text-6xl font-extrabold leading-tight">
            Welcome to <span class="text-red-500">Toddyz Family Restaurant</span>
        </h1>

        <p class="mt-4 text-lg text-gray-300">
            Where families gather, flavors shine, and every meal feels like home ❤️
        </p>

        <div class="mt-8 flex flex-col md:flex-row gap-4 justify-center">
            <a href="{{ route('menu') }}" 
               class="bg-red-600 hover:bg-red-700 px-8 py-3 rounded-full font-semibold shadow-lg">
                🍽️ Explore Menu
            </a>

            <a href="{{ route('reservations.create') }}" 
               class="bg-white text-black px-8 py-3 rounded-full font-semibold hover:bg-gray-200">
                📅 Book a Table
            </a>

            <a href="tel:+94771234567" 
               class="border border-white px-8 py-3 rounded-full hover:bg-white hover:text-black">
                📞 Call Now
            </a>
        </div>
    </div>
</section>

<!-- WHY CHOOSE US -->
<section class="py-16 px-6 bg-white text-center">
    <h2 class="text-3xl font-bold mb-10">Why Choose Toddyz?</h2>

    <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">
        <div>
            <h3 class="text-xl font-semibold">🍛 Authentic Taste</h3>
            <p class="text-gray-600 mt-2">Traditional Sri Lankan recipes made fresh daily</p>
        </div>

        <div>
            <h3 class="text-xl font-semibold">👨‍👩‍👧‍👦 Family Friendly</h3>
            <p class="text-gray-600 mt-2">Comfortable dining space for families & groups</p>
        </div>

        <div>
            <h3 class="text-xl font-semibold">⚡ Fast Service</h3>
            <p class="text-gray-600 mt-2">Quick preparation with consistent quality</p>
        </div>
    </div>
</section>

<!-- FEATURED ITEMS -->
<section class="bg-[#0A0C10] text-white py-16 px-6">
    <h2 class="text-3xl font-bold text-center mb-10">🔥 Customer Favorites</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @foreach($featuredItems as $item)
        <div class="bg-[#12151C] rounded-2xl overflow-hidden shadow-lg hover:scale-105 transition duration-300">
            <img src="{{ $item->image ?? asset('images/default.jpg') }}" 
                 class="w-full h-48 object-cover">

            <div class="p-4">
                <h3 class="text-lg font-semibold">{{ $item->name }}</h3>
                <p class="text-gray-400 text-sm mt-1">{{ $item->description ?? '' }}</p>
                <p class="mt-2 text-red-500 font-bold text-lg">Rs. {{ $item->price_full }}</p>
            </div>
        </div>
        @endforeach
    </div>

    <div class="text-center mt-10">
        <a href="{{ route('menu') }}" class="text-red-500 hover:underline">
            View Full Menu →
        </a>
    </div>
</section>

<!-- PROMOTIONS -->
@if($promotions->count())
<section class="py-16 px-6 bg-white">
    <h2 class="text-3xl font-bold text-center mb-10">🎉 Special Deals</h2>

    <div class="grid md:grid-cols-2 gap-6 max-w-6xl mx-auto">
        @foreach($promotions as $promo)
        <div class="relative rounded-xl overflow-hidden shadow-lg">
            <img src="{{ $promo->image }}" class="w-full h-64 object-cover">

            <div class="absolute inset-0 bg-black/60 flex flex-col justify-center p-6 text-white">
                <h3 class="text-2xl font-bold">{{ $promo->title }}</h3>
                <p class="mt-2">{{ $promo->description }}</p>
            </div>
        </div>
        @endforeach
    </div>
</section>
@endif

<!-- GALLERY -->
<section class="bg-[#0A0C10] py-16 px-6">
    <h2 class="text-3xl font-bold text-center text-white mb-10">📸 Moments at Toddyz</h2>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($gallery as $media)
        <img src="{{ $media->url }}" 
             class="w-full h-40 object-cover rounded-lg hover:scale-105 transition">
        @endforeach
    </div>
</section>

<!-- REVIEWS -->
@if($reviews->count())
<section class="bg-white py-16 px-6">
    <h2 class="text-3xl font-bold text-center mb-10">⭐ Happy Customers</h2>

    <div class="grid md:grid-cols-3 gap-6 max-w-6xl mx-auto">
        @foreach($reviews as $review)
        <div class="bg-gray-100 p-6 rounded-xl shadow">
            <p class="text-gray-700">"{{ $review->body }}"</p>
            <div class="mt-3 text-yellow-500">
                @for($i = 0; $i < $review->rating; $i++) ★ @endfor
            </div>
            <p class="text-sm text-gray-500 mt-2">{{ $review->source }}</p>
        </div>
        @endforeach
    </div>
</section>
@endif

<!-- RESERVATION CTA -->
<section class="bg-red-600 text-white py-16 text-center">
    <h2 class="text-3xl font-bold">Book Your Family Table Today</h2>
    <p class="mt-2">Enjoy a warm and delicious dining experience</p>

    <a href="{{ route('reservations.create') }}" 
       class="mt-6 inline-block bg-white text-black px-8 py-3 rounded-full font-semibold hover:bg-gray-200">
        Reserve Now
    </a>
</section>

<!-- CONTACT -->
<section class="bg-black text-white py-16 px-6 text-center">
    <h2 class="text-3xl font-bold mb-4">Visit Us</h2>
    <p>📍 Negombo, Sri Lanka</p>
    <p class="mt-2">📞 <a href="tel:+94771234567" class="text-red-500">+94 77 123 4567</a></p>
</section>

@endsection