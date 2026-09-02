<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>SMTSC | Van Booking</title>
	<!-- Favicon -->
	<link rel="icon" type="image/png" href="assets/image/smtsc logo.png">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
	<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gradient-to-br from-blue-50 to-green-50 text-gray-800">
	<!-- Header -->
	<header class="w-full bg-white/80 backdrop-blur border-b border-gray-100">
		<div class="w-full px-6">
			<div class="h-16 flex items-center justify-between">
				<a href="#" class="flex items-center gap-2">
					<img src="assets/image/smtsc logo.png" alt="SMTSC Logo" class="w-8 h-8 object-contain">
					<span class="font-bold">SMTSC</span>
				</a>
				<nav class="hidden md:flex items-center gap-6">
					<a href="#about" class="text-sm hover:text-blue-600">About</a>
					<a href="#features" class="text-sm hover:text-blue-600">Features</a>
					<a href="#contact" class="text-sm hover:text-blue-600">Contact</a>
				</nav>
				<a href="login.php"
					class="hidden md:inline-flex items-center bg-blue-600 text-white text-sm font-semibold px-4 py-2 rounded hover:bg-blue-700 transition">
					<i class="fa-solid fa-right-to-bracket mr-2"></i>Login
				</a>
			</div>
		</div>
	</header>

	<section class="relative overflow-hidden">
		<div class="max-w-6xl mx-auto px-4 py-16 md:py-24">
			<div class="grid md:grid-cols-2 gap-16 items-center">
				<div>
					<h1 class="text-4xl md:text-5xl font-extrabold leading-tight mb-4">Ride Smarter with SMTSC</h1>
					<p class="text-gray-600 mb-6 text-lg">Streamlined van booking and operations for reliable,
						comfortable, and on‑time journeys.</p>
					<div class="flex items-center gap-3">
						
					</div>
					<div class="mt-8 grid grid-cols-3 gap-4 max-w-md">
						<div class="p-4 rounded-lg bg-white/70 text-center shadow-sm">
							<div class="text-2xl font-bold">20+</div>
							<div class="text-xs text-gray-500">Active Vans</div>
						</div>
						<div class="p-4 rounded-lg bg-white/70 text-center shadow-sm">
							<div class="text-2xl font-bold">1K+</div>
							<div class="text-xs text-gray-500">Monthly Riders</div>
						</div>
						<div class="p-4 rounded-lg bg-white/70 text-center shadow-sm">
							<div class="text-2xl font-bold">15</div>
							<div class="text-xs text-gray-500">Destinations</div>
						</div>
					</div>
				</div>
				<div class="relative">
					<div class="relative bg-white rounded-lg shadow-xl border border-gray-200 p-8 w-[500px] h-[450px] flex flex-col justify-center">
						<div class="text-center mb-8">
					<div class="h-16 w-16 flex items-center justify-center mx-auto mb-4">
						<img src="assets/image/smtsc logo.png" alt="SMTSC Logo" class="w-full h-full object-contain">
					</div>
							<h3 class="text-2xl font-bold text-gray-800 mb-2">Admin Login</h3>
							<p class="text-gray-500 text-sm">Sign in to manage your system</p>
						</div>
						<form method="POST" action="login.php" class="space-y-4">
							<div>
								<label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
								<input type="text" name="username" required
									class="w-full border-gray-300 rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-blue-500" />
							</div>
							<div>
								<label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
								<input type="password" name="password" required
									class="w-full border-gray-300 rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-blue-500" />
							</div>
							<button type="submit"
								class="w-full inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-3 rounded-lg transition text-base mt-6">
								<i class="fa-solid fa-right-to-bracket mr-2"></i>Login
							</button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- Features -->
	<section id="features" class="bg-white border-t border-gray-100">
		<div class="max-w-7xl mx-auto px-6 py-16">
			<h2 class="text-2xl font-bold mb-8">Why choose SMTSC</h2>
			<div class="grid md:grid-cols-3 gap-6">
				<div class="p-6 rounded-xl border border-gray-100 shadow-sm bg-gray-50">
					<div class="text-blue-600 mb-3"><i class="fa-solid fa-calendar-check"></i></div>
					<h3 class="font-semibold mb-2">Smart Scheduling</h3>
					<p class="text-sm text-gray-600">Plan and manage trips efficiently with real-time availability.</p>
				</div>
				<div class="p-6 rounded-xl border border-gray-100 shadow-sm bg-gray-50">
					<div class="text-green-600 mb-3"><i class="fa-solid fa-shield-halved"></i></div>
					<h3 class="font-semibold mb-2">Reliable & Safe</h3>
					<p class="text-sm text-gray-600">Trusted drivers and well-maintained vans ensure safe rides.</p>
				</div>
				<div class="p-6 rounded-xl border border-gray-100 shadow-sm bg-gray-50">
					<div class="text-purple-600 mb-3"><i class="fa-solid fa-gauge-high"></i></div>
					<h3 class="font-semibold mb-2">Optimized Operations</h3>
					<p class="text-sm text-gray-600">Keep track of vans, fares, and bookings from one dashboard.</p>
				</div>
			</div>
		</div>
	</section>

	<!-- About -->
	<section id="about" class="bg-white border-t border-gray-100">
		<div class="max-w-6xl mx-auto px-4 py-16">
			<h2 class="text-2xl font-bold mb-4">About SMTSC</h2>
			<p class="text-gray-600 max-w-3xl">SMTSC is a transport cooperative dedicated to providing safe and reliable
				van services. Our booking system streamlines trip scheduling, passenger management, and fare
				calculations to keep operations smooth and riders happy.</p>
		</div>
	</section>

	<footer id="contact" class="border-t border-gray-100 py-8 text-center text-sm text-gray-500">
		&copy; <?php echo date('Y'); ?> SMTSC. All rights reserved.
	</footer>
</body>

</html>