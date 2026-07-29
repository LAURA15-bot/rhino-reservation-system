// js/manage_rooms.js

document.addEventListener("DOMContentLoaded", loadRooms);

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
        // Determine the best icon based on the room name
        let iconClass = 'fa-door-closed text-slate-500';
        let bgAccent = 'bg-slate-50';
        let textAccent = 'text-slate-600';
        
        const typeLower = rm.type.toLowerCase();
        if (typeLower.includes('single')) {
            iconClass = 'fa-user text-emerald-600';
            bgAccent = 'bg-emerald-50';
            textAccent = 'text-emerald-700';
        } else if (typeLower.includes('double')) {
            iconClass = 'fa-user-group text-blue-600';
            bgAccent = 'bg-blue-50';
            textAccent = 'text-blue-700';
        } else if (typeLower.includes('triple')) {
            iconClass = 'fa-users text-amber-500';
            bgAccent = 'bg-amber-50';
            textAccent = 'text-amber-700';
        } else if (typeLower.includes('family')) {
            iconClass = 'fa-house-user text-purple-600';
            bgAccent = 'bg-purple-50';
            textAccent = 'text-purple-700';
        }

        const card = document.createElement('div');
        card.className = "bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow overflow-hidden flex flex-col";
        
        card.innerHTML = `
            <div class="p-5 flex-1">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center text-xl ${bgAccent}">
                        <i class="fa-solid ${iconClass}"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-800 tracking-wide uppercase">${rm.type}</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Active Configuration</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <span class="block text-[10px] text-slate-400 uppercase font-bold tracking-wider mb-1">Total Rooms</span>
                        <div class="text-2xl font-black text-slate-800 leading-none">${rm.total_inventory}</div>
                    </div>
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <span class="block text-[10px] text-slate-400 uppercase font-bold tracking-wider mb-1">Max Pax</span>
                        <div class="text-2xl font-black ${textAccent} leading-none">${rm.max_guests_per_room}</div>
                    </div>
                </div>
            </div>
            
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                <button onclick='openEditModal(${JSON.stringify(rm)})' class="w-full bg-white hover:bg-slate-100 text-slate-600 border border-slate-200 font-bold py-2.5 rounded-xl transition shadow-sm text-xs flex items-center justify-center gap-2">
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
            Swal.fire({
                icon: 'success', 
                title: 'Configuration Saved', 
                text: 'Property limits and audit logs have been successfully updated.',
                timer: 2000, 
                showConfirmButton: false
            });
            loadRooms(); // Refresh the grid
        } else {
            Swal.fire('Error', res.message, 'error');
        }
    })
    .catch(() => Swal.fire('Network Error', 'Unable to reach the database API.', 'error'));
}