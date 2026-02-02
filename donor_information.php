<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Donor Health Reports</title>
<link rel="stylesheet" href="style.css" />
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<style>
    body {
        font-family: 'Poppins', sans-serif;
        margin: 0;
        padding: 0;
        background: #fde7e7;
        color: #1f2937;
    }
    .page-container {
    max-width: 900px;
    margin: 120px 60px 0 320px;
    margin-left: calc(260px + (100% - 260px - 900px) / 2);
    padding: 30px;
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    }

    /* Page intro card like Blood Inventory */
    .page-intro-card {
        background: #f44040;          /* red gradient or solid color */
        color: #fff;
        border-radius: 15px;
        padding: 25px 30px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }

    .page-intro-card h2 {
        margin: 0 0 10px 0;
        font-size: 24px;
        font-weight: 600;
    }

    .page-intro-card p {
        margin: 0;
        font-size: 16px;
    }

    .table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 18px;
    }

    .table-header input {
    padding: 12px 16px;
    border-radius: 14px;
    border: 1px solid #e5e7eb;
    width: 100%;
    max-width: 380px;
    font-size: 0.95rem;
    background: #fff;
    transition: all 0.25s ease;
    }

    .table-header input:focus {
    outline: none;
    border-color: #f16363;
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
    }
    .table-wrapper {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 12px 30px rgba(0,0,0,0.06);
    overflow: hidden;
    }

    #donorTable {
    width: 100%;
    border-collapse: collapse;
    }

    #donorTable thead {
    background: #fbf9f9;
    }

    #donorTable th {
    padding: 16px;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6b7280;
    }

    #donorTable td {
    padding: 16px;
    font-size: 0.9rem;
    border-bottom: 1px solid #f1f1f1;
    align-items: center;
    }

    #donorTable tbody tr {
    transition: all 0.25s ease;
    }

    #donorTable tbody tr:hover {
    background: #fff5f5;
    transform: scale(1.003);
    cursor: pointer;
    }

    #donorTable td, #donorTable th {
        text-align: center;       /* horizontal alignment */
        vertical-align: middle;   /* vertical alignment */
    }
    .status {
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 500;
    display: inline-block;
    }

    .status.pending {
    background: #fff7ed;
    color: #c2410c;
    }

    .status.approved {
    background: #ecfdf5;
    color: #047857;
    }

    .status.rejected {
    background: #fef2f2;
    color: #b91c1c;
    }
    .modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(17, 24, 39, 0.55);
    backdrop-filter: blur(4px);
    z-index: 999;
    }
    .modal-content {
    background: #fff;
    margin: auto;
    padding: 20px;
    border-radius: 22px;
    width: 92%;
    max-width: 520px;
    max-height: 85vh; 
    overflow: hidden;
    box-shadow: 0 25px 60px rgba(0,0,0,0.18);
    animation: slideUp 0.35s ease;
    position: relative;
    }

    @keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px) scale(0.96);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
    }
    .close {
    position: absolute;
    top: 18px;
    right: 18px;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    cursor: pointer;
    transition: all 0.2s ease;
    }

    .close:hover {
    background: #e5e7eb;
    }

    .modal-content form {
    display: flex;
    flex-direction: column;
    gap: 14px;
    }

    .modal-content label {
    font-size: 0.75rem;
    font-weight: 500;
    color: #6b7280;
    }

    .modal-content input,
    .modal-content select,
    .modal-content textarea {
    padding: 12px 14px;
    border-radius: 14px;
    border: 1px solid #e5e7eb;
    font-size: 0.9rem;
    transition: all 0.25s ease;
    }

    .modal-content input:focus,
    .modal-content select:focus,
    .modal-content textarea:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
    }
    .modal-actions {
    display: flex;
    gap: 12px;
    margin-top: 22px;
    }

    .modal-actions button {
    flex: 1;
    padding: 12px;
    border-radius: 14px;
    font-size: 0.85rem;
    font-weight: 500;
    border: none;
    cursor: pointer;
    transition: all 0.25s ease;
    }

    .approve-btn {
    background: #22c55e;
    color: #fff;
    }

    .reject-btn {
    background: #ef4444;
    color: #fff;
    }

    .modal-actions button:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.12);
    }
    /* ===== PROFILE CARD (MODAL HEADER) ===== */
    .profile-card {
    display: flex;
    gap: 16px;
    align-items: center;
    padding-bottom: 18px;
    border-bottom: 1px solid #eee;
    margin-bottom: 20px;
    }

    .profile-card img {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    border: 3px solid #6366f1;
    object-fit: cover;
    }

    .profile-info h2 {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 600;
    }

    .profile-info p {
    margin: 2px 0;
    font-size: 0.85rem;
    color: #6b7280;
    }

    /* ===== HEALTH REPORT SECTION ===== */
    .edit-section h3 {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 14px;
    color: #111827;
    }

    /* ===== FIELD ROWS ===== */
    .profile-field {
    display: grid;
    grid-template-columns: 150px 1fr auto;
    align-items: center;
    padding: 14px 0;
    border-bottom: 1px dashed #e5e7eb;
    }

    .field-label {
    font-size: 0.8rem;
    color: #6b7280;
    }

    .field-value {
    font-size: 0.9rem;
    font-weight: 500;
    color: #111827;
    }

    .edit-icon {
    font-size: 18px;
    color: #9ca3af;
    cursor: pointer;
    transition: color 0.2s ease;
    }

    .edit-icon:hover {
    color: #6366f1;
    }

    /* ===== APPROVE / REJECT BUTTONS ===== */
    .modal-buttons {
    display: flex;
    gap: 12px;
    margin-top: 24px;
    }

    .save-btn {
    flex: 1;
    padding: 12px;
    border-radius: 14px;
    border: none;
    font-size: 0.85rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.25s ease;
    color: #ffffff;
    background-color: #ff4646;
    }

    #approveBtn {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    }

    #rejectBtn {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    }

    .save-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.12);
    }

    /* ===== MOBILE ===== */
    @media (max-width: 600px) {
    .profile-card {
        flex-direction: column;
        text-align: center;
    }

    .profile-field {
        grid-template-columns: 1fr;
        gap: 6px;
    }

    .modal-buttons {
        flex-direction: column;
    }
    }
    /* ===== Status Pills ===== */
    .status-pill {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: capitalize;
    white-space: nowrap;
    }

    /* Pending */
    .status-pill.pending {
    background: #fff7ed;
    color: #c2410c;
    }

    /* Approved */
    .status-pill.approved {
    background: #ecfdf5;
    color: #047857;
    }

    /* Rejected */
    .status-pill.rejected {
    background: #fef2f2;
    color: #b91c1c;
    }

