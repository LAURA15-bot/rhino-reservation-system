// js/settings.js

function saveAllSettings() {
    const formData = new FormData();
    formData.append('action', 'save_settings');
    formData.append('header_title', document.getElementById('setting_header_title').value);
    formData.append('header_subtitle', document.getElementById('setting_header_subtitle').value);
    formData.append('header_icon', document.getElementById('setting_header_icon').value);
    formData.append('sidebar_title', document.getElementById('setting_sidebar_title').value);
    formData.append('sidebar_subtitle', document.getElementById('setting_sidebar_subtitle').value);
    formData.append('sidebar_icon', document.getElementById('setting_sidebar_icon').value);
    formData.append('footer_text', document.getElementById('setting_footer_text').value);
    
    const selectedTheme = document.querySelector('input[name="theme_color"]:checked');
    if (selectedTheme) {
        formData.append('theme_color', selectedTheme.value);
    }

    fetch('api/settings_api.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Preferences Updated',
                text: data.message,
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                location.reload(); // Refresh to apply branding changes camp-wide
            });
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message });
        }
    })
    .catch(() => Swal.fire({ icon: 'error', title: 'Network Error', text: 'Could not communicate with settings API.' }));
}