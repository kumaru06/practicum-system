document.addEventListener('DOMContentLoaded', () => {
    initSidebar();
    initToasts();
    initFloatingLabels();
    initCharacterCounters();
    initForms();
    initCounters();
    initWizards();
    initViewToggles();
    initTimelineDetails();
    document.querySelectorAll('.data-table').forEach(table => enhanceTable(table));
    document.querySelector('.modal-close')?.addEventListener('click', () => document.getElementById('modal').classList.remove('open'));
    document.addEventListener('click', handleOutsideMenus);
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeSlidePanel(); });
    initStudentModal();
    renderDashboardCharts();
});

function initSidebar() {
    if (localStorage.getItem('sidebarCollapsed') === '1') document.body.classList.add('sidebar-collapsed');
    document.querySelector('.sidebar-toggle')?.addEventListener('click', () => {
        document.body.classList.toggle('sidebar-collapsed');
        localStorage.setItem('sidebarCollapsed', document.body.classList.contains('sidebar-collapsed') ? '1' : '0');
    });
    // Collapsible nav groups
    document.querySelectorAll('.nav-group-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            btn.closest('.nav-group').classList.toggle('open');
        });
    });
}

function initToasts() {
    document.querySelectorAll('.toast').forEach((toast, i) => {
        setTimeout(() => toast.classList.add('show'), 80 + i * 120);
        setTimeout(() => toast.classList.remove('show'), 4200 + i * 150);
    });
}

function initFloatingLabels() {
    document.querySelectorAll('.form label, .filter-bar label').forEach(label => {
        if (label.querySelector('.label-text')) return;
        const text = [...label.childNodes].find(n => n.nodeType === Node.TEXT_NODE && n.textContent.trim());
        const field = label.querySelector('input,select,textarea');
        if (!text || !field || field.type === 'hidden' || field.type === 'file') return;
        const span = document.createElement('span');
        span.className = 'label-text';
        span.textContent = text.textContent.trim();
        text.textContent = '';
        label.insertBefore(span, field);
        label.classList.add('floating-label');
        const sync = () => label.classList.toggle('has-value', !!field.value);
        field.addEventListener('input', sync);
        field.addEventListener('change', sync);
        sync();
    });
}

function initCharacterCounters() {
    document.querySelectorAll('textarea').forEach(textarea => {
        if (!textarea.maxLength || textarea.maxLength < 0) textarea.maxLength = 500;
        const counter = document.createElement('small');
        counter.className = 'char-counter';
        textarea.insertAdjacentElement('afterend', counter);
        const update = () => {
            const remaining = textarea.maxLength - textarea.value.length;
            counter.textContent = `${remaining} characters remaining`;
            counter.classList.toggle('warning', remaining <= Math.min(50, textarea.maxLength * 0.1));
        };
        textarea.addEventListener('input', update);
        update();
    });
}

function initForms() {
    document.querySelectorAll('.js-validate').forEach(form => {
        form.querySelectorAll('input,select,textarea').forEach(el => el.addEventListener('blur', () => el.classList.add('touched')));
        form.addEventListener('submit', e => {
            if (!form.checkValidity()) {
                e.preventDefault();
                form.querySelectorAll('input,select,textarea').forEach(el => el.classList.add('touched'));
                return;
            }
            const btn = form.querySelector('button[type="submit"]');
            if (btn) { btn.classList.add('loading'); btn.disabled = true; }
        });
    });
}

