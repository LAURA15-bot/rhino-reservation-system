// js/rates_controller.js

let currentSystemConsoleMode = 'view';
let contractRatesDatabase = {};

document.addEventListener("DOMContentLoaded", () => {
    fetchRatesData();
});

function fetchRatesData() {
    fetch('api/rates_controller_api.php?action=fetch_rates')
        .then(res => res.json())
        .then(response => {
            if (response.success) {
                contractRatesDatabase = response.data;
                renderMasterGroupedMatrixBoard();
            } else {
                console.error("Failed to load rates database");
            }
        })
        .catch(err => console.error("API Error: ", err));
}

function openRateModal() {
    if (currentSystemConsoleMode === 'view') return;
    document.getElementById('modal-terminal-title').innerHTML = `<i class="fa-solid fa-money-check-dollar text-[#046a38] dark:text-emerald-500"></i> Post Group Rates Assignment Terminal`;
    document.getElementById('rate-season').disabled = false;
    document.getElementById('rate-room-tier').disabled = false;
    document.getElementById('rate-entry-form').reset();
    document.getElementById('rate-modal-backdrop').className = "fixed inset-0 bg-slate-900/60 dark:bg-slate-900/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 overflow-y-auto transition-colors";
}

function closeRateModal() {
    document.getElementById('rate-modal-backdrop').className = "fixed inset-0 bg-slate-900/60 dark:bg-slate-900/80 backdrop-blur-sm z-50 hidden items-center justify-center p-4 overflow-y-auto transition-colors";
}

function switchSystemConsoleMode(targetMode) {
    currentSystemConsoleMode = targetMode;
    const viewBtn = document.getElementById('mode-view-btn');
    const editBtn = document.getElementById('mode-edit-btn');
    const createBtn = document.getElementById('create-rate-btn');
    const advisory = document.getElementById('console-lock-advisory');

    if (targetMode === 'view') {
        viewBtn.className = "px-4 py-1.5 rounded-lg text-xs font-bold transition-all bg-white dark:bg-slate-700 text-slate-800 dark:text-white shadow-sm";
        editBtn.className = "px-4 py-1.5 rounded-lg text-xs font-bold transition-all text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300";
        if(advisory) advisory.classList.remove('hidden');
        createBtn.setAttribute('disabled', true);
        createBtn.className = "bg-slate-200 dark:bg-slate-800 text-slate-400 dark:text-slate-600 cursor-not-allowed text-xs font-bold py-2.5 px-4 rounded-xl shadow-sm transition-colors flex items-center gap-2";
        document.querySelectorAll('.log-actions-column').forEach(el => el.classList.add('hidden'));
    } else {
        editBtn.className = "px-4 py-1.5 rounded-lg text-xs font-bold transition-all bg-white dark:bg-slate-700 text-slate-800 dark:text-white shadow-sm";
        viewBtn.className = "px-4 py-1.5 rounded-lg text-xs font-bold transition-all text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300";
        if(advisory) advisory.classList.add('hidden');
        createBtn.removeAttribute('disabled');
        createBtn.className = "bg-[#046a38] hover:bg-[#03542c] text-white text-xs font-bold py-2.5 px-4 rounded-xl shadow-sm transition flex items-center gap-2 cursor-pointer";
        document.querySelectorAll('.log-actions-column').forEach(el => el.classList.remove('hidden'));
    }
    renderMasterGroupedMatrixBoard();
}

