// js/dashboard.js

// Generate today's date perfectly formatted for HTML inputs
const _d = new Date();
const SERVER_TODAY = _d.getFullYear() + "-" + String(_d.getMonth() + 1).padStart(2, '0') + "-" + String(_d.getDate()).padStart(2, '0');

// Starts empty. Populated dynamically from database via API
let CAMP_INVENTORY_METRICS = {}; 

let reservationsDatabase = [];
let activeSelectedSourceMode = 'direct';

// Load data entirely via API on Page Load
document.addEventListener("DOMContentLoaded", () => {
    const urlParams = new URLSearchParams(window.location.search);
    let targetDate = urlParams.get('date') || SERVER_TODAY;
    document.getElementById('global-manifest-datepicker').value = targetDate;
    
    loadDashboardData(targetDate);
    
    // =======================================================
    // REAL-TIME BACKGROUND POLLING ENGINE (Every 15 seconds)
    // =======================================================
    setInterval(() => {
        // Prevent background refresh from disrupting a user typing in a modal
        const isResModalOpen = !document.getElementById('reservation-modal-backdrop').classList.contains('hidden');
        const isPayModalOpen = !document.getElementById('payment-modal').classList.contains('hidden');
        
        if (!isResModalOpen && !isPayModalOpen) {
            loadDashboardData(document.getElementById('global-manifest-datepicker').value);
        }
    }, 15000);
});

// Fetch Data from the new API Endpoint
function loadDashboardData(targetDate) {
    fetch('api/dashboard_api.php?action=fetch_reservations')
        .then(res => res.json())
        .then(response => {
            if(response.success) {
                
                // Dynamically build room metrics from the database definition
                CAMP_INVENTORY_METRICS = {};
                response.rooms.forEach(rm => {
                    let keyName = rm.type.split(' ')[0].toLowerCase();
                    let iconClass = 'fa-door-closed text-slate-500'; 
                    let bgAccent = 'bg-slate-50 text-slate-600';
                    
                    if (keyName === 'single') { iconClass = 'fa-user text-emerald-600'; bgAccent = 'bg-emerald-50 text-emerald-600'; }
                    else if (keyName === 'double') { iconClass = 'fa-user-friends text-blue-600'; bgAccent = 'bg-blue-50 text-blue-600'; }
                    else if (keyName === 'triple') { iconClass = 'fa-users text-amber-500'; bgAccent = 'bg-amber-50 text-amber-600'; }
                    else if (keyName === 'family') { iconClass = 'fa-house-user text-purple-600'; bgAccent = 'bg-purple-50 text-purple-600'; }
                    
                    CAMP_INVENTORY_METRICS[keyName] = {
                        name: rm.type,
                        maxRooms: parseInt(rm.total_inventory),
                        beds: parseInt(rm.max_guests_per_room),
                        maxOccupantsPerRoom: parseInt(rm.max_guests_per_room),
                        icon: iconClass,
                        bg: bgAccent
                    };
                });

                reservationsDatabase = response.data;
                syncSelectedManifestToDate(targetDate);
            } else {
                console.error('Failed to load reservations:', response.message);
            }
        })
        .catch(err => console.error("API Error: ", err));
}

function navigateDaysOffset(days) {
    const datepicker = document.getElementById('global-manifest-datepicker');
    let current = new Date(datepicker.value);
    current.setDate(current.getDate() + days);
    const nextDateStr = current.toISOString().split('T')[0];
    datepicker.value = nextDateStr;
    syncSelectedManifestToDate(nextDateStr);
}

