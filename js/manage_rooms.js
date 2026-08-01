// js/manage_rooms.js

// Initialize Top-Right Toast Notifications
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
    // Initial Load
    loadRooms();

    // =======================================================
    // REAL-TIME BACKGROUND POLLING ENGINE (Every 15 seconds)
    // =======================================================
    setInterval(() => {
        // Prevent background refresh from disrupting a user typing in the modal
        const modal = document.getElementById('room-modal-backdrop');
        const isModalOpen = modal && !modal.classList.contains('hidden');
        
        if (!isModalOpen) {
            loadRooms();
        }
    }, 15000);
});

function loadRooms() {
    fetch('api/manage_rooms_api.php')
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                renderRoomCards(res.data);
            } else {
                console.error("Failed to fetch rooms data.");
            }
        })
        .catch(err => console.error("API Error: ", err));
}

function renderRoomCards(rooms) {
    const container = document.getElementById('room-cards-container');
    container.innerHTML = '';

    rooms.forEach(rm => {
        // Determine the best icon and colors based on the room name
        let iconClass = 'fa-door-closed text-slate-500 dark:text-slate-400';
        let bgAccent = 'bg-slate-50 dark:bg-slate-900';
        let textAccent = 'text-slate-600 dark:text-slate-300';
        
        const typeLower = rm.type.toLowerCase();
        if (typeLower.includes('single')) {
            iconClass = 'fa-user text-emerald-600 dark:text-emerald-400';
            bgAccent = 'bg-emerald-50 dark:bg-emerald-900/30';
            textAccent = 'text-emerald-700 dark:text-emerald-400';
        } else if (typeLower.includes('double')) {
            iconClass = 'fa-user-group text-blue-600 dark:text-blue-400';
            bgAccent = 'bg-blue-50 dark:bg-blue-900/30';
            textAccent = 'text-blue-700 dark:text-blue-400';
        } else if (typeLower.includes('triple')) {
            iconClass = 'fa-users text-amber-500 dark:text-amber-400';
            bgAccent = 'bg-amber-50 dark:bg-amber-900/30';
            textAccent = 'text-amber-700 dark:text-amber-400';
        } else if (typeLower.includes('family')) {
            iconClass = 'fa-house-user text-purple-600 dark:text-purple-400';
            bgAccent = 'bg-purple-50 dark:bg-purple-900/30';
            textAccent = 'text-purple-700 dark:text-purple-400';
        }

        const card = document.createElement('div');
        card.className = "bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md transition-shadow overflow-hidden flex flex-col transition-colors duration-300";
        
        card.innerHTML = `
            <div class="p-5 flex-1">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center text-xl ${bgAccent}">
                        <i class="fa-solid ${iconClass}"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-800 dark:text-white tracking-wide uppercase">${rm.type}</h3>
                        <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Active Configuration</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-slate-50 dark:bg-slate-900/50 p-3 rounded-xl border border-slate-100 dark:border-slate-700/50 transition-colors">
                        <span class="block text-[10px] text-slate-400 dark:text-slate-500 uppercase font-bold tracking-wider mb-1">Total Rooms</span>
                        <div class="text-2xl font-black text-slate-800 dark:text-white leading-none">${rm.total_inventory}</div>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-900/50 p-3 rounded-xl border border-slate-100 dark:border-slate-700/50 transition-colors">
                        <span class="block text-[10px] text-slate-400 dark:text-slate-500 uppercase font-bold tracking-wider mb-1">Max Pax</span>
                        <div class="text-2xl font-black ${textAccent} leading-none">${rm.max_guests_per_room}</div>
                    </div>
                </div>
            </div>
            
            <div class="p-4 border-t border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/80 transition-colors">
                <button onclick='openEditModal(${JSON.stringify(rm)})' class="w-full bg-white dark:bg-slate-700 hover:bg-slate-100 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-600 font-bold py-2.5 rounded-xl transition-colors shadow-sm text-xs flex items-center justify-center gap-2">
                    <i class="fa-solid fa-pen-to-square"></i> Modify Limits
                </button>
            </div>
        `;
        
        container.appendChild(card);
    });
}

function openEditModal(roomObj) {
    document.getElementById('edit_room_id').value = roomObj.id;
    document.getElementById('modal-room-title').innerText = roomObj.type;
    document.getElementById('edit_total_inventory').value = roomObj.total_inventory;
    document.getElementById('edit_max_guests').value = roomObj.max_guests_per_room;

    const backdrop = document.getElementById('room-modal-backdrop');
    backdrop.classList.remove('hidden');
    backdrop.classList.add('flex');
}

function closeRoomModal() {
    const backdrop = document.getElementById('room-modal-backdrop');
    backdrop.classList.add('hidden');
    backdrop.classList.remove('flex');
}

function submitRoomUpdate(e) {
    e.preventDefault();
    const fd = new FormData(e.target);
    
    fetch('api/manage_rooms_api.php', {
        method: 'POST', 
        body: fd
    })
    .then(r => r.json())
    .then(res => {
        if(res.success) {
            closeRoomModal();
            
            // Replaced the intrusive SweetAlert with the sleek Toast for success
            Toast.fire({
                icon: 'success',
                title: 'Property limits updated.'
            });
            
            loadRooms(); // Refresh the grid instantly
        } else {
            // Replaced the intrusive SweetAlert with the sleek Toast for API errors
            Toast.fire({ icon: 'error', title: res.message });
        }
    })
    .catch(() => {
        // Maintained the critical SweetAlert modal for fatal network disconnections
        Swal.fire('Fatal Error', 'Unable to reach the database API.', 'error');
    });
}