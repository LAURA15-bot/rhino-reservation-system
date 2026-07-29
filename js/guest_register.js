// js/guest_register.js

let globalGuestRecords = [];

document.addEventListener("DOMContentLoaded", () => {
    // Initial Load
    loadGuestData();

    // =======================================================
    // REAL-TIME BACKGROUND POLLING ENGINE (Every 15 seconds)
    // =======================================================
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
                renderTableRows(globalGuestRecords);
            } else {
                console.error("Failed to load guest register data");
            }
        })
        .catch(err => console.error("API Error: ", err));
}

function renderTableRows(records) {
    const tbody = document.getElementById('guestTableBody');
    tbody.innerHTML = '';

    if (!records || records.length === 0) {
        tbody.innerHTML = `
            <tr id="noRecordsRow">
                <td colspan="7" class="px-6 py-12 text-center text-sm text-slate-500 bg-white">
                    <i class="fa-solid fa-folder-open text-3xl text-slate-300 mb-3 block"></i>
                    No guest records found.
                </td>
            </tr>
        `;
        return;
    }

    records.forEach(row => {
        const status = row.current_status;
        const isCheckedOut = (status === 'Checked Out');
        
        const rowClass = isCheckedOut ? "bg-slate-50 text-slate-400" : "hover:bg-slate-50/70 text-slate-700 transition-colors bg-white";
        const nameClass = isCheckedOut ? "text-slate-400" : "text-slate-900";
        
        let badgeClass = "bg-slate-100 text-slate-600 border border-slate-200";
        if (status === 'Fully Paid') badgeClass = "bg-emerald-50 text-emerald-700 border border-emerald-200";
        if (status === 'Partially Paid') badgeClass = "bg-amber-50 text-amber-700 border border-amber-200";
        if (status === 'Outstanding') badgeClass = "bg-rose-50 text-rose-700 border border-rose-200";
        if (status === 'Cancelled') badgeClass = "bg-slate-800 text-white border border-slate-900";
        if (status === 'Checked Out') badgeClass = "bg-indigo-50 text-indigo-700 border border-indigo-200";

        const tr = document.createElement('tr');
        tr.className = `guest-row ${rowClass}`;
        tr.setAttribute('data-checkin', row.check_in_date);
        tr.setAttribute('data-status', status);

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

    // Re-apply filters instantly after drawing new rows to prevent UI jumping
    filterTable();
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
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

function filterTable() {
    const dateFilter = document.getElementById('dateFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    const rows = document.querySelectorAll('.guest-row');
    const today = new Date();
    today.setHours(0,0,0,0);

    rows.forEach(row => {
        const rowDateStr = row.getAttribute('data-checkin');
        const rowStatus = row.getAttribute('data-status');
        
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
                const start = new Date(startDate);
                const end = new Date(endDate);
                start.setHours(0,0,0,0);
                end.setHours(0,0,0,0);
                showByDate = (rowDate >= start && rowDate <= end);
            }
        }

        let showByStatus = true;
        if (statusFilter !== 'All Statuses') {
            showByStatus = (rowStatus === statusFilter);
        }

        if (showByDate && showByStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}