// ==========================================
// PAYMENT POPUP HANDLERS
// ==========================================
function openPaymentModalFromId(id) {
    const res = reservationsDatabase.find(r => r.id === String(id));
    if(res) {
        document.getElementById('modal-booking-id').value = res.id;
        document.getElementById('modal-guest-name').innerText = res.guest_name;
        
        document.getElementById('modal-display-adult-portion').innerText = res.actualCurrency + ' ' + res.pricingObj.adult_total.toLocaleString(undefined, {minimumFractionDigits: 2});
        document.getElementById('modal-display-child-portion').innerText = res.actualCurrency + ' ' + res.pricingObj.child_total.toLocaleString(undefined, {minimumFractionDigits: 2});
        
        const discWrap = document.getElementById('modal-discount-wrapper');
        if(res.discount > 0) {
            discWrap.classList.remove('hidden');
            document.getElementById('modal-display-discount').innerText = "- " + res.actualCurrency + ' ' + res.discount.toLocaleString(undefined, {minimumFractionDigits: 2});
        } else {
            discWrap.classList.add('hidden');
        }
        
        document.getElementById('modal-display-total').innerText = res.actualCurrency + ' ' + res.totalAmount.toLocaleString(undefined, {minimumFractionDigits: 2});
        document.getElementById('modal-display-balance').innerText = res.actualCurrency + ' ' + res.balance.toLocaleString(undefined, {minimumFractionDigits: 2});
        
        document.getElementById('input-amount-paid').max = res.balance;
        document.getElementById('input-amount-paid').value = res.balance;
        document.getElementById('input-currency').value = res.actualCurrency;
        
        document.getElementById('payment-modal').classList.remove('hidden');
    }
}

function closePaymentModal() { document.getElementById('payment-modal').classList.add('hidden'); }

function toggleReferenceField() {
    const method = document.getElementById('input-payment-method').value;
    const refWrapper = document.getElementById('reference-wrapper');
    const refInput = document.getElementById('input-reference-no');
    if (method === 'M-Pesa' || method === 'Bank Transfer') {
        refWrapper.style.display = 'block'; refInput.required = true;
        document.getElementById('label-ref').innerText = method + ' Reference *';
    } else {
        refWrapper.style.display = 'block'; refInput.required = false;
        document.getElementById('label-ref').innerText = 'Transaction Reference (Optional)';
    }
}

function submitPaymentViaAjax(e) {
    e.preventDefault();
    const amt = parseFloat(document.getElementById('input-amount-paid').value);
    if (amt <= 0) {
        Swal.fire({ icon: 'error', title: 'Invalid Amount', text: 'Payment amount must be greater than zero.' });
        return;
    }

    const formData = new FormData(e.target);
    formData.append('ajax_record_payment', '1');

    fetch('api/dashboard_api.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            closePaymentModal();
            Swal.fire({ icon: 'success', title: 'Payment Recorded!', text: 'The booking has been successfully confirmed.', timer: 1500, showConfirmButton: false })
            .then(() => {
                loadDashboardData(document.getElementById('global-manifest-datepicker').value); 
            });
        } else {
            Swal.fire({ icon: 'error', title: 'Payment Failed', text: data.message });
        }
    })
    .catch(() => Swal.fire({ icon: 'error', title: 'Network Error', text: 'Could not communicate with the database.' }));
}

// ==========================================
// RESERVATION DATA ENTRY LOGIC
// ==========================================
function openReservationModal(editId = null) {
    document.getElementById('dynamic-allocation-rows-container').innerHTML = '';
    
    if (editId) {
        document.getElementById('modal-terminal-title').innerHTML = `<i class="fa-solid fa-pen-to-square text-[#046a38]"></i> Edit Reservation Terminal`;
        document.getElementById('editing-target-reservation-id').value = editId;
        
        let res = reservationsDatabase.find(r => r.id === String(editId));
        if (res) {
            if (res.sourceType === 'Travel Agency') {
                setBookingSource('agency');
                document.getElementById('input-agency-name').value = res.agentName;
                document.getElementById('input-booking-officer').value = res.bookingOfficer;
            } else {
                setBookingSource('direct');
                document.getElementById('input-agency-name').value = '';
                document.getElementById('input-booking-officer').value = '';
            }

            res.allocations.forEach(alloc => {
                let keyName = alloc.roomType.split(' ')[0].toLowerCase();
                addNewAllocationRowRow({ 
                    clientNames: alloc.clientNames, 
                    guestType: alloc.guestType || 'Resident', 
                    roomTier: alloc.roomTier || 'Superior Tent', 
                    roomType: keyName, 
                    roomCount: alloc.roomCount, 
                    checkIn: alloc.checkIn, 
                    nights: alloc.nights, 
                    adults: alloc.adults, 
                    children: alloc.children, 
                    under12: alloc.under12, 
                    childrenRooms: alloc.childrenRooms, 
                    discountPerRoom: alloc.discountPerRoom,
                    specialRequests: alloc.specialRequests 
                });
            });
        }
    } else {
        document.getElementById('modal-terminal-title').innerHTML = `<i class="fa-solid fa-calendar-plus text-[#046a38]"></i> New Reservation Terminal`;
        document.getElementById('editing-target-reservation-id').value = "";
        document.getElementById('input-agency-name').value = '';
        document.getElementById('input-booking-officer').value = '';
        setBookingSource('direct');
        addNewAllocationRowRow();
    }

    document.getElementById('reservation-modal-backdrop').classList.remove('hidden');
}

