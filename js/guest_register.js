// js/guest_register.js

let globalGuestRecords = [];
let filteredRecords = [];

// Pagination Variables
let currentPage = 1;
let rowsPerPage = 10;

document.addEventListener("DOMContentLoaded", () => {
    loadGuestData();

    setInterval(() => {
        loadGuestData();
    }, 15000);
});

function loadGuestData() {
    fetch('api/guest_register_api.php')
        .then(res => res.json())
        .then(response => {
            if(response.success) {
                globalGuestRecords = response.data;
                filterTable(); // Automatically handles pagination and rendering
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

// 1. FILTER ENGINE
function filterTable() {
    const dateFilter = document.getElementById('dateFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
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

        return showByDate && showByStatus;
    });

    // Reset to page 1 whenever filters change
    currentPage = 1;
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
                <td colspan="7" class="px-6 py-12 text-center text-sm text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-900">
                    <i class="fa-solid fa-folder-open text-3xl text-slate-300 dark:text-slate-600 mb-3 block"></i>
                    No guest records found for the selected criteria.
                </td>
            </tr>
        `;
        updatePaginationUI(0, 0, 0);
        return;
    }

    // Determine the slice for the current page
    let startIndex = (currentPage - 1) * (rowsPerPage === 'all' ? totalRecords : rowsPerPage);
    let endIndex = rowsPerPage === 'all' ? totalRecords : startIndex + rowsPerPage;
    if (endIndex > totalRecords) endIndex = totalRecords;

    const paginatedItems = rowsPerPage === 'all' ? filteredRecords : filteredRecords.slice(startIndex, endIndex);

    paginatedItems.forEach(row => {
        const status = row.current_status;
        const isCheckedOut = (status === 'Checked Out');
        
        // Dark Mode Native Classes included here
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

        const tr = document.createElement('tr');
        tr.className = `guest-row ${rowClass}`;

        tr.innerHTML = `
            <td class="px-6 py-4 text-xs font-bold ${nameClass} whitespace-nowrap">${escapeHtml(row.guest_name)}</td>
            <td class="px-6 py-4 text-xs whitespace-nowrap"><span class="px-2.5 py-1 text-[10px] uppercase tracking-wider font-bold rounded-lg ${badgeClass}">${escapeHtml(status)}</span></td>
            <td class="px-6 py-4 text-xs font-semibold whitespace-nowrap">${escapeHtml(row.room_type)}</td>
            <td class="px-6 py-4 text-xs font-semibold whitespace-nowrap">${escapeHtml(row.check_in_date)}</td>
            <td class="px-6 py-4 text-xs font-semibold whitespace-nowrap">${escapeHtml(row.check_out_date)}</td>
            <td class="px-6 py-4 text-xs font-mono font-bold whitespace-nowrap">${escapeHtml(String(row.number_of_nights))}</td>
            <td class="px-6 py-4 text-xs font-mono font-bold whitespace-nowrap">${escapeHtml(String(row.number_of_rooms))}</td>
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

    // Disable Prev if on page 1
    if (currentPage === 1 || total === 0 || rowsPerPage === 'all') {
        prevBtn.disabled = true;
    } else {
        prevBtn.disabled = false;
    }

    // Disable Next if on last page
    const totalPages = rowsPerPage === 'all' ? 1 : Math.ceil(total / rowsPerPage);
    if (currentPage >= totalPages || total === 0 || rowsPerPage === 'all') {
        nextBtn.disabled = true;
    } else {
        nextBtn.disabled = false;
    }
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}