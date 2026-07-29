// js/manage_users.js

document.addEventListener("DOMContentLoaded", () => {
    // Initial Load
    loadUsers();

    // =======================================================
    // REAL-TIME BACKGROUND POLLING ENGINE (Every 15 seconds)
    // =======================================================
    setInterval(() => {
        // Prevent background refresh from disrupting a user typing in the modal
        const backdrop = document.getElementById('user-modal-backdrop');
        const isModalOpen = backdrop && !backdrop.classList.contains('hidden');
        
        if (!isModalOpen) {
            loadUsers();
        }
    }, 15000);
});

function loadUsers() {
    fetch('api/manage_users_api.php')
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                const tbody = document.getElementById('usersTableBody');
                tbody.innerHTML = '';
                
                if (res.data.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="4" class="p-8 text-center text-slate-400 italic">No users found.</td></tr>`;
                    return;
                }

                res.data.forEach(user => {
                    // Normalize the role check
                    const isSystemAdmin = (user.role.toLowerCase() === 'admin');
                    
                    // Format role badge to match your screenshot
                    const roleBadge = isSystemAdmin 
                        ? '<span class="px-2 py-0.5 rounded border border-blue-200 bg-blue-50 text-[10px] font-bold text-blue-600 uppercase tracking-wider">ADMIN</span>' 
                        : '<span class="px-2 py-0.5 rounded border border-slate-200 bg-slate-50 text-[10px] font-bold text-slate-500 uppercase tracking-wider">CONSULTANT</span>';
                    
                    // Display name formatting
                    const displayName = user.display_name && user.display_name.trim() !== '' 
                        ? user.display_name 
                        : (isSystemAdmin ? 'System Administrator' : 'Standard Operator');

                    // ==========================================
                    // ACTION BUTTONS RENDERING
                    // ==========================================
                    // Provide the Edit button to everyone
                    let actionsHtml = `<button onclick='openUserModal("edit", ${JSON.stringify(user)})' class="text-blue-500 hover:text-blue-700 font-medium px-2 transition">Edit</button>`;
                    
                    // Hide the delete button for ID 2 (The protected core admin)
                    if (user.id == 2) {
                        actionsHtml += '<span class="text-xs text-slate-300 italic px-2 border-l border-slate-200 ml-2">Protected</span>';
                    } else {
                        actionsHtml += `<button onclick="deleteUser(${user.id})" class="text-rose-500 hover:text-rose-700 font-medium px-2 transition border-l border-slate-200 ml-2">Delete</button>`;
                    }

                    tbody.innerHTML += `
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-4 px-6 text-sm font-bold text-slate-800">${escapeHtml(displayName)}</td>
                            <td class="py-4 px-6 text-sm text-slate-400 font-mono">${escapeHtml(user.username)}</td>
                            <td class="py-4 px-6">${roleBadge}</td>
                            <td class="py-4 px-6 text-right space-x-2 text-xs">
                                ${actionsHtml}
                            </td>
                        </tr>
                    `;
                });
            }
        })
        .catch(err => console.error("Error fetching users:", err));
}

function openUserModal(mode, userObj = null) {
    const form = document.getElementById('userProfileForm');
    const actionInput = document.getElementById('form-action');
    const idInput = document.getElementById('user_id');
    const title = document.getElementById('modal-title');
    const passHint = document.getElementById('password-hint');
    const passInput = document.getElementById('user_password');
    const roleInput = document.getElementById('user_role');

    form.reset();
    roleInput.disabled = false; // Reset dropdown lock

    if (mode === 'add') {
        actionInput.value = 'add_user';
        idInput.value = '';
        title.innerText = 'Register Account Profile';
        passHint.classList.add('hidden');
        passInput.setAttribute('required', 'required');
    } else {
        actionInput.value = 'edit_user';
        idInput.value = userObj.id;
        title.innerText = 'Edit Account Profile';
        passHint.classList.remove('hidden');
        passInput.removeAttribute('required'); // Password is not required when editing

        // Pre-fill existing data
        form.elements['display_name'].value = userObj.display_name;
        form.elements['username'].value = userObj.username;
        roleInput.value = userObj.role.toLowerCase();

        // Lock the role dropdown if editing the core admin (ID 2)
        if (userObj.id == 2) {
            roleInput.disabled = true;
        }
    }

    const backdrop = document.getElementById('user-modal-backdrop');
    backdrop.classList.remove('hidden');
    backdrop.classList.add('flex');
}

function closeUserModal() {
    const backdrop = document.getElementById('user-modal-backdrop');
    backdrop.classList.add('hidden');
    backdrop.classList.remove('flex');
}

function submitUserForm(e) {
    e.preventDefault();
    const form = e.target;
    
    // Temporarily re-enable the role dropdown to ensure the data is captured in FormData
    const roleInput = document.getElementById('user_role');
    const roleWasDisabled = roleInput.disabled;
    if (roleWasDisabled) roleInput.disabled = false;
    
    const fd = new FormData(form);
    
    // Lock it back
    if (roleWasDisabled) roleInput.disabled = true;
    
    fetch('api/manage_users_api.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                closeUserModal();
                Swal.fire({
                    icon: 'success', 
                    title: 'Database Synced', 
                    text: 'The workspace identity has been successfully updated.',
                    timer: 2000, 
                    showConfirmButton: false
                });
                loadUsers();
            } else {
                Swal.fire('Update Failed', res.message, 'error');
            }
        })
        .catch(() => Swal.fire('Network Error', 'Unable to reach the security manager API.', 'error'));
}

function deleteUser(id) {
    Swal.fire({
        title: 'Revoke User Access?', 
        text: "This action cannot be undone and will be permanently logged.", 
        icon: 'warning', 
        showCancelButton: true, 
        confirmButtonColor: '#e11d48', 
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, Revoke Access'
    }).then(r => {
        if(r.isConfirmed){
            const fd = new FormData(); 
            fd.append('action', 'delete_user'); 
            fd.append('id', id);
            
            fetch('api/manage_users_api.php', { method:'POST', body:fd })
                .then(r => r.json())
                .then(res => {
                    if(res.success) {
                        Swal.fire({icon: 'success', title: 'Access Revoked', timer: 1200, showConfirmButton: false});
                        loadUsers();
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                });
        }
    });
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}