function initCounters() {
    document.querySelectorAll('.metric strong').forEach(el => {
        const raw = el.textContent.replace(/,/g, '').trim();
        if (!/^\d+(\.\d+)?$/.test(raw)) return;
        const target = Number(raw);
        const duration = 900;
        const start = performance.now();
        const decimals = raw.includes('.') ? 2 : 0;
        const tick = now => {
            const pct = Math.min(1, (now - start) / duration);
            const value = target * (1 - Math.pow(1 - pct, 3));
            el.textContent = value.toLocaleString(undefined, { maximumFractionDigits: decimals, minimumFractionDigits: decimals });
            if (pct < 1) requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
    });
}

function initWizards() {
    document.querySelectorAll('[data-wizard]').forEach(form => {
        let index = 0;
        const panels = [...form.querySelectorAll('.wizard-step')];
        const steps = [...form.querySelectorAll('.wizard-steps span')];
        const show = next => {
            index = Math.max(0, Math.min(next, panels.length - 1));
            panels.forEach((p, i) => p.classList.toggle('active', i === index));
            steps.forEach((s, i) => s.classList.toggle('active', i <= index));
            updateWizardSummary(form);
        };
        form.querySelectorAll('.wizard-next').forEach(btn => btn.addEventListener('click', () => {
            const fields = [...panels[index].querySelectorAll('input,select,textarea')];
            if (fields.some(field => !field.checkValidity())) { fields.forEach(field => field.classList.add('touched')); return; }
            show(index + 1);
        }));
        form.querySelectorAll('.wizard-prev').forEach(btn => btn.addEventListener('click', () => show(index - 1)));
        show(0);
    });
}

function updateWizardSummary(form) {
    const box = form.querySelector('.confirm-box');
    if (!box) return;
    const student = form.querySelector('[name="student_id"]')?.selectedOptions[0]?.textContent || '-';
    const company = form.querySelector('[name="company_id"]')?.selectedOptions[0]?.textContent || '-';
    const start = form.querySelector('[name="start_date"]')?.value || '-';
    const end = form.querySelector('[name="end_date"]')?.value || '-';
    const hours = form.querySelector('[name="required_hours"]')?.value || '-';
    box.innerHTML = `<h3>Confirm Enrollment</h3><p><strong>Student:</strong> ${escapeHtml(student)}</p><p><strong>Company:</strong> ${escapeHtml(company)}</p><p><strong>Schedule:</strong> ${escapeHtml(start)} to ${escapeHtml(end)}</p><p><strong>Required Hours:</strong> ${escapeHtml(hours)}</p><p class="muted">Submitting will send the student enrollment and company deployment emails.</p>`;
}

function initViewToggles() {
    document.querySelectorAll('.view-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            const wrapper = document.getElementById(btn.dataset.target);
            wrapper?.classList.toggle('cards-mode');
            btn.textContent = wrapper?.classList.contains('cards-mode') ? 'Table View' : 'Card View';
        });
    });
    document.querySelectorAll('.student-list-card .table-search').forEach(search => {
        search.addEventListener('input', () => {
            const q = search.value.toLowerCase();
            document.querySelectorAll('.student-card').forEach(card => card.style.display = card.dataset.search.includes(q) ? '' : 'none');
        });
    });
    document.querySelectorAll('.student-card').forEach(card => {
        card.addEventListener('click', () => {
            const title = card.querySelector('h3')?.textContent || 'Student';
            const body = card.querySelector('p')?.textContent || '';
            const status = card.querySelector('.badge')?.textContent || '';
            const company = card.querySelector('small')?.textContent || '';
            openSlidePanel(`<h2>${escapeHtml(title)}</h2><div class="detail-row"><span>Student</span><strong>${escapeHtml(body)}</strong></div><div class="detail-row"><span>Status</span><strong>${escapeHtml(status)}</strong></div><div class="detail-row"><span>Company</span><strong>${escapeHtml(company)}</strong></div>`);
        });
    });
}

function enhanceTable(table) {
    const card = table.closest('.card');
    const search = card?.querySelector('.table-search');
    const tbody = table.tBodies[0];
    if (!tbody) return;
    addTableTools(table);
    let rows = [...tbody.rows];
    let page = 1;
    const perPage = 10;
    const filtered = () => {
        const q = (search?.value || '').toLowerCase();
        return rows.filter(r => r.innerText.toLowerCase().includes(q));
    };
    const render = () => {
        const list = filtered();
        tbody.innerHTML = '';
        list.slice((page - 1) * perPage, page * perPage).forEach(r => tbody.appendChild(r));
        const pager = card?.querySelector('.pagination');
        if (pager) {
            pager.innerHTML = '';
            const pages = Math.max(1, Math.ceil(list.length / perPage));
            for (let i = 1; i <= pages; i++) {
                const b = document.createElement('button');
                b.textContent = i;
                b.className = i === page ? 'active' : '';
                b.type = 'button';
                b.onclick = () => { page = i; render(); };
                pager.appendChild(b);
            }
        }
        attachRowDetails(table);
        applyHiddenColumns(table);
    };
    search?.addEventListener('input', () => { page = 1; render(); });
    table.querySelectorAll('th[data-sort]').forEach((th, i) => {
        let asc = true;
        th.addEventListener('click', () => {
            rows.sort((a, b) => asc ? a.cells[i].innerText.localeCompare(b.cells[i].innerText) : b.cells[i].innerText.localeCompare(a.cells[i].innerText));
            asc = !asc;
            render();
        });
    });
    table._getFilteredRows = filtered;
    render();
}

