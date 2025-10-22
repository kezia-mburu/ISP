<?php
    // --- Use a centralized database connection ---
    // This file now assumes that a 'db.php' file exists in the same directory
    // and contains the database connection logic.
    require_once 'db.php';

    $message = ""; // Variable to hold the success or error message

    // --- Process Form Submission ---
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Sanitize and validate user input
        $client_name = htmlspecialchars(trim($_POST['client_name']));
        $client_phone = htmlspecialchars(trim($_POST['client_phone']));
        $client_email = htmlspecialchars(trim($_POST['client_email']));
        $client_area = htmlspecialchars(trim($_POST['client_area']));

        // Simple validation to ensure fields are not empty
        if (empty($client_name) || empty($client_phone) || empty($client_email) || empty($client_area)) {
            $message = "<div class='bg-red-900/50 text-red-300 p-4 rounded-lg'>All fields are required.</div>";
        } elseif (!filter_var($client_email, FILTER_VALIDATE_EMAIL)) {
            $message = "<div class='bg-red-900/50 text-red-300 p-4 rounded-lg'>Invalid email format.</div>";
        } elseif (!preg_match('/^(07|01)\d{8}$/', $client_phone)) {
            $message = "<div class='bg-red-900/50 text-red-300 p-4 rounded-lg'>Invalid phone number format. Please use 07XXXXXXXX or 01XXXXXXXX.</div>";
        } else {
            // Check if the email or phone number already exists in the database
            $sql_check = "SELECT client_id FROM clients WHERE client_email = ? OR client_phone = ?";
            $stmt_check = $conn->prepare($sql_check);
            
            // Convert phone to 254 format for database check and storage
            $phone_254_format = '254' . substr($client_phone, 1);
            
            $stmt_check->bind_param("ss", $client_email, $phone_254_format);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows > 0) {
                $message = "<div class='bg-red-900/50 text-red-300 p-4 rounded-lg'>This email or phone number is already registered.</div>";
            } else {
                // Set username and password to the original phone number
                $username = $client_phone;
                $client_password = $client_phone;
                
                // Hash the password for security
                $hashed_password = password_hash($client_password, PASSWORD_DEFAULT);
                
                // Prepare and execute the SQL statement to insert the new user
                $sql_insert = "INSERT INTO clients (client_name, username, client_email, client_phone, client_area, client_password) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt_insert = $conn->prepare($sql_insert);
                $stmt_insert->bind_param("ssssss", $client_name, $username, $client_email, $phone_254_format, $client_area, $hashed_password);

                if ($stmt_insert->execute()) {
                    $message = "<div class='bg-green-900/50 text-green-300 p-4 rounded-lg'>Registration successful! You can now log in.</div>";
                } else {
                    $message = "<div class='bg-red-900/50 text-red-300 p-4 rounded-lg'>Error: Could not register user.</div>";
                }

                $stmt_insert->close();
            }

            $stmt_check->close();
        }
    }

    // Close the connection at the end of the script
    $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            @apply bg-slate-950 text-slate-200;
        }
    </style>
    <?php require_once 'header.php'; ?>
    <br>
</head>
<body class="flex flex-col min-h-screen p-6">

    <!-- The main content container that will fill remaining space and center its content -->
    <div class="flex-grow flex items-center justify-center">
        <div class="bg-orange-950 rounded-2xl shadow-xl p-8 max-w-lg w-full border border-orange-700/20">
            <h1 class="text-3xl font-bold text-center text-orange-300 mb-6">Create Your Account</h1>

            <div class="mb-6">
                <?php echo $message; ?>
            </div>

            <form action="user_Registration.php" method="POST" class="space-y-4">
                <div>
                    <label for="client_name" class="sr-only">Name</label>
                    <input type="text" id="client_name" name="client_name" placeholder="Full Name"
                        class="w-full p-3 rounded-lg bg-orange-900 text-slate-200 placeholder-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all duration-200" required>
                </div>
                <div>
                    <label for="client_phone" class="sr-only">Phone Number</label>
                    <input type="tel" id="client_phone" name="client_phone" placeholder="Phone Number (e.g., 0712345678)"
                        class="w-full p-3 rounded-lg bg-orange-900 text-slate-200 placeholder-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all duration-200" required>
                </div>
                <div>
                    <label for="client_email" class="sr-only">Email</label>
                    <input type="email" id="client_email" name="client_email" placeholder="Email Address"
                        class="w-full p-3 rounded-lg bg-orange-900 text-slate-200 placeholder-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all duration-200" required>
                </div>
                <div>
                    <label for="client_area" class="sr-only">Area/Estate</label>
                    <input type="text" id="client_area" name="client_area" placeholder="Area/Estate"
                        class="w-full p-3 rounded-lg bg-orange-900 text-slate-200 placeholder-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all duration-200" required>
                </div>
                <button type="submit"
                    class="w-full bg-orange-600 hover:bg-orange-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-300 transform hover:scale-105 shadow-md">
                    Register
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-400">
                Already have an account? <a href="user_login.php" class="text-orange-400 hover:underline">Log in here</a>
            </p>
        </div>
    </div>
    <br>
    <br>
    <!-- Import the shared footer file -->
    <?php require_once 'footer.php'; ?>
</body>
</html>
