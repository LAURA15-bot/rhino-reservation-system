// js/guest_register.js

let globalGuestRecords = [];
let filteredRecords = [];

let currentPage = 1;
let rowsPerPage = 10;

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
});

document.addEventListener("DOMContentLoaded", () => {
    loadGuestData();

    setInterval(() => {
        const viewModal = document.getElementById('view-modal-backdrop');
        const editModal = document.getElementById('edit-modal-backdrop');
        const isViewOpen = viewModal && !viewModal.classList.contains('hidden');
        const isEditOpen = editModal && !editModal.classList.contains('hidden');
        
        if (!isViewOpen && !isEditOpen) {
            loadGuestData();
        }
    }, 15000);
});

function loadGuestData() {
    fetch('api/guest_register_api.php')
        .then(res => res.json())
        .then(response => {
            if(response.success) {
                globalGuestRecords = response.data;
                filterTable(); 
            } else {
                console.error("Failed to load guest register data");
            }
        })
        .catch(err => console.error("API Error: ", err));
}

function toggleCustomDates() {
    const dateFilter = document.getElementById('dateFilter').value;
    const customWrapper = document.getElementById('customDateWrapper');
    if (dateFilter === 'custom') {
        customWrapper.classList.remove('hidden');
        customWrapper.classList.add('flex');
    } else {
        customWrapper.classList.add('hidden');
        customWrapper.classList.remove('flex');
        document.getElementById('startDate').value = '';
        document.getElementById('endDate').value = '';
    }
}

