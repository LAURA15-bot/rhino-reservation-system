// js/payment_billing.js

let globalBillingData = [];

document.addEventListener("DOMContentLoaded", () => {
    loadBillingData();

    setInterval(() => {
        const isPayModalOpen = !document.getElementById('payment-modal').classList.contains('hidden');
        const isDocModalOpen = !document.getElementById('document-modal').classList.contains('hidden');
        
        if (!isPayModalOpen && !isDocModalOpen) {
            const query = document.getElementById('searchInput').value;
            loadBillingData(query);
        }
    }, 15000);
});

function loadBillingData(searchQuery = '') {
    let url = 'api/payment_billing_api.php?action=fetch_billing';
    if(searchQuery) url += '&search=' + encodeURIComponent(searchQuery);

    fetch(url)
        .then(res => res.json())
        .then(response => {
            if(response.success) {
                globalBillingData = response.data;
                renderBillingTable(globalBillingData);
            } else {
                console.error("Failed to load billing ledger");
            }
        })
        .catch(err => console.error("API Error: ", err));
}

function handleSearchSubmit(e) {
    e.preventDefault();
    const query = document.getElementById('searchInput').value;
    loadBillingData(query);
}

function resetSearch() {
    document.getElementById('searchInput').value = '';
    loadBillingData('');
}

