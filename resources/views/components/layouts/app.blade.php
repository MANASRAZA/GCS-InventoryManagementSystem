<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Inventory Management System - Purchase Entry' }}</title>

    <!-- Google Fonts - Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    @livewireStyles
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Outfit', sans-serif;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col antialiased">
    <!-- Navbar -->
    <header class="bg-white border-b border-slate-200/80 sticky top-0 z-40 shadow-[0_2px_15px_rgb(0,0,0,0.01)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo / Title -->
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-slate-900 rounded-xl flex items-center justify-center text-white font-bold text-lg tracking-wider shadow-sm">
                        I
                    </div>
                    <div>
                        <span class="text-lg font-bold tracking-tight text-slate-900">Inventory Management System</span>
                    </div>
                </div>

                <!-- Navigation & User Profile -->
                @auth
                    <div class="flex items-center gap-4">
                        <div class="flex flex-col text-right">
                            <span class="text-sm font-semibold text-slate-900">{{ auth()->user()->name }}</span>
                            <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">
                                Role: <span class="{{ auth()->user()->role === 'admin' ? 'text-amber-600 font-semibold' : 'text-slate-500' }}">{{ auth()->user()->role }}</span>
                            </span>
                        </div>
                        <div class="h-6 w-px bg-slate-200"></div>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-sm font-medium text-rose-600 hover:text-rose-800 transition-colors bg-rose-50 hover:bg-rose-100/60 px-3.5 py-2 rounded-xl cursor-pointer">
                                Logout
                            </button>
                        </form>
                    </div>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-10">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200/80 py-6 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} Inventory Management System. Purchase Entry & Legacy Data Migration System.
        </div>
    </footer>

    @livewireScripts
</body>
</html>
