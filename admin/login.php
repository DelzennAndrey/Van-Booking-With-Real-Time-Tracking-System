<?php
require_once 'includes/db_connect.php';
require_once 'includes/session.php';


$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Sanitize and validate input
	$username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
	$password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
	
	// Additional trimming and validation
	$username = trim($username);
	$password = trim($password);
	
	// Validate input length and content
	if (empty($username) || empty($password)) {
		$error = 'Please fill in all fields.';
	} elseif (strlen($username) < 3 || strlen($username) > 50) {
		$error = 'Username must be between 3 and 50 characters.';
	} elseif (strlen($password) < 6) {
		$error = 'Password must be at least 6 characters long.';
	} else {
		// Validate against the admin table per ERD (username/password stored in admin)
		$stmt = $conn->prepare('SELECT admin_id, password FROM admin WHERE username = ? LIMIT 1');
		$stmt->bind_param('s', $username);
		$stmt->execute();
		$stmt->store_result();

		if ($stmt->num_rows === 1) {
			$stmt->bind_result($admin_id, $hashed_password);
			$stmt->fetch();
			if (password_verify($password, $hashed_password)) {
				// Ensure logs table exists (for environments where schema isn't imported)
				$conn->query("CREATE TABLE IF NOT EXISTS `logs` (
				  `log_id` int(11) NOT NULL AUTO_INCREMENT,
				  `admin_id` int(11) NOT NULL,
				  `action` varchar(50) NOT NULL,
				  `ip_address` varchar(45) DEFAULT NULL,
				  `user_agent` varchar(255) DEFAULT NULL,
				  `created_at` datetime NOT NULL,
				  PRIMARY KEY (`log_id`),
				  KEY `idx_logs_admin_id` (`admin_id`),
				  KEY `idx_logs_action` (`action`),
				  KEY `idx_logs_created_at` (`created_at`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

				// Capture request meta
				$ip = $_SERVER['REMOTE_ADDR'] ?? null;
				$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
				if (strlen($ua) > 255) { $ua = substr($ua, 0, 255); }

				// Insert login log (ignore failures)
				$logStmt = $conn->prepare('INSERT INTO logs (admin_id, action, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, NOW())');
				if ($logStmt) {
					$action = 'login';
					$logStmt->bind_param('isss', $admin_id, $action, $ip, $ua);
					$logStmt->execute();
					$logStmt->close();
				}

				$_SESSION['admin_id'] = $admin_id;
				header('Location: dashboard.php');
				exit();
			} else {
				$error = 'Invalid username or password.';
			}
		} else {
			$error = 'Invalid username or password.';
		}
		$stmt->close();
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Admin Login</title>
	<!-- Favicon -->
	<link rel="icon" type="image/png" href="assets/image/smtsc logo.png">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
	<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gradient-to-br from-blue-50 to-green-50 p-4">
	<header class="w-full">
		<div class="bg-transparent px-8 py-6 flex items-center justify-between">
			<a href="index.php" class="flex items-center space-x-2 text-gray-700 hover:text-blue-600">
				<i class="fa-solid fa-angle-left"></i>
				<span class="text-sm font-medium">Back to Landing</span>
			</a>
			<a href="index.php" class="flex items-center space-x-2">
				<img src="assets/image/smtsc logo.png" alt="SMTSC Logo" class="w-6 h-6 object-contain">
				<span class="font-semibold">SMTSC</span>
			</a>
		</div>
	</header>
	<div class="w-full max-w-md mx-auto flex items-center justify-center">
		<div class="bg-white rounded-2xl shadow-xl p-8">
			<div class="flex items-center justify-center mb-6">
				<div class="h-20 w-20 flex items-center justify-center">
					<img src="assets/image/smtsc logo.png" alt="SMTSC Logo" class="w-full h-full object-contain">
				</div>
			</div>
			<h1 class="text-2xl font-bold text-center text-gray-800 mb-2">Admin Login</h1>
			<p class="text-center text-gray-500 mb-6">Sign in to manage trips, drivers, and bookings</p>
			<?php if ($error): ?>
				<div class="mb-4 bg-red-50 text-red-700 border border-red-200 rounded px-4 py-3 text-sm"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
			<?php endif; ?>
			<form method="POST" action="" class="space-y-4">
				<div>
					<label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
					<input type="text" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8') : ''; ?>" required autofocus class="w-full border-gray-300 rounded px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500" />
				</div>
				<div>
					<label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
					<input type="password" id="password" name="password" required class="w-full border-gray-300 rounded px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500" />
				</div>
				<button type="submit" class="w-full inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-3 rounded-lg transition">
					<i class="fa-solid fa-right-to-bracket mr-2"></i>Login
				</button>
			</form>
		</div>
		
	</div>
</body>
</html>
