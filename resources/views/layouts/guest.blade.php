<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'POSPro') }} - Masuk Sistem Kasir</title>

        <!-- Google Fonts: Plus Jakarta Sans -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- FontAwesome 6 Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

        <style>
            body {
                font-family: 'Plus Jakarta Sans', sans-serif;
            }
        </style>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-900 bg-slate-950 min-h-screen relative flex items-center justify-center p-4 selection:bg-blue-600 selection:text-white overflow-x-hidden">
        
        <!-- Glowing Background Orbs -->
        <div class="fixed top-0 left-1/4 w-96 h-96 bg-blue-600/20 rounded-full blur-3xl pointer-events-none -translate-y-1/2"></div>
        <div class="fixed bottom-0 right-1/4 w-96 h-96 bg-purple-600/20 rounded-full blur-3xl pointer-events-none translate-y-1/2"></div>

        <div class="w-full max-w-md relative z-10 my-8">
            <!-- Brand Logo -->
            <div class="text-center mb-6">
                <a href="/" class="inline-flex items-center space-x-2.5">
                    <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center text-2xl shadow-xl shadow-blue-600/30">
                        <i class="fa-solid fa-cash-register"></i>
                    </div>
                    <span class="text-2xl font-extrabold text-white tracking-tight">POS<span class="text-blue-500">Pro</span></span>
                </a>
            </div>

            <!-- Content Card -->
            <div class="bg-white/95 backdrop-blur-xl p-6 sm:p-8 rounded-3xl shadow-2xl border border-white/20">
                {{ $slot }}
            </div>

            <!-- Footer Meta -->
            <div class="text-center mt-6 text-xs text-slate-400">
                &copy; {{ date('Y') }} POSPro Point of Sale. All rights reserved.
            </div>
        </div>
    </body>
</html>
