// js/calendar.js

let INVENTORY_LIMITS = { single: 0, double: 0, triple: 0, family: 0 }; 

let currentYear = new Date().getFullYear();
let currentMonth = new Date().getMonth(); 
let activeCalendarTabMode = 'all';
let selectedHighlightDate = null;
let globalReservationsDatabase = [];
let currentManifestDateStr = null;

// Dynamically fetch Theme Color
let primaryThemeColor = '#046a38';

document.addEventListener("DOMContentLoaded", () => {
    primaryThemeColor = document.body.dataset.primaryColor || '#046a38';
    loadCalendarData();

    // REAL-TIME BACKGROUND POLLING ENGINE
    setInterval(() => {
        // Pause updating if the popup is open to prevent visual jumps
        const modal = document.getElementById('manifest-modal');
        if (!modal || modal.classList.contains('hidden')) {
            loadCalendarData();
        }
    }, 15000);
});

function loadCalendarData() {
    fetch('api/calendar_api.php')
        .then(res => res.json())
        .then(response => {
            if(response.success) {
                response.rooms.forEach(rm => {
                    let keyName = rm.type.split(' ')[0].toLowerCase();
                    INVENTORY_LIMITS[keyName] = parseInt(rm.total_inventory);
                });

                globalReservationsDatabase = response.data;
                renderCalendarMatrixGrid();
            }
        })
        .catch(err => console.error("API Error: ", err));
}

function jumpToSelectedDate(dateVal) {
    const d = new Date(dateVal);
    currentYear = d.getFullYear();
    currentMonth = d.getMonth();
    selectedHighlightDate = dateVal; 
    renderCalendarMatrixGrid();
}

function changeMonth(direction) {
    currentMonth += direction;
    if (currentMonth < 0) { currentMonth = 11; currentYear--; }
    else if (currentMonth > 11) { currentMonth = 0; currentYear++; }
    renderCalendarMatrixGrid();
}

function switchCalendarViewTab(mode) {
    activeCalendarTabMode = mode;
    
    // Reset all tabs to neutral
    document.querySelectorAll('[id^="tab-"]').forEach(btn => {
        btn.className = "flex-1 min-w-[100px] text-center py-2 px-3 rounded-lg text-xs font-bold transition-all text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700";
        btn.style.backgroundColor = '';
        btn.style.color = '';
    });
    
    // Apply theme color to active tab dynamically
    const activeTab = document.getElementById('tab-' + mode);
    activeTab.className = "flex-1 min-w-[100px] text-center py-2 px-3 rounded-lg text-xs font-bold transition-all text-white shadow-sm";
    activeTab.style.backgroundColor = primaryThemeColor;
    
    renderCalendarMatrixGrid();
}

function compileDayOccupancyMap(dateStr) {
    let map = { single: 0, double: 0, triple: 0, family: 0, totalRooms: 0, confirmedRooms: 0, reservedRooms: 0, checkOutPax: 0 };
    let evalDate = new Date(dateStr);
    
    globalReservationsDatabase.forEach(res => {
        let start = new Date(res.checkIn);
        let end = new Date(res.checkIn); end.setDate(start.getDate() + res.nights);
        
        let isOccupying = (evalDate >= start && evalDate < end);
        let isCheckingOut = (evalDate.getTime() === end.getTime());
        
        if (isOccupying) {
            let roomsOccupied = 0;
            res.allocations.forEach(a => {
                if(map[a.roomType] !== undefined) map[a.roomType] += a.roomCount;
                roomsOccupied += a.roomCount;
                map.totalRooms += a.roomCount;
            });
            
            let isConfirmed = (res.paid > 0 || res.status === 'Booked' || res.paymentStatus === 'Paid in Full' || res.paymentStatus === 'Partially Paid');
            if (isConfirmed) {
                map.confirmedRooms += roomsOccupied;
            } else {
                map.reservedRooms += roomsOccupied;
            }
        }

        if (isCheckingOut) {
            map.checkOutPax += res.guestCount;
        }
    });
    return map;
}