function addTableTools(table) {
    const wrap = table.closest('.table-wrap');
    if (!wrap || wrap.previousElementSibling?.classList.contains('table-tools')) return;
    const tools = document.createElement('div');
    tools.className = 'table-tools';
    tools.innerHTML = '<button class="btn btn-small export-csv" type="button">Export CSV</button><div class="column-menu"><button class="btn btn-small column-toggle" type="button">Columns</button><div class="column-options"></div></div>';
    wrap.insertAdjacentElement('beforebegin', tools);
    tools.querySelector('.export-csv').addEventListener('click', () => exportCsv(table));
    const options = tools.querySelector('.column-options');
    [...table.tHead.rows[0].cells].forEach((th, i) => {
        const label = document.createElement('label');
        label.innerHTML = `<input type="checkbox" checked data-col="${i}"> ${escapeHtml(th.innerText || 'Column ' + (i + 1))}`;
        options.appendChild(label);
    label.querySelector('input').addEventListener('change', e => setColumnVisible(table, i, e.target.checked));
    });
    tools.querySelector('.column-toggle').addEventListener('click', () => options.classList.toggle('open'));
}

function handleOutsideMenus(event) {
    document.querySelectorAll('.column-options.open').forEach(menu => {
        if (!menu.parentElement.contains(event.target)) menu.classList.remove('open');
    });
}

function setColumnVisible(table, index, visible) {
    table._hiddenCols = table._hiddenCols || new Set();
    if (visible) table._hiddenCols.delete(index); else table._hiddenCols.add(index);
    applyHiddenColumns(table);
}

function applyHiddenColumns(table) {
    const hidden = table._hiddenCols || new Set();
    [...table.rows].forEach(row => [...row.cells].forEach((cell, i) => { cell.style.display = hidden.has(i) ? 'none' : ''; }));
}

function exportCsv(table) {
    const rows = [table.tHead.rows[0], ...table.tBodies[0].rows];
    const csv = rows.map(row => [...row.cells].filter(cell => cell.style.display !== 'none').map(cell => `"${cell.innerText.replace(/"/g, '""').trim()}"`).join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'table-export.csv';
    a.click();
    URL.revokeObjectURL(a.href);
}

function attachRowDetails(table) {
    [...table.tBodies[0].rows].forEach(row => {
        if (row.dataset.detailReady) return;
        row.dataset.detailReady = '1';
        row.addEventListener('click', e => {
            if (e.target.closest('a,button,form,input,select,textarea')) return;
            const headers = [...table.tHead.rows[0].cells].map(th => th.innerText.trim());
            const html = [...row.cells].map((cell, i) => `<div class="detail-row"><span>${escapeHtml(headers[i] || 'Field')}</span><strong>${escapeHtml(cell.innerText.trim())}</strong></div>`).join('');
            openSlidePanel('<h2>Record Details</h2>' + html);
        });
    });
}

function initTimelineDetails() {
    document.querySelectorAll('.timeline-item').forEach(item => {
        item.addEventListener('click', () => {
            const parts = (item.dataset.detail || '').split('|');
            openSlidePanel(`<h2>Activity Details</h2>${parts.map((p, i) => `<div class="detail-row"><span>${['Date','Time','Hours','Tasks'][i] || 'Info'}</span><strong>${escapeHtml(p)}</strong></div>`).join('')}`);
        });
    });
}

function openSlidePanel(html) {
    const modal = document.getElementById('modal');
    const body  = document.getElementById('modal-body');
    if (!modal || !body) return;
    body.innerHTML = html;
    modal.classList.add('open');
    modal.addEventListener('click', e => { if (e.target === modal) closeSlidePanel(); }, { once: true });
}
function closeSlidePanel() {
    document.getElementById('modal')?.classList.remove('open');
    document.getElementById('studentModal')?.classList.remove('open');
}
function escapeHtml(value) {
    return String(value).replace(/[&<>'"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' }[c]));
}

function renderDashboardCharts() {
    if (!window.dashboardCharts) return;

    function drawAll() {
        drawBars('monthlyChart', window.dashboardCharts.monthlyTrends || [], '', false, true);
        drawPie('statusChart',   window.dashboardCharts.statusDistribution || []);
        drawBars('courseChart',  window.dashboardCharts.completionRates || [], '%', true);
    }

    drawAll();

    // Redraw on window resize (covers zoom changes too)
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(drawAll, 80);
    });

    // ResizeObserver for container size changes (more reliable)
    if (window.ResizeObserver) {
        const ro = new ResizeObserver(() => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(drawAll, 80);
        });
        ['monthlyChart', 'statusChart', 'courseChart'].forEach(id => {
            const el = document.getElementById(id);
            if (el && el.parentElement) ro.observe(el.parentElement);
        });
    }
}
// ─── Chart helpers ────────────────────────────────────────────────────────────
const CHART_COLORS = ['#8B1A1A', '#c0392b', '#16a34a', '#f59e0b', '#dc2626', '#8b5cf6', '#0891b2', '#64748b'];
const CHART_BAR_COLOR = '#8B1A1A';

