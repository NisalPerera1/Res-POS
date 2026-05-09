<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#0A0C10">
        
        <!-- Favicon -->
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

        <title>{{ config('app.name', 'Restaurant') }}</title>

        <!-- Tailwind CSS -->
        <script src="https://cdn.tailwindcss.com"></script>
        
        <!-- External Libraries -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
        <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
        
        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800;900&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        
        <!-- Tailwind Config -->
        <script>
        tailwind.config = {
          theme: {
            extend: {
              fontFamily: {
                display: ['"Playfair Display"', 'Georgia', 'serif'],
                ui: ['Montserrat', 'system-ui', 'sans-serif'],
              },
              colors: {
                spice: '#D85A30',
                'spice-dim': 'rgba(216,90,48,0.12)',
                ink: '#08080A',
                'ink-2': '#0F0F13',
                'ink-3': '#161620',
                'ink-4': '#1E1E2A',
                t1: '#F8F8F6',
                t2: 'rgba(248,248,246,0.58)',
                t3: 'rgba(248,248,246,0.32)',
              },
              animation: {
                'ticker': 'ticker 28s linear infinite',
                'float': 'float 4s ease-in-out infinite',
                'pulse-slow': 'pulse 2s ease-in-out infinite',
                'badge-spin': 'badgeSpin 7s ease-in-out infinite',
                'stat-float': 'statFloat 3s ease-in-out infinite',
                'ken-burns': 'kenBurns 6s ease forwards',
                'badge-fade': 'heroBadgeFade 1s 0.3s ease forwards',
                'hero-title': 'heroTitle 2s 0.8s ease forwards',
                'hero-desc': 'heroDesc 1.2s 2s ease forwards',
                'hero-btns': 'heroBtns 1s 2.5s ease forwards',
                'btn-pulse': 'btnPulse 2s 3s ease-in-out infinite',
                'btn-float': 'btnFloat 3s 3.2s ease-in-out infinite',
                'shake': 'shake 4s ease-in-out infinite',
                'glow': 'glow 3s ease-in-out infinite',
                'bounce-dot': 'bounceDot 2s ease-in-out infinite',
                'mq': 'marquee 22s linear infinite',
              },
              keyframes: {
                ticker: {
                  'from': { transform: 'translateX(0)' },
                  'to': { transform: 'translateX(-50%)' },
                },
                float: {
                  '0%,100%': { transform: 'translateY(0)' },
                  '50%': { transform: 'translateY(-10px)' },
                },
                kenBurns: {
                  'from': { transform: 'scale(1)' },
                  'to': { transform: 'scale(1.08)' },
                },
                heroBadgeFade: {
                  '0%': { opacity: '0', transform: 'translateY(-20px) scale(0.9)' },
                  '50%': { opacity: '0.7', transform: 'translateY(-5px) scale(1.02)' },
                  '100%': { opacity: '1', transform: 'translateY(0) scale(1)' },
                },
                heroTitle: {
                  '0%': { opacity: '0', transform: 'translateX(-50px)', clipPath: 'inset(0 100% 0 0)' },
                  '30%': { opacity: '1', transform: 'translateX(-10px)', clipPath: 'inset(0 70% 0 0)' },
                  '70%': { opacity: '1', transform: 'translateX(0)', clipPath: 'inset(0 0 0 0)' },
                  '100%': { opacity: '1', transform: 'translateX(0)', clipPath: 'inset(0 0 0 0)' },
                },
                heroDesc: {
                  '0%': { opacity: '0', transform: 'translateX(-30px)', filter: 'blur(2px)' },
                  '50%': { opacity: '0.8', transform: 'translateX(-5px)', filter: 'blur(0.5px)' },
                  '100%': { opacity: '1', transform: 'translateX(0)', filter: 'blur(0)' },
                },
                heroBtns: {
                  '0%': { opacity: '0', transform: 'translateY(20px) scale(0.8)' },
                  '60%': { opacity: '1', transform: 'translateY(-5px) scale(1.05)' },
                  '80%': { opacity: '1', transform: 'translateY(2px) scale(0.98)' },
                  '100%': { opacity: '1', transform: 'translateY(0) scale(1)' },
                },
                btnPulse: {
                  '0%,100%': { transform: 'scale(1)', boxShadow: '0 4px 15px rgba(216,90,48,0.3)' },
                  '50%': { transform: 'scale(1.05)', boxShadow: '0 6px 25px rgba(216,90,48,0.5)' },
                },
                btnFloat: {
                  '0%,100%': { transform: 'translateY(0)' },
                  '50%': { transform: 'translateY(-3px)' },
                },
                shake: {
                  '0%,100%': { transform: 'translateX(0)' },
                  '10%,30%,50%,70%,90%': { transform: 'translateX(-2px)' },
                  '20%,40%,60%,80%': { transform: 'translateX(2px)' },
                },
                glow: {
                  '0%,100%': { boxShadow: '0 0 5px rgba(216,90,48,0.3)' },
                  '50%': { boxShadow: '0 0 20px rgba(216,90,48,0.6)' },
                },
                bounceDot: {
                  '0%,100%': { transform: 'scale(1)', opacity: '1' },
                  '50%': { transform: 'scale(1.05)', opacity: '0.8' },
                },
                statFloat: {
                  '0%': { transform: 'rotateX(0deg) rotateY(0deg) translateY(0px)' },
                  '25%': { transform: 'rotateX(6deg) rotateY(3deg) translateY(-4px)' },
                  '50%': { transform: 'rotateX(0deg) rotateY(6deg) translateY(-8px)' },
                  '75%': { transform: 'rotateX(-4deg) rotateY(2deg) translateY(-4px)' },
                  '100%': { transform: 'rotateX(0deg) rotateY(0deg) translateY(0px)' },
                },
                badgeSpin: {
                  '0%': { transform: 'rotateY(0deg) rotateX(8deg)' },
                  '45%': { transform: 'rotateY(170deg) rotateX(-4deg)' },
                  '55%': { transform: 'rotateY(190deg) rotateX(4deg)' },
                  '100%': { transform: 'rotateY(360deg) rotateX(8deg)' },
                },
                marquee: {
                  'from': { transform: 'translateX(0)' },
                  'to': { transform: 'translateX(-50%)' },
                },
                blink: {
                  '0%,100%': { opacity: '1' },
                  '50%': { opacity: '0.3' },
                },
                fadeUp: {
                  'from': { opacity: '0', transform: 'translateY(22px)' },
                  'to': { opacity: '1', transform: 'translateY(0)' },
                },
              },
            }
          }
        }
        </script>
        
        @vite(['resources/css/app.css'])
        @stack('styles')
    </head>

    <body class="bg-[#0A0C10] text-white">
        @if(request()->path() != '/')
            @include('components.navigation')
        @endif
        @yield('content')
        @include('components.footer')
        @include('components.footer-styles')
        @include('components.footer-scripts')
        @stack('scripts')
    </body>
</html>

