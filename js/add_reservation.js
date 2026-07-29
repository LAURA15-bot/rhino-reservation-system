// js/add_reservation.js

// Restrict check-in date to today or later
document.addEventListener("DOMContentLoaded", () => {
    const checkInInput = document.getElementById('check_in');
    const todayStr = new Date().toISOString().split('T')[0];
    checkInInput.setAttribute('min', todayStr);
});

// Toggle Agency Form Fields
function toggleBookingSource() {
    var source = document.getElementById('booking_source').value;
    var agencyFields = document.getElementById('agency_specific_fields');
    var agencyDiscountWrapper = document.getElementById('agency_discount_wrapper');
    var officerInput = document.getElementById('booking_officer');
    
    if(source === 'Travel Agency') {
        agencyFields.style.display = 'block'; 
        if (agencyDiscountWrapper) agencyDiscountWrapper.style.display = 'grid';
        officerInput.value = ''; // Clear default
    } else {
        agencyFields.style.display = 'none';
        if (agencyDiscountWrapper) agencyDiscountWrapper.style.display = 'none';
        document.getElementById('discount').value = 0;
        officerInput.value = 'Front Desk'; // Reset to default
    }
}

// Toggle Special Requests Form Field
function toggleSpecialReqStandalone(checkbox) {
    const wrap = document.getElementById('special_req_wrapper');
    const textarea = document.getElementById('special_requests');
    if (checkbox.checked) {
        wrap.style.display = 'block';
        textarea.setAttribute('required', 'required');
    } else {
        wrap.style.display = 'none';
        textarea.removeAttribute('required');
        textarea.value = ''; 
    }
}

// Handle Form Submission securely via API
function submitReservationForm(event) {
    event.preventDefault();
    const form = document.getElementById('reservationForm');
    const formData = new FormData(form);

    // Send payload to API
    fetch('api/add_reservation_api.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            // Render the green success summary UI block
            renderSuccessSummary(data.summary);
            form.reset(); // Clear the form
            toggleBookingSource(); // Reset agency fields
            
            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(err => {
        alert("Network error. Could not connect to API.");
        console.error(err);
    });
}

// Renders the PHP summary payload visually
function renderSuccessSummary(summary) {
    const container = document.getElementById('success-summary-container');
    
    // Format numbers nicely
    const fmtOrig = Number(summary.original_total).toLocaleString(undefined, {minimumFractionDigits: 2});
    const fmtDisc = Number(summary.discount).toLocaleString(undefined, {minimumFractionDigits: 2});
    const fmtFinal = Number(summary.final_total).toLocaleString(undefined, {minimumFractionDigits: 2});
    const fmtDep = Number(summary.deposit_paid).toLocaleString(undefined, {minimumFractionDigits: 2});
    const fmtBal = Number(summary.balance).toLocaleString(undefined, {minimumFractionDigits: 2});

    let discountHtml = '';
    if (summary.discount > 0) {
        discountHtml = `
            <div style="display: flex; justify-content: space-between; margin-bottom: 8px; color: #dc2626;">
                <span>Contract Discount Applied:</span>
                <strong>- ${summary.currency} ${fmtDisc}</strong>
            </div>
        `;
    }

    container.innerHTML = `
        <div class="success-summary" style="background: #f4fcfa; border: 1px solid #10b981; padding: 20px; border-radius: 8px; margin-bottom: 24px; color: #065f46;">
            <h3 style="margin-top: 0; margin-bottom: 15px; font-size: 18px; border-bottom: 1px solid #a7f3d0; padding-bottom: 10px;">Reservation Saved Successfully</h3>
            
            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                <span>Original Room Total:</span>
                <strong>${summary.currency} ${fmtOrig}</strong>
            </div>
            
            ${discountHtml}
            
            <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 16px; font-weight: bold;">
                <span>Final Room Total:</span>
                <span>${summary.currency} ${fmtFinal}</span>
            </div>
            
            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                <span>Deposit Paid:</span>
                <span>${summary.currency} ${fmtDep}</span>
            </div>
            
            <div style="display: flex; justify-content: space-between; margin-top: 15px; padding-top: 15px; border-top: 2px dashed #a7f3d0; font-size: 18px; font-weight: 900;">
                <span>Balance Due:</span>
                <span>${summary.currency} ${fmtBal}</span>
            </div>
        </div>
    `;
}