// js/dashboard.js

const _d = new Date();
const SERVER_TODAY = _d.getFullYear() + "-" + String(_d.getMonth() + 1).padStart(2, '0') + "-" + String(_d.getDate()).padStart(2, '0');

let CAMP_INVENTORY_METRICS = {}; 
let reservationsDatabase = [];
let activeSelectedSourceMode = 'direct';

const primaryThemeColor = document.body.dataset.primaryColor || '#046a38';

// Initialize Top-Right Toast Notifications
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3500,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
});

document.addEventListener("DOMContentLoaded", () => {
    const urlParams = new URLSearchParams(window.location.search);
    let targetDate = urlParams.get('date') || SERVER_TODAY;
    document.getElementById('global-manifest-datepicker').value = targetDate;
    
    updateSearchPlaceholder();
    loadDashboardData(targetDate);
    
    setInterval(() => {
        const isResModalOpen = !document.getElementById('reservation-modal-backdrop').classList.contains('hidden');
        const isPayModalOpen = !document.getElementById('payment-modal').classList.contains('hidden');
        
        if (!isResModalOpen && !isPayModalOpen) {
            loadDashboardData(document.getElementById('global-manifest-datepicker').value);
        }
    }, 15000);
});

function updateSearchPlaceholder() {
    const cat = document.getElementById('filter-category-select').value;
    const input = document.getElementById('filter-search-value');
    
    if (cat === 'pipeline') {
        input.placeholder = "Search by Agency Name (e.g., Perfect Safaris)...";
    } else if (cat === 'client') {
        input.placeholder = "Search by Guest Name...";
    } else if (cat === 'officer') {
        input.placeholder = "Search by Booking Officer...";
    } else {
        input.placeholder = "Search by Client Name, Travel Agency, or Booking Officer...";
    }
    applyLedgerLiveSearchFilters();
}