function renderBillingTable(data) {
    const tbody = document.getElementById('billingTableBody');
    tbody.innerHTML = '';
    document.getElementById('records-counter-badge').innerText = `${data.length} Records Found`;

    if (!data || data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" class="p-8 text-center text-slate-400 italic">No reservation records found.</td></tr>`;
        return;
    }

    data.forEach(item => {
        const res = item.res;
        const pricingObj = item.pricingObj;
        const currency = item.actualCurrency;
        const total = item.total;
        const discount = item.discount;
        const paid = item.paid;
        const bal = item.balance;
        const status = item.computedStatus;
        const isPastDue = item.isPastDue;

        let badgeClass = 'bg-rose-50 text-rose-700 border border-rose-200';
        if (status === 'Paid in Full') badgeClass = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
        else if (status === 'Partially Paid') badgeClass = 'bg-amber-50 text-amber-700 border border-amber-200';
        else if (status === 'Checked Out') badgeClass = 'bg-indigo-50 text-indigo-700 border border-indigo-200';

        let sourceHtml = `<span class="font-bold text-slate-700">${escapeHtml(res.booking_source || 'Direct Client')}</span>`;
        if (res.booking_source === 'Travel Agency') {
            let displayAgencyName = (res.agency_name && res.agency_name.trim() !== '') ? res.agency_name : 'Agency name not specified';
            sourceHtml += `<span class="block text-[10px] text-blue-600 font-bold mt-0.5">(${escapeHtml(displayAgencyName)})</span>`;
        }

        const tr = document.createElement('tr');
        tr.className = "billing-row hover:bg-slate-50/70 transition bg-white";
        tr.setAttribute('data-checkin', res.check_in);
        tr.setAttribute('data-status', status);

        let actionBtnHtml = '';
        if (status !== 'Cancelled' && res.status !== 'Checked Out') {
            if (isPastDue) {
                actionBtnHtml = `<span class="px-2 py-1 bg-rose-100 border border-rose-200 text-rose-700 rounded font-bold text-[10px] uppercase">Expired Hold</span>`;
            } else {
                actionBtnHtml = `<button onclick='openPaymentModal(${JSON.stringify(res)}, ${paid}, ${bal}, ${JSON.stringify(pricingObj)}, "${currency}", ${total}, ${discount})' class="bg-emerald-50 hover:bg-emerald-100 text-[#046a38] border border-emerald-100 font-bold px-3 py-1.5 rounded-lg transition text-xs"><i class="fa-solid fa-cash-register mr-1"></i> Pay</button>`;
            }
        }

        tr.innerHTML = `
            <td class="p-4"><span class="font-bold text-slate-900">#${res.id}</span> - ${escapeHtml(res.guest_name)}<span class="block text-[10px] text-slate-400 font-bold tracking-wider uppercase mt-0.5">${res.guest_type || 'Resident'}</span></td>
            <td class="p-4 text-slate-500">${sourceHtml}</td>
            <td class="p-4 text-center font-medium">${res.check_in} <span class="text-slate-400">to</span> ${res.check_out}</td>
            <td class="p-4 text-center font-bold">${escapeHtml(res.room_type)}<span class="block text-[10px] text-slate-500 font-bold uppercase tracking-wider mt-0.5">${escapeHtml(res.room_tier || 'Superior Tent')}</span><span class="block text-[10px] text-slate-400 mt-0.5">${res.guests_count || 1} Guests</span></td>
            <td class="p-4 text-right font-black text-slate-900">${currency} ${total.toLocaleString(undefined, {minimumFractionDigits: 2})}${discount > 0 ? `<span class="block text-[10px] text-rose-500 italic mt-0.5">-${discount.toLocaleString(undefined, {minimumFractionDigits: 2})} Disc</span>` : ''}</td>
            <td class="p-4 text-right font-bold text-emerald-600">${currency} ${paid.toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
            <td class="p-4 text-right font-black text-rose-600">${currency} ${bal.toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
            <td class="p-4 text-center"><span class="px-2.5 py-1 rounded-lg border text-[10px] font-bold uppercase tracking-wider inline-block ${badgeClass}">${status}</span></td>
            <td class="p-4 text-center whitespace-nowrap space-x-1">
                ${actionBtnHtml}
                <button onclick='openDocumentModal(${JSON.stringify(res)}, ${paid}, ${bal}, ${JSON.stringify(pricingObj)}, "${currency}", ${total}, ${discount})' class="bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 font-bold px-3 py-1.5 rounded-lg transition text-xs"><i class="fa-solid fa-file-lines mr-1"></i> Docs</button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    filterBillingTable();
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

function filterBillingTable() {
    const dateFilter = document.getElementById('dateFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    const rows = document.querySelectorAll('.billing-row');
    const today = new Date(); today.setHours(0,0,0,0);

    rows.forEach(row => {
        const rowDateStr = row.getAttribute('data-checkin');
        const rowStatus = row.getAttribute('data-status');
        const rowDate = new Date(rowDateStr); rowDate.setHours(0,0,0,0);

        let showByDate = true;
        if (dateFilter === 'today') {
            showByDate = (rowDate.getTime() === today.getTime());
        } else if (dateFilter === '7') {
            const sevenDaysAgo = new Date(today); sevenDaysAgo.setDate(today.getDate() - 7);
            showByDate = (rowDate >= sevenDaysAgo && rowDate <= today);
        } else if (dateFilter === '30') {
            const thirtyDaysAgo = new Date(today); thirtyDaysAgo.setDate(today.getDate() - 30);
            showByDate = (rowDate >= thirtyDaysAgo && rowDate <= today);
        }

        let showByStatus = true;
        if (statusFilter !== 'All Statuses') {
            showByStatus = (rowStatus === statusFilter);
        }

        if (showByDate && showByStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function openPaymentModal(booking, paid, balance, pricingData, currency, finalTotal, discount) {
    document.getElementById('modal-booking-id').value = booking.id;
    document.getElementById('modal-guest-name').innerText = booking.guest_name;
    document.getElementById('modal-display-adult-portion').innerText = currency + ' ' + pricingData.adult_total.toLocaleString(undefined, {minimumFractionDigits: 2});
    document.getElementById('modal-display-child-portion').innerText = currency + ' ' + pricingData.child_total.toLocaleString(undefined, {minimumFractionDigits: 2});
    
    const discWrap = document.getElementById('modal-discount-wrapper');
    if(discount > 0) {
        discWrap.classList.remove('hidden');
        document.getElementById('modal-display-discount').innerText = "- " + currency + ' ' + discount.toLocaleString(undefined, {minimumFractionDigits: 2});
    } else {
        discWrap.classList.add('hidden');
    }
    
    document.getElementById('modal-display-total').innerText = currency + ' ' + finalTotal.toLocaleString(undefined, {minimumFractionDigits: 2});
    document.getElementById('modal-display-balance').innerText = currency + ' ' + balance.toLocaleString(undefined, {minimumFractionDigits: 2});
    
    document.getElementById('input-amount-paid').max = balance;
    document.getElementById('input-amount-paid').value = balance;
    document.getElementById('input-currency').value = currency;
    
    const backdrop = document.getElementById('payment-modal');
    backdrop.classList.remove('hidden');
    backdrop.classList.add('flex');
}

function closePaymentModal() { 
    const backdrop = document.getElementById('payment-modal');
    backdrop.classList.add('hidden');
    backdrop.classList.remove('flex');
}

function toggleReferenceField() {
    const method = document.getElementById('input-payment-method').value;
    const refWrapper = document.getElementById('reference-wrapper');
    const refInput = document.getElementById('input-reference-no');
    if (method === 'M-Pesa' || method === 'Bank Transfer') {
        refWrapper.style.display = 'block'; refInput.required = true;
        document.getElementById('label-ref').innerText = method + ' Reference *';
    } else {
        refWrapper.style.display = 'block'; refInput.required = false;
        document.getElementById('label-ref').innerText = 'Transaction Reference (Optional)';
    }
}

function submitPaymentViaAjax(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('record_payment', '1');

    fetch('api/payment_billing_api.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            closePaymentModal();
            Swal.fire({ icon: 'success', title: 'Payment Saved!', text: data.message, timer: 1500, showConfirmButton: false })
            .then(() => loadBillingData());
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message });
        }
    })
    .catch(() => Swal.fire({ icon: 'error', title: 'Network Error', text: 'Could not communicate with the API.' }));
}

function openDocumentModal(booking, paid, balance, pricingData, currency, finalTotal, discount) {
    window.activeTargetBooking = booking;
    window.activePricingData = pricingData;
    window.activeTotalAmount = finalTotal;
    window.activeBalance = balance;
    window.activeCurrency = currency;
    window.activeDiscount = discount;
    
    document.getElementById('doc-guest-name').innerText = booking.guest_name;
    
    // THE RECEIPT IS NOW ALWAYS UNLOCKED
    const receiptBtn = document.getElementById('btn-generate-receipt');
    if(receiptBtn) receiptBtn.style.display = 'flex';
    
    const backdrop = document.getElementById('document-modal');
    backdrop.classList.remove('hidden');
    backdrop.classList.add('flex');
}

function closeDocumentModal() { 
    const backdrop = document.getElementById('document-modal');
    backdrop.classList.add('hidden');
    backdrop.classList.remove('flex');
}

function generatePDFReceipt() {
    populatePrintableDocument("Official Payment Receipt");
    const element = document.getElementById('printable-document-area');
    element.classList.remove('hidden');
    html2pdf().set({ margin: 0.5, filename: `Receipt_Booking_${window.activeTargetBooking.id}.pdf`, image: { type: 'jpeg', quality: 0.98 }, html2canvas: { scale: 2 }, jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' } }).from(element).save().then(() => { element.classList.add('hidden'); closeDocumentModal(); });
}

function generatePDFInvoice() {
    populatePrintableDocument("Itemized Accommodation Invoice");
    const element = document.getElementById('printable-document-area');
    element.classList.remove('hidden');
    html2pdf().set({ margin: 0.5, filename: `Invoice_Booking_${window.activeTargetBooking.id}.pdf`, image: { type: 'jpeg', quality: 0.98 }, html2canvas: { scale: 2 }, jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' } }).from(element).save().then(() => { element.classList.add('hidden'); closeDocumentModal(); });
}

// SAFE RENDER FUNCTION: Prevents 'Cannot set properties of null' console crashes
function safeSetText(id, text) {
    const el = document.getElementById(id);
    if (el) el.innerText = text;
}

function populatePrintableDocument(title) {
    if (!window.activeTargetBooking) return;
    
    safeSetText('doc-title-badge', title);
    safeSetText('p-book-id', window.activeTargetBooking.id);
    safeSetText('p-guest-name', window.activeTargetBooking.guest_name);
    safeSetText('p-checkin', window.activeTargetBooking.check_in);
    safeSetText('p-checkout', window.activeTargetBooking.check_out);
    safeSetText('pdf-header-currency', window.activeCurrency);
    safeSetText('p-nights-count', window.activePricingData.nights);

    const finalTotal = window.activeTotalAmount;
    const discount = window.activeDiscount;
    const bal = window.activeBalance;
    const paid = finalTotal - bal;

    safeSetText('p-original-total', (finalTotal + discount).toLocaleString(undefined, {minimumFractionDigits: 2}));
    
    const discRow = document.getElementById('tr-discount-row');
    if(discount > 0) {
        if(discRow) discRow.classList.remove('hidden');
        safeSetText('p-discount-amount', "- " + discount.toLocaleString(undefined, {minimumFractionDigits: 2}));
    } else {
        if(discRow) discRow.classList.add('hidden');
    }
    
    safeSetText('p-total-amount', finalTotal.toLocaleString(undefined, {minimumFractionDigits: 2}));
    safeSetText('p-total-paid', paid.toLocaleString(undefined, {minimumFractionDigits: 2}));
    safeSetText('p-balance-due', bal.toLocaleString(undefined, {minimumFractionDigits: 2}));
}