function prepCanvas(id) {
    const c = document.getElementById(id);
    if (!c) return null;
    const dpr = window.devicePixelRatio || 1;
    // Clear fixed size so browser can recalculate responsive dimensions
    c.style.width  = '100%';
    c.style.height = '';
    c.removeAttribute('width');
    c.removeAttribute('height');
    const cssW = c.offsetWidth || c.parentElement.clientWidth;
    const cssH = 320;
    c.width  = cssW * dpr;
    c.height = cssH * dpr;
    c.style.width  = cssW + 'px';
    c.style.height = cssH + 'px';
    const ctx = c.getContext('2d');
    ctx.scale(dpr, dpr);
    ctx.clearRect(0, 0, cssW, cssH);
    return { c, ctx, w: cssW, h: cssH };
}

function chartFont(ctx, size = 12, weight = '500') {
    ctx.font = `${weight} ${size}px "Plus Jakarta Sans", system-ui, sans-serif`;
}

function drawEmpty(ctx, w, h) {
    chartFont(ctx, 13);
    ctx.fillStyle = '#94a3b8';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText('No data available', w / 2, h / 2);
}

function niceMax(val) {
    if (val <= 0) return 10;
    const exp = Math.pow(10, Math.floor(Math.log10(val)));
    return Math.ceil(val / exp) * exp;
}

function roundRect(ctx, x, y, w, h, r) {
    if (h <= 0) return;
    r = Math.min(r, h / 2, w / 2);
    ctx.beginPath();
    ctx.moveTo(x + r, y);
    ctx.lineTo(x + w - r, y);
    ctx.quadraticCurveTo(x + w, y, x + w, y + r);
    ctx.lineTo(x + w, y + h);
    ctx.lineTo(x, y + h);
    ctx.lineTo(x, y + r);
    ctx.quadraticCurveTo(x, y, x + r, y);
    ctx.closePath();
}

// ─── Donut / Pie ─────────────────────────────────────────────────────────────
function drawPie(id, data) {
    const p = prepCanvas(id);
    if (!p) return;
    const { ctx, w, h } = p;
    const total = data.reduce((s, d) => s + Number(d.value || 0), 0);
    if (!total) { drawEmpty(ctx, w, h); return; }

    // layout: calculate exact space for donut, center it above legend
    const legendRowH  = 44;
    const legendRows  = data.length;
    const legendH     = legendRows * legendRowH + 8;
    const donutSpace  = h - legendH;          // pixels available for the circle
    const r   = Math.min(w * 0.38, donutSpace / 2 * 0.88);
    const cx  = w / 2;
    const cy  = donutSpace / 2;              // true vertical centre of donut area
    const inner = r * 0.55;
    let start = -Math.PI / 2;

    // slices
    data.forEach((d, i) => {
        const val = Number(d.value || 0);
        const sweep = (val / total) * Math.PI * 2;
        ctx.beginPath();
        ctx.moveTo(cx, cy);
        ctx.arc(cx, cy, r, start, start + sweep);
        ctx.closePath();
        ctx.fillStyle = CHART_COLORS[i % CHART_COLORS.length];
        ctx.fill();
        start += sweep;
    });

    // donut hole
    ctx.beginPath();
    ctx.arc(cx, cy, inner, 0, Math.PI * 2);
    ctx.fillStyle = '#fff';
    ctx.fill();

    // centre label
    chartFont(ctx, 14, '700');
    ctx.fillStyle = '#172033';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(total, cx, cy - 9);
    chartFont(ctx, 10, '500');
    ctx.fillStyle = '#64748b';
    ctx.fillText('Total', cx, cy + 10);

    // legend below donut — centred horizontally
    const legendTop = donutSpace + 8;
    const swatchW   = 12;
    const colW      = 110; // fixed column width for centering
    const totalLegW = data.length * colW;
    const legendStartX = (w - totalLegW) / 2;

    data.forEach((d, i) => {
        const val = Number(d.value || 0);
        const pct = Math.round((val / total) * 100);
        const x   = legendStartX + i * colW;
        const y   = legendTop + 12;

        // swatch
        ctx.fillStyle = CHART_COLORS[i % CHART_COLORS.length];
        roundRect(ctx, x, y, swatchW, swatchW, 3);
        ctx.fill();

        // label
        chartFont(ctx, 12, '600');
        ctx.fillStyle = '#172033';
        ctx.textAlign = 'left';
        ctx.textBaseline = 'top';
        ctx.fillText(String(d.label).charAt(0).toUpperCase() + String(d.label).slice(1), x + swatchW + 6, y);

        // value
        chartFont(ctx, 11, '700');
        ctx.fillStyle = '#64748b';
        ctx.fillText(`${val}  (${pct}%)`, x + swatchW + 6, y + 17);
    });
}