function renderMasterGroupedMatrixBoard() {
    const tbody = document.getElementById('standard-matrix-tbody');
    tbody.innerHTML = '';
    const showActions = (currentSystemConsoleMode === 'edit');

    for (let seasonKey in contractRatesDatabase) {
        const seasonData = contractRatesDatabase[seasonKey];
        let isFirstRowForSeason = true;

        for (let tierKey in seasonData) {
            if (tierKey === 'label') continue;
            const tierValues = seasonData[tierKey];
            const tr = document.createElement('tr');
            tr.className = "hover:bg-slate-50/40 dark:hover:bg-slate-800/50 transition-colors border-b border-slate-100 dark:border-slate-700/50 font-medium text-slate-600 dark:text-slate-300 group";

            let seasonLabelCell = '';
            if (isFirstRowForSeason) {
                seasonLabelCell = `<td rowspan="2" class="p-4 pl-6 text-left font-bold text-slate-900 dark:text-white bg-slate-50/40 dark:bg-slate-900/40 align-middle border-r border-slate-100 dark:border-slate-700/50 leading-snug">${seasonData.label}</td>`;
                isFirstRowForSeason = false;
            }

            let actionCellHTML = '';
            if (showActions) {
                actionCellHTML = `
                    <td class="p-4 text-right pr-6 log-actions-column whitespace-nowrap">
                        <div class="inline-flex gap-3 justify-end text-sm">
                            <button type="button" onclick="loadGroupedMatrixToPopupModal('${seasonKey}','${tierKey}')" title="Edit Rates Block" class="text-blue-500 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </button>
                        </div>
                    </td>`;
            } else {
                actionCellHTML = `<td class="log-actions-column hidden"></td>`;
            }

            tr.innerHTML = `
                ${seasonLabelCell}
                <td class="p-4 text-left font-bold text-slate-800 dark:text-slate-200 bg-slate-50/10 dark:bg-slate-800/10">${tierKey}</td>
                <td class="p-4 text-center border-x border-slate-100/60 dark:border-slate-700/50 font-mono text-slate-900 dark:text-white">KSh ${tierValues.single.ksh.toLocaleString()}<span class="block text-[11px] text-slate-400 dark:text-slate-500 font-normal mt-0.5">$${tierValues.single.usd}</span></td>
                <td class="p-4 text-center border-r border-slate-100/60 dark:border-slate-700/50 font-mono text-slate-900 dark:text-white">KSh ${tierValues.double.ksh.toLocaleString()}<span class="block text-[11px] text-slate-400 dark:text-slate-500 font-normal mt-0.5">$${tierValues.double.usd}</span></td>
                <td class="p-4 text-center border-r border-slate-100/60 dark:border-slate-700/50 font-mono text-slate-900 dark:text-white">KSh ${tierValues.triple.ksh.toLocaleString()}<span class="block text-[11px] text-slate-400 dark:text-slate-500 font-normal mt-0.5">$${tierValues.triple.usd}</span></td>
                <td class="p-4 text-center border-r border-slate-100/60 dark:border-slate-700/50 font-mono text-slate-900 dark:text-white">KSh ${tierValues.family.ksh.toLocaleString()}<span class="block text-[11px] text-slate-400 dark:text-slate-500 font-normal mt-0.5">$${tierValues.family.usd}</span></td>
                ${actionCellHTML}
            `;
            tbody.appendChild(tr);
        }
    }
}

function loadGroupedMatrixToPopupModal(season, tier) {
    document.getElementById('modal-terminal-title').innerHTML = `<i class="fa-solid fa-pen-to-square text-blue-600 dark:text-blue-400"></i> Edit Rates Matrix: ${season} (${tier})`;
    
    const seasonSelect = document.getElementById('rate-season');
    const tierSelect = document.getElementById('rate-room-tier');
    seasonSelect.value = season; seasonSelect.disabled = true;
    tierSelect.value = tier; tierSelect.disabled = true;

    const targetSourceNode = contractRatesDatabase[season][tier];
    
    document.getElementById('amt-single-ksh').value = targetSourceNode.single.ksh;
    document.getElementById('amt-single-usd').value = targetSourceNode.single.usd;
    document.getElementById('amt-double-ksh').value = targetSourceNode.double.ksh;
    document.getElementById('amt-double-usd').value = targetSourceNode.double.usd;
    document.getElementById('amt-triple-ksh').value = targetSourceNode.triple.ksh;
    document.getElementById('amt-triple-usd').value = targetSourceNode.triple.usd;
    document.getElementById('amt-family-ksh').value = targetSourceNode.family.ksh;
    document.getElementById('amt-family-usd').value = targetSourceNode.family.usd;

    document.getElementById('rate-modal-backdrop').className = "fixed inset-0 bg-slate-900/60 dark:bg-slate-900/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 overflow-y-auto transition-colors";
}

function handleRateFormSubmit(e) {
    e.preventDefault();
    if (currentSystemConsoleMode === 'view') return;

    const season = document.getElementById('rate-season').value;
    const tier = document.getElementById('rate-room-tier').value;

    const ratesPayload = {
        single: { ksh: parseFloat(document.getElementById('amt-single-ksh').value) || 0, usd: parseFloat(document.getElementById('amt-single-usd').value) || 0 },
        double: { ksh: parseFloat(document.getElementById('amt-double-ksh').value) || 0, usd: parseFloat(document.getElementById('amt-double-usd').value) || 0 },
        triple: { ksh: parseFloat(document.getElementById('amt-triple-ksh').value) || 0, usd: parseFloat(document.getElementById('amt-triple-usd').value) || 0 },
        family: { ksh: parseFloat(document.getElementById('amt-family-ksh').value) || 0, usd: parseFloat(document.getElementById('amt-family-usd').value) || 0 }
    };

    const formData = new FormData();
    formData.append('action', 'save_rates');
    formData.append('season', season);
    formData.append('tier', tier);
    formData.append('rates', JSON.stringify(ratesPayload));

    fetch('api/rates_controller_api.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Swal.fire({ icon: 'success', title: 'Database Synced', text: 'Master Matrix successfully updated.', timer: 1200, showConfirmButton: false })
            .then(() => { closeRateModal(); fetchRatesData(); });
        } else {
            Swal.fire({ icon: 'error', title: 'Database Error', text: data.message });
        }
    }).catch(() => Swal.fire({ icon: 'error', title: 'Network Error', text: 'Could not communicate with the API.' }));
}

