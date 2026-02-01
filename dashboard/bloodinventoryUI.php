<?php
session_start();
// For testing
$_SESSION['role'] = 'hospital';
$role = $_SESSION['role'];
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

.card-list { display:grid; grid-template-columns: repeat(2, 1fr); gap:16px; }

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
.modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,0.5); display:flex; justify-content:center; align-items:center; opacity:0; pointer-events:none; transition:opacity 0.3s; }
.modal-overlay.show { opacity:1; pointer-events:auto; }
.modal-card { background:#fff; border-radius:20px; width:90%; max-width:500px; padding:25px 30px; box-shadow:0 15px 40px rgba(0,0,0,0.2); position:relative; max-height:90vh; overflow-y:auto; }
.modal-card h3 { margin-top:0; text-align:center; }
.close-modal { position:absolute; top:15px; right:20px; font-size:30px; cursor:pointer; color:#999; }
.close-modal:hover { color:#1976d2; }

.modal-card input { width:100%; padding:10px; margin:6px 0; border-radius:6px; border:1px solid #ccc; }
.modal-footer { display:flex; justify-content:flex-end; gap:10px; margin-top:10px; }

/* Inventory table */
/* Table container with scrollable body */
.inventory-table-container {
  max-height: 250px;
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

/* Modern pill badges centered perfectly */
.inventory-table td.status {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 35px; /* consistent row height */
  border-radius: 999px; /* pill shape */
  padding: 4px 12px;
  font-size: 12px;
  font-weight: 500;
  text-transform: capitalize;
  color: #fff;
  min-width: 80px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.15);
  transition: transform 0.2s, box-shadow 0.2s;
}

/* Status colors */
.inventory-table td.status.available { background: #3fc944; }
.inventory-table td.status.used { background: #38a2fa; }
.inventory-table td.status.delivered { background: #ffb039; }
.inventory-table td.status.expired { background: #f9473a; }

/* Hover effect for badges */
.inventory-table td.status:hover {
  transform: translateY(-2px);
  box-shadow: 0 3px 6px rgba(0,0,0,0.2);
}
</style>
</head>

<body>
<?php include "navbar/navbar_$role.php"; ?>

<div class="main-container">

  <!-- Page Intro Card -->
  <div class="page-intro">
    <h2>Manage Blood Inventory</h2> 
    <p>Here you can review and edit the blood inventory. Keep track of all blood types and their usage.</p>
  </div>

<div class="card-list">
<?php
$bloodTypes = [
    ["A", 20, 2, 5, 10],
    ["B", 15, 1, 3, 8],
    ["AB", 10, 0, 2, 5],
    ["O", 25, 3, 7, 12]
];
foreach($bloodTypes as $bt):
?>
<div class="blood-card" data-type="<?= $bt[0] ?>">
    <div class="type-badge"><?= $bt[0] ?></div>

    <div class="card-info">
        <div>Available <span class="num available"><?= $bt[1] ?></span></div>
        <div>Expired <span class="num expired"><?= $bt[2] ?></span></div>
        <div>Used <span class="num used"><?= $bt[3] ?></span></div>
        <div>Delivered <span class="num delivered"><?= $bt[4] ?></span></div>
    </div>

    <div class="card-actions">
        <button class="btn secondary edit-btn">Edit</button>
    </div>
</div>
<?php endforeach; ?>
</div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="edit-modal">
  <div class="modal-card">
    <h3>Edit Blood Inventory</h3>
    <span class="close-modal">&times;</span>

    <label>Available</label>
    <input type="number" id="modal-available" min="0">
    <label>Used</label>
    <input type="number" id="modal-used" min="0">
    <label>Delivered</label>
    <input type="number" id="modal-delivered" min="0">

    <div class="inventory-table-container">
      <table class="inventory-table">
        <thead>
          <tr>
            <th>Collection Time</th>
            <th>Expiry Date</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody id="inventory-tbody"></tbody>
      </table>
    </div>

    <div class="modal-footer">
      <button class="btn primary" id="save-modal-btn">Submit</button>
      <button class="btn" id="cancel-modal-btn">Cancel</button>
    </div>
  </div>
</div>

<script>
const USER_ROLE = "<?= $role ?>";
const editModal = document.getElementById("edit-modal");
let currentCard = null;

// Sample inventory data
const inventoryData = {
    "A": [
        {collection:"2026-01-01 10:00", expiry:"2026-01-31", status:"Available"},
        {collection:"2026-01-05 12:00", expiry:"2026-02-04", status:"Used"},
        {collection:"2026-01-08 09:30", expiry:"2026-02-07", status:"Delivered"},
        {collection:"2026-01-08 09:30", expiry:"2026-02-07", status:"Delivered"},
        {collection:"2026-01-08 09:30", expiry:"2026-02-07", status:"Delivered"},
        {collection:"2026-01-08 09:30", expiry:"2026-02-07", status:"Delivered"},
    ],
    "B": [
        {collection:"2026-01-02 11:00", expiry:"2026-02-01", status:"Available"},
    ],
    "AB": [],
    "O": [
        {collection:"2026-01-03 14:00", expiry:"2026-02-02", status:"Used"},
    ]
};

// Open edit modal
document.addEventListener("click", e=>{
    if(e.target.classList.contains("edit-btn") && USER_ROLE==="hospital"){
        currentCard = e.target.closest(".blood-card");
        const type = currentCard.dataset.type;

        editModal.querySelector("#modal-available").value = currentCard.querySelector(".available").innerText;
        editModal.querySelector("#modal-used").value = currentCard.querySelector(".used").innerText;
        editModal.querySelector("#modal-delivered").value = currentCard.querySelector(".delivered").innerText;

        // Fill table
        const tbody = document.getElementById("inventory-tbody");
        tbody.innerHTML = "";
        (inventoryData[type] || []).forEach(item=>{
    const tr = document.createElement("tr");
    tr.innerHTML = `
        <td>${item.collection}</td>
        <td>${item.expiry}</td>
        <td class="status ${item.status.toLowerCase()}">${item.status}</td>
    `;
    tbody.appendChild(tr);
});


        editModal.classList.add("show");
    }
});

// Close modal
editModal.querySelector(".close-modal").addEventListener("click", ()=>{ editModal.classList.remove("show"); });
document.getElementById("cancel-modal-btn").addEventListener("click", ()=>{ editModal.classList.remove("show"); });

// Save changes
document.getElementById("save-modal-btn").addEventListener("click", ()=>{
    if(!currentCard) return;

    currentCard.querySelector(".available").innerText = editModal.querySelector("#modal-available").value;
    currentCard.querySelector(".used").innerText = editModal.querySelector("#modal-used").value;
    currentCard.querySelector(".delivered").innerText = editModal.querySelector("#modal-delivered").value;

    editModal.classList.remove("show");
});
</script>

</body>
</html>
