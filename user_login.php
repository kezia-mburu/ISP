<?php
// Start a PHP session to store user login status
session_start();

// Include the centralized database connection file
require_once 'db.php';

$message = ""; // Variable to hold the success or error message

// --- Process Form Submission ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate user input
    $client_phone = htmlspecialchars(trim($_POST['client_phone']));
    $client_password = $_POST['client_password'];

    // Simple validation to ensure fields are not empty
    if (empty($client_phone) || empty($client_password)) {
        $message = "<div class='bg-red-900/50 text-red-300 p-4 rounded-lg'>All fields are required.</div>";
    } elseif (!preg_match('/^(07|01)\d{8}$/', $client_phone)) {
        $message = "<div class='bg-red-900/50 text-red-300 p-4 rounded-lg'>Invalid phone number format.</div>";
    } else {
        // Prepare and execute the SQL statement to fetch user data based on the username (which is the phone number)
        $sql = "SELECT client_id, client_name, client_password FROM clients WHERE username = ?";
        $stmt = $conn->prepare($sql);

        // Check if the prepare statement was successful
        if ($stmt === false) {
            // This is the source of your Fatal Error. Log the error for debugging.
            $message = "<div class='bg-red-900/50 text-red-300 p-4 rounded-lg'>Database query failed to prepare.</div>";
            error_log("Prepare failed: " . $conn->error);
        } else {
            $stmt->bind_param("s", $client_phone);
            $stmt->execute();
            $result = $stmt->get_result();
            
            // Check if a user was found
            if ($result->num_rows === 1) {
                $client = $result->fetch_assoc();
                
                // Verify the submitted password against the hashed password in the database
                if (password_verify($client_password, $client['client_password'])) {
                    // Password is correct, start a new session
                    $_SESSION['loggedin'] = true;
                    $_SESSION['client_id'] = $client['client_id'];
                    $_SESSION['username'] = $client['client_name']; 
                    
                    // Redirect to the client dashboard
                    header("Location: dashboard.php");
                    exit;
                } else {
                    // Incorrect password
                    $message = "<div class='bg-red-900/50 text-red-300 p-4 rounded-lg'>Invalid phone number or password.</div>";
                }
            } else {
                // User not found
                $message = "<div class='bg-red-900/50 text-red-300 p-4 rounded-lg'>Invalid phone number or password.</div>";
            }
            $stmt->close();
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Include the shared header file -->
    <?php require_once 'header.php'; ?>
    <!-- Use Tailwind CSS for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            @apply bg-slate-950 text-slate-200;
        }
    </style>
</head>
<body class="flex flex-col min-h-screen p-6">

    <!-- The main content container that will fill remaining space and center its content -->
    <div class="flex-grow flex items-center justify-center">
        <div class="bg-orange-950 rounded-2xl shadow-xl p-8 max-w-lg w-full border border-orange-700/20">
            <h1 class="text-3xl font-bold text-center text-orange-300 mb-6">Client Login</h1>

            <!-- Message container for success or error messages -->
            <div class="mb-6">
                <?php echo $message; ?>
            </div>

            <form action="user_login.php" method="POST" class="space-y-4">
                <div>
                    <label for="client_phone" class="sr-only">Phone Number</label>
                    <input type="tel" id="client_phone" name="client_phone" placeholder="Phone Number"
                           class="w-full p-3 rounded-lg bg-orange-900 text-slate-200 placeholder-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all duration-200" required>
                </div>
                <div>
                    <label for="client_password" class="sr-only">Password</label>
                    <input type="password" id="client_password" name="client_password" placeholder="Password"
                           class="w-full p-3 rounded-lg bg-orange-900 text-slate-200 placeholder-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all duration-200" required>
                </div>
                <button type="submit"
                        class="w-full bg-orange-600 hover:bg-orange-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-300 transform hover:scale-105 shadow-md">
                    Log In
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-400">
                Don't have an account? <a href="user_registration.php" class="text-orange-400 hover:underline">Register here</a>
            </p>
        </div>
    </div>
    <br>
    <br>
    <!-- Import the shared footer file -->
    <?php require_once 'footer.php'; ?>

</body>
</html>
