<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SMTSC Admin Panel</title>
  <!-- Favicon -->
  <link rel="icon" type="image/png" href="assets/image/smtsc logo.png">
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Font Awesome for icons -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
  <?php require_once __DIR__ . '/utils.php'; ?>
  <!-- Custom styles -->
  <style>
    .sidebar-transition { 
      transition: none; 
    }
    .nav-item { 
      transition: all 0.3s ease; 
    }
    .nav-item:hover { 
      background-color: #f3f4f6; 
      transform: translateX(4px);
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .nav-item.active {
      transition: all 0.3s ease;
    }
    .card-hover { 
      transition: all 0.3s ease; 
      will-change: transform, box-shadow;
    }
    .card-hover:hover { 
      transform: translateY(-2px); 
      box-shadow: 0 10px 25px rgba(0,0,0,0.1); 
    }
    .status-badge { 
      transition: all 0.2s ease; 
    }
    /* Page layout */
    body {
      overflow-x: hidden;
    }
    main {
      position: relative;
      z-index: 10;
    }
    /* Prevent layout shifts and ensure stable sidebar */
    aside {
      position: fixed !important;
      transform: none !important;
      top: 0 !important;
      left: 0 !important;
      width: 16rem !important;
      height: 100vh !important;
    }
  </style>
</head>

<body class="bg-gray-50 min-h-screen">
  <div class="flex min-h-screen">
    <!-- Enhanced Sidebar -->
    <aside class="w-64 bg-white shadow-xl h-screen flex flex-col fixed left-0 top-0 z-30 sidebar-transition">
      <!-- Logo Section -->
      <div class="p-4 border-b border-gray-200">
        <div class="flex items-center space-x-3">
          <div class="w-8 h-8 flex items-center justify-center">
            <img src="assets/image/smtsc logo.png" alt="SMTSC Logo" class="w-full h-full object-contain">
          </div>
          <div>
            <span class="text-lg font-bold text-gray-800">SMTSC</span>
            <p class="text-xs text-gray-500">Admin Panel</p>
          </div>
        </div>
      </div>
      
      <!-- Navigation -->
      <nav class="flex-1 px-3 py-4 space-y-1">
        <a href="dashboard.php" class="<?php echo getNavItemClasses('dashboard'); ?>">
          <i class="fas fa-tachometer-alt w-5 mr-3"></i>
          Dashboard
        </a>
        <a href="trips.php" class="<?php echo getNavItemClasses('trips'); ?>">
          <i class="fas fa-route w-5 mr-3"></i>
          Trips
        </a>
        <a href="routes.php" class="<?php echo getNavItemClasses('routes'); ?>">
          <i class="fas fa-road w-5 mr-3"></i>
          Routes
        </a>
        <a href="bookings.php" class="<?php echo getNavItemClasses('bookings'); ?>">
          <i class="fas fa-calendar-check w-5 mr-3"></i>
          Bookings
        </a>
        <a href="vans.php" class="<?php echo getNavItemClasses('vans'); ?>">
          <i class="fas fa-bus w-5 mr-3"></i>
          Vans
        </a>
        <a href="drivers.php" class="<?php echo getNavItemClasses('drivers'); ?>">
          <i class="fas fa-user-tie w-5 mr-3"></i>
          Drivers
        </a>
        <a href="passengers.php" class="<?php echo getNavItemClasses('passengers'); ?>">
          <i class="fas fa-users w-5 mr-3"></i>
          Passengers
        </a>
        <a href="discounts.php" class="<?php echo getNavItemClasses('discounts'); ?>">
          <i class="fas fa-percentage w-5 mr-3"></i>
          Discounts
        </a>
        <a href="fare_settings.php" class="<?php echo getNavItemClasses('fare_settings'); ?>">
          <i class="fas fa-cog w-5 mr-3"></i>
          Fare Settings
        </a>
        <!-- <a href="reports.php"
          class="nav-item flex items-center px-4 py-3 rounded-lg text-gray-700 font-medium <?php if (basename($_SERVER['PHP_SELF']) == 'reports.php')
            echo 'bg-blue-50 text-blue-700 border-r-2 border-blue-600'; else echo 'hover:bg-gray-50'; ?>">
          <i class="fas fa-chart-bar w-5 mr-3"></i>
          Reports
        </a> -->
      </nav>
      
      <!-- User Section -->
      <div class="mt-auto p-4 border-t border-gray-200">
        <div class="flex items-center space-x-3 mb-4">
          <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
            <i class="fas fa-user text-gray-600 text-sm"></i>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-800">Admin User</p>
            <p class="text-xs text-gray-500">Administrator</p>
          </div>
        </div>
        <a href="logout.php"
          class="block w-full text-center bg-red-500 text-white py-2 rounded-lg hover:bg-red-600 font-semibold transition duration-200 flex items-center justify-center">
          <i class="fas fa-sign-out-alt mr-2"></i>
          Log Out
        </a>
      </div>
    </aside>
    
    <!-- Main content wrapper -->
    <main class="flex-1 ml-64 flex flex-col min-h-screen">