<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "password";
$dbname = "cbdc_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Query inventory summary
$sql = "
SELECT 
    bloodType,
    CASE 
        WHEN status = 'available' AND expiryDate < NOW() THEN 'expired'
        ELSE status
    END AS status,
    COUNT(*) AS count
FROM BloodInventory
GROUP BY bloodType, status;
";

$result = $conn->query($sql);

// Initialize summary with all blood types and statuses
$summary = [
    "A" => ["available" => 0, "expired" => 0, "used" => 0, "delivered" => 0],
    "B" => ["available" => 0, "expired" => 0, "used" => 0, "delivered" => 0],
    "AB" => ["available" => 0, "expired" => 0, "used" => 0, "delivered" => 0],
    "O" => ["available" => 0, "expired" => 0, "used" => 0, "delivered" => 0],
];

// Fill in counts from query results
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $bloodType = $row["bloodType"];
        $status = $row["status"];
        $count = $row["count"];

        $summary[$bloodType][$status] = $count;
    }
}

$conn->close();

// Return JSON to frontend
header('Content-Type: application/json');
echo json_encode($summary);
?>