function renderCalendarMatrixGrid() {
    const monthsList = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    document.getElementById('calendar-month-year-label').innerText = `${monthsList[currentMonth]} ${currentYear}`;
    const container = document.getElementById('calendar-days-container');
    container.innerHTML = '';
    
    const firstDayIndex = new Date(currentYear, currentMonth, 1).getDay();
    const totalDaysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();

    // Blank cells for alignment
    for(let i=0; i<firstDayIndex; i++) {
        let blankCell = document.createElement('div');
        blankCell.className = "bg-slate-50/50 dark:bg-slate-800/30 p-4 min-h-[90px] border-b border-r border-slate-100 dark:border-slate-700/50";
        container.appendChild(blankCell);
    }

    for(let day=1; day<=totalDaysInMonth; day++) {
        const m = String(currentMonth + 1).padStart(2, '0');
        const d = String(day).padStart(2, '0');
        const ISOStringDate = `${currentYear}-${m}-${d}`;
        
        let metrics = compileDayOccupancyMap(ISOStringDate);
        let dayCell = document.createElement('div');
        let isSelected = (ISOStringDate === selectedHighlightDate);
        
        let currentBooked = 0;
        let maxLimit = 0;

        if (activeCalendarTabMode === 'all') {
            currentBooked = metrics.totalRooms;
            maxLimit = INVENTORY_LIMITS.single + INVENTORY_LIMITS.double + INVENTORY_LIMITS.triple + INVENTORY_LIMITS.family;
        } else {
            currentBooked = metrics[activeCalendarTabMode];
            maxLimit = INVENTORY_LIMITS[activeCalendarTabMode];
        }

        let occupancyPercentage = maxLimit > 0 ? (currentBooked / maxLimit) * 100 : 0;
        
        // SEMANTIC COLORS: Preserved exactly as requested
        let cellColorClass = "bg-white dark:bg-slate-800"; 
        if (currentBooked === 0) {
            cellColorClass = "bg-white dark:bg-slate-800";
        } else if (occupancyPercentage >= 100) {
            cellColorClass = "bg-rose-200 dark:bg-rose-900/80"; 
        } else if (occupancyPercentage > 75) {
            cellColorClass = "bg-amber-200 dark:bg-amber-900/80"; 
        } else {
            cellColorClass = "bg-emerald-200 dark:bg-emerald-900/80"; 
        }

        // Apply Tailwind classes with inline CSS variable for the ring color
        dayCell.className = `p-3 min-h-[100px] flex flex-col border-b border-r border-slate-100 dark:border-slate-700/50 relative transition-colors ${cellColorClass} cursor-pointer hover:opacity-90 ${isSelected ? 'ring-2 ring-inset' : ''}`;
        
        if (isSelected) {
            dayCell.style.setProperty('--tw-ring-color', primaryThemeColor);
        }

        dayCell.setAttribute('onclick', `openDailyManifest('${ISOStringDate}')`);
        
        let innerHTML = `<div class="flex justify-between items-center"><span class="text-sm font-black text-slate-800 dark:text-slate-100">${day}</span>`;
        if(metrics.totalRooms > 0) {
            let badgeColor = "bg-emerald-500 text-white shadow-sm";
            if (occupancyPercentage >= 100) badgeColor = "bg-rose-500 text-white shadow-sm";
            else if (occupancyPercentage > 75) badgeColor = "bg-amber-500 text-white shadow-sm";

            innerHTML += `<span class="text-[9px] font-bold px-1.5 py-0.5 rounded uppercase tracking-wider ${badgeColor}">${Math.round(occupancyPercentage)}%</span>`;
        }
        innerHTML += `</div>`;
        
        if (activeCalendarTabMode === 'all') {
            innerHTML += `<div class="mt-auto grid grid-cols-2 gap-x-2 text-[11px] font-bold text-slate-700 dark:text-slate-300 pt-2 border-t border-slate-300/40 dark:border-slate-700/50">
                <div>S: ${metrics.single}/${INVENTORY_LIMITS.single}</div>
                <div>D: ${metrics.double}/${INVENTORY_LIMITS.double}</div>
                <div>T: ${metrics.triple}/${INVENTORY_LIMITS.triple}</div>
                <div>F: ${metrics.family}/${INVENTORY_LIMITS.family}</div>
            </div>`;
        } else {
            let textCol = occupancyPercentage >= 100 ? 'text-rose-700 dark:text-rose-400 font-black' : (occupancyPercentage > 75 ? 'text-amber-700 dark:text-amber-400 font-bold' : 'text-slate-800 dark:text-slate-200 font-bold');
            innerHTML += `<div class="mt-auto pt-2 text-center border-t border-slate-300/40 dark:border-slate-700/50"><span class="text-xs ${textCol}">${currentBooked} / ${maxLimit} Booked</span></div>`;
        }

        dayCell.innerHTML = innerHTML;
        container.appendChild(dayCell);
    }
}

