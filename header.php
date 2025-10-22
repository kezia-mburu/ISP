<?php
// Start the session. This MUST be the first line of code on every page.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get the name of the current page.
$current_page = basename($_SERVER['PHP_SELF']);

// Define the public pages that do not require a user to be logged in.
$public_pages = [
    'homepage.php',
    'client_packages.php',
    'coverage.php',
    'contactus.php',
    'user_login.php',
    'user_registration.php',
    'aboutus.php'
];

// --- SECURITY CHECK: Redirect users who are not logged in ---
// Check if the user is not logged in AND the current page is a protected one.
if (!isset($_SESSION['username']) && !in_array($current_page, $public_pages)) {
    header('Location: user_login.php');
    exit();
}

// --- USER EXPERIENCE: Redirect logged-in users away from the login page ---
if (isset($_SESSION['username']) && $current_page === 'user_login.php') {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wifimtaani</title>
    <!-- Use Tailwind CSS for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/x-icon" href="favicon.png">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <nav class="container mx-auto px-4 py-4 flex justify-between items-center relative">
            <!-- Logo -->
            <a href="homepage.php" class="flex items-center space-x-2">
                <img src="logo1.png" alt="Mtaakonnect Logo" class="h-10 w-auto">
            </a>

            <!-- Desktop Menu -->
            <div class="hidden md:flex space-x-6 items-center">

                <!-- Internet Dropdown (was Packages) -->
                <div class="group relative">
                    <a href="client_packages.php" class="text-gray-600 hover:text-orange-500 transition">Internet</a>
                    <div class="absolute left-0 mt-3 w-64 bg-white shadow-lg rounded-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 p-4 grid gap-4">
                        <div class="p-3 border rounded-lg hover:bg-orange-50 transition cursor-pointer">
                            <h4 class="font-semibold text-gray-800">Bronze</h4>
                            <p class="text-sm text-gray-500">10 Mbps — perfect for light use.</p>
                        </div>
                        <div class="p-3 border rounded-lg hover:bg-orange-50 transition cursor-pointer">
                            <h4 class="font-semibold text-gray-800">Silver</h4>
                            <p class="text-sm text-gray-500">20 Mbps — great for work & streaming.</p>
                        </div>
                        <div class="p-3 border rounded-lg hover:bg-orange-50 transition cursor-pointer">
                            <h4 class="font-semibold text-gray-800">Gold</h4>
                            <p class="text-sm text-gray-500">40 Mbps — best for businesses.</p>
                        </div>
                    </div>
                </div>

                <!-- Coverage link -->
                <a href="coverage.php" class="text-gray-600 hover:text-orange-500 transition">Coverage</a>

                <!-- About Dropdown -->
                <div class="group relative">
                    <a href="aboutus.php" class="text-gray-600 hover:text-orange-500 transition">About</a>
                    <div class="absolute left-0 mt-3 w-72 bg-white shadow-lg rounded-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 p-4 grid gap-4">
                        <div class="p-3 border rounded-lg hover:bg-orange-50 transition cursor-pointer">
                            <h4 class="font-semibold text-gray-800">Mtaakonnect</h4>
                            <p class="text-sm text-gray-500">Fast, reliable internet for Mwea.</p>
                        </div>
                        <div class="p-3 border rounded-lg hover:bg-orange-50 transition cursor-pointer">
                            <h4 class="font-semibold text-gray-800">Team</h4>
                            <p class="text-sm text-gray-500">Meet our local experts.</p>
                        </div>
                        <div class="p-3 border rounded-lg hover:bg-orange-50 transition cursor-pointer">
                            <h4 class="font-semibold text-gray-800">Careers</h4>
                            <p class="text-sm text-gray-500">Join us to build the future.</p>
                        </div>
                    </div>
                </div>

                <!-- Contact link -->
                <a href="contactus.php" class="text-gray-600 hover:text-orange-500 transition">Contact us</a>

                <!-- Login/Logout & Dashboard links -->
                <?php if (isset($_SESSION['username'])): ?>
                    <span class="text-gray-700 font-medium">Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                    <a href="payment.php" class="text-gray-500 hover:text-orange-500 font-medium transition-colors">Payment</a>
                    <a href="dashboard.php" class="px-4 py-2 bg-orange-500 text-white rounded-full font-medium hover:bg-orange-600 transition shadow-md">Dashboard</a>
                    <a href="logout.php" class="px-4 py-2 bg-red-500 text-white rounded-full font-medium hover:bg-red-600 transition">Logout</a>
                <?php else: ?>
                    <a href="user_login.php" class="px-4 py-2 bg-orange-500 text-white rounded-full hover:bg-orange-600 transition shadow-md">Get Connected</a>
                <?php endif; ?>
            </div>

            <!-- Mobile Menu Button -->
            <button id="mobile-menu-button" class="md:hidden text-gray-600 hover:text-orange-500 focus:outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
                </svg>
            </button>
        </nav>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="md:hidden bg-white shadow-md transition-all duration-300 transform -translate-y-full absolute top-full left-0 w-full z-40">
            <a href="client_packages.php" class="block px-4 py-3 text-gray-600 hover:bg-gray-100 hover:text-orange-500 transition">Internet Plans</a>
            <a href="coverage.php" class="block px-4 py-3 text-gray-600 hover:bg-gray-100 hover:text-orange-500 transition">Coverage</a>
            <a href="aboutus.php" class="block px-4 py-3 text-gray-600 hover:bg-gray-100 hover:text-orange-500 transition">About Us</a>
            <a href="contactus.php" class="block px-4 py-3 text-gray-600 hover:bg-gray-100 hover:text-orange-500 transition">Contact Us</a>
            <?php if (isset($_SESSION['username'])): ?>
                <a href="dashboard.php" class="block px-4 py-3 text-white bg-orange-500 text-center hover:bg-orange-600 transition">Dashboard</a>
                <a href="payment.php" class="block px-4 py-3 text-white bg-orange-500 text-center hover:bg-orange-600 transition">Payment</a>
                <a href="logout.php" class="block px-4 py-3 text-white bg-red-500 text-center hover:bg-red-600 transition">Logout</a>
            <?php else: ?>
                <a href="user_login.php" class="block px-4 py-3 text-white bg-orange-500 text-center hover:bg-orange-600 transition">Get Connected</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Keep this small script wherever you prefer (end of body is fine) -->
    <script>
        const btn = document.getElementById('mobile-menu-button');
        const menu = document.getElementById('mobile-menu');
        if (btn && menu) {
            btn.addEventListener('click', () => {
                menu.classList.toggle('-translate-y-full');
            });
        }
    </script>
