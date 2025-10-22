<?php
// Disable display of PHP errors to prevent HTML output from breaking the JSON response.
ini_set('display_errors', 0);
header('Content-Type: application/json');

// Include the database connection file
require_once 'db.php';

// Check if the connection was established. If not, return a JSON error.
if (!$conn || $conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

// Check if the 'area' parameter is set in the POST request
if (!isset($_POST['area'])) {
    echo json_encode(['status' => 'error', 'message' => 'Area not provided.']);
    exit;
}

$searchArea = htmlspecialchars($_POST['area']);

try {
    // Check if the area exists in the 'covered_areas' table
    // The query has been updated to use the `area_name` column.
    $stmt = $conn->prepare("SELECT COUNT(*) FROM covered_areas WHERE area_name = ?");
    
    if (!$stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    
    $stmt->bind_param("s", $searchArea);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_row();
    $isCovered = ($row[0] > 0);

    // Prepare a JSON response based on the search result
    if ($isCovered) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'not_available']);
    }

    $stmt->close();
} catch (Exception $e) {
    // Return a more detailed error message in JSON format
    echo json_encode(['status' => 'error', 'message' => 'Database query failed: ' . $e->getMessage()]);
}

$conn->close();
?>
