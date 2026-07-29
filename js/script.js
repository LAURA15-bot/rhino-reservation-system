// Validation strictly for the add_reservation.php form
function validateReservationForm() {
    const checkInDate = document.getElementById('check_in').value;
    const checkOutDate = document.getElementById('check_out').value;
    const guestsCount = document.getElementById('guests_count').value;

    // Check if dates are selected
    if (!checkInDate || !checkOutDate) {
        alert("Please select both check-in and check-out dates.");
        return false;
    }

    // Convert strings to Date objects
    const start = new Date(checkInDate);
    const end = new Date(checkOutDate);

    // Ensure Check-out is strictly after Check-in
    if (end <= start) {
        alert("Check-out date must be after the check-in date.");
        return false;
    }

    // Ensure guests are not negative
    if (parseInt(guestsCount) <= 0) {
        alert("Number of guests must be at least 1.");
        return false;
    }

    return true; // Form is valid, allow submission
}

// Ensure today's date is the minimum allowable date for check-in
document.addEventListener("DOMContentLoaded", function() {
    const today = new Date().toISOString().split('T')[0];
    const checkInInput = document.getElementById('check_in');
    const checkOutInput = document.getElementById('check_out');

    if (checkInInput && checkOutInput) {
        checkInInput.setAttribute('min', today);
        
        // Dynamically update checkout min date when checkin changes
        checkInInput.addEventListener('change', function() {
            checkOutInput.setAttribute('min', this.value);
            
            // If checkout is now invalid, clear it
            if (checkOutInput.value && checkOutInput.value <= this.value) {
                checkOutInput.value = '';
            }
        });
    }
});