<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MallPark - Smart Parking System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gradient-to-r from-blue-600 to-purple-700">
    <div class="min-h-screen flex items-center justify-center">
        <div class="text-center text-white">
            <i class="fas fa-parking text-7xl mb-6"></i>
            <h1 class="text-5xl font-bold mb-4">MallPark System</h1>
            <p class="text-xl mb-8">Smart Parking Management System</p>

            <div class="space-x-4">
                <a href="{{ route('login') }}" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition inline-block">
                    <i class="fas fa-sign-in-alt mr-2"></i> Login
                </a>
                <a href="{{ route('register') }}" class="bg-transparent border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-blue-600 transition inline-block">
                    <i class="fas fa-user-plus mr-2"></i> Register
                </a>
            </div>

            <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl mx-auto">
                <div class="bg-white/10 rounded-lg p-4 backdrop-blur">
                    <i class="fas fa-car text-3xl mb-2"></i>
                    <h3 class="font-bold">50 Parking Slots</h3>
                    <p class="text-sm">Ample parking space</p>
                </div>
                <div class="bg-white/10 rounded-lg p-4 backdrop-blur">
                    <i class="fas fa-clock text-3xl mb-2"></i>
                    <h3 class="font-bold">24/7 Access</h3>
                    <p class="text-sm">Park anytime</p>
                </div>
                <div class="bg-white/10 rounded-lg p-4 backdrop-blur">
                    <i class="fas fa-shield-alt text-3xl mb-2"></i>
                    <h3 class="font-bold">Secure Parking</h3>
                    <p class="text-sm">CCTV monitored</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
