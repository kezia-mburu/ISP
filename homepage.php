<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mtaakonnect Technologies - Fiber Internet</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
  <style>
    body { font-family: 'Inter', sans-serif; }
  </style>
  <!-- Header -->
 <?php require_once 'header.php'; ?>
</head>
<body class="bg-gray-50 text-gray-800">
<main>
<!-- Hero Section -->
<section class="bg-gradient-to-r from-cyan-500 to-orange-500 text-white py-20 px-4 md:py-32 rounded-b-[40px] shadow-lg">
  <div class="container mx-auto text-center">
    <h1 class="text-4xl md:text-6xl font-extrabold leading-tight mb-4 drop-shadow-md">
      Blazing-Fast Fiber Internet<br class="hidden md:inline">across 
  </h1>
    <p class="text-lg md:text-xl text-white/90 mb-8 max-w-2xl mx-auto drop-shadow">
      Say goodbye to slow connections. Mtaakonnect delivers reliable, high-speed fiber right to your home or business.
    </p>
    <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
      <a href="#packages" class="px-8 py-3 bg-white text-orange-500 text-lg font-medium rounded-full hover:bg-gray-100 transition shadow-lg">
        View Packages
      </a>
      <a href="#contact" class="px-8 py-3 text-lg font-medium text-white border border-white rounded-full hover:bg-white hover:text-orange-500 transition shadow-lg">
        Check Availability
      </a>
    </div>
  </div>
</section>

<!-- Features Section -->
<section id="features" class="py-20 px-4 md:py-24 bg-gray-50">
  
<div class="container mx-auto">
  <div class="text-center mb-16">
    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Why Choose Mtaakonnect?</h2>
    <p class="text-lg text-gray-500 max-w-xl mx-auto">
      We’re a local team bringing world-class fiber internet.
    </p>
  </div>

  <!-- Two Image Section -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
    <!-- First Image -->
    <div class="overflow-hidden rounded-2xl shadow-lg hover:shadow-2xl transition transform hover:scale-[1.02]">
      <img src="bg.png" alt="Technician on pole installing internet cables" class="w-full h-full object-cover">
    </div>
    <!-- Second Image -->
    <div class="overflow-hidden rounded-2xl shadow-lg hover:shadow-2xl transition transform hover:scale-[1.02]">
      <img src="man.jpg" alt="Technician inspecting fiber installation indoors" class="w-full h-full object-cover">
    </div>
  </div>
</div>


    <!-- Packages Section -->
    <section id="packages" class="py-20 px-4 md:py-24 bg-white">
  <div class="container mx-auto">
<section 
  class="relative bg-cover bg-center bg-no-repeat py-20 px-4 md:py-24" 
  style="background-image: url('bg.jpg');"
>
  <!-- Overlay for better text contrast -->
  <div class="absolute inset-0 bg-black/40"></div>

  <div class="container mx-auto relative z-10">
    <!-- Highlighted installation info -->
    <div class="bg-orange-500 text-white rounded-2xl shadow-md py-3 px-6 mb-10 max-w-2xl mx-auto">
      <p class="text-lg font-medium text-center">
        One-time installation fee: <span class="font-bold">KES 3,500</span> — 
        <span class="font-bold">First month FREE!</span>
      </p>
    </div>
    <!-- Packages heading -->
    <div class="text-center mb-16">
      <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">Our Internet Packages</h2>
      <p class="text-lg text-gray-100 max-w-xl mx-auto">
        Choose a plan that fits your home or business. 
        Enjoy unlimited data and reliable 24/7 support.
      </p>
    </div>
  </div>
</section>
  </div>