// ─── Bar chart (auto horizontal when many bars) ─────────────────────────────
const MONTH_NAMES = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
function fmtBarLabel(lbl) {
    // "2025-11" → "Nov '25"
    const m = String(lbl).match(/^(\d{4})-(\d{2})$/);
    if (m) return MONTH_NAMES[parseInt(m[2], 10) - 1] + ' \'' + m[1].slice(2);
    return String(lbl);
}
function drawBars(id, data, suffix = '', forceHorizontal = false, forceVertical = false) {
    const horizontal = !forceVertical && (forceHorizontal || data.length > 5);
    if (horizontal) { drawHBars(id, data, suffix); return; }

    const p = prepCanvas(id);
    if (!p) return;
    const { ctx, w, h } = p;
    if (!data.length) { drawEmpty(ctx, w, h); return; }

    const pad = { top: 30, right: 24, bottom: 66, left: 52 };
    const gW = w - pad.left - pad.right;
    const gH = h - pad.top - pad.bottom;
    const maxVal = niceMax(Math.max(...data.map(d => Number(d.value || 0)), 1));
    const ticks = 5;

    ctx.strokeStyle = '#e8edf5';
    ctx.lineWidth = 1;
    chartFont(ctx, 11, '500');
    ctx.fillStyle = '#94a3b8';
    ctx.textAlign = 'right';
    ctx.textBaseline = 'middle';
    for (let i = 0; i <= ticks; i++) {
        const val = (maxVal / ticks) * i;
        const y = pad.top + gH - (val / maxVal) * gH;
        ctx.beginPath(); ctx.moveTo(pad.left, y); ctx.lineTo(pad.left + gW, y); ctx.stroke();
        ctx.fillText(Math.round(val) + suffix, pad.left - 8, y);
    }

    ctx.strokeStyle = '#cbd5e1'; ctx.lineWidth = 1.5;
    ctx.beginPath(); ctx.moveTo(pad.left, pad.top); ctx.lineTo(pad.left, pad.top + gH); ctx.lineTo(pad.left + gW, pad.top + gH); ctx.stroke();

    const gap = 0.82;
    const barW = Math.max(4, (gW / data.length) * (1 - gap));
    const step = gW / data.length;
    data.forEach((d, i) => {
        const val = Number(d.value || 0);
        const bH  = (val / maxVal) * gH;
        const x   = pad.left + i * step + (step - barW) / 2;
        const y   = pad.top + gH - bH;
        const grad = ctx.createLinearGradient(x, y, x, pad.top + gH);
        grad.addColorStop(0, '#c0392b'); grad.addColorStop(1, '#8B1A1A');
        ctx.fillStyle = val > 0 ? grad : '#e2e8f0';
        roundRect(ctx, x, y, barW, bH || 2, 6); ctx.fill();
        if (val > 0) {
            chartFont(ctx, 11, '700'); ctx.fillStyle = '#172033'; ctx.textAlign = 'center'; ctx.textBaseline = 'bottom';
            ctx.fillText(val + suffix, x + barW / 2, y - 4);
        }
        chartFont(ctx, 11, '500'); ctx.fillStyle = '#64748b';
        const labelStr = fmtBarLabel(d.label);
        ctx.save(); ctx.translate(x + barW / 2, pad.top + gH + 10); ctx.rotate(-Math.PI / 4);
        ctx.textAlign = 'right'; ctx.textBaseline = 'middle';
        ctx.fillText(labelStr, 0, 0); ctx.restore();
    });
}

