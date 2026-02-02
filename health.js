// // // ---------- MOCK DONOR DATA ----------
// // const donors = [
// //   {
// //     id: 1,
// //     name: "John Doe",
// //     email: "john@example.com",
// //     phone: "0123456789",
// //     avatar: "default-avatar.png",
// //     status: "Pending",
// //     health: {
// //       bp: "120/80",
// //       hb: 14.2,
// //       weight: 70,
// //       notes: "Healthy"
// //     }
// //   },
// //   {
// //     id: 2,
// //     name: "Jane Smith",
// //     email: "jane@example.com",
// //     phone: "0198765432",
// //     avatar: "default-avatar.png",
// //     status: "Pending",
// //     health: {
// //       bp: "110/70",
// //       hb: 13.5,
// //       weight: 60,
// //       notes: "No issues"
// //     }
// //   }
// // ];

// // ---------- CREATE DONOR TABLE ----------
// const tableBody = document.getElementById("donorTableBody");

// function renderDonorTable() {
//   tableBody.innerHTML = "";
//   donors.forEach(donor => {
//     const tr = document.createElement("tr");

//     tr.innerHTML = `
//       <td>${donor.name}</td>
//       <td>${donor.email}</td>
//       <td>${donor.phone}</td>
//       <td>
//         <span class="status-pill ${donor.status.toLowerCase()}">
//             ${donor.status}
//         </span>
//       </td>
//     `;

//     tr.addEventListener("click", () => openHealthModal(donor.id));
//     tableBody.appendChild(tr);
//   });
// }

// // ---------- MODAL ELEMENTS ----------
// const modal = document.getElementById("healthModal");
// const avatarEl = document.getElementById("avatar");
// const nameEl = document.getElementById("nameValue");
// const emailEl = document.getElementById("emailValue");
// const phoneEl = document.getElementById("phoneValue");
// const statusEl = document.getElementById("statusValue");

// const bpEl = document.getElementById("bpValue");
// const hbEl = document.getElementById("hbValue");
// const weightEl = document.getElementById("weightValue");
// const notesEl = document.getElementById("notesValue");

// let currentDonor = null;

// // ---------- OPEN MODAL AND LOAD DATA ----------
// function openHealthModal(donorId) {
//   currentDonor = donors.find(d => d.id === donorId);

//   avatarEl.src = currentDonor.avatar;
//   nameEl.textContent = currentDonor.name;
//   emailEl.textContent = currentDonor.email;
//   phoneEl.textContent = currentDonor.phone;
//   statusEl.textContent = currentDonor.status;
//   statusEl.className = "status-pill " + currentDonor.status.toLowerCase();

//   bpEl.textContent = currentDonor.health.bp;
//   hbEl.textContent = currentDonor.health.hb + " g/dL";
//   weightEl.textContent = currentDonor.health.weight + " kg";
//   notesEl.textContent = currentDonor.health.notes;

//   modal.style.display = "flex";

//   initEditIcons(); // Initialize pencil edits
// }

// // ---------- CLOSE MODAL ----------
// window.onclick = e => { 
//   if(e.target==modal) modal.style.display="none"; 
// };

// // ---------- INLINE EDIT LOGIC ----------
// function initEditIcons() {
//   document.querySelectorAll('.edit-icon').forEach(icon => {
//     icon.onclick = () => {
//       const field = icon.dataset.field;
//       let spanEl = document.getElementById(field + "Value");
//       const currentValue = spanEl.textContent.replace(/[^0-9.\/a-zA-Z ]/g,"");

//       // Create input
//       const input = document.createElement('input');
//       input.type = (field==="hb" || field==="weight") ? "number" : "text";
//       input.value = currentValue;
//       input.classList.add('modern-input');

//       spanEl.replaceWith(input);
//       icon.style.display = "none";

//       // Create save button
//       const saveBtn = document.createElement('button');
//       saveBtn.textContent = "Save";
//       saveBtn.className = "save-btn";
//       input.after(saveBtn);

//       saveBtn.onclick = () => {
//         if(field==="hb") spanEl.textContent = input.value + " g/dL";
//         else if(field==="weight") spanEl.textContent = input.value + " kg";
//         else spanEl.textContent = input.value;

//         currentDonor.health[field] = input.value;

//         input.replaceWith(spanEl);
//         saveBtn.remove();
//         icon.style.display = "inline";
//       };
//     };
//   });
// }

// // ---------- APPROVE / REJECT BUTTONS ----------
// document.getElementById("approveBtn").onclick = () => {
//   if (!currentDonor) return;

//   currentDonor.status = "Approved";
//   statusEl.textContent = "Approved";
//   statusEl.className = "status-pill approved";

//   modal.style.display = "none";
//   renderDonorTable();
// };

// document.getElementById("rejectBtn").onclick = () => {
//   if (!currentDonor) return;

//   currentDonor.status = "Rejected";
//   statusEl.textContent = "Rejected";
//   statusEl.className = "status-pill rejected";

//   modal.style.display = "none";
//   renderDonorTable();
// };

// // ---------- INITIAL RENDER ----------
// renderDonorTable();
