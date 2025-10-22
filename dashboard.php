<?php
// Include the shared header file
require_once 'header.php';

// Include the centralized database connection file
require_once 'db.php';

// Get user details from the session
$client_name = htmlspecialchars($_SESSION['username']);
$client_id = $_SESSION['client_id'];

// Initialize variables with default values
$client_data = null;
$package_name = "Not Subscribed";
$balance = "0.00";
$next_due_date = "N/A";

// Query to get the client's account summary details
$sql = "SELECT c.client_id, c.client_phone, c.client_email, c.client_area, c.balance, p.package_name, c.next_due_date
        FROM clients AS c
        LEFT JOIN packages AS p ON c.package_id = p.package_id
        WHERE c.client_id = ?";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $client_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $client_data = $result->fetch_assoc();
        $balance = $client_data['balance'];
        $package_name = $client_data['package_name'] ? htmlspecialchars($client_data['package_name']) : "Not Subscribed";
        $next_due_date = $client_data['next_due_date'] ? htmlspecialchars($client_data['next_due_date']) : "N/A";
    }
    $stmt->close();
} else {
    // This is a critical error, so we will terminate script execution.
    die("Error preparing statement: " . $conn->error);
}

// Close the database connection
$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- Include Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            @apply bg-gray-100 text-gray-800;
        }
    </style>
</head>
<body class="flex flex-col min-h-screen p-6">

    <!-- The main content container that will fill remaining space and center its content -->
    <div class="flex-grow flex items-center justify-center">
        <div class="bg-white rounded-2xl shadow-xl p-8 max-w-2xl w-full border-4 border-cyan-500">
            <h1 class="text-3xl font-bold text-center text-cyan-600 mb-6">Client Dashboard</h1>

            <div class="mb-6 text-center">
                <h2 class="text-2xl font-semibold mb-2 text-gray-700">Welcome, <span class="text-orange-600"><?php echo $client_name; ?></span>!</h2>
                <p class="text-gray-500">This is your personal dashboard. You can view your account details and manage your services here.</p>
            </div>

            <div class="grid grid-cols-1 gap-6 mt-6">
                <!-- Account Summary Card -->
                <div class="bg-gray-50 rounded-xl p-6 shadow-md border border-gray-200 transition-transform duration-300 transform hover:scale-105">
                    <h3 class="text-xl font-bold mb-2 text-cyan-600">Account Summary</h3>
                    <p class="text-gray-600">Current Balance: <span class="font-bold text-orange-600">KES <?php echo htmlspecialchars(number_format($balance, 2)); ?></span></p>
                    <p class="text-gray-600">Package: <span class="font-bold text-orange-600"><?php echo $package_name; ?></span></p>
                    <p class="text-gray-600">Next Due Date: <span class="font-bold text-orange-600"><?php echo $next_due_date; ?></span></p>
                </div>
            </div>

            <div class="mt-8 text-center">
                <a href="logout.php" class="inline-block bg-orange-600 hover:bg-orange-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-300 transform hover:scale-105 shadow-md">
                    Log Out
                </a>
            </div>
        </div>
    </div>
    <br>
    <br>
    <!-- Import the shared footer file -->
    <?php require_once 'footer.php'; ?>
</body>
</html>