// ==========================================
// DAILY MANIFEST POPUP LOGIC
// ==========================================

function openDailyManifest(dateStr) {
    currentManifestDateStr = dateStr;
    const displayOptions = { day: 'numeric', month: 'long', year: 'numeric' };
    document.getElementById('manifest-date-title').innerText = new Date(dateStr).toLocaleDateString('en-US', displayOptions);
    
    // Inject the Detailed Room Summary + The Semantic C/R/OUT Badges
    let metrics = compileDayOccupancyMap(dateStr);
    document.getElementById('manifest-room-summary').innerHTML = `
        <div class="flex items-center gap-3 bg-slate-50 dark:bg-slate-800/80 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wide">
            <div class="flex flex-col items-center"><span class="text-[9px] text-slate-400 mb-0.5">Single</span><span>${metrics.single}<span class="text-slate-400 mx-0.5">/</span>${INVENTORY_LIMITS.single}</span></div>
            <div class="w-px h-6 bg-slate-200 dark:bg-slate-600"></div>
            <div class="flex flex-col items-center"><span class="text-[9px] text-slate-400 mb-0.5">Double</span><span>${metrics.double}<span class="text-slate-400 mx-0.5">/</span>${INVENTORY_LIMITS.double}</span></div>
            <div class="w-px h-6 bg-slate-200 dark:bg-slate-600"></div>
            <div class="flex flex-col items-center"><span class="text-[9px] text-slate-400 mb-0.5">Triple</span><span>${metrics.triple}<span class="text-slate-400 mx-0.5">/</span>${INVENTORY_LIMITS.triple}</span></div>
            <div class="w-px h-6 bg-slate-200 dark:bg-slate-600"></div>
            <div class="flex flex-col items-center"><span class="text-[9px] text-slate-400 mb-0.5">Family</span><span>${metrics.family}<span class="text-slate-400 mx-0.5">/</span>${INVENTORY_LIMITS.family}</span></div>
            
            <div class="w-px h-6 bg-slate-200 dark:bg-slate-600 ml-2"></div>
            
            <div class="flex items-center gap-1.5 ml-1">
                <span class="bg-emerald-500 text-white text-[10px] font-bold px-2 py-1 rounded shadow-sm" title="Confirmed Rooms">C:${metrics.confirmedRooms}</span>
                <span class="bg-amber-500 text-white text-[10px] font-bold px-2 py-1 rounded shadow-sm" title="Reserved Rooms (Unpaid)">R:${metrics.reservedRooms}</span>
                <span class="bg-rose-500 text-white text-[10px] font-bold px-2 py-1 rounded shadow-sm" title="Check-out Guests">OUT:${metrics.checkOutPax}</span>
            </div>
        </div>
    `;

    document.getElementById('manifest-room-filter').value = 'all';
    document.getElementById('manifest-status-filter').value = 'all';
    
    renderManifestTable();
    
    const modal = document.getElementById('manifest-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeManifestModal() {
    const modal = document.getElementById('manifest-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function renderManifestTable() {
    const tbody = document.getElementById('manifestTableBody');
    tbody.innerHTML = '';
    
    if (!currentManifestDateStr) return;
    const evalDate = new Date(currentManifestDateStr);

    const roomFilter = document.getElementById('manifest-room-filter').value;
    const statusFilter = document.getElementById('manifest-status-filter').value;

    let hasVisibleRecords = false;

    globalReservationsDatabase.forEach(res => {
        let start = new Date(res.checkIn);
        let end = new Date(res.checkIn); end.setDate(start.getDate() + res.nights);
        
        let isOccupying = (evalDate >= start && evalDate < end);
        let isCheckingOut = (evalDate.getTime() === end.getTime());
        
        if (!isOccupying && !isCheckingOut) return;

        let isConfirmed = (res.paid > 0 || res.status === 'Booked' || res.paymentStatus === 'Paid in Full' || res.paymentStatus === 'Partially Paid');
        let mappedStatus = isCheckingOut ? 'checkout' : (isConfirmed ? 'confirmed' : 'reserved');
        
        if (statusFilter !== 'all' && statusFilter !== mappedStatus) return;
        if (roomFilter !== 'all' && res.roomTypeFull !== roomFilter) return;

        hasVisibleRecords = true;

        let sourceHtml = `<span class="font-bold text-slate-800 dark:text-slate-200">${escapeHtml(res.sourceType)}</span>`;
        if (res.sourceType === 'Travel Agency') {
            let displayAgencyName = (res.agentName && res.agentName.trim() !== '') ? res.agentName : 'Agency name not specified';
            sourceHtml += `<span class="block text-[10px] text-blue-600 dark:text-blue-400 font-bold mt-0.5">(${escapeHtml(displayAgencyName)})</span><span class="block text-[10px] text-slate-400 dark:text-slate-500">${escapeHtml(res.bookingOfficer)}</span>`;
        } else {
            sourceHtml += `<span class="block text-[10px] text-slate-400 dark:text-slate-500">${escapeHtml(res.bookingOfficer)}</span>`;
        }

        // SEMANTIC COLORS: Used strictly for operational awareness.
        let badgeClass = 'bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 border-rose-200 dark:border-rose-800';
        let badgeLabel = 'RESERVED';
        if (isCheckingOut) {
            badgeClass = 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 border-indigo-200 dark:border-indigo-800';
            badgeLabel = 'CHECKING OUT';
        } else if (isConfirmed) {
            badgeClass = 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800';
            badgeLabel = 'CONFIRMED';
        }

        const tr = document.createElement('tr');
        tr.className = "hover:bg-slate-50 dark:hover:bg-slate-800/50 transition bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300";

        tr.innerHTML = `
            <td class="p-4"><span class="font-bold text-slate-900 dark:text-white">BK-${res.id}</span></td>
            <td class="p-4">
                <span class="font-bold text-slate-900 dark:text-white block">${escapeHtml(res.guestName)}</span>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Guests: ${res.guestCount} pax</span>
            </td>
            <td class="p-4">${sourceHtml}</td>
            <td class="p-4 text-center font-bold">${escapeHtml(res.roomTypeFull)}</td>
            <td class="p-4 text-center font-black">${escapeHtml(String(res.allocations[0].roomCount))}</td>
            <td class="p-4 text-center text-[10px] whitespace-nowrap">
                <span class="block text-slate-500 dark:text-slate-400">In: <strong class="text-slate-800 dark:text-slate-200">${res.checkIn}</strong></span>
                <span class="block text-slate-500 dark:text-slate-400">Stay: ${res.nights} nights</span>
            </td>
            <td class="p-4 text-left whitespace-nowrap text-[10px]">
                <span class="block font-bold text-emerald-600 dark:text-emerald-400">Dep: ${res.currency} ${res.paid.toLocaleString(undefined, {minimumFractionDigits: 2})}</span>
                <span class="block font-bold text-rose-600 dark:text-rose-400">Bal: ${res.currency} ${res.balance.toLocaleString(undefined, {minimumFractionDigits: 2})}</span>
            </td>
            <td class="p-4 text-center">
                <span class="px-2 py-1 rounded border text-[9px] font-bold uppercase tracking-wider inline-block ${badgeClass}">${badgeLabel}</span>
            </td>
        `;
        tbody.appendChild(tr);
    });

    if (!hasVisibleRecords) {
        tbody.innerHTML = `<tr><td colspan="8" class="p-8 text-center text-slate-400 italic">No activity registered for this date based on selected filters.</td></tr>`;
    }
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}