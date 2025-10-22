<?php

// Include the database connection file
require_once 'db.php';

// Check if the database connection was successful. If not, stop the script.
if (!$conn || $conn->connect_error) {
    die("Database connection failed. Please check your db.php file.");
}

// Include the header file
require_once 'header.php';

$message = '';

// --- Form Submission Logic (Handles expansion requests) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_expansion'])) {
    $clientName = htmlspecialchars($_POST['name']);
    $clientEmail = htmlspecialchars($_POST['email']);
    $clientPhone = htmlspecialchars($_POST['phone']);
    $area = htmlspecialchars($_POST['area']);

    // Use a prepared statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO expansion_requests (name, email, phone, area) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $clientName, $clientEmail, $clientPhone, $area);
    
    if ($stmt->execute()) {
        $message = "Thank you for your request! We will notify you when we expand to your area.";
    } else {
        $message = "There was an error processing your request: " . $stmt->error . ". Please try again.";
    }
    $stmt->close();
}

// --- Fetch Unique Areas from the Database for the Map ---
// Select all unique areas from the 'expansion_requests' table.
$sql_requested = "SELECT DISTINCT area FROM expansion_requests ORDER BY area ASC";
$result_requested = $conn->query($sql_requested);

$requestedAreas = [];
if ($result_requested->num_rows > 0) {
    while ($row = $result_requested->fetch_assoc()) {
        $requestedAreas[] = $row['area'];
    }
}
// Encode the areas array as a JSON object for JavaScript to use.
$requestedAreas_json = json_encode($requestedAreas);


// Select all unique covered areas from the 'covered_areas' table.
$sql_covered = "SELECT DISTINCT area_name FROM covered_areas ORDER BY area_name ASC";
$result_covered = $conn->query($sql_covered);

$coveredAreas = [];
if ($result_covered->num_rows > 0) {
    while ($row = $result_covered->fetch_assoc()) {
        $coveredAreas[] = $row['area_name'];
    }
}
// Encode the areas array as a JSON object for JavaScript to use.
$coveredAreas_json = json_encode($coveredAreas);

?>

<!-- Include Leaflet CSS for the map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #mapid {
        height: 400px;
        border-radius: 0.5rem;
    }
    /* Simple transition for fade effect */
    .fade-in {
        opacity: 1;
        transition: opacity 1s ease-in-out;
    }
    .fade-out {
        opacity: 0;
        transition: opacity 1s ease-in-out;
    }
</style>

<main class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow p-6 sm:p-8">
        <h1 class="text-3xl font-bold text-center mb-6">Check Our Fiber Coverage</h1>
        <?php if ($message): ?>
            <div id="php-message" class="bg-green-100 text-green-800 p-4 rounded-md mb-4 fade-in">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- Search and Map Section -->
        <div class="mb-8">
            <p class="text-center text-gray-600 mb-4">Enter your area to see if Mtaakonnect is available.</p>
            <div class="flex flex-col sm:flex-row gap-4">
                <input type="text" id="search-area" placeholder="e.g., Mwea Town" class="flex-grow p-3 border rounded-md focus:ring-2 focus:ring-orange-500">
                <button id="search-button" class="bg-orange-500 text-white p-3 rounded-md font-semibold hover:bg-orange-600 transition">Search Area</button>
            </div>
            
            <!-- Coverage status message with fading effect -->
            <div id="coverage-status" class="mt-4 p-4 text-center rounded-md text-sm fade-out" style="display:none;"></div>

            <!-- Expansion Request Form with Fading Effect -->
            <div class="bg-gray-50 rounded-lg p-6 sm:p-8 mt-8 fade-out" id="expansion-form-section" style="display:none;">
                <h2 class="text-2xl font-semibold mb-4 text-center">Request an Expansion</h2>
                <p class="text-center text-gray-600 mb-4">It looks like your area isn't covered yet. Fill out the form below to help us prioritize our next expansion!</p>
                <form action="coverage.php" method="POST" id="expansion-form" class="grid grid-cols-1 md:grid-cols-2 gap-4" onsubmit="return validateForm()">
                    <div class="relative">
                        <input type="text" name="name" id="name-input" placeholder="Your Name" class="w-full p-3 border rounded-md focus:ring-2 focus:ring-orange-500">
                        <span id="name-error" class="text-red-500 text-xs absolute left-0 -bottom-4"></span>
                    </div>
                    <div class="relative">
                        <input type="email" name="email" id="email-input" placeholder="Your Email" class="w-full p-3 border rounded-md focus:ring-2 focus:ring-orange-500">
                        <span id="email-error" class="text-red-500 text-xs absolute left-0 -bottom-4"></span>
                    </div>
                    <div class="relative">
                        <input type="tel" name="phone" id="phone-input" placeholder="Your Phone Number" class="w-full p-3 border rounded-md focus:ring-2 focus:ring-orange-500">
                        <span id="phone-error" class="text-red-500 text-xs absolute left-0 -bottom-4"></span>
                    </div>
                    <div class="relative md:col-span-2">
                        <input type="text" name="area" id="requested-area-input" placeholder="Area you are requesting" class="w-full p-3 border rounded-md focus:ring-2 focus:ring-orange-500">
                        <span id="area-error" class="text-red-500 text-xs absolute left-0 -bottom-4"></span>
                    </div>
                    <button type="submit" name="request_expansion" class="md:col-span-2 bg-orange-500 text-white p-3 rounded-md font-semibold hover:bg-orange-600 transition">Submit Request</button>
                </form>
            </div>

            <!-- The interactive map will be rendered here -->
            <div class="w-full md:w-3/4 mx-auto mt-8">
                <div id="mapid"></div>
            </div>
        </div>
    </div>