function populatePDFMatrixTbody() {
    const pdfTbody = document.getElementById('pdf-matrix-tbody');
    pdfTbody.innerHTML = '';

    for (let seasonKey in contractRatesDatabase) {
        const seasonData = contractRatesDatabase[seasonKey];
        let isFirstRowForSeason = true;

        for (let tierKey in seasonData) {
            if (tierKey === 'label') continue;
            const tierValues = seasonData[tierKey];
            const tr = document.createElement('tr');
            
            // NOTE: No dark mode classes here so the PDF generates perfectly in light mode
            tr.className = "text-slate-900 font-bold border-b-2 border-slate-900";

            let seasonLabelCell = '';
            if (isFirstRowForSeason) {
                seasonLabelCell = `<td rowspan="2" class="border-r-2 border-slate-900 p-2.5 text-left font-black bg-slate-50/50 leading-tight">${seasonData.label}</td>`;
                isFirstRowForSeason = false;
            }

            tr.innerHTML = `
                ${seasonLabelCell}
                <td class="border-r-2 border-slate-900 p-2 text-left bg-slate-50/20 text-[10px] uppercase">${tierKey}</td>
                <td class="border-r border-slate-300 p-1.5 font-mono">${tierValues.single.ksh.toLocaleString()}</td>
                <td class="border-r-2 border-slate-900 p-1.5 font-mono text-slate-600">${tierValues.single.usd.toLocaleString()}</td>
                <td class="border-r border-slate-300 p-1.5 font-mono">${tierValues.double.ksh.toLocaleString()}</td>
                <td class="border-r-2 border-slate-900 p-1.5 font-mono text-slate-600">${tierValues.double.usd.toLocaleString()}</td>
                <td class="border-r border-slate-300 p-1.5 font-mono">${tierValues.triple.ksh.toLocaleString()}</td>
                <td class="border-r-2 border-slate-900 p-1.5 font-mono text-slate-600">${tierValues.triple.usd.toLocaleString()}</td>
                <td class="border-r border-slate-300 p-1.5 font-mono">${tierValues.family.ksh.toLocaleString()}</td>
                <td class="p-1.5 font-mono text-slate-600">${tierValues.family.usd.toLocaleString()}</td>
            `;
            pdfTbody.appendChild(tr);
        }
    }
}

function compileAndDownloadRatesPDF() {
    populatePDFMatrixTbody();
    const targetElementNode = document.getElementById('hidden-pdf-document-canvas');
    targetElementNode.classList.remove('hidden');

    html2pdf().set({
        margin: [0.3, 0.3, 0.3, 0.3],
        filename: `Rhino_Tourist_Camp_Rack_Rates_Matrix.pdf`,
        image: { type: 'jpeg', quality: 0.99 },
        html2canvas: { scale: 2.5, useCORS: true, letterRendering: true },
        jsPDF: { unit: 'in', format: 'letter', orientation: 'landscape' }
    }).from(targetElementNode).save().then(() => {
        targetElementNode.classList.add('hidden');
    });
}