function closeReservationModal() { document.getElementById('reservation-modal-backdrop').classList.add('hidden'); }

function setBookingSource(mode) {
    activeSelectedSourceMode = mode;
    const directBtn = document.getElementById('src-direct-btn');
    const agencyBtn = document.getElementById('src-agency-btn');
    const wrapper = document.getElementById('agency-fields-wrapper');

    if(mode === 'direct') {
        directBtn.className = "py-2.5 px-4 rounded-xl border-2 font-semibold text-xs flex items-center justify-center gap-2 transition-all border-[#046a38] bg-emerald-50/50 text-[#046a38]";
        agencyBtn.className = "py-2.5 px-4 rounded-xl border-2 font-semibold text-xs flex items-center justify-center gap-2 transition-all border-slate-200 text-slate-600 hover:bg-slate-50";
        wrapper.classList.add('hidden'); wrapper.classList.remove('grid');
    } else {
        agencyBtn.className = "py-2.5 px-4 rounded-xl border-2 font-semibold text-xs flex items-center justify-center gap-2 transition-all border-[#046a38] bg-emerald-50/50 text-[#046a38]";
        directBtn.className = "py-2.5 px-4 rounded-xl border-2 font-semibold text-xs flex items-center justify-center gap-2 transition-all border-slate-200 text-slate-600 hover:bg-slate-50";
        wrapper.classList.remove('hidden'); wrapper.classList.add('grid');
    }
}

window.toggleSpecialReq = function(checkbox, rowId) {
    const wrap = document.getElementById('req-wrap-' + rowId);
    const input = wrap.querySelector('textarea');
    if (checkbox.checked) {
        wrap.classList.remove('hidden');
        input.setAttribute('required', 'required');
    } else {
        wrap.classList.add('hidden');
        input.removeAttribute('required');
        input.value = ''; 
    }
}