</style>
<body>
    <?php include "navbar_hospital.php"; ?>

<div class="page-container">

  <!-- Page Heading -->
  <div class="page-intro-card">
    <h2>Manage Donor Information</h2>
    <p>Here you can review, approve, reject, or edit donors’ submitted health reports.</p>
  </div>


  <!-- Search -->
  <div class="table-header">
    <input type="text" id="searchInput" placeholder="Search donors by name, email or phone..." />
  </div>

  <!-- Donor Table -->
  <table id="donorTable">
    <thead>
      <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody id="donorTableBody">
      <!-- Donor rows will be populated here -->
    </tbody>
  </table>

</div>

<!-- Health Report Modal -->
<div class="modal" id="healthModal">
  <div class="modal-content modern-modal">
    <!-- Upper profile card -->
    <div class="profile-card">
      <img id="avatar" src="default-avatar.png" alt="Avatar">
      <div class="profile-info">
        <h2 id="nameValue">John Doe</h2>
        <p id="emailValue">john@example.com</p>
        <p id="phoneValue">0123456789</p>
        <p>
        Status: 
        <span id="statusValue" class="status-pill pending">Pending</span>
        </p>
      </div>
    </div>

    <!-- Lower editable health report -->
    <div class="edit-section">
      <h3>Health Report</h3>

      <div class="profile-field">
        <span class="field-label">Blood Pressure</span>
        <span class="field-value" id="bpValue">120/80</span>
        <span class="edit-icon" data-field="bp"><i class='bx bx-pencil'></i></span>
      </div>

      <div class="profile-field">
        <span class="field-label">Hemoglobin</span>
        <span class="field-value" id="hbValue">14.2 g/dL</span>
        <span class="edit-icon" data-field="hb"><i class='bx bx-pencil'></i></span>
      </div>

      <div class="profile-field">
        <span class="field-label">Weight</span>
        <span class="field-value" id="weightValue">70 kg</span>
        <span class="edit-icon" data-field="weight"><i class='bx bx-pencil'></i></span>
      </div>

      <div class="profile-field">
        <span class="field-label">Other Notes</span>
        <span class="field-value" id="notesValue">Healthy</span>
        <span class="edit-icon" data-field="notes"><i class='bx bx-pencil'></i></span>
      </div>
    </div>
    <!-- Approve / Reject buttons -->
      <div class="modal-buttons">
        <button class="save-btn" id="approveBtn">Approve</button>
        <button class="save-btn" id="rejectBtn" style="background-color:#e74c3c">Reject</button>
      </div>
  </div>
</div>
<script src="script.js"></script>
<script src="health.js"></script>
</body>
</html>
