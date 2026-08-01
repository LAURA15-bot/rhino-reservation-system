// js/settings.js

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

function switchSettingsTab(tabId, btnElement) {
    document.querySelectorAll('.settings-content-section').forEach(sec => {
        sec.classList.add('hidden');
    });
    document.getElementById(tabId).classList.remove('hidden');

    // Reset styles (Need to access global primary color from the DOM style property we injected)
    const primaryColor = document.body.dataset.primaryColor || '#046a38';

    document.querySelectorAll('.settings-tab-btn').forEach(btn => {
        btn.style.backgroundColor = 'transparent';
        btn.style.color = ''; 
        btn.className = "settings-tab-btn w-full text-left px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-3 transition-colors text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50";
    });
    
    // Set Active Tab style
    btnElement.className = "settings-tab-btn w-full text-left px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-3 transition-colors text-white shadow-sm";
    btnElement.style.backgroundColor = primaryColor;
}

// Stylish Accordion Dropdown Toggler
function toggleAccordion(contentId, btnElement) {
    const content = document.getElementById(contentId);
    const icon = btnElement.querySelector('.fa-chevron-down, .fa-chevron-up');
    
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        if (icon) {
            icon.classList.remove('fa-chevron-down');
            icon.classList.add('fa-chevron-up');
        }
    } else {
        content.classList.add('hidden');
        if (icon) {
            icon.classList.remove('fa-chevron-up');
            icon.classList.add('fa-chevron-down');
        }
    }
}

// File display helpers
function displayFileName(input, displayId) {
    const display = document.getElementById(displayId);
    if (input.files && input.files[0]) {
        display.textContent = 'Selected: ' + input.files[0].name;
        display.classList.remove('hidden');
    } else {
        display.classList.add('hidden');
    }
}

// Toggling views inside the forms
function toggleDisplayMode(radioName, iconWrapId, logoWrapId) {
    const mode = document.querySelector(`input[name="${radioName}"]:checked`).value;
    const iconWrap = document.getElementById(iconWrapId);
    const logoWrap = document.getElementById(logoWrapId);

    if (mode === 'icon') {
        iconWrap.classList.remove('hidden');
        logoWrap.classList.add('hidden');
    } else {
        iconWrap.classList.add('hidden');
        logoWrap.classList.remove('hidden');
    }
}

function toggleCustomHexFields(show) {
    const wrap = document.getElementById('wrapper_custom_colors');
    if (show) wrap.classList.remove('hidden');
    else wrap.classList.add('hidden');
}

// Master Form Submitter
function saveSection(sectionName, formElement) {
    const formData = new FormData(formElement);
    formData.append('action', 'save_settings');
    formData.append('section_name', sectionName);

    Swal.fire({
        title: 'Saving...',
        text: 'Applying ' + sectionName + ' configuration.',
        allowEscapeKey: false,
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading() }
    });

    fetch('api/settings_api.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        Swal.close(); // Close the loading overlay
        
        if (data.success) {
            Toast.fire({ icon: 'success', title: data.message });
            // Delay the reload slightly so the user can see the success toast
            setTimeout(() => { location.reload(); }, 1500);
        } else {
            Toast.fire({ icon: 'error', title: data.message });
        }
    })
    .catch(() => {
        // Keep the critical SweetAlert modal for fatal network disconnections
        Swal.fire('Fatal Error', 'Could not communicate with settings API.', 'error');
    });
}

// UI Feedback for Theme Selection
function selectThemeUI(selectedTheme) {
    const themeStyles = {
        'safari': 'border-[#8B3C28] bg-amber-50/50 dark:bg-amber-900/20',
        'kairi': 'border-[#802b1f] bg-red-50/50 dark:bg-red-900/20',
        'emerald': 'border-[#046a38] bg-emerald-50/50 dark:bg-emerald-900/20',
        'blue': 'border-[#2563eb] bg-blue-50/50 dark:bg-blue-900/20',
        'custom': 'border-slate-800 dark:border-slate-400 bg-slate-100 dark:bg-slate-800'
    };

    // Strip out all active colors and set all cards back to the default grey border
    document.querySelectorAll('.theme-option-label').forEach(label => {
        label.className = "theme-option-label cursor-pointer border-2 rounded-xl p-4 flex flex-col items-center text-center gap-2 transition-all border-slate-200 dark:border-slate-700";
    });

    // Find the specific card the user just clicked and inject the bright active colors
    const radio = document.querySelector(`input[name="theme_color"][value="${selectedTheme}"]`);
    if (radio) {
        const activeLabel = radio.closest('label');
        activeLabel.className = `theme-option-label cursor-pointer border-2 rounded-xl p-4 flex flex-col items-center text-center gap-2 transition-all ${themeStyles[selectedTheme]}`;
        radio.checked = true;
    }
}