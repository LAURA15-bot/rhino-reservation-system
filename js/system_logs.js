// js/system_logs.js
document.addEventListener("DOMContentLoaded", () => {
    fetch('api/system_logs_api.php').then(r=>r.json()).then(res => {
        if(res.success){
            const tbody = document.getElementById('logsTableBody');
            if(res.data.length === 0) tbody.innerHTML = `<tr><td colspan="6" class="p-8 text-center text-slate-400 italic text-xs">Awaiting audit events...</td></tr>`;
            
            res.data.forEach(log => {
                tbody.innerHTML += `
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-3 px-5 text-[11px] font-mono text-slate-500">${log.created_at}</td>
                        <td class="py-3 px-5 text-xs font-bold text-slate-800">${log.username}</td>
                        <td class="py-3 px-5"><span class="px-2 py-0.5 rounded border border-slate-200 bg-slate-50 text-[9px] font-bold text-slate-500 uppercase shadow-sm tracking-wider">${log.role}</span></td>
                        <td class="py-3 px-5 text-xs font-bold text-blue-600 tracking-wide">${log.action_code}</td>
                        <td class="py-3 px-5 text-xs text-slate-500">${log.action}</td>
                        <td class="py-3 px-5 text-[11px] font-mono text-slate-400 text-right">${log.ip_address}</td>
                    </tr>
                `;
            });
        }
    });
});