<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Management System - Login</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2 { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex items-center justify-center p-6 antialiased">
    <div class="w-full max-w-[420px] space-y-6">
        <!-- Logo -->
        <div class="text-center">
            <div class="w-12 h-12 bg-slate-900 rounded-2xl flex items-center justify-center text-white font-bold text-2xl tracking-wider shadow-md mx-auto mb-4">
                P
            </div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900">Sign in to your account</h2>
            <p class="text-sm text-slate-500 mt-1">Enter your credentials to access the purchase module.</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white border border-slate-200 p-8 rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <!-- Email Address -->
                <div class="space-y-1.5">
                    <label for="email" class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Email Address</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        class="w-full p-3 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-slate-400 focus:ring-1 focus:ring-slate-400 transition-all"
                        placeholder="you@example.com"
                    >
                    @error('email')
                        <span class="text-xs text-rose-600 font-medium block mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password -->
                <div class="space-y-1.5">
                    <label for="password" class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Password</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        class="w-full p-3 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-slate-400 focus:ring-1 focus:ring-slate-400 transition-all"
                        placeholder="••••••••"
                    >
                    @error('password')
                        <span class="text-xs text-rose-600 font-medium block mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center">
                    <input
                        id="remember_me"
                        type="checkbox"
                        name="remember"
                        class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900 cursor-pointer"
                    >
                    <label for="remember_me" class="ml-2 block text-sm text-slate-600 select-none cursor-pointer">
                        Remember me
                    </label>
                </div>

                <!-- Submit -->
                <button
                    type="submit"
                    class="w-full py-3 px-4 font-semibold text-sm text-white bg-slate-900 hover:bg-slate-800 rounded-xl transition-all duration-200 shadow-sm cursor-pointer"
                >
                    Sign In
                </button>
            </form>
        </div>

        <!-- Callout to Register -->
        <p class="text-center text-sm text-slate-500">
            Don't have an account?
            <a href="{{ route('register') }}" class="font-semibold text-slate-900 hover:underline">
                Create one now
            </a>
        </p>
    </div>
</body>
</html>
