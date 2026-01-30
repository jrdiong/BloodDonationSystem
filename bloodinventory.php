<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "cbdc_system";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Initialize summary for all blood types and statuses
$summary = [
    "A" => ["available" => 0, "expired" => 0, "used" => 0, "delivered" => 0],
    "B" => ["available" => 0, "expired" => 0, "used" => 0, "delivered" => 0],
    "AB" => ["available" => 0, "expired" => 0, "used" => 0, "delivered" => 0],
    "O" => ["available" => 0, "expired" => 0, "used" => 0, "delivered" => 0],
];

// Query inventory summary
$sql = "
SELECT 
    bloodType,
    CASE 
        WHEN status = 'available' AND expiryDate < NOW() THEN 'expired'
        ELSE status
    END AS status,
    COUNT(*) AS count
FROM blood inventory
GROUP BY bloodType, status;
";

$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $bloodType = $row['bloodType'];
        $status = $row['status'];
        $count = (int)$row['count'];

        // Fill in the summary array
        if (isset($summary[$bloodType][$status])) {
            $summary[$bloodType][$status] = $count;
        }
    }
} else {
    // SQL fail
    echo json_encode(["error" => "Query failed: " . $conn->error]);
    $conn->close();
    exit;
}

$conn->close();

// Return the summary JSON
echo json_encode($summary);
?>
