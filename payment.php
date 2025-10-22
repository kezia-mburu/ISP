<?php
session_start();

// Include the centralized database connection file and header.
require_once 'db.php';
require_once 'header.php';

$message = '';

// --- Authentication Check ---
if (!isset($_SESSION['client_id'])) {
    header('Location: user_login.php');
    exit();
}

// Fetch logged-in user details from the 'clients' table using the client_id from the session.
$client_id = $_SESSION['client_id'];
$sql = "SELECT username, client_name, client_email, client_phone, balance FROM clients WHERE client_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $loggedInUser = $result->fetch_assoc();
} else {
    // If the user is not found, destroy the session and redirect to login.
    session_destroy();
    header('Location: user_login.php');
    exit();
}

// --- Fetch package details based on the package_id from the URL ---
$package_id = isset($_GET['package_id']) ? htmlspecialchars($_GET['package_id']) : null;
$package_name = 'Custom Amount';
$package_price = 0.00;

if ($package_id) {
    $sql_package = "SELECT package_name, package_price FROM packages WHERE package_id = ?";
    $stmt_package = $conn->prepare($sql_package);
    $stmt_package->bind_param("i", $package_id);
    $stmt_package->execute();
    $result_package = $stmt_package->get_result();

    if ($result_package->num_rows > 0) {
        $package = $result_package->fetch_assoc();
        $package_name = htmlspecialchars($package['package_name']);
        $package_price = htmlspecialchars($package['package_price']);
    }
}

// --- Calculate the total amount to pay (package price + pending balance) ---
$total_amount = $package_price + $loggedInUser['balance'];
?>

<main class="container mx-auto px-4 py-8 flex-grow flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl p-6 sm:p-8 max-w-lg mx-auto border-4 border-orange-500">
        <h1 class="text-3xl font-bold text-center mb-2 text-cyan-600">Complete Your Payment</h1>
        <p class="text-center text-gray-600 mb-8">
            Please fill out the form to complete your payment for the <span class="font-semibold text-orange-600"><?= htmlspecialchars($package_name) ?></span> package.
        </p>

        <!-- Payment Form Section -->
        <div id="payment-form-section" class="bg-gray-50 rounded-xl p-6 shadow-md border border-gray-200">
            <form id="payment-form" class="space-y-6">
                <input type="hidden" name="package_id" value="<?= htmlspecialchars($package_id) ?>">
                <input type="hidden" name="package_price" value="<?= htmlspecialchars($package_price) ?>">

                <div>
                    <label for="username-input" class="block text-sm font-medium text-gray-500">Username</label>
                    <input type="text" name="username" id="username-input" class="mt-1 block w-full p-3 border border-gray-300 rounded-md bg-gray-200 text-gray-600 cursor-not-allowed" value="<?= htmlspecialchars($loggedInUser['username']) ?>" readonly>
                </div>

                <div>
                    <label for="name-input" class="block text-sm font-medium text-gray-500">Name</label>
                    <input type="text" name="name" id="name-input" class="mt-1 block w-full p-3 border border-gray-300 rounded-md bg-gray-200 text-gray-600 cursor-not-allowed" value="<?= htmlspecialchars($loggedInUser['client_name']) ?>" readonly>
                </div>

                <div>
                    <label for="amount-input" class="block text-sm font-medium text-gray-500">
                        Amount to Pay (KES) <span class="font-normal text-orange-500">(Includes KES <?= number_format($loggedInUser['balance'], 2) ?> pending balance)</span>
                    </label>
                    <input type="text" name="amount" id="amount-input" class="mt-1 block w-full p-3 border border-gray-300 rounded-md bg-white text-cyan-600 font-bold text-lg" value="<?= number_format($total_amount, 2, '.', '') ?>" readonly>
                </div>

                <div>
                    <label for="phone-input" class="block text-sm font-medium text-gray-500">Phone Number</label>
                    <input type="tel" name="phone" id="phone-input" placeholder="e.g., 254712345678" class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-cyan-500 bg-white" value="<?= htmlspecialchars($loggedInUser['client_phone']) ?>" required>
                    <p class="mt-1 text-xs text-gray-400">This number will receive the M-Pesa STK Push notification.</p>
                </div>
                
                <button type="submit" id="pay-button" class="mt-6 bg-cyan-600 text-white p-3 rounded-md font-semibold w-full hover:bg-cyan-700 transition transform hover:scale-105">
                    Initiate Payment
                </button>
            </form>
            
            <div id="status-message" class="mt-6 text-center text-lg font-semibold hidden"></div>
        </div>
    </div>
</main>

<script>
    document.getElementById('payment-form').addEventListener('submit', function(event) {
        event.preventDefault();

        const payButton = document.getElementById('pay-button');
        const statusMessage = document.getElementById('status-message');
        const phone = document.getElementById('phone-input').value;

        if (!phone.match(/^2547\d{8}$/)) {
            statusMessage.textContent = 'Please enter a valid Kenyan phone number (e.g., 2547xxxxxx).';
            statusMessage.className = 'mt-6 text-center text-red-500 text-lg font-semibold';
            statusMessage.classList.remove('hidden');
            return;
        }

        payButton.disabled = true;
        payButton.textContent = 'Processing...';
        payButton.classList.add('bg-gray-400', 'cursor-not-allowed');
        payButton.classList.remove('bg-cyan-600', 'hover:bg-cyan-700');
        
        statusMessage.textContent = 'Initiating payment request...';
        statusMessage.className = 'mt-6 text-center text-blue-500 text-lg font-semibold';
        statusMessage.classList.remove('hidden');

        const formData = new FormData(document.getElementById('payment-form'));
        
        // --- Make a real POST request to the separate processing file ---
        fetch('process_payment.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            const contentType = response.headers.get("content-type");
            if (contentType && contentType.indexOf("application/json") !== -1) {
                return response.json();
            } else {
                throw new TypeError("Server response was not JSON.");
            }
        })
        .then(data => {
            if (data.status === 'success') {
                statusMessage.textContent = data.message;
                statusMessage.className = 'mt-6 text-center text-green-600 text-lg font-semibold';
                
                // Keep the button disabled, as we are waiting for the user to confirm on their phone.
                // The page will not redirect until a successful callback is received.
                payButton.textContent = 'STK Push Sent';
                payButton.classList.remove('bg-gray-400', 'hover:bg-cyan-700');
                payButton.classList.add('bg-green-600');
                
            } else {
                statusMessage.textContent = data.message;
                statusMessage.className = 'mt-6 text-center text-red-600 text-lg font-semibold';
                
                payButton.disabled = false;
                payButton.textContent = 'Try Again';
                payButton.classList.remove('bg-gray-400');
                payButton.classList.add('bg-cyan-600', 'hover:bg-cyan-700');
            }
            statusMessage.classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            statusMessage.textContent = 'An unexpected error occurred. Please try again later.';
            statusMessage.className = 'mt-6 text-center text-red-600 text-lg font-semibold';
            statusMessage.classList.remove('hidden');

            payButton.disabled = false;
            payButton.textContent = 'Try Again';
            payButton.classList.remove('bg-gray-400');
            payButton.classList.add('bg-cyan-600', 'hover:bg-cyan-700');
        });
    });
</script>
<?php
// Close the database connection
if ($conn) {
    $conn->close();
}
require_once 'footer.php';
?>