function drawHBars(id, data, suffix = '') {
    if (!data.length) return;
    const barH    = 36;
    const gapH    = 14;
    const padR    = 56;
    const padTop  = 16;
    const padBot  = 40;  // enough room for tick labels below the chart area
    const totalH  = padTop + data.length * (barH + gapH) - gapH + padBot;

    const c = document.getElementById(id);
    if (!c) return;
    const dpr  = window.devicePixelRatio || 1;
    c.style.width = '100%';
    c.removeAttribute('width');
    c.removeAttribute('height');
    const cssW = c.offsetWidth || c.parentElement.clientWidth || 700;

    // Measure max label width dynamically so labels never get clipped
    const measureCtx = document.createElement('canvas').getContext('2d');
    measureCtx.font = '600 12px "Plus Jakarta Sans", system-ui, sans-serif';
    const maxTextW = Math.max(...data.map(d => measureCtx.measureText(String(d.label)).width));
    const padL = Math.min(Math.ceil(maxTextW) + 20, 280);
    c.width  = cssW * dpr;
    c.height = totalH * dpr;
    c.style.setProperty('width',  cssW   + 'px', 'important');
    c.style.setProperty('height', totalH + 'px', 'important');
    const ctx = c.getContext('2d');
    ctx.scale(dpr, dpr);
    ctx.clearRect(0, 0, cssW, totalH);

    const gW     = cssW - padL - padR;
    const maxVal = niceMax(Math.max(...data.map(d => Number(d.value || 0)), 1));

    // faint vertical grid lines
    const ticks = 4;
    ctx.strokeStyle = '#e8edf5'; ctx.lineWidth = 1;
    chartFont(ctx, 10, '500'); ctx.fillStyle = '#94a3b8'; ctx.textAlign = 'center'; ctx.textBaseline = 'top';
    for (let i = 0; i <= ticks; i++) {
        const x = padL + (i / ticks) * gW;
        ctx.beginPath(); ctx.moveTo(x, padTop); ctx.lineTo(x, padTop + totalH - padBot); ctx.stroke();
        ctx.fillText(Math.round((maxVal / ticks) * i) + suffix, x, padTop + totalH - padBot + 4);
    }

    const hitRegions = [];
    data.forEach((d, i) => {
        const val  = Number(d.value || 0);
        const bW   = (val / maxVal) * gW;
        const y    = padTop + i * (barH + gapH);
        const x    = padL;
        hitRegions.push({ y, h: barH, label: d.label, val });

        // label — truncate by pixel width, not character count
        chartFont(ctx, 12, '600');
        ctx.fillStyle = '#172033';
        ctx.textAlign = 'right';
        ctx.textBaseline = 'middle';
        const labelStr = String(d.label);
        const maxLabelW = padL - 14;
        let truncated = labelStr;
        while (truncated.length > 1 && ctx.measureText(truncated).width > maxLabelW) {
            truncated = truncated.slice(0, -1);
        }
        if (truncated !== labelStr) truncated = truncated.slice(0, -1) + '…';
        ctx.fillText(truncated, padL - 10, y + barH / 2);

        // bar
        const grad = ctx.createLinearGradient(x, y, x + bW, y);
        grad.addColorStop(0, '#c0392b');
        grad.addColorStop(1, '#8B1A1A');
        ctx.fillStyle = val > 0 ? grad : '#e2e8f0';
        roundRect(ctx, x, y, Math.max(bW, 4), barH, 8);
        ctx.fill();

        // value label
        chartFont(ctx, 11, '700');
        ctx.fillStyle = val > 0 && bW > 40 ? '#fff' : '#172033';
        ctx.textAlign = val > 0 && bW > 40 ? 'right' : 'left';
        ctx.textBaseline = 'middle';
        if (val > 0 && bW > 40) {
            ctx.fillText(val + suffix, x + bW - 10, y + barH / 2);
        } else {
            ctx.fillText(val + suffix, x + bW + 8, y + barH / 2);
        }
    });
    if (id === 'courseChart') {
        window._courseHitRegions = hitRegions;
        window._courseChartPadL  = padL;
        window._courseTotalH     = totalH;
        attachCourseChartInteraction();
    }
}