async function exportRatesToExcel() {
    const workbook = new ExcelJS.Workbook();
    const worksheet = workbook.addWorksheet("Contract Rates", { views: [{ showGridLines: false }] });

    worksheet.columns = [
        { width: 28 }, { width: 20 }, { width: 10 }, { width: 8 }, 
        { width: 10 }, { width: 8 }, { width: 10 }, { width: 8 }, { width: 10 }, { width: 8 }
    ];

    worksheet.mergeCells('A1:J1');
    worksheet.getCell('A1').value = "RHINO TOURIST CAMP";
    worksheet.getCell('A1').font = { name: 'Times New Roman', size: 26, bold: true, color: { argb: 'FF9E4A1A' } };
    worksheet.getCell('A1').alignment = { horizontal: 'center' };

    worksheet.mergeCells('A2:J2');
    worksheet.getCell('A2').value = "Masai Mara";
    worksheet.getCell('A2').font = { name: 'Times New Roman', size: 18, bold: true, italic: true, color: { argb: 'FF065A3F' } };
    worksheet.getCell('A2').alignment = { horizontal: 'center' };

    let r = 12;
    const borderStyle = { style: 'medium', color: { argb: 'FF1F2937' } };
    worksheet.mergeCells(`A${r}:J${r}`);
    worksheet.getCell(`A${r}`).border = { bottom: borderStyle };
    r += 2;

    worksheet.mergeCells(`A${r}:A${r+1}`); worksheet.getCell(`A${r}`).value = "SEASONS / DATES";
    worksheet.mergeCells(`B${r}:B${r+1}`); worksheet.getCell(`B${r}`).value = "Room Tier";
    worksheet.mergeCells(`C${r}:D${r}`); worksheet.getCell(`C${r}`).value = "SINGLE";
    worksheet.mergeCells(`E${r}:F${r}`); worksheet.getCell(`E${r}`).value = "DOUBLE";
    worksheet.mergeCells(`G${r}:H${r}`); worksheet.getCell(`G${r}`).value = "TRIPLE";
    worksheet.mergeCells(`I${r}:J${r}`); worksheet.getCell(`I${r}`).value = "FAMILY";

    const curRow = r + 1;
    ['C', 'E', 'G', 'I'].forEach(col => {
        worksheet.getCell(`${col}${curRow}`).value = "KSH";
        worksheet.getCell(`${String.fromCharCode(col.charCodeAt(0)+1)}${curRow}`).value = "USD";
    });

    for(let row = r; row <= curRow; row++) {
        for(let col = 1; col <= 10; col++) {
            const c = worksheet.getCell(row, col);
            c.font = { bold: true, size: 9 };
            c.alignment = { horizontal: 'center', vertical: 'middle' };
            c.border = { top: borderStyle, left: borderStyle, bottom: borderStyle, right: borderStyle };
        }
    }
    r += 2;

    for (let seasonKey in contractRatesDatabase) {
        const seasonData = contractRatesDatabase[seasonKey];
        const parts = seasonData.label.split(/<br\s*[\/]?>/i);
        const title = parts[0].replace(/<[^>]+>/g, '').trim();
        const dates = parts.length > 1 ? parts[1].replace(/<[^>]+>/g, '').trim() : '';
        const tiers = Object.keys(seasonData).filter(k => k !== 'label');
        const rowStart = r;

        tiers.forEach(tierKey => {
            const vals = seasonData[tierKey];
            const rowObj = worksheet.getRow(r);
            
            rowObj.getCell(2).value = tierKey;
            rowObj.getCell(2).font = { bold: true, size: 9 };
            rowObj.getCell(2).alignment = { horizontal: 'left', vertical: 'middle', indent: 1 };

            rowObj.getCell(3).value = vals.single.ksh;
            rowObj.getCell(4).value = vals.single.usd;
            rowObj.getCell(5).value = vals.double.ksh;
            rowObj.getCell(6).value = vals.double.usd;
            rowObj.getCell(7).value = vals.triple.ksh;
            rowObj.getCell(8).value = vals.triple.usd;
            rowObj.getCell(9).value = vals.family.ksh;
            rowObj.getCell(10).value = vals.family.usd;

            for(let c = 3; c <= 10; c++) {
                const cell = rowObj.getCell(c);
                cell.alignment = { horizontal: 'center', vertical: 'middle' };
                cell.font = { bold: true, size: 9 };
                if (c % 2 !== 0) cell.numFmt = '#,##0';
                else { cell.numFmt = '0'; cell.font = { size: 9, color: { argb: 'FF555555' } }; }
            }
            
            for(let c = 1; c <= 10; c++) {
                worksheet.getCell(r, c).border = { top: borderStyle, left: borderStyle, bottom: borderStyle, right: borderStyle };
            }
            rowObj.height = 24;
            r++;
        });

        if (r > rowStart) {
            worksheet.mergeCells(`A${rowStart}:A${r - 1}`);
            const sCell = worksheet.getCell(`A${rowStart}`);
            sCell.value = {
                richText: [
                    { font: { bold: true, size: 10, color: { argb: 'FF000000' } }, text: title + '\n' },
                    { font: { size: 8, color: { argb: 'FF808080' } }, text: dates }
                ]
            };
            sCell.alignment = { horizontal: 'left', vertical: 'middle', wrapText: true, indent: 1 };
        }
    }

    const buffer = await workbook.xlsx.writeBuffer();
    saveAs(new Blob([buffer]), "Rhino_Tourist_Camp_Rates.xlsx");
}