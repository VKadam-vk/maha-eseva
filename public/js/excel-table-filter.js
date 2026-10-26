/**
 * ==========================================================================
 * EXCEL TABLE COLUMN FILTER ENGINE (Modern Spreadsheet Filter & Sort)
 * ==========================================================================
 * Provides Excel-like column header dropdowns with:
 * - Real-time value checklist with item count badges
 * - Instant search filter inside dropdown
 * - (Select All) and (Blanks) handling
 * - Sort A-Z and Sort Z-A
 * - Multi-column conjunctive (AND) filtering
 * - Summary metric auto-recalculation callbacks
 */

(function(window) {
    'use strict';

    class ExcelTableFilter {
        constructor(config) {
            this.config = Object.assign({
                table: null,                  // DOM table or selector
                rowSelector: 'tbody tr',      // Rows to be filtered
                ignoreRowSelector: null,      // Rows to NEVER hide (e.g. #active-service-row)
                clearBtnContainer: null,      // Container to mount Clear Filters button
                onFilterChange: null,         // Callback on filter change: ({ visibleCount, totalCount, activeFilterCount }) => {}
                columns: {}                   // Config per column { [colKey]: { label: '...', type: 'text'|'date'|'number' } }
            }, config);

            this.table = typeof this.config.table === 'string'
                ? document.querySelector(this.config.table)
                : this.config.table;

            if (!this.table) {
                console.warn('[ExcelTableFilter] Table not found:', this.config.table);
                return;
            }

            this.activeFilters = {}; // { [colKey]: Set of allowed strings or null }
            this.filterButtons = {}; // { [colKey]: DOM button }
            this.activePopup = null;
            this.currentOpenCol = null;

            this.init();
        }

        init() {
            // 1. Scan for header filter buttons in the table
            const buttons = this.table.querySelectorAll('.excel-col-filter-btn[data-col]');
            buttons.forEach(btn => {
                const colKey = btn.dataset.col;
                this.filterButtons[colKey] = btn;
                this.activeFilters[colKey] = null; // null = no filter (all values allowed)

                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    this.toggleDropdown(colKey, btn);
                });
            });

            // 2. Global listeners for closing dropdown
            document.addEventListener('click', (e) => {
                if (this.activePopup && !this.activePopup.contains(e.target)) {
                    // Check if clicked another filter button
                    if (!e.target.closest('.excel-col-filter-btn')) {
                        this.closeDropdown();
                    }
                }
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.activePopup) {
                    this.closeDropdown();
                }
            });

            // 3. Create / mount Clear All Filters button if container specified
            if (this.config.clearBtnContainer) {
                const container = typeof this.config.clearBtnContainer === 'string'
                    ? document.querySelector(this.config.clearBtnContainer)
                    : this.config.clearBtnContainer;

                if (container) {
                    this.clearAllBtn = document.createElement('button');
                    this.clearAllBtn.type = 'button';
                    this.clearAllBtn.className = 'btn-clear-all-filters';
                    this.clearAllBtn.style.display = 'none';
                    this.clearAllBtn.innerHTML = '<i class="fa-solid fa-filter-circle-xmark"></i> Clear Filters (<span class="filter-count-badge">0</span>)';
                    this.clearAllBtn.addEventListener('click', () => this.clearAll());
                    container.appendChild(this.clearAllBtn);
                }
            }

            // Run initial check
            this.updateClearAllButton();
        }

        /**
         * Extract normalized value for a column from a given row element
         */
        getRowValue(row, colKey) {
            // 1. Check data-* attributes on the row
            if (row.dataset[colKey] !== undefined && row.dataset[colKey] !== null) {
                return String(row.dataset[colKey]).trim();
            }

            // CamelCase fallback for data-attributes (e.g. data-main-service -> mainService)
            const camelKey = colKey.replace(/_([a-z])/g, g => g[1].toUpperCase());
            if (row.dataset[camelKey] !== undefined && row.dataset[camelKey] !== null) {
                return String(row.dataset[camelKey]).trim();
            }

            // 2. Inspect known inputs / selects inside row
            if (colKey === 'status') {
                const sel = row.querySelector('.status-select, select[id*="status"], select[name*="status"]');
                if (sel) return sel.value.trim().toUpperCase();
            } else if (colKey === 'operator') {
                const sel = row.querySelector('.excel-operator-select, select[id*="operator"], select[name*="operator"]');
                if (sel) {
                    const opt = sel.selectedOptions[0];
                    const txt = opt ? opt.textContent.trim() : '';
                    if (txt === '-- Staff --' || txt === '-- Unassigned --' || txt === '-- Operator --' || txt === '') return '';
                    return txt.toUpperCase();
                }
            } else if (colKey === 'followup_date' || colKey === 'followup') {
                const inp = row.querySelector('input[type="date"], input[id*="followup"]');
                if (inp) return inp.value.trim();
            } else if (colKey === 'sub_service') {
                const inp = row.querySelector('input[id*="sub"], select[id*="sub"]');
                if (inp) return inp.value.trim();
                const subTxt = row.querySelector('.sub-service-text');
                if (subTxt) return subTxt.textContent.trim();
            } else if (colKey === 'main_service') {
                const mainTxt = row.querySelector('.main-service-text');
                if (mainTxt) return mainTxt.textContent.trim();
            }

            return '';
        }

        /**
         * Collect distinct values & row counts for column colKey across visible / data rows
         */
        getDistinctValues(colKey) {
            const rows = this.getDataRows();
            const counts = new Map();

            rows.forEach(row => {
                const val = this.getRowValue(row, colKey);
                counts.set(val, (counts.get(val) || 0) + 1);
            });

            // Convert to array
            const list = [];
            let blanksCount = 0;

            counts.forEach((count, val) => {
                if (!val) {
                    blanksCount += count;
                } else {
                    list.push({ value: val, display: val, count: count });
                }
            });

            // Sort list alphabetically or date-wise
            const colConfig = this.config.columns[colKey] || {};
            if (colConfig.type === 'date') {
                list.sort((a, b) => a.value.localeCompare(b.value));
            } else {
                list.sort((a, b) => a.display.localeCompare(b.display, undefined, { sensitivity: 'base' }));
            }

            // Put blanks at the end if present
            if (blanksCount > 0) {
                list.push({ value: '', display: '(Blanks)', count: blanksCount, isBlank: true });
            }

            return list;
        }

        getDataRows() {
            const allRows = Array.from(this.table.querySelectorAll(this.config.rowSelector));
            if (!this.config.ignoreRowSelector) return allRows;
            return allRows.filter(r => !r.matches(this.config.ignoreRowSelector));
        }

        toggleDropdown(colKey, triggerBtn) {
            if (this.activePopup && this.currentOpenCol === colKey) {
                this.closeDropdown();
                return;
            }
            this.openDropdown(colKey, triggerBtn);
        }

        openDropdown(colKey, triggerBtn) {
            this.closeDropdown();
            this.currentOpenCol = colKey;

            const colConfig = this.config.columns[colKey] || {};
            const colTitle = colConfig.label || colKey.replace(/_/g, ' ').toUpperCase();
            const distinctItems = this.getDistinctValues(colKey);
            const currentFilterSet = this.activeFilters[colKey]; // null means all selected

            // Create popup element
            const card = document.createElement('div');
            card.className = 'excel-filter-dropdown-card';
            card.innerHTML = `
                <div class="excel-filter-header">
                    <div class="excel-filter-title">
                        <i class="fa-solid fa-filter"></i>
                        <span>Filter: ${escapeHtml(colTitle)}</span>
                    </div>
                    <button type="button" class="excel-filter-close-btn" title="Close">&times;</button>
                </div>
                <div class="excel-filter-sort-bar">
                    <button type="button" class="excel-filter-sort-btn btn-sort-asc" title="Sort A to Z">
                        <i class="fa-solid fa-arrow-down-a-z"></i> Sort A → Z
                    </button>
                    <button type="button" class="excel-filter-sort-btn btn-sort-desc" title="Sort Z to A">
                        <i class="fa-solid fa-arrow-down-z-a"></i> Sort Z → A
                    </button>
                </div>
                <div class="excel-filter-search-box">
                    <i class="fa-solid fa-magnifying-glass excel-filter-search-icon"></i>
                    <input type="text" class="excel-filter-search-input" placeholder="Search values..." autocomplete="off">
                    <button type="button" class="excel-filter-search-clear">&times;</button>
                </div>
                <div class="excel-filter-list-wrap">
                    <div class="excel-filter-item is-select-all" data-value="__SELECT_ALL__">
                        <div class="excel-filter-item-left">
                            <input type="checkbox" class="excel-filter-checkbox check-select-all" id="ef_select_all_${colKey}">
                            <label class="excel-filter-label" for="ef_select_all_${colKey}">(Select All)</label>
                        </div>
                        <span class="excel-filter-count-badge">${distinctItems.reduce((a, b) => a + b.count, 0)}</span>
                    </div>
                    <div class="items-list-container"></div>
                </div>
                <div class="excel-filter-footer">
                    <div class="excel-filter-footer-left">
                        <button type="button" class="btn-filter-footer-clear">Clear Filter</button>
                    </div>
                    <div class="excel-filter-footer-right">
                        <button type="button" class="btn-filter-footer-cancel">Cancel</button>
                        <button type="button" class="btn-filter-footer-apply">Apply</button>
                    </div>
                </div>
            `;

            document.body.appendChild(card);
            this.activePopup = card;

            // Position popup right below the trigger button
            this.positionDropdown(card, triggerBtn);

            // Populate items list
            const container = card.querySelector('.items-list-container');
            const selectAllCheck = card.querySelector('.check-select-all');

            const itemCheckboxes = [];

            distinctItems.forEach((item, idx) => {
                const isChecked = (currentFilterSet === null) || currentFilterSet.has(item.value);
                const itemId = `ef_chk_${colKey}_${idx}`;

                const itemRow = document.createElement('div');
                itemRow.className = 'excel-filter-item item-row';
                itemRow.dataset.value = item.value;
                itemRow.dataset.text = item.display.toLowerCase();

                itemRow.innerHTML = `
                    <div class="excel-filter-item-left">
                        <input type="checkbox" class="excel-filter-checkbox item-chk" id="${itemId}" ${isChecked ? 'checked' : ''}>
                        <label class="excel-filter-label" for="${itemId}" title="${escapeHtml(item.display)}">
                            ${escapeHtml(item.display)}
                        </label>
                    </div>
                    <span class="excel-filter-count-badge">${item.count}</span>
                `;

                container.appendChild(itemRow);

                const chk = itemRow.querySelector('.item-chk');
                itemCheckboxes.push(chk);

                itemRow.addEventListener('click', (e) => {
                    if (e.target !== chk) {
                        chk.checked = !chk.checked;
                    }
                    syncSelectAllState();
                });
            });

            // Function to sync Select All checkbox state
            const syncSelectAllState = () => {
                const visibleRows = Array.from(container.querySelectorAll('.item-row')).filter(r => r.style.display !== 'none');
                const visibleChks = visibleRows.map(r => r.querySelector('.item-chk'));

                if (visibleChks.length === 0) {
                    selectAllCheck.checked = false;
                    selectAllCheck.indeterminate = false;
                    return;
                }

                const checkedCount = visibleChks.filter(c => c.checked).length;
                if (checkedCount === visibleChks.length) {
                    selectAllCheck.checked = true;
                    selectAllCheck.indeterminate = false;
                } else if (checkedCount === 0) {
                    selectAllCheck.checked = false;
                    selectAllCheck.indeterminate = false;
                } else {
                    selectAllCheck.checked = false;
                    selectAllCheck.indeterminate = true;
                }
            };

            syncSelectAllState();

            // Select All listener
            selectAllCheck.addEventListener('change', () => {
                const targetState = selectAllCheck.checked;
                const visibleRows = Array.from(container.querySelectorAll('.item-row')).filter(r => r.style.display !== 'none');
                visibleRows.forEach(r => {
                    const c = r.querySelector('.item-chk');
                    if (c) c.checked = targetState;
                });
                syncSelectAllState();
            });

            card.querySelector('.is-select-all').addEventListener('click', (e) => {
                if (e.target !== selectAllCheck) {
                    selectAllCheck.checked = !selectAllCheck.checked;
                    selectAllCheck.dispatchEvent(new Event('change'));
                }
            });

            // Live Search Input inside popup
            const searchInput = card.querySelector('.excel-filter-search-input');
            const searchClear = card.querySelector('.excel-filter-search-clear');

            searchInput.addEventListener('input', () => {
                const q = searchInput.value.trim().toLowerCase();
                searchClear.style.display = q ? 'block' : 'none';

                const rows = container.querySelectorAll('.item-row');
                rows.forEach(r => {
                    const txt = r.dataset.text || '';
                    r.style.display = txt.includes(q) ? 'flex' : 'none';
                });

                syncSelectAllState();
            });

            searchClear.addEventListener('click', () => {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('input'));
                searchInput.focus();
            });

            // Sort buttons
            card.querySelector('.btn-sort-asc').addEventListener('click', () => {
                this.sortRows(colKey, 'asc');
                this.closeDropdown();
            });

            card.querySelector('.btn-sort-desc').addEventListener('click', () => {
                this.sortRows(colKey, 'desc');
                this.closeDropdown();
            });

            // Clear Filter button in footer
            card.querySelector('.btn-filter-footer-clear').addEventListener('click', () => {
                this.activeFilters[colKey] = null;
                this.applyFilters();
                this.closeDropdown();
            });

            // Cancel button
            card.querySelector('.btn-filter-footer-cancel').addEventListener('click', () => {
                this.closeDropdown();
            });

            // Close button (&times;)
            card.querySelector('.excel-filter-close-btn').addEventListener('click', () => {
                this.closeDropdown();
            });

            // Apply button
            card.querySelector('.btn-filter-footer-apply').addEventListener('click', () => {
                this.applyFromPopup(colKey, container, distinctItems.length);
            });

            // Press Enter in search to apply immediately
            searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.applyFromPopup(colKey, container, distinctItems.length);
                }
            });

            // Focus search input and position accurately
            this.positionDropdown(card, triggerBtn);
            this.bindReposition(card, triggerBtn);
            setTimeout(() => {
                this.positionDropdown(card, triggerBtn);
                searchInput.focus();
            }, 30);
        }

        applyFromPopup(colKey, container, totalDistinctCount) {
            const allItemRows = Array.from(container.querySelectorAll('.item-row'));
            const checkedRows = allItemRows.filter(r => r.querySelector('.item-chk').checked);

            if (checkedRows.length === totalDistinctCount) {
                // All items selected -> No filter
                this.activeFilters[colKey] = null;
            } else {
                // Subset selected
                const allowed = new Set();
                checkedRows.forEach(r => allowed.add(r.dataset.value));
                this.activeFilters[colKey] = allowed;
            }

            this.applyFilters();
            this.closeDropdown();
        }

        positionDropdown(card, triggerBtn) {
            if (!card || !triggerBtn) return;
            const rect = triggerBtn.getBoundingClientRect();
            const cardWidth = 280;
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;

            // Measure actual card height
            const cardHeight = card.offsetHeight || 320;

            const spaceBelow = viewportHeight - rect.bottom - 10;
            const spaceAbove = rect.top - 10;

            let top;
            // Always prefer opening below the button unless space below is very tight (< 160px) and space above is much larger
            if (spaceBelow < 160 && spaceAbove > spaceBelow) {
                // Open above button, with bottom of card anchored to top of button
                const maxH = Math.min(cardHeight, spaceAbove);
                top = rect.top - maxH - 4;
                if (top < 10) top = 10;
                card.style.maxHeight = `${spaceAbove}px`;
            } else {
                // Open directly below button
                top = rect.bottom + 4;
                card.style.maxHeight = `${Math.max(160, spaceBelow)}px`;
            }

            // Horizontal alignment: Align left edge of card with left edge of button
            let left = rect.left;
            // If it would overflow right edge of viewport, right-align with button
            if (left + cardWidth > viewportWidth - 10) {
                left = rect.right - cardWidth;
            }
            if (left < 10) left = 10;

            card.style.top = `${Math.round(top)}px`;
            card.style.left = `${Math.round(left)}px`;
        }

        bindReposition(card, triggerBtn) {
            this.unbindReposition();
            this.repositionHandler = () => {
                if (this.activePopup && triggerBtn) {
                    const rect = triggerBtn.getBoundingClientRect();
                    // Close if trigger button scrolled completely offscreen
                    if (rect.bottom < 0 || rect.top > window.innerHeight) {
                        this.closeDropdown();
                        return;
                    }
                    this.positionDropdown(card, triggerBtn);
                }
            };
            window.addEventListener('scroll', this.repositionHandler, true);
            window.addEventListener('resize', this.repositionHandler);
        }

        unbindReposition() {
            if (this.repositionHandler) {
                window.removeEventListener('scroll', this.repositionHandler, true);
                window.removeEventListener('resize', this.repositionHandler);
                this.repositionHandler = null;
            }
        }

        closeDropdown() {
            this.unbindReposition();
            if (this.activePopup) {
                this.activePopup.remove();
                this.activePopup = null;
                this.currentOpenCol = null;
            }
        }

        /**
         * Real-time Multi-Column Filter Application
         */
        applyFilters() {
            const rows = this.getDataRows();
            let visibleCount = 0;
            const totalCount = rows.length;

            const activeFilterKeys = Object.keys(this.activeFilters).filter(k => this.activeFilters[k] !== null);

            rows.forEach(row => {
                let matches = true;

                for (const colKey of activeFilterKeys) {
                    const allowedSet = this.activeFilters[colKey];
                    const rowVal = this.getRowValue(row, colKey);

                    if (!allowedSet.has(rowVal)) {
                        matches = false;
                        break;
                    }
                }

                if (matches) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Update filter trigger button classes
            Object.keys(this.filterButtons).forEach(colKey => {
                const btn = this.filterButtons[colKey];
                if (!btn) return;

                if (this.activeFilters[colKey] !== null) {
                    btn.classList.add('filter-active');
                    btn.title = `Filter Active: ${this.activeFilters[colKey].size} selected (Click to modify)`;
                } else {
                    btn.classList.remove('filter-active');
                    btn.title = `Filter by ${colKey.replace(/_/g, ' ')}`;
                }
            });

            this.updateClearAllButton();

            // Fire onFilterChange callback
            if (typeof this.config.onFilterChange === 'function') {
                this.config.onFilterChange({
                    visibleCount: visibleCount,
                    totalCount: totalCount,
                    activeFilterCount: activeFilterKeys.length,
                    activeFilters: this.activeFilters
                });
            }
        }

        /**
         * Reset all active filters
         */
        clearAll() {
            Object.keys(this.activeFilters).forEach(colKey => {
                this.activeFilters[colKey] = null;
            });
            this.applyFilters();
        }

        updateClearAllButton() {
            if (!this.clearAllBtn) return;
            const activeCount = Object.values(this.activeFilters).filter(v => v !== null).length;

            if (activeCount > 0) {
                this.clearAllBtn.style.display = 'inline-flex';
                const badge = this.clearAllBtn.querySelector('.filter-count-badge');
                if (badge) badge.innerText = activeCount;
            } else {
                this.clearAllBtn.style.display = 'none';
            }
        }

        /**
         * Sort rows by column value
         */
        sortRows(colKey, direction = 'asc') {
            const tbody = this.table.querySelector('tbody');
            if (!tbody) return;

            const rows = this.getDataRows();
            const colConfig = this.config.columns[colKey] || {};
            const isDate = colConfig.type === 'date';

            rows.sort((a, b) => {
                const valA = this.getRowValue(a, colKey);
                const valB = this.getRowValue(b, colKey);

                if (valA === '' && valB === '') return 0;
                if (valA === '') return 1; // blanks at end
                if (valB === '') return -1;

                let comp = 0;
                if (isDate) {
                    comp = valA.localeCompare(valB);
                } else {
                    comp = valA.localeCompare(valB, undefined, { numeric: true, sensitivity: 'base' });
                }

                return direction === 'asc' ? comp : -comp;
            });

            // Re-append sorted rows (keeps #active-service-row at the very top if present)
            const activeRow = this.config.ignoreRowSelector ? tbody.querySelector(this.config.ignoreRowSelector) : null;
            if (activeRow) {
                tbody.appendChild(activeRow);
            }
            rows.forEach(r => tbody.appendChild(r));
        }

        /**
         * Refresh filters when new rows added dynamically
         */
        refresh() {
            this.applyFilters();
        }
    }

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // =========================================================================
    // EXCEL TAB AUTO-SCROLL (Keeps active input centered/visible on Tab / Focus)
    // =========================================================================
    document.addEventListener('focusin', function (e) {
        const target = e.target;
        if (!target || !target.matches('input, select, textarea')) return;

        const table = target.closest('.pocket-excel-table, .sheet-table, table');
        if (!table) return;

        const container = target.closest('.excel-table-scroll-wrapper, .pocket-excel-wrap, .sheet-scroll-container, .table-responsive') || table.parentElement;
        if (!container || container.scrollWidth <= container.clientWidth) return;

        const cell = target.closest('td, th') || target;
        const cellRect = cell.getBoundingClientRect();
        const contRect = container.getBoundingClientRect();

        // Right margin reserved for sticky columns (Status & Actions usually take 250px - 340px)
        const rightStickyMargin = 280;
        const leftStickyMargin = 60;

        if (cellRect.right > (contRect.right - rightStickyMargin)) {
            const overflowRight = cellRect.right - (contRect.right - rightStickyMargin);
            container.scrollBy({ left: overflowRight + 50, behavior: 'smooth' });
        } else if (cellRect.left < (contRect.left + leftStickyMargin)) {
            const overflowLeft = (contRect.left + leftStickyMargin) - cellRect.left;
            container.scrollBy({ left: -(overflowLeft + 50), behavior: 'smooth' });
        }
    }, true);

    // =========================================================================
    // QUICK RECEIPT / INVOICE MODAL (On-Screen Receipt Display & Direct Print)
    // =========================================================================
    window.showReceiptModal = function(app) {
        if (!app) return;
        let modal = document.getElementById('global-quick-receipt-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'global-quick-receipt-modal';
            modal.className = 'receipt-modal-backdrop';
            modal.innerHTML = `
                <div class="receipt-modal-card" role="dialog" aria-modal="true" onclick="event.stopPropagation()">
                    <div class="receipt-modal-header">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-receipt" style="color: #4ade80; font-size: 16px;"></i>
                            <div>
                                <div style="font-weight: 800; font-size: 14px;">Citizen Service Receipt</div>
                                <div style="font-size: 11px; color: #94a3b8;">Official Acknowledgement Slip</div>
                            </div>
                        </div>
                        <button type="button" onclick="window.closeReceiptModal()" style="background: none; border: none; color: #94a3b8; font-size: 18px; cursor: pointer;">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div class="receipt-modal-body">
                        <div class="receipt-slip-box">
                            <div style="text-align: center; font-weight: 800; font-size: 14px; margin-bottom: 2px;">
                                ${escapeHtml(window.TENANT_NAME || 'MAHA E-SEVA KENDRA')}
                            </div>
                            <div style="text-align: center; font-size: 11px; color: #64748b; margin-bottom: 8px;">
                                GOVERNMENT & CITIZEN SERVICES ERP
                            </div>
                            <div style="border-top: 1px dashed #334155; margin: 8px 0;"></div>

                            <div style="display: flex; justify-content: space-between; margin: 3px 0;">
                                <span>SR / TOKEN:</span>
                                <strong style="color: #0f172a;" id="qrm-app-num">-</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin: 3px 0;">
                                <span>DATE & TIME:</span>
                                <span id="qrm-date">-</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin: 3px 0;">
                                <span>STATUS:</span>
                                <strong id="qrm-status" style="color: #d97706;">-</strong>
                            </div>

                            <div style="border-top: 1px dashed #94a3b8; margin: 8px 0;"></div>

                            <div style="margin: 4px 0;">
                                <span style="color: #64748b;">CITIZEN:</span>
                                <div style="font-weight: 700; color: #0f172a;" id="qrm-citizen">-</div>
                            </div>
                            <div style="margin: 4px 0;">
                                <span style="color: #64748b;">SERVICE:</span>
                                <div style="font-weight: 700; color: #0f172a;" id="qrm-service">-</div>
                                <div style="font-size: 11.5px; color: #475569;" id="qrm-subservice"></div>
                            </div>

                            <div style="border-top: 1px dashed #334155; margin: 8px 0;"></div>

                            <div style="display: flex; justify-content: space-between; margin: 4px 0; font-size: 13px;">
                                <span>SERVICE RATE:</span>
                                <strong id="qrm-rate">₹ 0.00</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin: 4px 0; font-size: 13px; color: #166534;">
                                <span>AMOUNT PAID:</span>
                                <strong id="qrm-paid">₹ 0.00</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin: 4px 0; font-size: 13px; color: #dc2626;">
                                <span>BALANCE DUE:</span>
                                <strong id="qrm-balance">₹ 0.00</strong>
                            </div>

                            <div style="border-top: 1px dashed #334155; margin: 8px 0;"></div>
                            <div style="display: flex; justify-content: space-between; font-size: 11px; color: #64748b;">
                                <span>OPERATOR:</span>
                                <span id="qrm-operator">Staff Desk</span>
                            </div>
                            <div style="text-align: center; font-size: 10.5px; color: #94a3b8; margin-top: 10px;">
                                *** Thank You - Keep Slip For Status Enquiry ***
                            </div>
                        </div>
                    </div>
                    <div class="receipt-modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="window.closeReceiptModal()" style="padding: 6px 14px; font-weight: 700;">
                            Close
                        </button>
                        <button type="button" class="btn btn-success btn-sm" id="qrm-btn-print" style="padding: 6px 16px; font-weight: 700; background: #16a34a; border-color: #15803d; color: white;">
                            <i class="fa-solid fa-print"></i> Print Slip
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            modal.addEventListener('click', window.closeReceiptModal);

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.classList.contains('active')) {
                    window.closeReceiptModal();
                }
            });
        }

        const rate = parseFloat(app.rate || 0);
        const paid = parseFloat(app.received || 0);
        const rem = Math.max(0, rate - paid);

        document.getElementById('qrm-app-num').textContent = app.application_number || '-';
        document.getElementById('qrm-date').textContent = app.formatted_date_time || app.application_date_time || app.application_date || 'Today';
        document.getElementById('qrm-status').textContent = (app.status || 'IN_PROGRESS').toUpperCase();
        document.getElementById('qrm-citizen').textContent = app.customer_name ? (app.customer_name + (app.customer_mobile ? ' (' + app.customer_mobile + ')' : '')) : (window.CURRENT_CUSTOMER_NAME || 'Citizen');
        document.getElementById('qrm-service').textContent = app.main_service_name || app.service_name || 'Government Service';
        document.getElementById('qrm-subservice').textContent = app.sub_service || '';
        document.getElementById('qrm-rate').textContent = '₹ ' + rate.toFixed(2);
        document.getElementById('qrm-paid').textContent = '₹ ' + paid.toFixed(2);
        const balEl = document.getElementById('qrm-balance');
        balEl.textContent = '₹ ' + rem.toFixed(2);
        balEl.style.color = rem > 0 ? '#dc2626' : '#166534';
        document.getElementById('qrm-operator').textContent = app.operator || 'Staff Desk';

        const printBtn = document.getElementById('qrm-btn-print');
        printBtn.onclick = function() {
            const receiptUrl = app.receipt_url || (`/applications/${app.id}/receipt`);
            window.open(receiptUrl, '_blank');
        };

        modal.classList.add('active');
    };

    window.closeReceiptModal = function() {
        const modal = document.getElementById('global-quick-receipt-modal');
        if (modal) modal.classList.remove('active');
    };

    window.openServiceReceiptModal = function(appId, appData = null) {
        if (appData && window.showReceiptModal) {
            window.showReceiptModal(appData);
            return;
        }
        const app = (window.loadedApplications && window.loadedApplications[appId]) 
                 || (window.committedApplications && window.committedApplications.find(a => a.id == appId));
        if (app && window.showReceiptModal) {
            window.showReceiptModal(app);
        } else {
            window.open(`/applications/${appId}/receipt`, '_blank');
        }
    };
    window.openQuickReceiptModal = window.openServiceReceiptModal;
    window.openReceiptModal = window.openServiceReceiptModal;

    window.ExcelTableFilter = ExcelTableFilter;

})(window);