function addNewAllocationRowRow(data = null) {
    const container = document.getElementById('dynamic-allocation-rows-container');
    const targetRowId = 'row-' + Date.now() + '-' + Math.floor(Math.random() * 1000);
    const outerDiv = document.createElement('div');
    
    const isEditing = document.getElementById('editing-target-reservation-id').value !== "";
    const minDateAttr = !isEditing ? `min="${SERVER_TODAY}"` : '';
    
    let hasReqChecked = (data && data.specialRequests && data.specialRequests.trim() !== '') ? 'checked' : '';
    let reqHidden = hasReqChecked ? '' : 'hidden';
    let reqVal = hasReqChecked ? data.specialRequests : '';

    outerDiv.id = targetRowId;
    outerDiv.className = "p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-2 relative animate-fadeIn";
    outerDiv.innerHTML = `
        <button type="button" onclick="deleteAllocationRowNode('${targetRowId}')" class="absolute top-2 right-2 text-slate-400 hover:text-rose-500 transition text-xs"><i class="fa-solid fa-trash"></i></button>
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-2">
            <div class="sm:col-span-12"><label class="block text-[9px] font-bold text-slate-400 uppercase mb-0.5">Guest Name *</label><input type="text" placeholder="e.g. John Doe" class="alloc-client-names w-full bg-white border border-slate-200 rounded-lg p-1.5 text-xs outline-none" value="${data ? data.clientNames : ''}" required></div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-1">
            <div><label class="block text-[9px] font-bold text-slate-400 uppercase mb-0.5">Guest Type *</label><select class="alloc-guest-type w-full bg-white border border-slate-200 rounded-lg p-1.5 text-xs outline-none" required><option value="Resident" ${data && data.guestType === 'Resident' ? 'selected' : ''}>Resident (Billed in KES)</option><option value="Non Resident" ${data && data.guestType === 'Non Resident' ? 'selected' : ''}>Non Resident (Billed in USD)</option></select></div>
            <div><label class="block text-[9px] font-bold text-slate-400 uppercase mb-0.5">Room Tier *</label><select class="alloc-room-tier w-full bg-white border border-slate-200 rounded-lg p-1.5 text-xs outline-none" required><option value="Deluxe Room" ${data && data.roomTier === 'Deluxe Room' ? 'selected' : ''}>Deluxe Room</option><option value="Superior Tent" ${!data || data.roomTier === 'Superior Tent' ? 'selected' : ''}>Superior Tent</option></select></div>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 mt-1 border-b border-slate-200 pb-2">
            <div class="sm:col-span-3"><label class="block text-[9px] font-bold text-slate-400 uppercase mb-0.5">Room Type *</label><select class="alloc-room-type w-full bg-white border border-slate-200 rounded-lg p-1.5 text-xs outline-none"><option value="Single Room" ${data && data.roomType === 'single' ? 'selected' : ''}>Single Room</option><option value="Double Room" ${!data || data.roomType === 'double' ? 'selected' : ''}>Double Room</option><option value="Triple Room" ${data && data.roomType === 'triple' ? 'selected' : ''}>Triple Room</option><option value="Family Room" ${data && data.roomType === 'family' ? 'selected' : ''}>Family Room</option></select></div>
            <div class="sm:col-span-2"><label class="block text-[9px] font-bold text-slate-400 uppercase mb-0.5">Rooms Needed</label><input type="number" min="1" class="alloc-room-count w-full bg-white border border-slate-200 rounded-lg p-1.5 text-xs text-center" value="${data ? data.roomCount : 1}" required></div>
            <div class="sm:col-span-3"><label class="block text-[9px] font-bold text-slate-400 uppercase mb-0.5">Check-In</label><input type="date" class="alloc-checkin-date bg-white border border-slate-200 rounded-lg p-1.5 text-xs w-full" ${minDateAttr} value="${data ? data.checkIn : document.getElementById('global-manifest-datepicker').value}"></div>
            <div class="sm:col-span-2"><label class="block text-[9px] font-bold text-slate-400 uppercase mb-0.5">Nights</label><input type="number" min="1" class="alloc-stay-nights w-full bg-white border border-slate-200 rounded-lg p-1.5 text-xs text-center" value="${data ? data.nights : 1}" required></div>
            <div class="sm:col-span-2"><label class="block text-[9px] font-bold text-emerald-600 uppercase mb-0.5" title="Per room, per night">Disc/Room</label><input type="number" step="0.01" min="0" class="alloc-discount w-full bg-emerald-50 border border-emerald-200 text-emerald-700 font-bold rounded-lg p-1.5 text-xs text-center outline-none focus:ring-2 focus:ring-emerald-500" value="${data && data.discountPerRoom ? data.discountPerRoom : '0.00'}" required></div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-4 gap-2 mt-1 bg-slate-100 p-2 rounded-lg">
            <div><label class="block text-[9px] font-bold text-slate-500 uppercase mb-0.5">Adults</label><input type="number" min="0" class="alloc-adults w-full bg-white border border-slate-300 rounded-lg p-1.5 text-xs text-center" value="${data ? data.adults : 1}" required></div>
            <div><label class="block text-[9px] font-bold text-slate-500 uppercase mb-0.5">Children</label><input type="number" min="0" class="alloc-children w-full bg-white border border-slate-300 rounded-lg p-1.5 text-xs text-center" value="${data ? data.children : 0}" required></div>
            <div><label class="block text-[9px] font-bold text-slate-500 uppercase mb-0.5" title="Rooms occupied by children only">Child Rooms</label><input type="number" min="0" class="alloc-children-rooms w-full bg-white border border-slate-300 rounded-lg p-1.5 text-xs text-center" value="${data ? data.childrenRooms : 0}" required></div>
            
            <div class="flex items-end pb-1.5 gap-4">
                <label class="flex items-center gap-1.5 cursor-pointer pl-1">
                    <input type="checkbox" class="alloc-under-12 w-3 h-3 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500" ${(!data || data.under12) ? 'checked' : ''}>
                    <span class="text-[9px] font-bold text-slate-600 uppercase">Under 12 Yrs?</span>
                </label>
                <label class="flex items-center gap-1.5 cursor-pointer">
                    <input type="checkbox" class="alloc-has-requests w-3 h-3 text-amber-500 rounded border-slate-300 focus:ring-amber-500" onchange="toggleSpecialReq(this, '${targetRowId}')" ${hasReqChecked}>
                    <span class="text-[9px] font-bold text-amber-600 uppercase">Special Req?</span>
                </label>
            </div>
        </div>
        
        <div id="req-wrap-${targetRowId}" class="${reqHidden} mt-2 transition-all">
            <textarea class="alloc-special-requests w-full bg-amber-50 border border-amber-200 rounded-lg p-2 text-xs outline-none focus:ring-2 focus:ring-amber-500 text-slate-700 placeholder-amber-700/40 font-medium" rows="2" placeholder="Enter dietary requirements, accessibility needs, or other special requests here..." ${hasReqChecked ? 'required' : ''}>${reqVal}</textarea>
        </div>
    `;
    container.appendChild(outerDiv);
    container.scrollTop = container.scrollHeight;
}