function attachCourseChartInteraction() {
    const canvas = document.getElementById('courseChart');
    if (!canvas || canvas._interactionAttached) return;
    canvas._interactionAttached = true;

    // ── Tooltip element ──────────────────────────────────────────────────────
    let tip = document.getElementById('_courseTooltip');
    if (!tip) {
        tip = document.createElement('div');
        tip.id = '_courseTooltip';
        tip.className = 'course-chart-tip';
        document.body.appendChild(tip);
    }

    function getHoveredBar(e) {
        const rect = canvas.getBoundingClientRect();
        const logH = window._courseTotalH || rect.height;
        const rawY = (e.clientY - rect.top) * (logH / rect.height);
        return (window._courseHitRegions || []).find(r => rawY >= r.y && rawY <= r.y + r.h) || null;
    }

    canvas.addEventListener('mousemove', e => {
        const bar = getHoveredBar(e);
        if (!bar) { tip.classList.remove('visible'); canvas.style.cursor = ''; return; }
        const students = (window.dashboardCharts.courseStudents || {})[bar.label] || [];
        canvas.style.cursor = students.length ? 'pointer' : 'default';
        const names = students.slice(0, 5).map(s => `<span>${escapeHtml(s.name)}<em>${s.pct}%</em></span>`).join('');
        const more  = students.length > 5 ? `<span class="tip-more">+${students.length - 5} more</span>` : '';
        tip.innerHTML = `<strong>${escapeHtml(bar.label)}</strong>${names}${more}${students.length ? '<small>Click to see details</small>' : ''}`;
        tip.classList.add('visible');
        const x = Math.min(e.clientX + 14, window.innerWidth - tip.offsetWidth - 12);
        const y = Math.max(e.clientY - tip.offsetHeight / 2, 8);
        tip.style.left = x + 'px';
        tip.style.top  = y + 'px';
    });

    canvas.addEventListener('mouseleave', () => {
        tip.classList.remove('visible');
        canvas.style.cursor = '';
    });

    canvas.addEventListener('click', e => {
        const bar      = getHoveredBar(e);
        if (!bar) return;
        const students = (window.dashboardCharts.courseStudents || {})[bar.label] || [];
        if (!students.length) return;
        tip.classList.remove('visible');

        const rows = students.map(s => {
            const pct   = Math.min(s.pct, 100);
            const color = pct >= 100 ? '#16a34a' : pct >= 50 ? '#f59e0b' : '#8B1A1A';
            return `
                <div class="cprogress-row">
                    <div class="cprogress-header">
                        <div>
                            <span class="cprogress-name">${escapeHtml(s.name)}</span>
                            <span class="cprogress-id">${escapeHtml(s.student_no || '')}</span>
                        </div>
                        <span class="cprogress-pct" style="color:${color}">${pct}%</span>
                    </div>
                    <div class="cprogress-track">
                        <div class="cprogress-fill" style="width:${pct}%;background:${color}"></div>
                    </div>
                    <div class="cprogress-sub">${s.logged}h logged / ${s.required}h required</div>
                </div>`;
        }).join('');

        openSlidePanel(`
            <h2>${escapeHtml(bar.label)}</h2>
            <p class="cprogress-meta">${students.length} student${students.length !== 1 ? 's' : ''} &mdash; avg ${bar.val}% completion</p>
            <div class="cprogress-list">${rows}</div>
        `);
    });
}

