// js/notifications.js

let globalNotificationsData = [];

document.addEventListener("DOMContentLoaded", () => {
    fetchNotifications();

    setInterval(() => {
        // Prevent background refresh if SweetAlert is currently open
        if (!Swal.isVisible()) {
            fetchNotifications();
        }
    }, 15000); 
});

function fetchNotifications() {
    fetch('api/notifications_api.php?action=fetch_notifications')
        .then(res => res.json())
        .then(response => {
            if(response.success) {
                globalNotificationsData = response.data;
                filterNotificationsTable(); 
            } else {
                console.error("Failed to load notifications.");
            }
        })
        .catch(err => console.error("API Error: ", err));
}

function filterNotificationsTable() {
    const searchVal = document.getElementById('searchInput').value.toLowerCase().trim();
    const urgencyFilter = document.getElementById('urgencyFilter').value;
    const tbody = document.getElementById('notificationsTableBody');
    tbody.innerHTML = '';
    
    let visibleCount = 0;

    globalNotificationsData.forEach(row => {
        
        let showBySearch = true;
        if (searchVal !== "") {
            showBySearch = row.guest_name.toLowerCase().includes(searchVal) || row.agency_name.toLowerCase().includes(searchVal);
        }

        let showByUrgency = true;
        if (urgencyFilter !== 'All') {
            showByUrgency = (row.urgency === urgencyFilter);
        }

        if (showBySearch && showByUrgency) {
            visibleCount++;
            
            let badgeClass = 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700';
            if (row.urgency === 'Expired') badgeClass = 'bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400 border-rose-200 dark:border-rose-800';
            else if (row.urgency === 'Today') badgeClass = 'bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400 border-rose-200 dark:border-rose-800';
            else if (row.urgency === 'Tomorrow') badgeClass = 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-800';
            else badgeClass = 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800';

            // Visual Checkmark Badge if followed up
            let followedUpBadge = row.is_followed_up === 1 ? `<span class="block mt-1.5 text-[9px] font-black text-emerald-600 dark:text-emerald-400"><i class="fa-solid fa-check-double mr-1"></i>FOLLOWED UP</span>` : '';

            let sourceHtml = `<span class="font-bold text-slate-700 dark:text-slate-300">${escapeHtml(row.booking_source)}</span>`;
            if (row.booking_source === 'Travel Agency') {
                let displayAgencyName = (row.agency_name && row.agency_name.trim() !== '') ? row.agency_name : 'Agency name not specified';
                sourceHtml += `<span class="block text-[10px] text-blue-600 dark:text-blue-400 font-bold mt-0.5">(${escapeHtml(displayAgencyName)})</span>`;
            }

            // Generate Action Buttons based on state
            let actionBtnsHtml = `<div class="flex items-center justify-center gap-1.5">`;
            
            if (row.is_followed_up === 0) {
                actionBtnsHtml += `<button onclick="markFollowedUp(${row.id})" class="bg-emerald-50 dark:bg-emerald-900/30 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 font-bold px-2.5 py-1.5 rounded-lg transition-colors text-xs" title="Mark as Followed Up"><i class="fa-solid fa-check"></i></button>`;
            }
            
            actionBtnsHtml += `
                <button onclick="rescheduleBooking(${row.id}, '${row.check_in}', '${row.check_out}')" class="bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-900/60 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800 font-bold px-2.5 py-1.5 rounded-lg transition-colors text-xs" title="Reschedule Dates"><i class="fa-solid fa-calendar-day"></i></button>
                <button onclick="cancelReservation(${row.id})" class="bg-rose-50 dark:bg-rose-900/30 hover:bg-rose-100 dark:hover:bg-rose-900/60 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800 font-bold px-2.5 py-1.5 rounded-lg transition-colors text-xs" title="Cancel Booking"><i class="fa-solid fa-ban"></i></button>
            </div>`;

            const tr = document.createElement('tr');
            tr.className = "hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors bg-white dark:bg-slate-900 border-b border-slate-100 dark:border-slate-700/50";
            
            tr.innerHTML = `
                <td class="p-4"><span class="font-bold text-slate-900 dark:text-white">#${row.id}</span></td>
                <td class="p-4 font-bold text-slate-800 dark:text-slate-200">${escapeHtml(row.guest_name)}<span class="block text-[10px] text-slate-400 dark:text-slate-500 font-bold uppercase mt-0.5">${escapeHtml(row.phone)}</span></td>
                <td class="p-4 text-slate-500 dark:text-slate-400">${sourceHtml}</td>
                <td class="p-4 text-center font-semibold text-slate-500 dark:text-slate-400">${escapeHtml(row.recorded_by)}</td>
                <td class="p-4 text-center font-bold text-slate-700 dark:text-slate-300">${row.check_in}</td>
                <td class="p-4 text-center">
                    <span class="px-2.5 py-1 rounded-lg border text-[10px] font-bold uppercase tracking-wider inline-block ${badgeClass}">${row.urgency}</span>
                    ${followedUpBadge}
                </td>
                <td class="p-4 text-right font-black text-rose-600 dark:text-rose-400">${row.currency} ${row.balance.toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                <td class="p-4 text-center whitespace-nowrap">${actionBtnsHtml}</td>
            `;
            tbody.appendChild(tr);
        }
    });

    document.getElementById('records-counter-badge').innerText = `${visibleCount} Alerts Found`;

    if (visibleCount === 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="p-8 text-center text-slate-400 dark:text-slate-500 italic">No pending follow-ups or alerts found.</td></tr>`;
    }
}

// ACTION: MARK FOLLOWED UP
function markFollowedUp(id) {
    const formData = new FormData();
    formData.append('action', 'mark_followed_up');
    formData.append('id', id);
    
    fetch('api/notifications_api.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            fetchNotifications(); 
        } else {
            Swal.fire({ icon: 'error', title: 'Action Failed', text: data.message });
        }
    });
}

// ACTION: RESCHEDULE BOOKING
function rescheduleBooking(id, currentIn, currentOut) {
    Swal.fire({
        title: 'Reschedule Reservation',
        html: `
            <div class="space-y-4 text-left p-2">
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">New Check-In Date</label>
                    <input type="date" id="swal-checkin" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white rounded-lg p-2.5 outline-none focus:border-blue-500" value="${currentIn}">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">New Check-Out Date</label>
                    <input type="date" id="swal-checkout" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white rounded-lg p-2.5 outline-none focus:border-blue-500" value="${currentOut}">
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Save New Dates',
        confirmButtonColor: '#046a38',
        cancelButtonColor: '#64748b',
        background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#ffffff',
        color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#0f172a',
        preConfirm: () => {
            const ci = document.getElementById('swal-checkin').value;
            const co = document.getElementById('swal-checkout').value;
            if(!ci || !co || ci >= co) {
                Swal.showValidationMessage('Valid check-in and check-out dates are required, and check-out must be after check-in.');
                return false;
            }
            return { checkIn: ci, checkOut: co };
        }
    }).then(res => {
        if(res.isConfirmed) {
            const fd = new FormData();
            fd.append('action', 'reschedule_reservation');
            fd.append('id', id);
            fd.append('check_in', res.value.checkIn);
            fd.append('check_out', res.value.checkOut);
            
            fetch('api/notifications_api.php', {method: 'POST', body: fd})
            .then(r => r.json())
            .then(data => {
                if(data.success) {
                    Swal.fire({ icon:'success', title:'Rescheduled!', text: 'Client marked as Followed Up.', timer:1500, showConfirmButton:false });
                    fetchNotifications();
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
        }
    });
}

// ACTION: CANCEL RESERVATION
function cancelReservation(id) {
    Swal.fire({
        title: 'Cancel this Reservation?',
        text: "This will remove the hold from the calendar. This action cannot be undone.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, Cancel Booking',
        background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#ffffff',
        color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#0f172a',
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'cancel_reservation');
            formData.append('id', id);
            
            fetch('api/notifications_api.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if(data.success) { 
                    Swal.fire({ icon: 'success', title: 'Cancelled!', text: 'Reservation has been soft-deleted.', timer: 1200, showConfirmButton: false }); 
                    fetchNotifications(); 
                    
                    if (typeof updateGlobalNotificationBadge === 'function') {
                        updateGlobalNotificationBadge();
                    }
                } else {
                    Swal.fire({ icon: 'error', title: 'Access Denied', text: data.message });
                }
            });
        }
    });
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}