function loadDashboardData(targetDate) {
    fetch('api/dashboard_api.php?action=fetch_reservations')
        .then(res => res.json())
        .then(response => {
            if(response.success) {
                CAMP_INVENTORY_METRICS = {};
                response.rooms.forEach(rm => {
                    let keyName = rm.type.split(' ')[0].toLowerCase();
                    let iconClass = 'fa-door-closed text-slate-500'; 
                    let bgAccent = 'bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300';
                    
                    if (keyName === 'single') { iconClass = 'fa-user text-emerald-600 dark:text-emerald-400'; bgAccent = 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400'; }
                    else if (keyName === 'double') { iconClass = 'fa-user-friends text-blue-600 dark:text-blue-400'; bgAccent = 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400'; }
                    else if (keyName === 'triple') { iconClass = 'fa-users text-amber-500 dark:text-amber-400'; bgAccent = 'bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400'; }
                    else if (keyName === 'family') { iconClass = 'fa-house-user text-purple-600 dark:text-purple-400'; bgAccent = 'bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400'; }
                    
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
        document.getElementById('payment-modal').classList.add('flex');
    }
}

function closePaymentModal() { 
    document.getElementById('payment-modal').classList.add('hidden'); 
    document.getElementById('payment-modal').classList.remove('flex'); 
}

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
    
    // Soft validation Toast
    if (amt <= 0) {
        Toast.fire({ icon: 'warning', title: 'Invalid Amount. Payment must be greater than zero.' });
        return;
    }

    const formData = new FormData(e.target);
    formData.append('ajax_record_payment', '1');

    fetch('api/dashboard_api.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            closePaymentModal();
            Toast.fire({ icon: 'success', title: 'Payment successfully recorded.' });
            loadDashboardData(document.getElementById('global-manifest-datepicker').value); 
        } else {
            // Critical Backend Error gets the full SweetAlert
            Swal.fire({ icon: 'error', title: 'Payment Failed', text: data.message });
        }
    })
    .catch(() => Swal.fire({ icon: 'error', title: 'Network Error', text: 'Could not communicate with the database.' }));
}

function openReservationModal(editId = null) {
    document.getElementById('dynamic-allocation-rows-container').innerHTML = '';
    
    const agName = document.getElementById('input-agency-name');
    const bkOff = document.getElementById('input-booking-officer');
    agName.className = "w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg p-2 text-xs outline-none focus:ring-2 transition-colors duration-300";
    bkOff.className = "w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg p-2 text-xs outline-none focus:ring-2 transition-colors duration-300";
    
    agName.style.setProperty('--tw-ring-color', primaryThemeColor);
    bkOff.style.setProperty('--tw-ring-color', primaryThemeColor);

    if (editId) {
        document.getElementById('modal-terminal-title').innerHTML = `<i class="fa-solid fa-pen-to-square theme-text"></i> Edit Reservation Terminal`;
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
        document.getElementById('modal-terminal-title').innerHTML = `<i class="fa-solid fa-calendar-plus theme-text"></i> New Reservation Terminal`;
        document.getElementById('editing-target-reservation-id').value = "";
        document.getElementById('input-agency-name').value = '';
        document.getElementById('input-booking-officer').value = '';
        setBookingSource('direct');
        addNewAllocationRowRow();
    }

    const modal = document.getElementById('reservation-modal-backdrop');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeReservationModal() { 
    const modal = document.getElementById('reservation-modal-backdrop');
    modal.classList.add('hidden'); 
    modal.classList.remove('flex'); 
}

function setBookingSource(mode) {
    activeSelectedSourceMode = mode;
    const directBtn = document.getElementById('src-direct-btn');
    const agencyBtn = document.getElementById('src-agency-btn');
    const wrapper = document.getElementById('agency-fields-wrapper');
    const agName = document.getElementById('input-agency-name');
    const bkOff = document.getElementById('input-booking-officer');

    const activeClass = "py-2.5 px-4 rounded-xl border-2 font-semibold text-xs flex items-center justify-center gap-2 transition-all shadow-sm";
    const inactiveClass = "py-2.5 px-4 rounded-xl border-2 font-semibold text-xs flex items-center justify-center gap-2 transition-all border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800";

    if(mode === 'direct') {
        directBtn.className = activeClass;
        directBtn.style.borderColor = primaryThemeColor;
        directBtn.style.color = primaryThemeColor;
        directBtn.style.backgroundColor = primaryThemeColor + "1A";
        
        agencyBtn.className = inactiveClass;
        agencyBtn.style.borderColor = "";
        agencyBtn.style.color = "";
        agencyBtn.style.backgroundColor = "";
        
        wrapper.classList.add('hidden'); wrapper.classList.remove('grid');
        agName.removeAttribute('required');
        bkOff.removeAttribute('required');
    } else {
        agencyBtn.className = activeClass;
        agencyBtn.style.borderColor = primaryThemeColor;
        agencyBtn.style.color = primaryThemeColor;
        agencyBtn.style.backgroundColor = primaryThemeColor + "1A";

        directBtn.className = inactiveClass;
        directBtn.style.borderColor = "";
        directBtn.style.color = "";
        directBtn.style.backgroundColor = "";
        
        wrapper.classList.remove('hidden'); wrapper.classList.add('grid');
        agName.setAttribute('required', 'required');
        bkOff.setAttribute('required', 'required');
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
        
        input.classList.remove('border-rose-500', 'dark:border-rose-500', 'focus:ring-rose-500', 'ring-1', 'ring-rose-500', 'border-emerald-500', 'dark:border-emerald-500', 'focus:ring-emerald-500', 'ring-emerald-500');
        input.classList.add('border-amber-300', 'dark:border-amber-700', 'focus:ring-amber-500');
    }
}

function addNewAllocationRowRow(data = null) {
    const container = document.getElementById('dynamic-allocation-rows-container');
    const targetRowId = 'row-' + Date.now() + '-' + Math.floor(Math.random() * 1000);
    const outerDiv = document.createElement('div');
    
    // Security validation to check if the Date Picker should be locked
    const isEditing = document.getElementById('editing-target-reservation-id').value !== "";
    let minDateAttr = '';
    
    // Check global permission constant injected by PHP
    if (!isEditing && !CAN_BACKDATE) {
        minDateAttr = `min="${SERVER_TODAY}"`;
    }
    
    let hasReqChecked = (data && data.specialRequests && data.specialRequests.trim() !== '') ? 'checked' : '';
    let reqHidden = hasReqChecked ? '' : 'hidden';
    let reqVal = hasReqChecked ? data.specialRequests : '';

    outerDiv.id = targetRowId;
    outerDiv.className = "p-3 bg-slate-50 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 space-y-2 relative animate-fadeIn transition-colors duration-300";
    
    outerDiv.innerHTML = `
        <button type="button" onclick="deleteAllocationRowNode('${targetRowId}')" class="absolute top-2 right-2 text-slate-400 dark:text-slate-500 hover:text-rose-500 dark:hover:text-rose-400 transition text-xs"><i class="fa-solid fa-trash"></i></button>
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-2">
            <div class="sm:col-span-12">
                <label class="block text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase mb-0.5">Guest Name *</label>
                <input type="text" placeholder="e.g. John Doe" class="alloc-client-names w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-lg p-1.5 text-xs outline-none text-slate-900 dark:text-white focus:ring-2 focus:border-transparent transition-all" style="--tw-ring-color: ${primaryThemeColor};" value="${data ? data.clientNames : ''}" required>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-1">
            <div>
                <label class="block text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase mb-0.5">Guest Type *</label>
                <select class="alloc-guest-type w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-lg p-1.5 text-xs outline-none text-slate-900 dark:text-white" required>
                    <option value="Resident" ${data && data.guestType === 'Resident' ? 'selected' : ''}>Resident (KES)</option>
                    <option value="Non Resident" ${data && data.guestType === 'Non Resident' ? 'selected' : ''}>Non Resident (USD)</option>
                </select>
            </div>
            <div>
                <label class="block text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase mb-0.5">Room Tier *</label>
                <select class="alloc-room-tier w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-lg p-1.5 text-xs outline-none text-slate-900 dark:text-white" required>
                    <option value="Deluxe Room" ${data && data.roomTier === 'Deluxe Room' ? 'selected' : ''}>Deluxe Room</option>
                    <option value="Superior Tent" ${!data || data.roomTier === 'Superior Tent' ? 'selected' : ''}>Superior Tent</option>
                </select>
            </div>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 mt-1 border-b border-slate-200 dark:border-slate-700 pb-2">
            <div class="sm:col-span-3">
                <label class="block text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase mb-0.5">Room Type *</label>
                <select class="alloc-room-type w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-lg p-1.5 text-xs outline-none text-slate-900 dark:text-white" required>
                    <option value="Single Room" ${data && data.roomType === 'single' ? 'selected' : ''}>Single Room</option>
                    <option value="Double Room" ${!data || data.roomType === 'double' ? 'selected' : ''}>Double Room</option>
                    <option value="Triple Room" ${data && data.roomType === 'triple' ? 'selected' : ''}>Triple Room</option>
                    <option value="Family Room" ${data && data.roomType === 'family' ? 'selected' : ''}>Family Room</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase mb-0.5">Rooms Needed</label>
                <input type="number" min="1" class="alloc-room-count w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-lg p-1.5 text-xs text-center text-slate-900 dark:text-white" value="${data ? data.roomCount : 1}" required>
            </div>
            <div class="sm:col-span-3">
                <label class="block text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase mb-0.5">Check-In *</label>
                <input type="date" class="alloc-checkin-date bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-lg p-1.5 text-xs w-full text-slate-900 dark:text-white" ${minDateAttr} value="${data ? data.checkIn : document.getElementById('global-manifest-datepicker').value}" required>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase mb-0.5">Nights</label>
                <input type="number" min="1" class="alloc-stay-nights w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-lg p-1.5 text-xs text-center text-slate-900 dark:text-white" value="${data ? data.nights : 1}" required>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-[9px] font-bold text-emerald-600 dark:text-emerald-400 uppercase mb-0.5" title="Per room, per night">Disc/Room</label>
                <input type="number" step="0.01" min="0" class="alloc-discount w-full bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 text-emerald-700 dark:text-emerald-400 font-bold rounded-lg p-1.5 text-xs text-center outline-none focus:ring-2 focus:ring-emerald-500" value="${data && data.discountPerRoom ? data.discountPerRoom : '0.00'}" required>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-4 gap-2 mt-1 bg-slate-100 dark:bg-slate-900 p-2 rounded-lg border border-slate-200 dark:border-slate-800">
            <div><label class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-0.5">Adults</label><input type="number" min="0" class="alloc-adults w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg p-1.5 text-xs text-center text-slate-900 dark:text-white" value="${data ? data.adults : 1}" required></div>
            <div><label class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-0.5">Children</label><input type="number" min="0" class="alloc-children w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg p-1.5 text-xs text-center text-slate-900 dark:text-white" value="${data ? data.children : 0}" required></div>
            <div><label class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-0.5" title="Rooms occupied by children only">Child Rooms</label><input type="number" min="0" class="alloc-children-rooms w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg p-1.5 text-xs text-center text-slate-900 dark:text-white" value="${data ? data.childrenRooms : 0}" required></div>
            
            <div class="flex items-end pb-1.5 gap-4">
                <label class="flex items-center gap-1.5 cursor-pointer pl-1">
                    <input type="checkbox" class="alloc-under-12 w-3 h-3 bg-slate-50 dark:bg-slate-800 rounded border-slate-300 dark:border-slate-600" style="accent-color: ${primaryThemeColor};" ${(!data || data.under12) ? 'checked' : ''}>
                    <span class="text-[9px] font-bold text-slate-600 dark:text-slate-400 uppercase">Under 12 Yrs?</span>
                </label>
                <label class="flex items-center gap-1.5 cursor-pointer">
                    <input type="checkbox" class="alloc-has-requests w-3 h-3 text-amber-500 bg-slate-50 dark:bg-slate-800 rounded border-slate-300 dark:border-slate-600 focus:ring-amber-500" onchange="toggleSpecialReq(this, '${targetRowId}')" ${hasReqChecked}>
                    <span class="text-[9px] font-bold text-amber-600 dark:text-amber-500 uppercase">Special Req?</span>
                </label>
            </div>
        </div>
        
        <div id="req-wrap-${targetRowId}" class="${reqHidden} mt-2 transition-all">
            <textarea class="alloc-special-requests w-full bg-amber-50 dark:bg-amber-900/20 border border-amber-300 dark:border-amber-700 rounded-lg p-2 text-xs outline-none focus:ring-2 focus:ring-amber-500 text-slate-700 dark:text-slate-300 placeholder-amber-700/40 dark:placeholder-amber-500/40 font-medium" rows="2" placeholder="Enter dietary requirements, accessibility needs, or other special requests here..." ${hasReqChecked ? 'required' : ''}>${reqVal}</textarea>
        </div>
    `;
    container.appendChild(outerDiv);
    container.scrollTop = container.scrollHeight;
}

function deleteAllocationRowNode(id) {
    const rows = document.querySelectorAll('#dynamic-allocation-rows-container > div');
    if(rows.length > 1) document.getElementById(id).remove();
}

function validateField(el) {
    if (!el || !el.hasAttribute('required')) return true;
    const val = el.value.trim();
    
    const neutralBorders = ['border-slate-200', 'dark:border-slate-600', 'border-slate-300', 'border-amber-300', 'dark:border-amber-700', 'dark:border-slate-700'];
    
    if (val === '') {
        el.classList.remove(...neutralBorders, 'border-emerald-500', 'dark:border-emerald-500', 'focus:ring-emerald-500', 'focus:ring-amber-500');
        el.classList.add('border-rose-500', 'dark:border-rose-500', 'focus:ring-rose-500', 'ring-1', 'ring-rose-500');
        
        el.addEventListener('input', function heal() {
            if(this.value.trim() !== '') {
                this.classList.remove('border-rose-500', 'dark:border-rose-500', 'focus:ring-rose-500', 'ring-rose-500');
                this.classList.add('border-emerald-500', 'dark:border-emerald-500', 'focus:ring-emerald-500', 'ring-1', 'ring-emerald-500');
            } else {
                this.classList.add('border-rose-500', 'dark:border-rose-500', 'focus:ring-rose-500', 'ring-1', 'ring-rose-500');
                this.classList.remove('border-emerald-500', 'dark:border-emerald-500', 'focus:ring-emerald-500', 'ring-emerald-500');
            }
        });
        return false;
    } else {
        el.classList.remove(...neutralBorders, 'border-rose-500', 'dark:border-rose-500', 'focus:ring-rose-500', 'ring-rose-500', 'focus:ring-amber-500');
        el.classList.add('border-emerald-500', 'dark:border-emerald-500', 'focus:ring-emerald-500', 'ring-1', 'ring-emerald-500');
        return true;
    }
}


function processAndValidateFormSubmission() {
    const nodes = document.querySelectorAll('#dynamic-allocation-rows-container > div');
    if(nodes.length === 0) return;

    let allocations = [];
    let hasEmptyFields = false;
    let hasInvalidDate = false;
    let redirectDate = '';
    const todayDate = new Date(SERVER_TODAY);
    const editId = document.getElementById('editing-target-reservation-id').value;

    if (activeSelectedSourceMode === 'agency') {
        if (!validateField(document.getElementById('input-agency-name'))) hasEmptyFields = true;
        if (!validateField(document.getElementById('input-booking-officer'))) hasEmptyFields = true;
    }

    nodes.forEach((node, index) => {
        const reqInputs = node.querySelectorAll('input[required], select[required], textarea[required]');
        reqInputs.forEach(inp => {
            if (!validateField(inp)) hasEmptyFields = true;
        });

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
        
        // Security validation flag logic
        if (!editId && new Date(checkIn) < todayDate) {
            if (!CAN_BACKDATE) {
                hasInvalidDate = true;
                const dateInp = node.querySelector('.alloc-checkin-date');
                dateInp.classList.remove('border-slate-200', 'dark:border-slate-600', 'border-emerald-500');
                dateInp.classList.add('border-rose-500', 'ring-1', 'ring-rose-500');
            }
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

    // Explicit Soft Alerts
    if (hasInvalidDate) {
        Toast.fire({ icon: 'warning', title: 'Cannot book past dates. Please select a valid date.' });
        return; 
    }

    if (hasEmptyFields) {
        Toast.fire({ icon: 'warning', title: 'Missing required fields. Check highlighted inputs.' });
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
            Toast.fire({ icon: 'success', title: 'Reservation successfully saved.' });
            loadDashboardData(redirectDate); 
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
                    Toast.fire({ icon: 'success', title: 'Reservation cancelled.' }); 
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
                    
                    const tr = document.createElement('tr'); 
                    tr.className = "hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition border-b border-slate-100 dark:border-slate-700/50 text-slate-800 dark:text-slate-300 bg-white dark:bg-slate-800";
                    
                    const sCell = alloc.roomType === 'single' ? `${alloc.roomCount}${isCheckoutNight ? '<sup class="ledger-out">out</sup>' : ''}` : '-'; 
                    const dCell = alloc.roomType === 'double' ? `${alloc.roomCount}${isCheckoutNight ? '<sup class="ledger-out">out</sup>' : ''}` : '-'; 
                    const tCell = alloc.roomType === 'triple' ? `${alloc.roomCount}${isCheckoutNight ? '<sup class="ledger-out">out</sup>' : ''}` : '-'; 
                    const fCell = alloc.roomType === 'family' ? `${alloc.roomCount}${isCheckoutNight ? '<sup class="ledger-out">out</sup>' : ''}` : '-';

                    if (res.status === 'Reserved') {
                        reservedCount++;
                        
                        let isPastDue = (alloc.checkIn < SERVER_TODAY);
                        let confirmBtnHtml = '';
                        
                        // Check if it's expired based on the backdate permission and historical flag
                        if (isPastDue && !CAN_BACKDATE && res.isHistorical === 0) {
                            confirmBtnHtml = `<span class="px-2 py-1 bg-rose-100 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-400 rounded font-bold text-[10px] uppercase mr-1" title="Check-in date has passed. Edit dates or cancel.">Expired Hold</span>`;
                        } else {
                            confirmBtnHtml = `<button onclick="openPaymentModalFromId('${res.id}')" class="px-2 py-1 bg-slate-100 dark:bg-slate-700 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 rounded font-bold text-[10px] transition" title="Submit Payment to Confirm">Confirm</button>`;
                        }

                        tr.innerHTML = `
                            <td class="p-4 font-bold text-slate-900 dark:text-white">${res.agentName} <span class="block text-[11px] text-blue-600 dark:text-blue-400 font-medium mt-0.5">(${res.bookingOfficer})</span></td>
                            <td class="p-4 font-bold text-slate-700 dark:text-slate-300">${alloc.clientNames}${requestBadge}</td>
                            <td class="p-3 text-center font-bold">${sCell}</td><td class="p-3 text-center font-bold">${dCell}</td><td class="p-3 text-center font-bold">${tCell}</td><td class="p-3 text-center font-bold">${fCell}</td>
                            <td class="p-3 text-center"><span class="bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 px-2 py-0.5 font-bold rounded text-[10px] whitespace-nowrap">${alloc.roomCount * (CAMP_INVENTORY_METRICS[alloc.roomType] ? CAMP_INVENTORY_METRICS[alloc.roomType].beds : 1)} Beds</span></td>
                            <td class="p-4 text-center text-slate-500 dark:text-slate-400 font-semibold">${res.bookingOfficer}</td>
                            <td class="p-4 text-center text-slate-500 dark:text-slate-400 font-semibold">${res.internalOfficer || 'Admin'}</td>
                            <td class="p-4 text-right whitespace-nowrap no-print-actions">
                                <div class="flex items-center justify-end gap-1.5">
                                    ${confirmBtnHtml}
                                    <button onclick="openReservationModal('${res.id}')" class="px-2 py-1 bg-slate-100 dark:bg-slate-700 hover:bg-emerald-50 dark:hover:bg-emerald-900/40 border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400 rounded font-bold text-[10px] transition" title="Edit Reservation"><i class="fa-solid fa-pen"></i></button>
                                    <button onclick="deleteReservationRecord('${res.id}')" class="px-2 py-1 bg-slate-100 dark:bg-slate-700 hover:bg-rose-50 dark:hover:bg-rose-900/40 border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:text-rose-600 dark:hover:text-rose-400 rounded font-bold text-[10px] transition" title="Cancel Booking"><i class="fa-solid fa-trash"></i></button>
                                </div>
                            </td>`;
                        rBody.appendChild(tr);
                    } else {
                        bookedCount++;
                        tr.innerHTML = `
                            <td class="p-4 font-bold text-slate-900 dark:text-white">${res.agentName} <span class="block text-[11px] text-blue-600 dark:text-blue-400 font-medium mt-0.5">(${res.bookingOfficer})</span></td>
                            <td class="p-4 font-bold text-slate-700 dark:text-slate-300">${alloc.clientNames}${requestBadge}</td>
                            <td class="p-3 text-center font-bold">${sCell}</td><td class="p-3 text-center font-bold">${dCell}</td><td class="p-3 text-center font-bold">${tCell}</td><td class="p-3 text-center font-bold">${fCell}</td>
                            <td class="p-3 text-center"><span class="bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 px-2 py-0.5 font-bold rounded text-[10px] whitespace-nowrap border border-emerald-200 dark:border-emerald-800">${alloc.roomCount * (CAMP_INVENTORY_METRICS[alloc.roomType] ? CAMP_INVENTORY_METRICS[alloc.roomType].beds : 1)} Beds</span></td>
                            <td class="p-4 text-slate-500 dark:text-slate-400 font-medium text-center">${res.bookingOfficer}</td>
                            <td class="p-4 text-center text-slate-500 dark:text-slate-400 font-semibold">${res.internalOfficer || 'Admin'}</td>
                            <td class="p-4 text-center font-bold text-emerald-700 dark:text-emerald-500"><i class="fa-solid fa-receipt mr-1"></i>#${res.receiptNo || 'N/A'}</td>`;
                        cBody.appendChild(tr);
                    }
                }
            }
        });
    });
    
    document.getElementById('reserved-counter-badge').innerText = `${reservedCount} Holds`; 
    document.getElementById('booked-counter-badge').innerText = `${bookedCount} Confirmed`; 
    document.getElementById('meta-checkouts-count').innerText = totalCheckoutsTodayCalculated;
    
    if(!rBody.children.length) rBody.innerHTML = `<tr><td colspan="10" class="p-6 text-center text-slate-400 dark:text-slate-500 italic">No temporary room holds pending today.</td></tr>`;
    if(!cBody.children.length) cBody.innerHTML = `<tr><td colspan="10" class="p-6 text-center text-slate-400 dark:text-slate-500 italic">No checked-in groups confirmed for this calendar run.</td></tr>`;
    
    renderDynamicUIStatusIndicators(runningDayCounts);
}

function renderDynamicUIStatusIndicators(metrics) {
    document.getElementById('meta-beds-used').innerText = metrics.globalBeds; 
    document.getElementById('meta-rooms-used').innerText = metrics.globalRooms;
    
    const grid = document.getElementById('room-metrics-grid'); 
    grid.innerHTML = '';
    
    for(let key in CAMP_INVENTORY_METRICS) {
        const used = metrics[key] || 0; 
        const max = CAMP_INVENTORY_METRICS[key].maxRooms; 
        const ratio = used / max; 
        const roomInfo = CAMP_INVENTORY_METRICS[key];
        
        let trackingColorClass = "bg-emerald-600 dark:bg-emerald-500"; 
        if(ratio >= 0.9) trackingColorClass = "bg-rose-600 dark:bg-rose-500"; 
        else if(ratio >= 0.6) trackingColorClass = "bg-amber-500 dark:bg-amber-400";
        
        grid.innerHTML += `<div class="bg-white dark:bg-slate-800 p-4 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 flex items-center gap-4 transition-colors duration-300">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm shrink-0 ${roomInfo.bg}">
                <i class="fa-solid ${roomInfo.icon}"></i>
            </div>
            <div class="space-y-0.5 flex-1">
                <span class="block text-[9px] uppercase tracking-wider font-bold text-slate-400 dark:text-slate-500">${roomInfo.name}s</span>
                <div class="flex items-baseline gap-1 leading-none">
                    <span class="text-lg font-black text-slate-900 dark:text-white">${used}</span>
                    <span class="text-slate-400 dark:text-slate-500 text-[10px] font-normal">/ ${max} rms</span>
                </div>
                <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-1 mt-1 overflow-hidden">
                    <div class="${trackingColorClass} h-1 rounded-full transition-all duration-500" style="width: ${Math.min(100, (used/max)*100)}%"></div>
                </div>
            </div>
        </div>`;
    }
}