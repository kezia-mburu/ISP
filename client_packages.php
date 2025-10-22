<?php
    // --- Use a centralized database connection ---
    // This file now assumes that a 'db.php' file exists in the same directory
    // and contains the database connection logic.
    require_once 'db.php';

    // Retrieve packages from the database
    $sql = "SELECT * FROM packages";
    $result = $conn->query($sql);
    
    // Check for query errors
    if (!$result) {
        die("Error fetching packages: " . $conn->error);
    }
?>

<!-- Import the shared header file -->
<?php require_once 'header.php'; ?>

<main class="container mx-auto p-4 flex flex-col items-center min-h-screen">
    <div class="bg-white rounded-xl shadow-xl p-8 max-w-4xl w-full border border-gray-200 mt-8">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-8">Our Packages</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
            <?php
            // Check if there are any packages in the database
            if ($result->num_rows > 0) {
                // Loop through each row of the result set
                while($row = $result->fetch_assoc()) {
                    $package_id = htmlspecialchars($row['package_id']);
                    $package_name = htmlspecialchars($row['package_name']);
                    $package_price = htmlspecialchars($row['package_price']);
                    $package_description = htmlspecialchars($row['package_description']);
            ?>
            <!-- Package Card -->
            <div class="bg-gray-100 rounded-xl p-6 shadow-md border border-gray-200 transition-transform duration-300 transform hover:scale-105">
                <div class="text-center">
                    <h3 class="text-2xl font-bold mb-2 text-orange-600"><?php echo $package_name; ?></h3>
                    <p class="text-3xl font-extrabold text-gray-900 mb-4">
                        KES <?php echo number_format($package_price, 2); ?>
                    </p>
                    <p class="text-gray-600 mb-6"><?php echo $package_description; ?></p>
                </div>
                <div class="text-center">
                    <!-- The link will redirect to a payment page with the package ID -->
                    <a href="payment.php?package_id=<?php echo $package_id; ?>" class="inline-block bg-orange-600 hover:bg-orange-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-300 transform hover:scale-105 shadow-md">
                        Pay Now
                    </a>
                </div>
            </div>
            <?php
                }
            } else {
                echo "<p class='col-span-full text-center text-gray-400'>No packages found. Please add packages via the admin panel.</p>";
            }
            ?>
        </div>
    </div>
</main>

<?php
    // Close the database connection
    $conn->close();
?>

<!-- Import the shared footer file -->
<?php require_once 'footer.php'; ?>
