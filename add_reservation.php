<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Reservation - Rhino Camp</title>
    <link rel="stylesheet" href="css/style.css">
    <!-- Link to the external javascript API handler -->
    <script src="js/add_reservation.js" defer></script>
</head>
<body class="admin-body">
    <div class="navbar">
        <a href="dashboard.php" class="btn-secondary">Back to Dashboard</a>
        <h2>New Reservation Entry</h2>
    </div>

    <div class="form-container">
        
        <!-- DYNAMIC SUMMARY CONTAINER (Populated by JS upon successful save) -->
        <div id="success-summary-container"></div>

        <form id="reservationForm" onsubmit="submitReservationForm(event)">
            
            <div class="form-group" style="margin-bottom: 20px; border-bottom: 1px solid #ccc; padding-bottom: 15px;">
                <label style="font-weight: 800; text-transform: uppercase;">Booking Source *</label>
                <select name="booking_source" id="booking_source" onchange="toggleBookingSource()" required style="width: 100%; max-width: 400px;">
                    <option value="Direct Client">👤 Direct Client</option>
                    <option value="Travel Agency">💼 Travel Agency</option>
                </select>
            </div>

            <!-- TRAVEL AGENCY SPECIFIC FIELDS -->
            <div id="agency_specific_fields" style="display: none; border: 1px solid #e1e5eb; border-radius: 8px; padding: 16px; margin-bottom: 24px; background-color: #ffffff;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div class="form-group">
                        <label style="font-size: 11px; font-weight: bold; color: #5a6270; text-transform: uppercase;">Agency Name *</label>
                        <input type="text" name="agency_name" placeholder="e.g., Perfect Safaris" style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px;">
                    </div>
                    <div class="form-group">
                        <label style="font-size: 11px; font-weight: bold; color: #5a6270; text-transform: uppercase;">Booking Officer *</label>
                        <input type="text" name="booking_officer" id="booking_officer" value="Front Desk" style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px;">
                    </div>
                </div>

                <div id="agency_discount_wrapper" style="display: grid; grid-template-columns: 1fr; gap: 16px;">
                    <div class="form-group" style="background: #f4fbfa; padding: 14px; border-radius: 6px; border: 1px dashed #047857;">
                        <label style="font-size: 11px; font-weight: bold; color: #047857; text-transform: uppercase; display: block; margin-bottom: 8px;">
                            Discount Amount (Per Room, Per Night)
                        </label>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <input type="number" step="0.01" name="discount" id="discount" placeholder="0.00" value="0.00" style="width: 150px; padding: 10px; border: 1px solid #047857; border-radius: 6px; font-weight: bold; color: #047857; outline: none;">
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid-form">
                <div class="form-group">
                    <label>Guest Name *</label>
                    <input type="text" name="guest_name" required>
                </div>
                
                <div class="form-group">
                    <label>Phone Number *</label>
                    <input type="text" name="phone" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email">
                </div>

                <div class="form-group">
                    <label>Check-in Date *</label>
                    <input type="date" name="check_in" id="check_in" required>
                </div>

                <div class="form-group">
                    <label>Check-out Date *</label>
                    <input type="date" name="check_out" id="check_out" required>
                </div>

                <div class="form-group">
                    <label>Number of Guests (Total) *</label>
                    <input type="number" min="1" name="guests_count" id="guests_count" required>
                </div>
                
                <div class="form-group">
                    <label>Guest Type *</label>
                    <select name="guest_type" required>
                        <option value="Resident">Resident (Billed in KES)</option>
                        <option value="Non Resident">Non Resident (Billed in USD)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Room Tier *</label>
                    <select name="room_tier" required>
                        <option value="Superior Tent">Superior Tent</option>
                        <option value="Deluxe Room">Deluxe Room</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Room Type *</label>
                    <select name="room_type" required>
                        <option value="Single Room">Single Room (Max 1 pax)</option>
                        <option value="Double Room">Double Room (Max 2 pax)</option>
                        <option value="Triple Room">Triple Room (Max 3 pax)</option>
                        <option value="Family Room">Family Room (Max 4 pax)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Number of Rooms *</label>
                    <input type="number" min="1" name="rooms_count" value="1" required>
                </div>

                <div class="form-group" style="grid-column: 1 / -1; margin-top: 15px; border-bottom: 1px solid #ccc; padding-bottom: 5px;">
                    <label style="font-weight: 800; text-transform: uppercase;">Guest Composition (Child Pricing Engine)</label>
                </div>

                <div class="form-group">
                    <label>Number of Adults</label>
                    <input type="number" min="0" name="number_of_adults" value="1" required>
                </div>

                <div class="form-group">
                    <label>Number of Children</label>
                    <input type="number" min="0" name="number_of_children" value="0" required>
                </div>

                <div class="form-group">
                    <label>Children in Own Rooms</label>
                    <input type="number" min="0" name="children_own_rooms" value="0">
                </div>
                
                <div class="form-group" style="display: flex; align-items: center; gap: 10px; margin-top: 25px;">
                    <input type="checkbox" name="children_under_12" id="children_under_12" value="1" checked style="width: auto;">
                    <label for="children_under_12" style="margin-bottom: 0;">Children under 12 yrs?</label>
                </div>

                <!-- NEW SPECIAL REQUESTS TOGGLE -->
                <div class="form-group" style="grid-column: 1 / -1; margin-top: 10px;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; color: #b45309; font-weight: bold;">
                        <input type="checkbox" name="has_special_requests" id="has_special_requests" onchange="toggleSpecialReqStandalone(this)" style="width: auto;">
                        Include Special Requests (Dietary, Accessibility, etc.)
                    </label>
                    <div id="special_req_wrapper" style="display: none; margin-top: 10px;">
                        <textarea name="special_requests" id="special_requests" rows="3" placeholder="Enter special requests here..." style="width: 100%; padding: 10px; border: 1px solid #fcd34d; background: #fffbeb; border-radius: 6px;"></textarea>
                    </div>
                </div>

                <div class="form-group" style="grid-column: 1 / -1; margin-top: 15px; border-bottom: 1px solid #ccc;"></div>

                <div class="form-group">
                    <label>Receipt Number</label>
                    <input type="text" name="receipt_no">
                </div>

                <div class="form-group">
                    <label>Deposit Paid</label>
                    <input type="number" step="0.01" name="deposit_paid" value="0.00">
                </div>

                <div class="form-group">
                    <label>Balance</label>
                    <input type="text" value="System Override" disabled style="background:#f1f1f1; cursor:not-allowed;">
                </div>

                <div class="form-group">
                    <label>Booking Status</label>
                    <select name="status">
                        <option value="Reserved">Reserved (Awaiting Payment)</option>
                        <option value="Booked">Booked (Confirmed)</option>
                    </select>
                </div>
            </div>
            
            <button type="submit" class="btn-primary mt-20">Save Reservation Entry</button>
        </form>
    </div>
</body>
</html>