function deleteAllocationRowNode(id) {
    const rows = document.querySelectorAll('#dynamic-allocation-rows-container > div');
    if(rows.length > 1) document.getElementById(id).remove();
}

function processAndValidateFormSubmission() {
    const nodes = document.querySelectorAll('#dynamic-allocation-rows-container > div');
    if(nodes.length === 0) return;

    let allocations = [];
    let isValid = true;
    let redirectDate = '';
    const todayDate = new Date(SERVER_TODAY);
    const editId = document.getElementById('editing-target-reservation-id').value;

    nodes.forEach((node, index) => {
        const clientNames = node.querySelector('.alloc-client-names').value.trim();
        const guestType = node.querySelector('.alloc-guest-type').value;
        const roomTier = node.querySelector('.alloc-room-tier').value;
        const formattedRoomType = node.querySelector('.alloc-room-type').value;
        const roomCount = parseInt(node.querySelector('.alloc-room-count').value) || 1;
        const checkIn = node.querySelector('.alloc-checkin-date').value;
        const nights = parseInt(node.querySelector('.alloc-stay-nights').value) || 1;
        const numAdults = parseInt(node.querySelector('.alloc-adults').value) || 0;
        const numChildren = parseInt(node.querySelector('.alloc-children').value) || 0;
        const childRooms = parseInt(node.querySelector('.alloc-children-rooms').value) || 0;
        const under12 = node.querySelector('.alloc-under-12').checked ? 1 : 0;
        const discountVal = parseFloat(node.querySelector('.alloc-discount').value) || 0;
        
        const hasRequests = node.querySelector('.alloc-has-requests').checked;
        const specialRequests = hasRequests ? node.querySelector('.alloc-special-requests').value.trim() : '';
        
        if(!clientNames || !checkIn) isValid = false;
        
        if(hasRequests && specialRequests === '') {
            isValid = false;
        }
        
        if (!editId && new Date(checkIn) < todayDate) {
            Swal.fire({ icon: 'error', title: 'Invalid Check-in Date', text: 'You cannot book a room for a date in the past.' });
            isValid = false;
            return;
        }

        if (index === 0) redirectDate = checkIn;

        let checkOutDate = new Date(checkIn);
        checkOutDate.setDate(checkOutDate.getDate() + nights);
        const checkOutStr = checkOutDate.toISOString().split('T')[0];
        let keyName = formattedRoomType.split(' ')[0].toLowerCase();
        let guestsCount = Math.max((numAdults + numChildren), ((CAMP_INVENTORY_METRICS[keyName] ? CAMP_INVENTORY_METRICS[keyName].maxOccupantsPerRoom : 1) * roomCount));

        allocations.push({ 
            guest_name: clientNames, 
            guest_type: guestType, 
            room_tier: roomTier, 
            check_in: checkIn, 
            check_out: checkOutStr, 
            guests_count: guestsCount, 
            room_type: formattedRoomType, 
            rooms_count: roomCount, 
            number_of_adults: numAdults, 
            number_of_children: numChildren, 
            children_own_rooms: childRooms, 
            children_under_12: under12, 
            discount: discountVal,
            special_requests: specialRequests 
        });
    });

    if(!isValid) {
        Swal.fire({ icon: 'error', title: 'Missing Fields', text: 'Please fill in all required fields, including marked special requests.' });
        return; 
    }

    const bookingSource = activeSelectedSourceMode === 'agency' ? 'Travel Agency' : 'Direct Client';
    const bookingOfficer = activeSelectedSourceMode === 'agency' ? document.getElementById('input-booking-officer').value.trim() : 'Front Desk';
    const agencyName = activeSelectedSourceMode === 'agency' ? document.getElementById('input-agency-name').value.trim() : '';

    const formData = new FormData();
    if (editId) {
        formData.append('ajax_edit_reservation_batch', '1');
        formData.append('reservation_id', editId);
    } else {
        formData.append('ajax_add_reservation_batch', '1');
    }
    formData.append('booking_source', bookingSource);
    formData.append('agency_name', agencyName);
    formData.append('booking_officer', bookingOfficer);
    formData.append('allocations', JSON.stringify(allocations));

    fetch('api/dashboard_api.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            closeReservationModal();
            Swal.fire({ icon: 'success', title: 'Saved!', text: 'Reservation successfully saved to database.', timer: 1200, showConfirmButton: false }).then(() => { 
                loadDashboardData(redirectDate); 
            });
        } else {
            Swal.fire({ icon: 'error', title: 'Action Denied', text: data.message });
        }
    })
    .catch(() => Swal.fire({ icon: 'error', title: 'Network Error', text: 'Could not communicate with the database.' }));
}

