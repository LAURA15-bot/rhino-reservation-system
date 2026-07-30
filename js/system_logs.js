// js/system_logs.js

let globalAuditLogs = [];

document.addEventListener("DOMContentLoaded", () => {
    // Initial Load
    fetchAuditLogs();

    // =======================================================
    // REAL-TIME BACKGROUND POLLING ENGINE (Every 15 seconds)
    // =======================================================
    setInterval(() => {
        fetchAuditLogs();
    }, 15000);
});

function fetchAuditLogs() {
    fetch('api/system_logs_api.php')
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                globalAuditLogs = res.data;
                renderLogsTable(globalAuditLogs);
            } else {
                console.error("Failed to load security audit stream.");
            }
        })
        .catch(err => console.error("API Error: ", err));
}

function renderLogsTable(records) {
    const tbody = document.getElementById('logsTableBody');
    tbody.innerHTML = '';

    if (!records || records.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="p-8 text-center text-slate-400 dark:text-slate-500 italic text-xs">No audit events found matching the criteria.</td></tr>`;
        document.getElementById('records-counter-badge').innerText = "0 Events Rendered";
        return;
    }

    records.forEach(log => {
        const tr = document.createElement('tr');
        tr.className = "log-row hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors bg-white dark:bg-slate-900";
        
        // Attach raw data attributes for ultra-fast filtering
        tr.setAttribute('data-timestamp', log.created_at);
        tr.setAttribute('data-text', `${log.username} ${log.role} ${log.action_code} ${log.action} ${log.ip_address}`.toLowerCase());

        tr.innerHTML = `
            <td class="py-3 px-5 text-[11px] font-mono text-slate-500 dark:text-slate-400 font-semibold">${log.created_at}</td>
            <td class="py-3 px-5 text-xs font-bold text-slate-800 dark:text-slate-200">${escapeHtml(log.username)}</td>
            <td class="py-3 px-5"><span class="px-2 py-0.5 rounded border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase shadow-sm tracking-wider">${escapeHtml(log.role)}</span></td>
            <td class="py-3 px-5 text-[11px] font-bold text-blue-600 dark:text-blue-400 tracking-wide">${escapeHtml(log.action_code)}</td>
            <td class="py-3 px-5 text-xs text-slate-600 dark:text-slate-300 truncate max-w-lg" title="${escapeHtml(log.action)}">${escapeHtml(log.action)}</td>
            <td class="py-3 px-5 text-[11px] font-mono font-bold text-slate-400 dark:text-slate-500 text-right">${escapeHtml(log.ip_address)}</td>
        `;
        
        tbody.appendChild(tr);
    });

    // Re-apply any active filters instantly after rendering
    filterLogsTable();
}

function toggleCustomDateFilters() {
    const dateFilter = document.getElementById('dateFilter').value;
    const customWrapper = document.getElementById('customDateWrapper');
    
    if (dateFilter === 'custom') {
        customWrapper.classList.remove('hidden');
        customWrapper.classList.add('flex');
    } else {
        customWrapper.classList.add('hidden');
        customWrapper.classList.remove('flex');
        document.getElementById('startDate').value = '';
        document.getElementById('endDate').value = '';
    }
}

function resetLogFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('dateFilter').value = 'all';
    document.getElementById('startTime').value = '';
    document.getElementById('endTime').value = '';
    
    toggleCustomDateFilters();
    filterLogsTable();
}

function filterLogsTable() {
    const searchVal = document.getElementById('searchInput').value.toLowerCase().trim();
    const dateFilter = document.getElementById('dateFilter').value;
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    const startTimeStr = document.getElementById('startTime').value;
    const endTimeStr = document.getElementById('endTime').value;

    // Convert requested time bounds into minutes for easy math comparison
    let startMins = null, endMins = null;
    if(startTimeStr) { const pts = startTimeStr.split(':'); startMins = parseInt(pts[0])*60 + parseInt(pts[1]); }
    if(endTimeStr) { const pts = endTimeStr.split(':'); endMins = parseInt(pts[0])*60 + parseInt(pts[1]); }
    
    const rows = document.querySelectorAll('.log-row');
    const today = new Date();
    today.setHours(0,0,0,0);

    let visibleCount = 0;

    rows.forEach(row => {
        const timestampStr = row.getAttribute('data-timestamp'); // Format: "YYYY-MM-DD HH:MM:SS"
        const rowText = row.getAttribute('data-text');
        
        // Ensure accurate JS Date parsing by replacing spaces with 'T'
        const rowDateTime = new Date(timestampStr.replace(' ', 'T'));
        const rowDate = new Date(rowDateTime.getFullYear(), rowDateTime.getMonth(), rowDateTime.getDate());
        const rowMins = rowDateTime.getHours() * 60 + rowDateTime.getMinutes();

        // 1. Check Date
        let showByDate = true;
        if (dateFilter === 'today') {
            showByDate = (rowDate.getTime() === today.getTime());
        } else if (dateFilter === '7') {
            const sevenDaysAgo = new Date(today);
            sevenDaysAgo.setDate(today.getDate() - 7);
            showByDate = (rowDate >= sevenDaysAgo && rowDate <= today);
        } else if (dateFilter === '30') {
            const thirtyDaysAgo = new Date(today);
            thirtyDaysAgo.setDate(today.getDate() - 30);
            showByDate = (rowDate >= thirtyDaysAgo && rowDate <= today);
        } else if (dateFilter === 'custom') {
            if (startDate && endDate) {
                const start = new Date(startDate); start.setHours(0,0,0,0);
                const end = new Date(endDate); end.setHours(0,0,0,0);
                showByDate = (rowDate >= start && rowDate <= end);
            }
        }

        // 2. Check Specific Time Range
        let showByTime = true;
        if (startMins !== null && rowMins < startMins) showByTime = false;
        if (endMins !== null && rowMins > endMins) showByTime = false;

        // 3. Check Text Search
        let showBySearch = true;
        if (searchVal !== "" && !rowText.includes(searchVal)) showBySearch = false;

        // Apply Final Visibility
        if (showByDate && showByTime && showBySearch) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    document.getElementById('records-counter-badge').innerText = `${visibleCount} Events Rendered`;
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}