</section>

    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <!-- Bronze -->
      <div class="bg-gray-50 rounded-2xl shadow-md hover:shadow-lg hover:scale-105 transform transition p-8 border-t-8 border-orange-400">
        <h3 class="text-2xl font-bold text-gray-900 mb-2 text-center">Bronze</h3>
        <p class="text-center text-gray-500 mb-6">Great for light browsing and emails.</p>
        <p class="text-center text-4xl font-extrabold text-orange-500 mb-6">10 Mbps</p>
        <p class="text-center text-gray-700 text-lg mb-6">KES 1,000 / month</p>
        <ul class="space-y-3 text-sm text-gray-600 mb-8">
          <li class="flex items-center"><span class="w-2 h-2 bg-orange-400 rounded-full mr-2"></span> Unlimited data</li>
          <li class="flex items-center"><span class="w-2 h-2 bg-orange-400 rounded-full mr-2"></span> Free router setup</li>
          <li class="flex items-center"><span class="w-2 h-2 bg-orange-400 rounded-full mr-2"></span> 24/7 support</li>
        </ul>
        <div class="text-center">
          <a href="#contact" class="inline-block px-6 py-3 bg-orange-500 text-white rounded-full hover:bg-orange-600 transition shadow-md">
            Get Bronze
          </a>
        </div>
      </div>

      <!-- Silver -->
      <div class="bg-gray-50 rounded-2xl shadow-md hover:shadow-lg hover:scale-105 transform transition p-8 border-t-8 border-cyan-400">
        <h3 class="text-2xl font-bold text-gray-900 mb-2 text-center">Silver</h3>
        <p class="text-center text-gray-500 mb-6">Perfect for streaming and remote work.</p>
        <p class="text-center text-4xl font-extrabold text-cyan-500 mb-6">20 Mbps</p>
        <p class="text-center text-gray-700 text-lg mb-6">KES 2,000 / month</p>
        <ul class="space-y-3 text-sm text-gray-600 mb-8">
          <li class="flex items-center"><span class="w-2 h-2 bg-cyan-400 rounded-full mr-2"></span> Unlimited data</li>
          <li class="flex items-center"><span class="w-2 h-2 bg-cyan-400 rounded-full mr-2"></span> Free router setup</li>
          <li class="flex items-center"><span class="w-2 h-2 bg-cyan-400 rounded-full mr-2"></span> Priority support</li>
        </ul>
        <div class="text-center">
          <a href="#contact" class="inline-block px-6 py-3 bg-cyan-500 text-white rounded-full hover:bg-cyan-600 transition shadow-md">
            Get Silver
          </a>
        </div>
      </div>

      <!-- Gold -->
      <div class="bg-gray-50 rounded-2xl shadow-md hover:shadow-lg hover:scale-105 transform transition p-8 border-t-8 border-yellow-400">
        <h3 class="text-2xl font-bold text-gray-900 mb-2 text-center">Gold</h3>
        <p class="text-center text-gray-500 mb-6">For heavy users, gaming, and small businesses.</p>
        <p class="text-center text-4xl font-extrabold text-yellow-500 mb-6">30 Mbps</p>
        <p class="text-center text-gray-700 text-lg mb-6">KES 3,000 / month</p>
        <ul class="space-y-3 text-sm text-gray-600 mb-8">
          <li class="flex items-center"><span class="w-2 h-2 bg-yellow-400 rounded-full mr-2"></span> Unlimited data</li>
          <li class="flex items-center"><span class="w-2 h-2 bg-yellow-400 rounded-full mr-2"></span> Free router setup</li>
          <li class="flex items-center"><span class="w-2 h-2 bg-yellow-400 rounded-full mr-2"></span> VIP support</li>
        </ul>
        <div class="text-center">
          <a href="#contact" class="inline-block px-6 py-3 bg-yellow-500 text-white rounded-full hover:bg-yellow-600 transition shadow-md">
            Get Gold
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

    </div>
  </div>
</section>
</main>

<!-- Import the shared footer file -->
<?php require_once 'footer.php'; ?>

<script>
  const btn = document.getElementById('mobile-menu-button');
  const menu = document.getElementById('mobile-menu');
  btn.addEventListener('click', () => {
    menu.classList.toggle('-translate-y-full');
  });
</script>

</body>
</html>
