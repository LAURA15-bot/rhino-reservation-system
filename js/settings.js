// js/settings.js

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
        if (data.success) {
            Swal.fire({ icon: 'success', title: 'Saved!', text: data.message, timer: 1500, showConfirmButton: false })
            .then(() => { location.reload(); });
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message });
        }
    })
    .catch(() => Swal.fire({ icon: 'error', title: 'Network Error', text: 'Could not communicate with settings API.' }));
}