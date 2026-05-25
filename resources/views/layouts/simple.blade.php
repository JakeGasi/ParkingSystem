<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MallPark - Parking Management System</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .slot-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .slot-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .alert-animation {
            animation: slideIn 0.5s ease;
        }
        @keyframes slideIn {
            from { transform: translateY(-30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body class="bg-gray-100">

    <!-- Navigation Bar -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <!-- Logo -->
                <div class="flex items-center space-x-3">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-2">
                        <i class="fas fa-parking text-white text-xl"></i>
                    </div>
                    <div>
                        <span class="font-bold text-xl text-gray-800">MallPark</span>
                        <span class="text-xs text-gray-500 block">Smart Parking System</span>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="hidden md:flex space-x-8">
                    <a href="{{ url('/dashboard') }}" class="text-gray-700 hover:text-blue-600 transition font-medium">
                        <i class="fas fa-chart-line mr-1"></i> Dashboard
                    </a>
                    <a href="{{ url('/user-map') }}" class="text-gray-700 hover:text-blue-600 transition font-medium">
                        <i class="fas fa-map-marked-alt mr-1"></i> Find Vehicle
                    </a>
                    <a href="{{ url('/active-vehicles') }}" class="text-gray-700 hover:text-blue-600 transition font-medium">
                        <i class="fas fa-car-side mr-1"></i> Active Vehicles
                    </a>
                    <a href="{{ url('/transactions') }}" class="text-gray-700 hover:text-blue-600 transition font-medium">
                        <i class="fas fa-list mr-1"></i> Transactions
                    </a>
                    <a href="{{ url('/payment-history') }}" class="text-gray-700 hover:text-blue-600 transition font-medium">
                        <i class="fas fa-history mr-1"></i> Payment History
                    </a>
                </div>

                <!-- User Section - Fixed (no profile.edit) -->
                <div class="flex items-center space-x-4">
                    @auth
                        <div class="relative group">
                            <button class="flex items-center space-x-2 bg-gray-100 rounded-full px-4 py-2 hover:bg-gray-200 transition">
                                <i class="fas fa-user-circle text-gray-600 text-xl"></i>
                                <span class="text-sm text-gray-700 font-medium">{{ Auth::user()->name }}</span>
                                <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                            </button>
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg hidden group-hover:block z-50">
                                <div class="px-4 py-2 text-gray-700 border-b">
                                    <i class="fas fa-envelope mr-2"></i> {{ Auth::user()->email }}
                                </div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-red-600 hover:bg-gray-100 rounded-b-lg">
                                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-blue-600 transition">
                            <i class="fas fa-sign-in-alt mr-1"></i> Login
                        </a>
                        <a href="{{ route('register') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                            <i class="fas fa-user-plus mr-1"></i> Register
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12 py-6">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <div class="flex flex-wrap justify-center gap-4 text-sm text-gray-500 mb-2">
                <span><i class="fas fa-clock mr-1"></i> First 30 minutes free</span>
                <span><i class="fas fa-dollar-sign mr-1"></i> $2 per hour after</span>
                <span><i class="fas fa-wheelchair mr-1"></i> 20% discount for PWD/Senior</span>
                <span><i class="fas fa-car mr-1"></i> 50 parking slots available</span>
            </div>
            <p class="text-gray-400 text-xs">
                &copy; {{ date('Y') }} MallPark System. All rights reserved.
            </p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