function deleteReservationRecord(id) {
    Swal.fire({ title: 'Cancel Reservation?', text: "This will remove the booking from the calendar and mark it as cancelled.", icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6', confirmButtonText: 'Yes, cancel it!' }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData(); formData.append('ajax_delete_reservation', '1'); formData.append('id', id);
            
            fetch('api/dashboard_api.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if(data.success) { 
                    reservationsDatabase = reservationsDatabase.filter(r => r.id !== String(id)); 
                    Swal.fire({ icon: 'success', title: 'Cancelled!', text: 'Reservation has been soft-deleted.', timer: 1000, showConfirmButton: false }); 
                    syncSelectedManifestToDate(document.getElementById('global-manifest-datepicker').value); 
                }
            });
        }
    });
}

function applyLedgerLiveSearchFilters() { syncSelectedManifestToDate(document.getElementById('global-manifest-datepicker').value); }

function syncSelectedManifestToDate(targetDateStr) {
    const daysOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    document.getElementById('label-active-manifest-date').innerText = new Date(targetDateStr).toLocaleDateString('en-US', daysOptions);
    document.getElementById('global-manifest-datepicker').value = targetDateStr;
    const selectedCategory = document.getElementById('filter-category-select').value;
    const searchVal = document.getElementById('filter-search-value').value.toLowerCase().trim();
    const rBody = document.getElementById('manifest-records-table-body');
    const cBody = document.getElementById('confirmed-records-table-body');
    rBody.innerHTML = ''; cBody.innerHTML = '';
    
    // START METRICS AT 0 DYNAMICALLY based on CAMP_INVENTORY_METRICS
    let runningDayCounts = { globalBeds: 0, globalRooms: 0 };
    for (let k in CAMP_INVENTORY_METRICS) runningDayCounts[k] = 0;
    
    let totalCheckoutsTodayCalculated = 0; let reservedCount = 0; let bookedCount = 0;

    reservationsDatabase.forEach(res => {
        res.allocations.forEach(alloc => {
            let aStart = new Date(alloc.checkIn); let aEnd = new Date(alloc.checkIn); aEnd.setDate(aStart.getDate() + alloc.nights);
            let finalActiveNight = new Date(alloc.checkIn); finalActiveNight.setDate(finalActiveNight.getDate() + alloc.nights - 1);
            let isCheckoutNight = (targetDateStr === finalActiveNight.toISOString().split('T')[0]);

            if(new Date(targetDateStr) >= aStart && new Date(targetDateStr) < aEnd) {
                if (isCheckoutNight) totalCheckoutsTodayCalculated += alloc.guestsCount;
                let matchesOmni = false;
                if (searchVal === "") matchesOmni = true;
                else if (selectedCategory === "all") matchesOmni = res.agentName.toLowerCase().includes(searchVal) || alloc.clientNames.toLowerCase().includes(searchVal) || res.bookingOfficer.toLowerCase().includes(searchVal);
                else if (selectedCategory === "pipeline") matchesOmni = res.agentName.toLowerCase().includes(searchVal);
                else if (selectedCategory === "client") matchesOmni = alloc.clientNames.toLowerCase().includes(searchVal);
                else if (selectedCategory === "officer") matchesOmni = res.bookingOfficer.toLowerCase().includes(searchVal);

                if (matchesOmni) {
                    
                    if (runningDayCounts[alloc.roomType] !== undefined) runningDayCounts[alloc.roomType] += alloc.roomCount; 
                    runningDayCounts.globalRooms += alloc.roomCount; 
                    runningDayCounts.globalBeds += (alloc.roomCount * (CAMP_INVENTORY_METRICS[alloc.roomType] ? CAMP_INVENTORY_METRICS[alloc.roomType].beds : 1));
                    
                    let requestBadge = '';
                    if (alloc.specialRequests && alloc.specialRequests.trim() !== '') {
                        requestBadge = `<span class="inline-block ml-1 text-amber-500 cursor-help" title="Special Request: ${alloc.specialRequests.replace(/"/g, '&quot;')}"><i class="fa-solid fa-star"></i></span>`;
                    }
                    
                    const tr = document.createElement('tr'); tr.className = "hover:bg-slate-50/70 transition border-b border-slate-100 text-slate-800";
                    const sCell = alloc.roomType === 'single' ? `${alloc.roomCount}${isCheckoutNight ? '<sup class="ledger-out">out</sup>' : ''}` : '-'; const dCell = alloc.roomType === 'double' ? `${alloc.roomCount}${isCheckoutNight ? '<sup class="ledger-out">out</sup>' : ''}` : '-'; const tCell = alloc.roomType === 'triple' ? `${alloc.roomCount}${isCheckoutNight ? '<sup class="ledger-out">out</sup>' : ''}` : '-'; const fCell = alloc.roomType === 'family' ? `${alloc.roomCount}${isCheckoutNight ? '<sup class="ledger-out">out</sup>' : ''}` : '-';

                    if (res.status === 'Reserved') {
                        reservedCount++;
                        
                        let isPastDue = (alloc.checkIn < SERVER_TODAY);
                        let confirmBtnHtml = '';
                        
                        if (isPastDue) {
                            confirmBtnHtml = `<span class="px-2 py-1 bg-rose-100 border border-rose-200 text-rose-700 rounded font-bold text-[10px] uppercase mr-1" title="Check-in date has passed. Edit dates or cancel.">Expired Hold</span>`;
                        } else {
                            confirmBtnHtml = `<button onclick="openPaymentModalFromId('${res.id}')" class="px-2 py-1 bg-slate-100 hover:bg-indigo-50 border border-slate-200 text-slate-600 hover:text-indigo-600 rounded font-bold text-[10px] transition" title="Submit Payment to Confirm">Confirm</button>`;
                        }

                        tr.innerHTML = `
                            <td class="p-4 font-bold text-slate-900">${res.agentName} <span class="block text-[11px] text-blue-600 font-medium mt-0.5">(${res.bookingOfficer})</span></td>
                            <td class="p-4 font-bold text-slate-700">${alloc.clientNames}${requestBadge}</td><td class="p-3 text-center font-bold">${sCell}</td><td class="p-3 text-center font-bold">${dCell}</td><td class="p-3 text-center font-bold">${tCell}</td><td class="p-3 text-center font-bold">${fCell}</td><td class="p-3 text-center"><span class="bg-slate-100 text-slate-700 px-2 py-0.5 font-bold rounded text-[10px] whitespace-nowrap">${alloc.roomCount * (CAMP_INVENTORY_METRICS[alloc.roomType] ? CAMP_INVENTORY_METRICS[alloc.roomType].beds : 1)} Beds</span></td><td class="p-4 text-center text-slate-500 font-semibold">${res.bookingOfficer}</td><td class="p-4 text-center text-slate-500 font-semibold">${res.internalOfficer || 'Admin'}</td>
                            <td class="p-4 text-right whitespace-nowrap no-print-actions">
                                <div class="flex items-center justify-end gap-1.5">
                                    ${confirmBtnHtml}
                                    <button onclick="openReservationModal('${res.id}')" class="px-2 py-1 bg-slate-100 hover:bg-emerald-50 border border-slate-200 text-slate-600 hover:text-emerald-600 rounded font-bold text-[10px] transition" title="Edit Reservation"><i class="fa-solid fa-pen"></i></button>
                                    <button onclick="deleteReservationRecord('${res.id}')" class="px-2 py-1 bg-slate-100 hover:bg-rose-50 border border-slate-200 text-slate-600 hover:text-rose-600 rounded font-bold text-[10px] transition" title="Cancel Booking"><i class="fa-solid fa-trash"></i></button>
                                </div>
                            </td>`;
                        rBody.appendChild(tr);
                    } else {
                        bookedCount++;
                        tr.innerHTML = `
                            <td class="p-4 font-bold text-slate-900">${res.agentName} <span class="block text-[11px] text-blue-600 font-medium mt-0.5">(${res.bookingOfficer})</span></td>
                            <td class="p-4 font-bold text-slate-700">${alloc.clientNames}${requestBadge}</td><td class="p-3 text-center font-bold">${sCell}</td><td class="p-3 text-center font-bold">${dCell}</td><td class="p-3 text-center font-bold">${tCell}</td><td class="p-3 text-center font-bold">${fCell}</td><td class="p-3 text-center"><span class="bg-emerald-50 text-emerald-700 px-2 py-0.5 font-bold rounded text-[10px] whitespace-nowrap">${alloc.roomCount * (CAMP_INVENTORY_METRICS[alloc.roomType] ? CAMP_INVENTORY_METRICS[alloc.roomType].beds : 1)} Beds</span></td><td class="p-4 text-slate-500 font-medium text-center">${res.bookingOfficer}</td><td class="p-4 text-center text-slate-500 font-semibold">${res.internalOfficer || 'Admin'}</td><td class="p-4 text-center font-bold text-emerald-700"><i class="fa-solid fa-receipt mr-1"></i>#${res.receiptNo || 'N/A'}</td>`;
                        cBody.appendChild(tr);
                    }
                }
            }
        });
    });
    document.getElementById('reserved-counter-badge').innerText = `${reservedCount} Holds`; document.getElementById('booked-counter-badge').innerText = `${bookedCount} Confirmed`; document.getElementById('meta-checkouts-count').innerText = totalCheckoutsTodayCalculated;
    if(!rBody.children.length) rBody.innerHTML = `<tr><td colspan="10" class="p-6 text-center text-slate-400 italic">No temporary room holds pending today.</td></tr>`;
    if(!cBody.children.length) cBody.innerHTML = `<tr><td colspan="10" class="p-6 text-center text-slate-400 italic">No checked-in groups confirmed for this calendar run.</td></tr>`;
    renderDynamicUIStatusIndicators(runningDayCounts);
}

