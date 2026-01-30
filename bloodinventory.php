<?php
session_start();

/* ---------- Access Control ---------- */
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'Hospital') {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$userID = $_SESSION['userID'];

/* ---------- Database Connection ---------- */
$conn = mysqli_connect("localhost", "root", "", "cbdc_system");

if (!$conn) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

/* ---------- Update expired blood bags ---------- */
$updateExpiredSQL = "
    UPDATE blood_inventory
    SET status = 'expired'
    WHERE status = 'available'
      AND expiryDate < CURDATE()
      AND userID = ?
";

$stmtUpdate = mysqli_prepare($conn, $updateExpiredSQL);
mysqli_stmt_bind_param($stmtUpdate, "i", $userID);
mysqli_stmt_execute($stmtUpdate);

/* ---------- Fetch inventory summary ---------- */
$sql = "
    SELECT 
        bloodType,
        status,
        COUNT(*) AS quantity
    FROM blood_inventory
    WHERE userID = ?
    GROUP BY bloodType, status
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

/* ---------- Prepare default structure ---------- */
$bloodTypes = ['A', 'B', 'AB', 'O'];
$summary = [];

foreach ($bloodTypes as $type) {
    $summary[$type] = [
        "blood_type" => $type,
        "available" => 0,
        "expired" => 0,
        "delivered" => 0
    ];
}

/* ---------- Fill data from DB ---------- */
while ($row = mysqli_fetch_assoc($result)) {
    $bloodType = $row['bloodType'];
    $status = $row['status'];
    $qty = (int)$row['quantity'];

    if (isset($summary[$bloodType][$status])) {
        $summary[$bloodType][$status] = $qty;
    }
}

/* ---------- Output JSON ---------- */
header("Content-Type: application/json");
echo json_encode(array_values($summary));
