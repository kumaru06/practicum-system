        </section>
    </main>
</div>
<div id="modal" class="modal"><div class="modal-card"><button class="modal-close" type="button">&times;</button><div id="modal-body"></div></div></div>
<script src="{{ asset('assets/js/main.js') }}?v=20260505-coordinator-student-year"></script>
<script>
(function () {
    'use strict';

    const MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    const DAYS   = ['Su','Mo','Tu','We','Th','Fr','Sa'];

    function today() {
        const d = new Date();
        return { y: d.getFullYear(), m: d.getMonth(), d: d.getDate() };
    }

    function parseISO(str) {
        if (!str) return null;
        const p = str.split('-');
        if (p.length !== 3) return null;
        return { y: +p[0], m: +p[1] - 1, d: +p[2] };
    }

    function formatISO(y, m, d) {
        return `${y}-${String(m+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
    }

    function formatDisplay(y, m, d) {
        return `${MONTHS[m]} ${d}, ${y}`;
    }

    function initPicker(input) {
        if (input._dpInit) return;
        input._dpInit = true;
        input.style.display = 'none';

        const wrap = document.createElement('div');
        wrap.className = 'dp-wrap';
        input.parentNode.insertBefore(wrap, input);
        wrap.appendChild(input);

        // Trigger button
        const trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'dp-trigger';
        trigger.setAttribute('aria-haspopup', 'true');
        trigger.setAttribute('aria-expanded', 'false');

        const trigText = document.createElement('span');
        trigText.className = 'dp-trigger-placeholder';
        trigText.textContent = 'Select date';

        const calIcon = document.createElement('span');
        calIcon.className = 'dp-trigger-icon';
        calIcon.innerHTML = `<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>`;

        trigger.appendChild(trigText);
        trigger.appendChild(calIcon);
        wrap.insertBefore(trigger, input);

        // State
        let selected = input.value ? parseISO(input.value) : null;
        let viewY = selected ? selected.y : today().y;
        let viewM = selected ? selected.m : today().m;
        let popup = null;
        let mode  = 'days'; // 'days' | 'months' | 'years'
        let yearPage = 0;

        function updateTrigger() {
            if (selected) {
                trigText.textContent = formatDisplay(selected.y, selected.m, selected.d);
                trigText.style.color = '#111827';
                // Add clear button
                let clr = trigger.querySelector('.dp-trigger-clear');
                if (!clr) {
                    clr = document.createElement('button');
                    clr.type = 'button';
                    clr.className = 'dp-trigger-clear';
                    clr.innerHTML = '&times;';
                    clr.addEventListener('click', (e) => { e.stopPropagation(); select(null); });
                    trigger.insertBefore(clr, calIcon);
                }
            } else {
                trigText.textContent = 'Select date';
                trigText.style.color = '';
                const clr = trigger.querySelector('.dp-trigger-clear');
                if (clr) clr.remove();
            }
        }

        function select(val) {
            selected = val;
            input.value = val ? formatISO(val.y, val.m, val.d) : '';
            input.dispatchEvent(new Event('change', { bubbles: true }));
            updateTrigger();
            close();
        }

        function open() {
            if (popup) return;
            viewY = selected ? selected.y : today().y;
            viewM = selected ? selected.m : today().m;
            mode  = 'days';
            popup = document.createElement('div');
            popup.className = 'dp-popup';
            // Align right if close to right edge
            const rect = wrap.getBoundingClientRect();
            if (rect.left + 300 > window.innerWidth - 16) popup.classList.add('dp-align-right');
            wrap.appendChild(popup);
            trigger.setAttribute('aria-expanded', 'true');
            renderPopup();
            setTimeout(() => document.addEventListener('mousedown', outsideClick), 0);
        }

        function close() {
            if (!popup) return;
            popup.remove();
            popup = null;
            trigger.setAttribute('aria-expanded', 'false');
            document.removeEventListener('mousedown', outsideClick);
        }

        function outsideClick(e) {
            if (!wrap.contains(e.target)) close();
        }

        function renderPopup() {
            if (!popup) return;
            popup.innerHTML = '';

            if (mode === 'days') renderDays();
            else if (mode === 'months') renderMonths();
            else renderYears();
        }

        function renderDays() {
            const t = today();

            // Header
            const hdr = document.createElement('div');
            hdr.className = 'dp-header';

            const prev = document.createElement('button');
            prev.type = 'button'; prev.className = 'dp-nav-btn';
            prev.innerHTML = `<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg>`;
            prev.addEventListener('click', () => { viewM--; if (viewM < 0) { viewM = 11; viewY--; } renderPopup(); });

            const lbl = document.createElement('button');
            lbl.type = 'button'; lbl.className = 'dp-month-year';
            lbl.textContent = `${MONTHS[viewM]} ${viewY}`;
            lbl.addEventListener('click', () => { mode = 'months'; renderPopup(); });

            const next = document.createElement('button');
            next.type = 'button'; next.className = 'dp-nav-btn';
            next.innerHTML = `<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>`;
            next.addEventListener('click', () => { viewM++; if (viewM > 11) { viewM = 0; viewY++; } renderPopup(); });

            hdr.appendChild(prev); hdr.appendChild(lbl); hdr.appendChild(next);
            popup.appendChild(hdr);

            // Grid
            const grid = document.createElement('div');
            grid.className = 'dp-grid';

            DAYS.forEach(n => {
                const dn = document.createElement('div');
                dn.className = 'dp-day-name'; dn.textContent = n;
                grid.appendChild(dn);
            });

            const firstDay = new Date(viewY, viewM, 1).getDay();
            const daysInMonth = new Date(viewY, viewM + 1, 0).getDate();
            const daysInPrev  = new Date(viewY, viewM, 0).getDate();

            // Prev month trailing days
            for (let i = firstDay - 1; i >= 0; i--) {
                const btn = makeDay(viewY, viewM - 1, daysInPrev - i, true);
                grid.appendChild(btn);
            }

            // Current month
            for (let d = 1; d <= daysInMonth; d++) {
                const btn = makeDay(viewY, viewM, d, false);
                grid.appendChild(btn);
            }

            // Next month leading days
            const total = firstDay + daysInMonth;
            const trailing = total % 7 === 0 ? 0 : 7 - (total % 7);
            for (let d = 1; d <= trailing; d++) {
                const btn = makeDay(viewY, viewM + 1, d, true);
                grid.appendChild(btn);
            }

            popup.appendChild(grid);

            // Footer
            const footer = document.createElement('div');
            footer.className = 'dp-footer';

            const clrBtn = document.createElement('button');
            clrBtn.type = 'button'; clrBtn.className = 'dp-footer-clear';
            clrBtn.textContent = 'Clear';
            clrBtn.addEventListener('click', () => select(null));

            const todayBtn = document.createElement('button');
            todayBtn.type = 'button'; todayBtn.className = 'dp-footer-today';
            todayBtn.textContent = 'Today';
            todayBtn.addEventListener('click', () => select({ y: t.y, m: t.m, d: t.d }));

            footer.appendChild(clrBtn); footer.appendChild(todayBtn);
            popup.appendChild(footer);

            function makeDay(y, m, d, otherMonth) {
                const actualY = m < 0 ? y - 1 : m > 11 ? y + 1 : y;
                const actualM = ((m % 12) + 12) % 12;
                const btn = document.createElement('button');
                btn.type = 'button'; btn.className = 'dp-day';
                btn.textContent = d;
                if (otherMonth) btn.classList.add('dp-day-other-month');
                if (actualY === t.y && actualM === t.m && d === t.d) btn.classList.add('dp-day-today');
                if (selected && actualY === selected.y && actualM === selected.m && d === selected.d) btn.classList.add('dp-day-selected');
                btn.addEventListener('click', () => select({ y: actualY, m: actualM, d }));
                return btn;
            }
        }

        function renderMonths() {
            const hdr = document.createElement('div');
            hdr.className = 'dp-header';

            const prev = document.createElement('button');
            prev.type = 'button'; prev.className = 'dp-nav-btn';
            prev.innerHTML = `<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg>`;
            prev.addEventListener('click', () => { viewY--; renderPopup(); });

            const lbl = document.createElement('button');
            lbl.type = 'button'; lbl.className = 'dp-month-year';
            lbl.textContent = `${viewY}`;
            lbl.addEventListener('click', () => { mode = 'years'; yearPage = Math.floor(viewY / 12); renderPopup(); });

            const next = document.createElement('button');
            next.type = 'button'; next.className = 'dp-nav-btn';
            next.innerHTML = `<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>`;
            next.addEventListener('click', () => { viewY++; renderPopup(); });

            hdr.appendChild(prev); hdr.appendChild(lbl); hdr.appendChild(next);
            popup.appendChild(hdr);

            const grid = document.createElement('div');
            grid.className = 'dp-ym-grid';
            MONTHS.forEach((name, idx) => {
                const btn = document.createElement('button');
                btn.type = 'button'; btn.className = 'dp-ym-btn';
                btn.textContent = name.slice(0, 3);
                if (idx === viewM) btn.classList.add('active');
                btn.addEventListener('click', () => { viewM = idx; mode = 'days'; renderPopup(); });
                grid.appendChild(btn);
            });
            popup.appendChild(grid);
        }

        function renderYears() {
            const base = yearPage * 12;
            const hdr = document.createElement('div');
            hdr.className = 'dp-header';

            const prev = document.createElement('button');
            prev.type = 'button'; prev.className = 'dp-nav-btn';
            prev.innerHTML = `<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg>`;
            prev.addEventListener('click', () => { yearPage--; renderPopup(); });

            const lbl = document.createElement('div');
            lbl.className = 'dp-month-year'; lbl.style.cursor = 'default';
            lbl.textContent = `${base} – ${base + 11}`;

            const next = document.createElement('button');
            next.type = 'button'; next.className = 'dp-nav-btn';
            next.innerHTML = `<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>`;
            next.addEventListener('click', () => { yearPage++; renderPopup(); });

            hdr.appendChild(prev); hdr.appendChild(lbl); hdr.appendChild(next);
            popup.appendChild(hdr);

            const grid = document.createElement('div');
            grid.className = 'dp-ym-grid';
            for (let y = base; y < base + 12; y++) {
                const btn = document.createElement('button');
                btn.type = 'button'; btn.className = 'dp-ym-btn';
                btn.textContent = y;
                if (y === viewY) btn.classList.add('active');
                btn.addEventListener('click', () => { viewY = y; mode = 'months'; renderPopup(); });
                grid.appendChild(btn);
            }
            popup.appendChild(grid);
        }

        trigger.addEventListener('click', () => popup ? close() : open());
        updateTrigger();
    }

    function initAll() {
        document.querySelectorAll('input[type="date"]').forEach(input => {
            if (!input._dpInit) initPicker(input);
        });
    }

    // Init on DOM ready and also observe dynamically added inputs
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }

    const obs = new MutationObserver(() => initAll());
    obs.observe(document.body, { childList: true, subtree: true });
})();

// ── Custom Select ──────────────────────────────────────────────────────────
(function () {
    'use strict';

    const SKIP_CLASSES = ['pf-input']; // class names to leave as native

    function initSelect(sel) {
        if (sel._csInit) return;
        // Skip if it has a class we want to leave native
        if (SKIP_CLASSES.some(c => sel.classList.contains(c))) return;
        // Skip multi-select
        if (sel.multiple) return;
        sel._csInit = true;
        sel.style.display = 'none';

        const wrap = document.createElement('div');
        wrap.className = 'cs-wrap';
        sel.parentNode.insertBefore(wrap, sel);
        wrap.appendChild(sel);

        // Trigger
        const trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'cs-trigger';
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');

        const txt = document.createElement('span');
        txt.className = 'cs-trigger-text';

        const chev = document.createElement('span');
        chev.className = 'cs-chevron';
        // Pure CSS chevron — no SVG, no color inheritance

        trigger.appendChild(txt);
        trigger.appendChild(chev);
        wrap.insertBefore(trigger, sel);

        let dropdown = null;

        function getOptions() {
            return Array.from(sel.options);
        }

        function updateTrigger() {
            const opt = sel.options[sel.selectedIndex];
            if (opt && opt.value !== '') {
                txt.textContent = opt.text;
                txt.classList.remove('placeholder');
            } else if (opt) {
                txt.textContent = opt.text || 'Select…';
                txt.classList.add('placeholder');
            } else {
                txt.textContent = 'Select…';
                txt.classList.add('placeholder');
            }
        }

        function open() {
            if (dropdown) return;
            dropdown = document.createElement('div');
            dropdown.className = 'cs-dropdown';
            dropdown.setAttribute('role', 'listbox');

            getOptions().forEach((opt, i) => {
                if (opt.disabled && opt.value === '' && i === 0) {
                    // placeholder — still render but not selectable
                }
                // Optional divider after first (placeholder) option
                if (i === 1 && getOptions()[0].value === '') {
                    const div = document.createElement('div');
                    div.className = 'cs-divider';
                    dropdown.appendChild(div);
                }
                const item = document.createElement('div');
                item.className = 'cs-option';
                item.setAttribute('role', 'option');
                item.dataset.index = i;
                if (i === sel.selectedIndex) item.classList.add('selected');
                if (opt.disabled) item.style.opacity = '.45';

                const dot = document.createElement('span');
                dot.className = 'cs-option-dot';

                const label = document.createElement('span');
                label.textContent = opt.text;

                item.appendChild(dot);
                item.appendChild(label);

                if (!opt.disabled || opt.value !== '') {
                    item.addEventListener('click', () => {
                        sel.selectedIndex = i;
                        sel.dispatchEvent(new Event('change', { bubbles: true }));
                        updateTrigger();
                        close();
                    });
                } else {
                    item.style.cursor = 'default';
                }

                dropdown.appendChild(item);
            });

            wrap.appendChild(dropdown);
            wrap.classList.add('open');
            trigger.setAttribute('aria-expanded', 'true');

            // Scroll selected into view
            const active = dropdown.querySelector('.cs-option.selected');
            if (active) active.scrollIntoView({ block: 'nearest' });

            setTimeout(() => document.addEventListener('mousedown', outside), 0);
        }

        function close() {
            if (!dropdown) return;
            dropdown.remove();
            dropdown = null;
            wrap.classList.remove('open');
            trigger.setAttribute('aria-expanded', 'false');
            document.removeEventListener('mousedown', outside);
        }

        function outside(e) {
            if (!wrap.contains(e.target)) close();
        }

        trigger.addEventListener('click', () => dropdown ? close() : open());

        // Keyboard navigation
        trigger.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault();
                if (!dropdown) open();
                const items = dropdown.querySelectorAll('.cs-option:not([style*="cursor: default"])');
                const cur   = dropdown.querySelector('.cs-option.selected');
                let idx = Array.from(items).indexOf(cur);
                idx = e.key === 'ArrowDown' ? Math.min(idx + 1, items.length - 1) : Math.max(idx - 1, 0);
                items[idx] && items[idx].click();
            } else if (e.key === 'Escape') {
                close();
            } else if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                dropdown ? close() : open();
            }
        });

        // Sync if native select value is changed programmatically
        sel.addEventListener('change', updateTrigger);
        updateTrigger();
    }

    function initAllSelects() {
        document.querySelectorAll('select').forEach(s => {
            if (!s._csInit) initSelect(s);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAllSelects);
    } else {
        initAllSelects();
    }

    const obs2 = new MutationObserver(() => initAllSelects());
    obs2.observe(document.body, { childList: true, subtree: true });
})();
</script>
</body>
</html>
