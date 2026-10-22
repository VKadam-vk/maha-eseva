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

            // Focus search input
            setTimeout(() => searchInput.focus(), 50);
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
            const rect = triggerBtn.getBoundingClientRect();
            const cardWidth = 280;
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;

            // Default: position below trigger
            let top = rect.bottom + 6;
            let left = rect.left;

            // Check right edge
            if (left + cardWidth > viewportWidth - 10) {
                left = viewportWidth - cardWidth - 12;
            }
            if (left < 10) left = 10;

            // Check bottom edge
            const cardEstimatedHeight = 340;
            if (top + cardEstimatedHeight > viewportHeight - 10) {
                // Flip upward
                top = rect.top - cardEstimatedHeight - 6;
                if (top < 10) top = 10;
            }

            card.style.top = `${top}px`;
            card.style.left = `${left}px`;
        }

        closeDropdown() {
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

    window.ExcelTableFilter = ExcelTableFilter;

})(window);
