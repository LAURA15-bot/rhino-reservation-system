// js/calendar.js

// Starts empty. Populated dynamically from database via API
let INVENTORY_LIMITS = { single: 0, double: 0, triple: 0, family: 0 }; 

let currentYear = new Date().getFullYear();
let currentMonth = new Date().getMonth(); 
let activeCalendarTabMode = 'all';
let selectedHighlightDate = null;
let globalReservationsDatabase = [];

document.addEventListener("DOMContentLoaded", () => {
    loadCalendarData();

    // =======================================================
    // REAL-TIME BACKGROUND POLLING ENGINE (Every 15 seconds)
    // =======================================================
    setInterval(() => {
        loadCalendarData();
    }, 15000);
});

function loadCalendarData() {
    fetch('api/calendar_api.php')
        .then(res => res.json())
        .then(response => {
            if(response.success) {
                // Dynamically update the limits from the database
                response.rooms.forEach(rm => {
                    let keyName = rm.type.split(' ')[0].toLowerCase();
                    INVENTORY_LIMITS[keyName] = parseInt(rm.total_inventory);
                });

                globalReservationsDatabase = response.data;
                renderCalendarMatrixGrid();
            } else {
                console.error("Failed to load calendar data");
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
    document.querySelectorAll('[id^="tab-"]').forEach(btn => btn.className = "flex-1 min-w-[100px] text-center py-2 px-3 rounded-lg text-xs font-bold transition-all text-slate-600 hover:bg-slate-50");
    document.getElementById('tab-' + mode).className = "flex-1 min-w-[100px] text-center py-2 px-3 rounded-lg text-xs font-bold transition-all bg-[#046a38] text-white shadow-sm";
    renderCalendarMatrixGrid();
}

function compileDayOccupancyMap(dateStr) {
    let map = { single: 0, double: 0, triple: 0, family: 0, totalRooms: 0, checkoutHolds: 0 };
    let evalDate = new Date(dateStr);
    
    globalReservationsDatabase.forEach(res => {
        let start = new Date(res.checkIn);
        let end = new Date(res.checkIn); end.setDate(start.getDate() + res.nights);
        let finalNight = new Date(res.checkIn); finalNight.setDate(start.getDate() + res.nights - 1);
        
        if (evalDate >= start && evalDate < end) {
            res.allocations.forEach(a => {
                if(map[a.roomType] !== undefined) {
                    map[a.roomType] += a.roomCount;
                }
                map.totalRooms += a.roomCount;
                if(evalDate.getTime() === finalNight.getTime()) map.checkoutHolds++;
            });
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

    for(let i=0; i<firstDayIndex; i++) {
        let blankCell = document.createElement('div');
        blankCell.className = "bg-slate-50/50 p-4 min-h-[90px] border-b border-r border-slate-100";
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
        let cellColorClass = "bg-white"; 

        if (currentBooked === 0) {
            cellColorClass = "bg-white";
        } else if (occupancyPercentage >= 100) {
            cellColorClass = "bg-rose-50"; 
        } else if (occupancyPercentage > 75) {
            cellColorClass = "bg-amber-50"; 
        } else {
            cellColorClass = "bg-emerald-50/40"; 
        }

        dayCell.className = `p-3 min-h-[105px] flex flex-col border-b border-r border-slate-100 relative transition-colors ${cellColorClass} ${isSelected ? 'ring-2 ring-[#046a38] ring-inset' : ''}`;
        
        let innerHTML = `<div class="flex justify-between items-center"><span class="text-sm font-black text-slate-800">${day}</span>`;
        if(metrics.totalRooms > 0) {
            let badgeColor = "bg-slate-200 text-slate-700";
            if (occupancyPercentage >= 100) badgeColor = "bg-rose-200 text-rose-800";
            else if (occupancyPercentage > 75) badgeColor = "bg-amber-200 text-amber-800";
            else badgeColor = "bg-emerald-200 text-emerald-800";

            innerHTML += `<span class="text-[9px] font-bold px-1.5 py-0.5 rounded uppercase tracking-wider ${badgeColor}">${Math.round(occupancyPercentage)}%</span>`;
        }
        innerHTML += `</div>`;
        
        if (activeCalendarTabMode === 'all') {
            innerHTML += `<div class="grid grid-cols-2 gap-x-2 text-[11px] font-bold text-slate-700 mt-2 pt-2 border-t border-slate-200/50">
                <div>S: ${metrics.single}/${INVENTORY_LIMITS.single}</div>
                <div>D: ${metrics.double}/${INVENTORY_LIMITS.double}</div>
                <div>T: ${metrics.triple}/${INVENTORY_LIMITS.triple}</div>
                <div>F: ${metrics.family}/${INVENTORY_LIMITS.family}</div>
            </div>`;
        } else {
            let textCol = occupancyPercentage >= 100 ? 'text-rose-700 font-black' : (occupancyPercentage > 75 ? 'text-amber-700 font-bold' : 'text-slate-800 font-bold');
            innerHTML += `<div class="mt-4 text-center"><span class="text-xs ${textCol}">${currentBooked} / ${maxLimit} Booked</span></div>`;
        }
        
        dayCell.innerHTML = innerHTML;
        container.appendChild(dayCell);
    }

    let parentWrapper = container.closest('.bg-white.rounded-2xl');
    if (!document.getElementById('calendar-legend-bar')) {
        let legendDiv = document.createElement('div');
        legendDiv.id = 'calendar-legend-bar';
        legendDiv.className = "p-4 bg-slate-50 border-t border-slate-100 flex flex-wrap items-center justify-center gap-6 text-xs font-bold text-slate-600";
        legendDiv.innerHTML = `
            <div class="flex items-center gap-2"><span class="w-4 h-4 bg-white border border-slate-300 rounded shadow-inner inline-block"></span> 0% (Available / Empty)</div>
            <div class="flex items-center gap-2"><span class="w-4 h-4 bg-emerald-100 border border-emerald-300 rounded shadow-inner inline-block"></span> 1% - 75% (Available)</div>
            <div class="flex items-center gap-2"><span class="w-4 h-4 bg-amber-100 border border-amber-300 rounded shadow-inner inline-block"></span> Above 75% (Almost Full)</div>
            <div class="flex items-center gap-2"><span class="w-4 h-4 bg-rose-100 border border-rose-300 rounded shadow-inner inline-block"></span> 100% (Fully Booked)</div>
        `;
        parentWrapper.appendChild(legendDiv);
    }
}