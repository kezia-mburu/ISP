<?php
// Include the shared header file
require_once 'header.php';

// Check if the request is a form submission (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Include the centralized database connection file
    require_once 'db.php';

    // Clean input values and prepare for insertion
    $name    = trim($conn->real_escape_string($_POST['name'] ?? ''));
    $email   = trim($conn->real_escape_string($_POST['email'] ?? ''));
    $phone   = trim($conn->real_escape_string($_POST['phone'] ?? ''));
    $estate  = trim($conn->real_escape_string($_POST['estate'] ?? ''));
    $subject = trim($conn->real_escape_string($_POST['subject'] ?? ''));
    $message = trim($conn->real_escape_string($_POST['message'] ?? ''));

    // Basic required field validation
    if (!$name || !$email || !$phone || !$estate || !$message) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'All required fields must be filled.']);
        exit;
    }

    // Phone format validation: must start with 07 or 01 and be exactly 10 digits
    if (!preg_match('/^(07|01)\d{8}$/', $phone)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid phone number format. Use e.g. 0758311311']);
        exit;
    }

    // Insert into database
    $sql = "INSERT INTO contactus (name, email, phone, estate, subject, message, status)
            VALUES (?, ?, ?, ?, ?, ?, 'unread')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $name, $email, $phone, $estate, $subject, $message);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Thank you for your message!']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to save message.']);
    }
    $stmt->close();
    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | Mtaakonnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="flex flex-col min-h-screen bg-gray-100">

    <!-- Main Content -->
    <main class="flex-grow flex flex-col items-center justify-center p-6">
        <div class="bg-white rounded-2xl shadow-xl p-8 max-w-4xl w-full border-4 border-cyan-500 mb-12">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                
                <!-- Contact Form -->
                <div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-cyan-600 mb-6">Send a Message</h2>
                    <form id="contact-form" class="space-y-4">
                        <input type="text" name="name" placeholder="Your Name"
                            class="w-full p-3 rounded-lg bg-white border border-gray-300 text-gray-800 placeholder-gray-400
                                   focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all duration-200"
                            required />
                        <input type="email" name="email" placeholder="Your Email"
                            class="w-full p-3 rounded-lg bg-white border border-gray-300 text-gray-800 placeholder-gray-400
                                   focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all duration-200"
                            required />
                        <input type="text" name="phone" placeholder="Your Phone Number"
                            class="w-full p-3 rounded-lg bg-white border border-gray-300 text-gray-800 placeholder-gray-400
                                   focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all duration-200"
                            required />
                        <input type="text" name="estate" placeholder="Your Area / Estate"
                            class="w-full p-3 rounded-lg bg-white border border-gray-300 text-gray-800 placeholder-gray-400
                                   focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all duration-200"
                            required />
                        <input type="text" name="subject" placeholder="Subject"
                            class="w-full p-3 rounded-lg bg-white border border-gray-300 text-gray-800 placeholder-gray-400
                                   focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all duration-200" />
                        <textarea name="message" rows="5" placeholder="Your Message"
                            class="w-full p-3 rounded-lg bg-white border border-gray-300 text-gray-800 placeholder-gray-400
                                   focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all duration-200"
                            required></textarea>
                        <button type="submit"
                            class="w-full bg-orange-600 hover:bg-orange-700 text-white font-semibold py-3 px-6
                                   rounded-lg transition-all duration-300 transform hover:scale-105 shadow-md">
                            Send Message
                        </button>
                    </form>
                    <div id="status-message" class="mt-4 p-3 rounded-lg text-sm hidden"></div>
                </div>

                <!-- Contact Info Column -->
                <div class="md:border-l md:border-gray-200 md:pl-12">
                    <h2 class="text-2xl sm:text-3xl font-bold text-orange-600 mb-6">Contact Information</h2>
                    <div class="space-y-6 text-gray-600">
                        
                        <!-- Email -->
                        <div class="flex items-center space-x-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <div>
                                <h4 class="font-semibold text-orange-600">Email Address</h4>
                                <p>mtaakonnect@gmail.com</p>
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="flex items-center space-x-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.99 1.07l.95 4.75a1 1 0 01-.52 1.03l-1.92 1.11a13.04 13.04 0 006.14 6.14l1.11-1.92a1 1 0 011.03-.52l4.75.95a1 1 0 011.07.99V19a2 2 0 01-2 2h-1a17.9 17.9 0 01-16.7-16.7h-1z" />
                            </svg>
                            <div>
                                <h4 class="font-semibold text-orange-600">Phone Number</h4>
                                <p>0715 276 176 | 0758 311 311</p>
                            </div>
                        </div>

                        <!-- Location -->
                        <div class="flex items-start space-x-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 3.992-5.525 8.544-7.5 10.5S4.5 14.492 4.5 10.5a7.5 7.5 0 0115 0z" />
                            </svg>
                            <div>
                                <h4 class="font-semibold text-orange-600">Our Location</h4>
                                <p>Mwea, Kenya</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer container -->
    <?php require_once 'footer.php'; ?>

    <script>
        // AJAX form handling
        const form = document.getElementById('contact-form');
        const statusMessage = document.getElementById('status-message');
        if (form) {
            form.addEventListener('submit', e => {
                e.preventDefault();
                const formData = new FormData(form);
                fetch('contactus.php', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        statusMessage.textContent = data.message;
                        statusMessage.className = 'mt-4 p-3 rounded-lg text-sm ';
                        statusMessage.classList.remove('hidden');

                        if (data.status === 'success') {
                            statusMessage.classList.add('bg-cyan-100/50', 'text-cyan-700');
                            form.reset();
                        } else {
                            statusMessage.classList.add('bg-red-100/50', 'text-red-700');
                        }
                    })
                    .catch(() => {
                        statusMessage.textContent = 'An error occurred. Please try again.';
                        statusMessage.className = 'mt-4 p-3 rounded-lg text-sm bg-red-100/50 text-red-700';
                        statusMessage.classList.remove('hidden');
                    });
            });
        }
    </script>
</body>
</html>
