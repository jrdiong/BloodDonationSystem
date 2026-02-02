<?php
session_start();

if (!isset($_SESSION['userID'], $_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

$loggedInUserID = $_SESSION['userID'];
$sessionRole = $_SESSION['role']; // already normalized

// map normalized role → navbar / logic role
$roleMap = [
    'Admin' => 'admin',
    'Event Organizer' => 'organizer',
    'Hospital' => 'hospital',
    'Donor' => 'donor'
];

$role = $roleMap[$sessionRole] ?? 'guest';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Blood Inventory</title>
<link rel="stylesheet" href="style.css" />
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">

<style>
body { font-family:'Poppins',sans-serif; background:#fde7e7; margin:0; }

.main-container {
  max-width: 900px;
  margin: 120px 60px 0 320px;
  margin-left: calc(260px + (100% - 260px - 900px)/2);
  padding: 30px;
  background:#fff;
  border-radius:15px;
  box-shadow:0 8px 25px rgba(0,0,0,0.08);
}

/* Page intro card */
.page-intro {
  background:#f44040;
  color:#fff;
  border-radius:15px;
  padding:25px 30px;
  box-shadow:0 4px 15px rgba(0,0,0,0.1);
  margin-bottom: 30px;
}
.page-intro h2 { margin:0 0 10px 0; font-size:24px; }
.page-intro p { margin:0; font-size:16px; }

.card-list {
  display: grid;
  grid-template-columns: repeat(2, 1fr); /* 2 columns */
  gap: 16px;
}

.blood-card { display:flex; align-items:center; background:#fff; border-radius:14px; padding:16px; box-shadow:0 2px 6px rgba(0,0,0,.1); }

.type-badge { width:90px; height:110px; background:#f44040; color:#fff; display:flex; align-items:center; justify-content:center; border-radius:14px; font-size:24px; font-weight:600; }

.card-info { flex:1; padding:0 20px; display:flex; flex-direction:column; justify-content:center; gap:6px; }
.card-info div { display:flex; justify-content:space-between; font-size:14px; color:#555; }
.card-info div span.num { font-weight:600; }

.card-actions { display:flex; gap:10px; }

/* Buttons */
.btn { padding:8px 16px; border-radius:8px; border:none; cursor:pointer; font-size:14px; }
.btn.primary { background:#d21919; color:#fff; }
.btn.secondary { border:1px solid #1976d2; color:#1976d2; background:#fff; }
.btn.danger { border:1px solid #d32f2f; color:#d32f2f; background:#fff; }

/* Modal */
.modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,0.5); top: 45px; display:flex; justify-content:center; align-items:center; opacity:0; pointer-events:none; transition:opacity 0.3s; }
.modal-overlay.show { opacity:1; pointer-events:auto; }
.modal-card { background:#fff; border-radius:20px; width:90%; max-width:500px; max-height: 85vh; padding:25px 30px; box-shadow:0 15px 40px rgba(0,0,0,0.2); position:relative; overflow-y:auto; }
.modal-card h3 { margin-top:0; text-align:center; }
.close-modal { position:absolute; top:15px; right:20px; font-size:30px; cursor:pointer; color:#999; }
.close-modal:hover { color:#1976d2; }

.modal-card input { width:100%; padding:10px; margin:6px 0; border-radius:6px; border:1px solid #ccc; }
.modal-footer { display:flex; justify-content:flex-end; gap:10px; margin-top:10px; }

/* Inventory table */
/* Table container with scrollable body */
.inventory-table-container {
  max-height: 190px;
  overflow-y: auto;
  margin-top: 10px;
  border-radius: 12px;
  box-shadow: inset 0 0 5px rgba(0,0,0,0.05);
}

/* Table styling */
.inventory-table {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed;
}

.inventory-table thead {
  position: sticky;
  top: 0;
  background: #f5f5f5;
  z-index: 2;
  box-shadow: 0 2px 3px rgba(0,0,0,0.05);
}

.inventory-table th, .inventory-table td {
  padding: 10px;
  text-align: center;
  font-size: 14px;
  overflow: hidden;
  white-space: nowrap;
}

/* Zebra stripes */
.inventory-table tbody tr:nth-child(even) { background: #fdfdfd; }
.inventory-table tbody tr:nth-child(odd) { background: #fff; }

/* Hover effect for rows */
.inventory-table tbody tr:hover { background: #f9f9f9; }

/* Modern pill-style status */
.inventory-table td.status {
    display: flex;                  /* make the cell a flex container */
    justify-content: center;        /* center horizontally */
    align-items: center; 
    padding: 6px 14px;
    border-radius: 999px;            /* fully rounded */
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: capitalize;
    color: #fff;
    text-align: center;
    min-width: 80px;
    transition: transform 0.2s, box-shadow 0.2s;
}
.inventory-table td {
    vertical-align: middle;
}

/* Status colors */
.inventory-table td.status.available { background: #3fc944; }
.inventory-table td.status.used { background: #38a2fa; }
.inventory-table td.status.delivered { background: #ffb039; }
.inventory-table td.status.expired { background: #f9473a; }

/* Optional hover effect */
.inventory-table td.status:hover {
    transform: translateY(-2px);
    box-shadow: 0 3px 6px rgba(0,0,0,0.2);
}
/* Donor pill style reused for inventory */
.status-pill {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: capitalize;
    white-space: nowrap;
}

/* Colors */
.status-pill.available { background: #3fc944; color: #fff; }
.status-pill.used { background: #38a2fa; color: #fff; }
.status-pill.delivered { background: #ffb039; color: #fff; }
.status-pill.expired { background: #f9473a; color: #fff; }

.status-pill:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

</style>
</head>

<body>
<?php include "navbar_$role.php"; ?>

<div class="main-container">
  <div class="page-intro">
    <h2>Manage Blood Inventory</h2>
    <p>Review and edit blood inventory details. Click on a card to edit quantities and see detailed logs.</p>
  </div>

  <div class="card-list" id="cardList"></div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editModal">
  <div class="modal-card">
    <h3 id="modalTitle">Edit Blood Inventory</h3>
    <span class="close-modal" id="closeModal">&times;</span>

    <label>Available (+/-):</label>
    <input type="number" id="modalAvailable" value="0"><br>
    <label>Used (+ only):</label>
    <input type="number" id="modalUsed" value="0" min="0"><br>
    <label>Delivered (+/-):</label>
    <input type="number" id="modalDelivered" value="0"><br>

    <div class="inventory-table-container">
      <table class="inventory-table">
        <thead>
          <tr>
            <th>Collection Time</th>
            <th>Expiry Date</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody id="modalTableBody"></tbody>
      </table>
    </div>

    <div class="modal-footer">
      <button class="btn primary" id="saveModal">Submit</button>
      <button class="btn" id="cancelModal">Cancel</button>
    </div>
  </div>
</div>

<script>
const USER_ROLE = "<?= $role ?>";
let currentType = "";
let inventorySummary = {};

// Fetch inventory summary
function loadInventory(){
    fetch("bloodinventory.php")
    .then(res=>res.json())
    .then(data=>{
        inventorySummary = data;
        const container = document.getElementById("cardList");
        container.innerHTML = "";
        for(const type in data){
            const card = document.createElement("div");
            card.className = "blood-card";
            card.dataset.type = type;
            card.innerHTML = `
                <div class="type-badge">${type}</div>
                <div class="card-info">
                    <div>Available <span class="num available">${data[type].available}</span></div>
                    <div>Expired <span class="num expired">${data[type].expired}</span></div>
                    <div>Used <span class="num used">${data[type].used}</span></div>
                    <div>Delivered <span class="num delivered">${data[type].delivered}</span></div>
                </div>
            `;
            card.onclick = ()=>openModal(type);
            container.appendChild(card);
        }
    });
}

// Open modal for editing
function openModal(type){
    currentType = type;
    document.getElementById("modalTitle").innerText = `Edit ${type} Inventory`;
    document.getElementById("modalAvailable").value = 0;
    document.getElementById("modalUsed").value = 0;
    document.getElementById("modalDelivered").value = 0;

    // Show modal
    document.getElementById("editModal").classList.add("show");

    // Load detailed inventory
    fetch(`edit_inventory.php?getDetails=1&bloodType=${encodeURIComponent(type)}`)
    .then(res=>res.json())
    .then(data=>{
        const tbody = document.getElementById("modalTableBody");
        tbody.innerHTML = "";
        data.forEach(item=>{
            const tr = document.createElement("tr");
            tr.innerHTML = `<td>${item.collectionTime}</td>
                            <td>${item.expiryDate}</td>
                            <td><span class="status-pill ${item.status.toLowerCase()}">${item.status}</span></td>`;
            tbody.appendChild(tr);
        });
    });
}

// Close modal
document.getElementById("closeModal").onclick = () => document.getElementById("editModal").classList.remove("show");
document.getElementById("cancelModal").onclick = () => document.getElementById("editModal").classList.remove("show");

// Save modal changes
document.getElementById("saveModal").onclick = ()=>{
    const addAvailable = parseInt(document.getElementById("modalAvailable").value)||0;
    const addUsed = parseInt(document.getElementById("modalUsed").value)||0;
    const addDelivered = parseInt(document.getElementById("modalDelivered").value)||0;

    const currentAvailable = inventorySummary[currentType].available;

    // Prevent Used + Delivered > Available
    if(addUsed + addDelivered > currentAvailable){
        alert("Error: Cannot increase Used/Delivered more than Available!");
        return;
    }

    const formData = new FormData();
    formData.append("bloodType", currentType);
    formData.append("addAvailable", addAvailable>0?addAvailable:0);
    formData.append("removeAvailable", addAvailable<0?-addAvailable:0);
    formData.append("addUsed", addUsed);
    formData.append("addDelivered", addDelivered>0?addDelivered:0);

    fetch("edit_inventory.php", {method:"POST", body:formData})
    .then(res=>res.json())
    .then(resp=>{
        if(resp.success){
            alert("Inventory updated!");
            document.getElementById("editModal").classList.remove("show");
            loadInventory();
        }else{
            alert("Error: "+resp.error);
        }
    });
}

// Initial load
loadInventory();
</script>


</body>
</html>