function renderDynamicUIStatusIndicators(metrics) {
    document.getElementById('meta-beds-used').innerText = metrics.globalBeds; document.getElementById('meta-rooms-used').innerText = metrics.globalRooms;
    const grid = document.getElementById('room-metrics-grid'); grid.innerHTML = '';
    for(let key in CAMP_INVENTORY_METRICS) {
        const used = metrics[key] || 0; const max = CAMP_INVENTORY_METRICS[key].maxRooms; const ratio = used / max; const roomInfo = CAMP_INVENTORY_METRICS[key];
        let trackingColorClass = "bg-emerald-600"; if(ratio >= 0.9) trackingColorClass = "bg-rose-600"; else if(ratio >= 0.6) trackingColorClass = "bg-amber-500";
        grid.innerHTML += `<div class="bg-white p-4 rounded-2xl custom-shadow border border-slate-100 flex items-center gap-4"><div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm shrink-0 ${roomInfo.bg}"><i class="fa-solid ${roomInfo.icon}"></i></div><div class="space-y-0.5 flex-1"><span class="block text-[9px] uppercase tracking-wider font-bold text-slate-400">${roomInfo.name}s</span><div class="flex items-baseline gap-1 leading-none"><span class="text-lg font-black text-slate-900">${used}</span><span class="text-slate-400 text-[10px] font-normal">/ ${max} rms</span></div><div class="w-full bg-slate-100 rounded-full h-1 mt-1"><div class="${trackingColorClass} h-1 rounded-full transition-all duration-500" style="width: ${Math.min(100, (used/max)*100)}%"></div></div></div></div>`;
    }
}