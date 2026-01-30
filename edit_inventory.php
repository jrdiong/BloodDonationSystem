<?php
session_start();

/* ---------- Access Control ---------- */
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'Hospital') {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$userID = $_SESSION['userID'];

/* ---------- DB Connection ---------- */
$conn = mysqli_connect("localhost", "root", "", "cbdc_system");

if (!$conn) {
    http_response_code(500);
    echo json_encode(["error" => "DB connection failed"]);
    exit();
}

/* ---------- Update expired automatically ---------- */
$expireSQL = "
    UPDATE blood_inventory
    SET status = 'expired'
    WHERE status = 'available'
      AND expiryDate < CURDATE()
      AND userID = ?
";
$stmtExpire = mysqli_prepare($conn, $expireSQL);
mysqli_stmt_bind_param($stmtExpire, "i", $userID);
mysqli_stmt_execute($stmtExpire);

/* ---------- Get Request Data ---------- */
$bloodType = $_POST['bloodType'] ?? null;
$action    = $_POST['action'] ?? null;
$qty       = intval($_POST['quantity'] ?? 0);

if (!$bloodType || !$action || $qty <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid request"]);
    exit();
}

/* ---------- Helper: Add Available ---------- */
function addAvailable($conn, $userID, $bloodType, $qty) {
    $sql = "
        INSERT INTO blood_inventory
        (userID, bloodType, status, collectionTime, expiryDate)
        VALUES (?, ?, 'available', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY))
    ";
    $stmt = mysqli_prepare($conn, $sql);

    for ($i = 0; $i < $qty; $i++) {
        mysqli_stmt_bind_param($stmt, "is", $userID, $bloodType);
        mysqli_stmt_execute($stmt);
    }
}

/* ---------- Helper: FIFO update ---------- */
function fifoUpdate($conn, $userID, $bloodType, $from, $to, $qty) {
    for ($i = 0; $i < $qty; $i++) {
        $selectSQL = "
            SELECT inventoryID
            FROM blood_inventory
            WHERE userID = ?
              AND bloodType = ?
              AND status = ?
            ORDER BY expiryDate ASC
            LIMIT 1
        ";
        $stmt = mysqli_prepare($conn, $selectSQL);
        mysqli_stmt_bind_param($stmt, "iss", $userID, $bloodType, $from);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (!$row = mysqli_fetch_assoc($result)) {
            break; // no more available
        }

        $updateSQL = "
            UPDATE blood_inventory
            SET status = ?
            WHERE inventoryID = ?
        ";
        $stmtUpdate = mysqli_prepare($conn, $updateSQL);
        mysqli_stmt_bind_param(
            $stmtUpdate,
            "si",
            $to,
            $row['inventoryID']
        );
        mysqli_stmt_execute($stmtUpdate);
    }
}

/* ---------- Helper: LIFO update ---------- */
function lifoUpdate($conn, $userID, $bloodType, $from, $to, $qty) {
    for ($i = 0; $i < $qty; $i++) {
        $selectSQL = "
            SELECT inventoryID
            FROM blood_inventory
            WHERE userID = ?
              AND bloodType = ?
              AND status = ?
            ORDER BY collectionTime DESC
            LIMIT 1
        ";
        $stmt = mysqli_prepare($conn, $selectSQL);
        mysqli_stmt_bind_param($stmt, "iss", $userID, $bloodType, $from);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (!$row = mysqli_fetch_assoc($result)) {
            break;
        }

        $updateSQL = "
            UPDATE blood_inventory
            SET status = ?
            WHERE inventoryID = ?
        ";
        $stmtUpdate = mysqli_prepare($conn, $updateSQL);
        mysqli_stmt_bind_param(
            $stmtUpdate,
            "si",
            $to,
            $row['inventoryID']
        );
        mysqli_stmt_execute($stmtUpdate);
    }
}

/* ---------- Action Router ---------- */
switch ($action) {

    case 'add_available':
        addAvailable($conn, $userID, $bloodType, $qty);
        break;

    case 'deliver':
        // available → delivered (FIFO)
        fifoUpdate($conn, $userID, $bloodType, 'available', 'delivered', $qty);
        break;

    case 'return_delivered':
        // delivered → available (LIFO)
        lifoUpdate($conn, $userID, $bloodType, 'delivered', 'available', $qty);
        break;

    case 'use':
        // delivered → used (FIFO)
        fifoUpdate($conn, $userID, $bloodType, 'delivered', 'used', $qty);
        break;

    default:
        http_response_code(400);
        echo json_encode(["error" => "Unknown action"]);
        exit();
}

echo json_encode(["status" => "success"]);
