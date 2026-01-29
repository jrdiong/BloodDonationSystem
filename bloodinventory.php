<?php
session_start();

/* ---------- Access Control ---------- */
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'Hospital') {
    header("Location: login.html");
    exit();
}

$userID = $_SESSION['userID'];

/* ---------- Database Connection ---------- */
$conn = mysqli_connect("localhost", "root", "", "cbdc_system");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

/* ---------- Update expired blood bags ---------- */
$updateExpiredSQL = "
    UPDATE blood_inventory
    SET status = 'expired'
    WHERE status = 'available' AND expiryDate < CURDATE()
";
mysqli_query($conn, $updateExpiredSQL);

/* ---------- Fetch distinct statuses from DB ---------- */
$statusQuery = "SELECT DISTINCT status FROM blood_inventory";
$statusResult = mysqli_query($conn, $statusQuery);
$statuses = [];
$statusColors = []; // optional: can map colors dynamically or from DB if needed

while ($row = mysqli_fetch_assoc($statusResult)) {
    $statuses[] = $row['status'];
    
    // Optional: dynamic color mapping if you want
    switch ($row['status']) {
        case 'available':
            $statusColors[$row['status']] = '#d4edda'; // green
            break;
        case 'expired':
            $statusColors[$row['status']] = '#f8d7da'; // red
            break;
        case 'delivered':
            $statusColors[$row['status']] = '#fff3cd'; // yellow
            break;
        default:
            $statusColors[$row['status']] = '#e2e3e5'; // gray for unknown status
    }
}

/* ---------- Fetch Inventory (COUNT per blood type & status) ---------- */
$sql = "
    SELECT 
        bloodType,
        status,
        COUNT(*) AS quantity,
        MIN(expiryDate) AS nearestExpiry
    FROM blood_inventory
    WHERE userID = ?
    GROUP BY bloodType, status
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

/* ---------- Organize results for display ---------- */
$inventory = [];
while ($row = mysqli_fetch_assoc($result)) {
    $inventory[$row['bloodType']][$row['status']] = [
        'quantity' => $row['quantity'],
        'nearestExpiry' => $row['nearestExpiry']
    ];
}

/* Example display */
foreach ($inventory as $bloodType => $statusesData) {
    echo "<h3>Blood Type: $bloodType</h3>";
    foreach ($statuses as $status) {
        if (isset($statusesData[$status])) {
            $qty = $statusesData[$status]['quantity'];
            $expiry = $statusesData[$status]['nearestExpiry'];
            $color = $statusColors[$status] ?? '#fff';
            echo "<div style='background-color:$color; padding:5px; margin:3px;'>
                    Status: $status | Quantity: $qty | Nearest Expiry: $expiry
                  </div>";
        } else {
            echo "<div style='background-color:#f0f0f0; padding:5px; margin:3px;'>
                    Status: $status | Quantity: 0
                  </div>";
        }
    }
}
?>