// 1. FILTER & SEARCH ENGINE
function filterTable() {
    const dateFilter = document.getElementById('dateFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    const searchVal = document.getElementById('searchInput').value.toLowerCase().trim();
    
    const today = new Date();
    today.setHours(0,0,0,0);

    filteredRecords = globalGuestRecords.filter(row => {
        const rowDateStr = row.check_in_date;
        const rowStatus = row.current_status;
        
        const rowDate = new Date(rowDateStr);
        rowDate.setHours(0,0,0,0);

        let showByDate = true;
        if (dateFilter === 'today') {
            showByDate = (rowDate.getTime() === today.getTime());
        } else if (dateFilter === '7') {
            const sevenDaysAgo = new Date(today);
            sevenDaysAgo.setDate(today.getDate() - 7);
            showByDate = (rowDate >= sevenDaysAgo && rowDate <= today);
        } else if (dateFilter === '30') {
            const thirtyDaysAgo = new Date(today);
            thirtyDaysAgo.setDate(today.getDate() - 30);
            showByDate = (rowDate >= thirtyDaysAgo && rowDate <= today);
        } else if (dateFilter === 'custom') {
            if (startDate && endDate) {
                const start = new Date(startDate); start.setHours(0,0,0,0);
                const end = new Date(endDate); end.setHours(0,0,0,0);
                showByDate = (rowDate >= start && rowDate <= end);
            }
        }

        let showByStatus = true;
        if (statusFilter !== 'All Statuses') {
            showByStatus = (rowStatus === statusFilter);
        }
        
        let showBySearch = true;
        if (searchVal !== '') {
            const gName = (row.guest_name || '').toLowerCase();
            const aName = (row.agency_name || '').toLowerCase();
            const bOfficer = (row.booking_officer || '').toLowerCase();
            const bSource = (row.booking_source || '').toLowerCase();
            
            showBySearch = gName.includes(searchVal) || aName.includes(searchVal) || bOfficer.includes(searchVal) || bSource.includes(searchVal);
        }

        return showByDate && showByStatus && showBySearch;
    });

    const totalPages = rowsPerPage === 'all' ? 1 : Math.ceil(filteredRecords.length / rowsPerPage);
    if (currentPage > totalPages && totalPages > 0) {
        currentPage = totalPages;
    } else if (totalPages === 0) {
        currentPage = 1;
    }

    renderTablePage();
}

// 2. PAGINATION CONTROLS
window.changeRowsPerPage = function() {
    const val = document.getElementById('rowsPerPageFilter').value;
    rowsPerPage = val === 'all' ? 'all' : parseInt(val);
    currentPage = 1;
    renderTablePage();
}

window.changePage = function(direction) {
    currentPage += direction;
    renderTablePage();
}

// 3. TABLE RENDER ENGINE
function renderTablePage() {
    const tbody = document.getElementById('guestTableBody');
    tbody.innerHTML = '';

    const totalRecords = filteredRecords.length;

    if (totalRecords === 0) {
        tbody.innerHTML = `
            <tr id="noRecordsRow">
                <td colspan="9" class="px-6 py-12 text-center text-sm text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-900">
                    <i class="fa-solid fa-folder-open text-3xl text-slate-300 dark:text-slate-600 mb-3 block"></i>
                    No guest records found for the selected criteria.
                </td>
            </tr>
        `;
        updatePaginationUI(0, 0, 0);
        return;
    }

    let startIndex = (currentPage - 1) * (rowsPerPage === 'all' ? totalRecords : rowsPerPage);
    let endIndex = rowsPerPage === 'all' ? totalRecords : startIndex + rowsPerPage;
    if (endIndex > totalRecords) endIndex = totalRecords;

    const paginatedItems = rowsPerPage === 'all' ? filteredRecords : filteredRecords.slice(startIndex, endIndex);
    const primaryThemeColor = document.body.dataset.primaryColor || '#046a38';

    paginatedItems.forEach(row => {
        const status = row.current_status;
        const isCheckedOut = (status === 'Checked Out');
        
        const rowClass = isCheckedOut 
            ? "bg-slate-50 dark:bg-slate-800/30 text-slate-400 dark:text-slate-500" 
            : "hover:bg-slate-50/70 dark:hover:bg-slate-800/50 text-slate-700 dark:text-slate-300 transition-colors bg-white dark:bg-slate-900";
            
        const nameClass = isCheckedOut 
            ? "text-slate-400 dark:text-slate-500" 
            : "text-slate-900 dark:text-white";
        
        let badgeClass = "bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700";
        if (status === 'Fully Paid') badgeClass = "bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800";
        if (status === 'Partially Paid') badgeClass = "bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800";
        if (status === 'Outstanding') badgeClass = "bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800";
        if (status === 'Cancelled') badgeClass = "bg-slate-800 dark:bg-slate-700 text-white border border-slate-900 dark:border-slate-600";
        if (status === 'Checked Out') badgeClass = "bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800";

        let actionsHtml = `<button onclick="openViewModal(${row.id})" class="text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 px-2 transition-colors" title="View Detailed Particulars"><i class="fa-solid fa-eye"></i></button>`;
        
        if (IS_ADMIN && !isCheckedOut) {
            actionsHtml += `<button onclick="openEditModal(${row.id})" style="color: ${primaryThemeColor};" class="hover:opacity-70 px-2 border-l border-slate-200 dark:border-slate-700 ml-1 transition-opacity" title="Edit Booking (Admin)"><i class="fa-solid fa-pen"></i></button>`;
        }

        const tr = document.createElement('tr');
        tr.className = `guest-row ${rowClass}`;

        tr.innerHTML = `
            <td class="px-6 py-4 text-xs font-bold ${nameClass} whitespace-nowrap">${escapeHtml(row.guest_name)}</td>
            <td class="px-6 py-4 text-xs whitespace-nowrap"><span class="px-2.5 py-1 text-[10px] uppercase tracking-wider font-bold rounded-lg ${badgeClass}">${escapeHtml(status)}</span></td>
            <td class="px-6 py-4 text-xs font-semibold whitespace-nowrap">${escapeHtml(row.room_type)}</td>
            
            <td class="px-6 py-4 text-xs font-mono whitespace-nowrap text-slate-500 dark:text-slate-400">${escapeHtml(row.display_booking_date)}</td>
            
            <td class="px-6 py-4 text-xs font-semibold whitespace-nowrap">${escapeHtml(row.check_in_date)}</td>
            <td class="px-6 py-4 text-xs font-semibold whitespace-nowrap">${escapeHtml(row.check_out_date)}</td>
            <td class="px-6 py-4 text-xs font-mono font-bold whitespace-nowrap">${escapeHtml(String(row.number_of_nights))}</td>
            <td class="px-6 py-4 text-xs font-mono font-bold whitespace-nowrap">${escapeHtml(String(row.number_of_rooms))}</td>
            
            <td class="px-6 py-4 text-xs text-center whitespace-nowrap">${actionsHtml}</td>
        `;
        tbody.appendChild(tr);
    });

    updatePaginationUI(startIndex + 1, endIndex, totalRecords);
}

function updatePaginationUI(startIdx, endIdx, total) {
    document.getElementById('page-info').innerText = total === 0 
        ? "Showing 0 records" 
        : `Showing ${startIdx} to ${endIdx} of ${total} entries`;

    const prevBtn = document.getElementById('prev-page-btn');
    const nextBtn = document.getElementById('next-page-btn');

    if (currentPage === 1 || total === 0 || rowsPerPage === 'all') { prevBtn.disabled = true; } 
    else { prevBtn.disabled = false; }

    const totalPages = rowsPerPage === 'all' ? 1 : Math.ceil(total / rowsPerPage);
    if (currentPage >= totalPages || total === 0 || rowsPerPage === 'all') { nextBtn.disabled = true; } 
    else { nextBtn.disabled = false; }
}

// ==========================================
// VIEW MODAL LOGIC (All Users)
// ==========================================
window.openViewModal = function(id) {
    const row = globalGuestRecords.find(r => r.id == id);
    if (!row) return;

    document.getElementById('view-booking-id').innerText = row.id;
    document.getElementById('view-name').innerText = row.guest_name;
    document.getElementById('view-booking-date').innerText = row.display_booking_date;
    document.getElementById('view-checkin').innerText = row.check_in_date;
    document.getElementById('view-checkout').innerText = row.check_out_date;
    document.getElementById('view-room-type').innerText = row.room_type + " (" + (row.room_tier || 'Superior Tent') + ")";
    
    let sourceText = row.booking_source || 'Direct Client';
    if(row.agency_name) sourceText += ` - ${row.agency_name}`;
    document.getElementById('view-source').innerText = sourceText;

    const notes = document.getElementById('view-notes');
    if (row.special_requests && row.special_requests.trim() !== '') {
        notes.innerText = row.special_requests;
        notes.classList.remove('italic', 'text-slate-400');
    } else {
        notes.innerText = "No special requests filed.";
        notes.classList.add('italic', 'text-slate-400');
    }

    // NEW: Extract variables and populate the Financial Ledger Summary
    const currency = row.currency || 'KES';
    const total = parseFloat(row.total_amount) || 0;
    const paid = parseFloat(row.actual_paid) || 0;
    const balance = Math.max(0, total - paid);

    document.getElementById('view-total-amount').innerText = `${currency} ${total.toLocaleString(undefined, {minimumFractionDigits: 2})}`;
    document.getElementById('view-total-paid').innerText = `${currency} ${paid.toLocaleString(undefined, {minimumFractionDigits: 2})}`;
    document.getElementById('view-balance-due').innerText = `${currency} ${balance.toLocaleString(undefined, {minimumFractionDigits: 2})}`;

    const modal = document.getElementById('view-modal-backdrop');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

window.closeViewModal = function() {
    const modal = document.getElementById('view-modal-backdrop');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// ==========================================
// EDIT MODAL LOGIC (Admin Only)
// ==========================================
window.toggleEditSpecialReq = function(checkbox) {
    const wrap = document.getElementById('edit-req-wrap');
    const input = document.getElementById('edit_special_requests');
    if (checkbox.checked) {
        wrap.classList.remove('hidden');
        input.setAttribute('required', 'required');
    } else {
        wrap.classList.add('hidden');
        input.removeAttribute('required');
        input.value = '';
    }
}

window.openEditModal = function(id) {
    if (!IS_ADMIN) return;
    
    const row = globalGuestRecords.find(r => r.id == id);
    if (!row) return;

    if (row.current_status === 'Checked Out') {
        Swal.fire({ icon: 'error', title: 'Action Denied', text: 'You cannot edit the particulars of a guest who has already checked out.' });
        return;
    }

    document.getElementById('edit_id').value = row.id;
    
    document.getElementById('edit_agency_name').value = row.agency_name || 'Direct Client';
    document.getElementById('edit_booking_officer').value = row.booking_officer || 'Front Desk';
    
    document.getElementById('edit_guest_name').value = row.guest_name;
    document.getElementById('edit_booking_date').value = row.display_booking_date;
    
    document.getElementById('edit_guest_type').value = row.guest_type || 'Resident';
    document.getElementById('edit_room_tier').value = row.room_tier || 'Superior Tent';
    document.getElementById('edit_room_type').value = row.room_type;
    document.getElementById('edit_rooms_count').value = row.rooms_count || 1;
    
    document.getElementById('edit_check_in').value = row.check_in_date;
    document.getElementById('edit_nights').value = row.number_of_nights || 1;
    
    let totalDiscount = parseFloat(row.discount) || 0;
    let nights = parseInt(row.number_of_nights) || 1;
    let rooms = parseInt(row.rooms_count) || 1;
    let discPerRoom = (totalDiscount / (nights * rooms)).toFixed(2);
    document.getElementById('edit_discount').value = discPerRoom;

    document.getElementById('edit_adults').value = row.number_of_adults || 1;
    document.getElementById('edit_children').value = row.number_of_children || 0;
    document.getElementById('edit_child_rooms').value = row.children_own_rooms || 0;
    
    document.getElementById('edit_under_12').checked = (row.children_under_12 == 1);
    
    const reqText = row.special_requests || '';
    document.getElementById('edit_special_requests').value = reqText;
    
    const reqCheck = document.getElementById('edit_has_requests');
    const reqWrap = document.getElementById('edit-req-wrap');
    
    if (reqText.trim() !== '') {
        reqCheck.checked = true;
        reqWrap.classList.remove('hidden');
        document.getElementById('edit_special_requests').setAttribute('required', 'required');
    } else {
        reqCheck.checked = false;
        reqWrap.classList.add('hidden');
        document.getElementById('edit_special_requests').removeAttribute('required');
    }

    const modal = document.getElementById('edit-modal-backdrop');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

window.closeEditModal = function() {
    const modal = document.getElementById('edit-modal-backdrop');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

window.submitEditForm = function(e) {
    e.preventDefault();
    const formData = new FormData(e.target);

    fetch('api/guest_register_api.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            closeEditModal();
            Toast.fire({ icon: 'success', title: 'Booking particulars updated.' });
            loadGuestData(); 
        } else {
            Swal.fire({ icon: 'error', title: 'Update Denied', text: data.message });
        }
    })
    .catch(() => Swal.fire({ icon: 'error', title: 'Network Error', text: 'Could not communicate with the database.' }));
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}