// ─── Line chart ──────────────────────────────────────────────────────────────
function drawLine(id, data) {
    const p = prepCanvas(id);
    if (!p) return;
    const { ctx, w, h } = p;
    if (!data.length) { drawEmpty(ctx, w, h); return; }

    const pad = { top: 30, right: 24, bottom: 62, left: 52 };
    const gW = w - pad.left - pad.right;
    const gH = h - pad.top - pad.bottom;
    const maxVal = niceMax(Math.max(...data.map(d => Number(d.value || 0)), 1));
    const ticks = 5;

    // grid + y labels
    ctx.strokeStyle = '#e8edf5';
    ctx.lineWidth = 1;
    chartFont(ctx, 11, '500');
    ctx.fillStyle = '#94a3b8';
    ctx.textAlign = 'right';
    ctx.textBaseline = 'middle';
    for (let i = 0; i <= ticks; i++) {
        const val = (maxVal / ticks) * i;
        const y = pad.top + gH - (val / maxVal) * gH;
        ctx.beginPath();
        ctx.moveTo(pad.left, y);
        ctx.lineTo(pad.left + gW, y);
        ctx.stroke();
        ctx.fillText(Math.round(val), pad.left - 8, y);
    }

    // axes
    ctx.strokeStyle = '#cbd5e1';
    ctx.lineWidth = 1.5;
    ctx.beginPath();
    ctx.moveTo(pad.left, pad.top);
    ctx.lineTo(pad.left, pad.top + gH);
    ctx.lineTo(pad.left + gW, pad.top + gH);
    ctx.stroke();

    const pts = data.map((d, i) => ({
        x: pad.left + (data.length === 1 ? gW / 2 : i * (gW / (data.length - 1))),
        y: pad.top + gH - (Number(d.value || 0) / maxVal) * gH,
        d
    }));

    // area fill
    const areaGrad = ctx.createLinearGradient(0, pad.top, 0, pad.top + gH);
    areaGrad.addColorStop(0, 'rgba(139,26,26,0.18)');
    areaGrad.addColorStop(1, 'rgba(139,26,26,0)');
    ctx.beginPath();
    pts.forEach((pt, i) => i === 0 ? ctx.moveTo(pt.x, pt.y) : ctx.lineTo(pt.x, pt.y));
    ctx.lineTo(pts[pts.length - 1].x, pad.top + gH);
    ctx.lineTo(pts[0].x, pad.top + gH);
    ctx.closePath();
    ctx.fillStyle = areaGrad;
    ctx.fill();

    // line
    ctx.strokeStyle = '#8B1A1A';
    ctx.lineWidth = 3;
    ctx.lineJoin = 'round';
    ctx.beginPath();
    pts.forEach((pt, i) => i === 0 ? ctx.moveTo(pt.x, pt.y) : ctx.lineTo(pt.x, pt.y));
    ctx.stroke();

    // dots + labels
    pts.forEach(pt => {
        // outer ring
        ctx.beginPath();
        ctx.arc(pt.x, pt.y, 7, 0, Math.PI * 2);
        ctx.fillStyle = '#fff';
        ctx.fill();
        ctx.strokeStyle = '#8B1A1A';
        ctx.lineWidth = 2.5;
        ctx.stroke();
        // inner dot
        ctx.beginPath();
        ctx.arc(pt.x, pt.y, 3.5, 0, Math.PI * 2);
        ctx.fillStyle = '#8B1A1A';
        ctx.fill();

        // value above dot
        chartFont(ctx, 11, '700');
        ctx.fillStyle = '#172033';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'bottom';
        ctx.fillText(pt.d.value, pt.x, pt.y - 12);

        // x label – draw after dots so we can do rotation outside the per-dot loop
    });

    // x-axis labels: skip any that would overlap, rotate -35°
    chartFont(ctx, 11, '500');
    ctx.fillStyle = '#64748b';
    const labelY = pad.top + gH + 10;
    const minGap = 52; // minimum px between label centres before skipping
    let lastDrawnX = -Infinity;
    // decide step: draw every Nth label so neighbours are ≥ minGap apart
    const step = Math.ceil(minGap / (pts.length > 1 ? gW / (pts.length - 1) : 1));
    pts.forEach((pt, i) => {
        if (i % step !== 0 && i !== pts.length - 1) return;
        if (pt.x - lastDrawnX < minGap && i !== pts.length - 1) return;
        lastDrawnX = pt.x;
        ctx.save();
        ctx.translate(pt.x, labelY);
        ctx.rotate(-Math.PI / 5); // -36°
        ctx.textAlign = 'right';
        ctx.textBaseline = 'middle';
        ctx.fillText(String(pt.d.label), 0, 0);
        ctx.restore();
    });}

function closeStudentModal() { closeSlidePanel(); }

function initStudentModal() {
    const modal = document.getElementById('studentModal');
    if (!modal) return;

    // Close handlers
    document.getElementById('studentModalClose')?.addEventListener('click', closeStudentModal);
    modal.addEventListener('click', e => { if (e.target === modal) closeStudentModal(); });

    // Open handler — event delegation on table body
    document.addEventListener('click', e => {
        const btn = e.target.closest('.student-view-btn');
        if (!btn) return;

        const d = btn.dataset;
        document.getElementById('sm-name').textContent        = d.name || '';
        document.getElementById('sm-email').textContent       = d.email || '';
        document.getElementById('sm-student-no').textContent  = d.studentNo || '';
        document.getElementById('sm-course').textContent      = d.course || '';
        document.getElementById('sm-company').textContent     = d.company || '';
        document.getElementById('sm-progress').textContent    = `${d.rendered} / ${d.required} hrs (${d.percent}%)`;

        // Status badge
        const statusEl = document.getElementById('sm-status');
        statusEl.innerHTML = '';
        const badge = document.createElement('span');
        badge.className = `badge ${d.status}`;
        badge.textContent = d.status;
        statusEl.appendChild(badge);

        // COR link
        const corWrap = document.getElementById('sm-cor-wrap');
        const corLink = document.getElementById('sm-cor-link');
        if (d.cor && d.cor.trim() !== '') {
            corLink.href = d.cor;
            corWrap.style.display = '';
        } else {
            corWrap.style.display = 'none';
        }

        // Reset form
        document.getElementById('sm-csrf').value       = d.csrf || '';
        document.getElementById('sm-student-id').value = d.studentId || '';

        modal.classList.add('open');
    });
}