</main>

<!-- Include Leaflet JavaScript -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', async () => {
        const requestedAreas = <?php echo $requestedAreas_json; ?>;
        const coveredAreas = <?php echo $coveredAreas_json; ?>;

        // Set the map view to Kirinyaga County with a suitable zoom level
        const mymap = L.map('mapid').setView([-0.5362, 37.4091], 10);
        
        // Define the bounds for Kirinyaga County to prevent panning outside.
        const kirinyagaBounds = L.latLngBounds([[-0.8, 37.1], [-0.2, 37.7]]);
        mymap.setMaxBounds(kirinyagaBounds);
        
        const searchButton = document.getElementById('search-button');
        const searchInput = document.getElementById('search-area');
        const statusDiv = document.getElementById('coverage-status');
        const expansionFormSection = document.getElementById('expansion-form-section');
        const requestedAreaInput = document.getElementById('requested-area-input');
        const phpMessage = document.getElementById('php-message');

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxBounds: kirinyagaBounds
        }).addTo(mymap);

        // Define custom icons
        const coveredIcon = L.icon({
            iconUrl: 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32"><circle cx="16" cy="16" r="14" fill="%234CAF50" stroke="%23388E3C" stroke-width="2"/><path d="M10 16 L14 20 L22 12" stroke="%23FFFFFF" stroke-width="3" fill="none"/></svg>',
            iconSize: [32, 32],
            iconAnchor: [16, 32],
            popupAnchor: [0, -28]
        });

        const requestedIcon = L.icon({
            iconUrl: 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32"><circle cx="16" cy="16" r="14" fill="%23FF9800" stroke="%23F57C00" stroke-width="2"/><circle cx="16" cy="16" r="8" fill="%23FFFFFF"/></svg>',
            iconSize: [32, 32],
            iconAnchor: [16, 32],
            popupAnchor: [0, -28]
        });

        // Hide PHP message after 5 seconds
        if (phpMessage) {
            setTimeout(() => {
                phpMessage.classList.add('fade-out');
                setTimeout(() => phpMessage.style.display = 'none', 1000);
            }, 5000);
        }

        async function geocodeAndAddMarker(areaName, icon, popupText) {
            const formData = new FormData();
            formData.append('area', areaName);
            try {
                const response = await fetch('geocode.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                if (data.length > 0) {
                    const coords = { lat: parseFloat(data[0].lat), lon: parseFloat(data[0].lon) };
                    if (kirinyagaBounds.contains([coords.lat, coords.lon])) {
                        L.marker([coords.lat, coords.lon], {icon: icon}).addTo(mymap)
                            .bindPopup(`<b>${areaName}</b><br>${popupText}`);
                    }
                }
            } catch (error) {
                console.error('Geocoding error:', error);
            }
        }
        
        // Add markers for all covered areas on page load.
        for (const area of coveredAreas) {
            await geocodeAndAddMarker(area, coveredIcon, 'This area is covered by our network!');
        }

        // Add markers for all requested areas on page load.
        for (const area of requestedAreas) {
            await geocodeAndAddMarker(area, requestedIcon, 'This area has an expansion request.');
        }

        searchButton.addEventListener('click', async () => {
            const searchInputValue = searchInput.value.trim();

            // First, hide the previous message and form with a transition
            statusDiv.classList.add('fade-out');
            expansionFormSection.classList.add('fade-out');
            
            // Wait for the fade-out to complete before changing display
            await new Promise(resolve => setTimeout(resolve, 1000));
            statusDiv.style.display = 'none';
            expansionFormSection.style.display = 'none';

            if (searchInputValue.length === 0) {
                statusDiv.innerHTML = `<span class="text-yellow-600 font-semibold">Please enter an area name to search.</span>`;
                statusDiv.classList.remove('fade-out');
                statusDiv.classList.add('bg-yellow-100', 'fade-in');
                statusDiv.style.display = 'block';
                return;
            }

            try {
                const formData = new FormData();
                formData.append('area', searchInputValue);
                
                const response = await fetch('check_coverage.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();
                
                statusDiv.classList.remove('bg-green-100', 'bg-red-100', 'bg-yellow-100');
                statusDiv.style.display = 'block';

                if (result.status === 'success') {
                    statusDiv.innerHTML = `<span class="text-green-600 font-semibold">Great news! Your area is covered.</span>`;
                    statusDiv.classList.remove('fade-out');
                    statusDiv.classList.add('bg-green-100', 'fade-in');
                } else if (result.status === 'not_available') {
                    statusDiv.innerHTML = `<span class="text-red-600 font-semibold">Sorry, your area is not yet covered.</span> Please request an expansion below!`;
                    statusDiv.classList.remove('fade-out');
                    statusDiv.classList.add('bg-red-100', 'fade-in');
                    expansionFormSection.style.display = 'block';
                    expansionFormSection.classList.remove('fade-out');
                    expansionFormSection.classList.add('fade-in');
                    requestedAreaInput.value = searchInputValue;
                } else {
                    statusDiv.innerHTML = `<span class="text-red-600 font-semibold">An error occurred: ${result.message}</span>`;
                    statusDiv.classList.remove('fade-out');
                    statusDiv.classList.add('bg-red-100', 'fade-in');
                }

            } catch (error) {
                console.error('Error during coverage search:', error);
                statusDiv.style.display = 'block';
                statusDiv.innerHTML = `<span class="text-red-600 font-semibold">An unexpected network error occurred. Please try again.</span>`;
                statusDiv.classList.remove('fade-out');
                statusDiv.classList.add('bg-red-100', 'fade-in');
            }
        });
    });

    function validateForm() {
        const nameInput = document.getElementById('name-input');
        const emailInput = document.getElementById('email-input');
        const phoneInput = document.getElementById('phone-input');
        const areaInput = document.getElementById('requested-area-input');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        let isValid = true;

        // Clear previous error messages
        document.querySelectorAll('.text-red-500').forEach(el => el.textContent = '');

        if (nameInput.value.trim() === '') {
            document.getElementById('name-error').textContent = 'Name is required.';
            isValid = false;
        }

        if (emailInput.value.trim() === '' || !emailRegex.test(emailInput.value)) {
            document.getElementById('email-error').textContent = 'Valid email is required.';
            isValid = false;
        }

        if (phoneInput.value.trim() === '') {
            document.getElementById('phone-error').textContent = 'Phone is required.';
            isValid = false;
        }

        if (areaInput.value.trim() === '') {
            document.getElementById('area-error').textContent = 'Area is required.';
            isValid = false;
        }

        return isValid;
    }
</script>

<?php 
// Close the database connection after all PHP logic is done
if ($conn) {
    $conn->close();
}
// Include the footer file
require_once 'footer.php'; 
?>
