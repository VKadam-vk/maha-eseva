/**
 * Maha E-Seva ERP - Field Change History & Audit Trail Modal
 * Handles cell-level history icons and field-specific audit modal popups.
 */

(function () {
    'use strict';

    let currentAppId = null;
    let currentField = 'all';
    let cachedData = {};

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function ensureModalElement() {
        let backdrop = document.getElementById('field-history-modal-backdrop');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.id = 'field-history-modal-backdrop';
            backdrop.className = 'field-history-backdrop';
            backdrop.innerHTML = `
                <div class="field-history-dialog" role="dialog" aria-modal="true" onclick="event.stopPropagation()">
                    <div class="field-history-header">
                        <div class="field-history-title-box">
                            <h4 class="field-history-title">
                                <i class="fa-solid fa-clock-rotate-left" style="color: #38bdf8;"></i>
                                <span id="fhist-app-title">Application History</span>
                            </h4>
                            <div class="field-history-subtitle" id="fhist-app-subtitle">
                                Loading audit details...
                            </div>
                        </div>
                        <button type="button" class="field-history-close-btn" id="fhist-close-btn" title="Close (Esc)">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <div class="field-history-tabs" id="fhist-tabs">
                        <!-- Dynamic field filter tabs inserted here -->
                    </div>

                    <div class="field-history-body" id="fhist-body">
                        <!-- History timeline items inserted here -->
                    </div>

                    <div class="field-history-footer">
                        <div class="field-history-footer-info" id="fhist-footer-info">
                            0 change records found
                        </div>
                        <button type="button" class="field-history-footer-close-btn" id="fhist-footer-close-btn">
                            Close
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(backdrop);

            // Close handlers
            backdrop.addEventListener('click', closeFieldHistoryModal);
            document.getElementById('fhist-close-btn').addEventListener('click', closeFieldHistoryModal);
            document.getElementById('fhist-footer-close-btn').addEventListener('click', closeFieldHistoryModal);

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && backdrop.classList.contains('active')) {
                    closeFieldHistoryModal();
                }
            });
        }
        return backdrop;
    }

    function openFieldHistory(appId, fieldName, event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (!appId) return;

        currentAppId = appId;
        const aliases = {
            'status': 'work_status',
            'operator': 'assigned_employee_id',
            'followup': 'follow_up_date',
            'followup_date': 'follow_up_date',
            'service': 'sub_service',
            'subservice': 'sub_service'
        };
        currentField = (fieldName && aliases[fieldName]) ? aliases[fieldName] : (fieldName || 'all');

        const backdrop = ensureModalElement();
        backdrop.classList.add('active');

        // Show loading state
        const body = document.getElementById('fhist-body');
        body.innerHTML = `
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px; color: #64748b; gap: 10px;">
                <i class="fa-solid fa-spinner fa-spin" style="font-size: 28px; color: #0284c7;"></i>
                <span style="font-size: 13px; font-weight: 600;">Fetching change audit history...</span>
            </div>
        `;

        // Fetch from API
        fetch(`/applications/${appId}/field-history?field=all`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            return res.json();
        })
        .then(data => {
            if (data.success) {
                cachedData[appId] = data;
                renderModalContent(data);
            } else {
                showError(data.message || 'Failed to load change history.');
            }
        })
        .catch(err => {
            showError('Unable to load history. Please check your internet connection.');
        });
    }

    function closeFieldHistoryModal() {
        const backdrop = document.getElementById('field-history-modal-backdrop');
        if (backdrop) {
            backdrop.classList.remove('active');
        }
    }

    function renderModalContent(data) {
        const app = data.application || {};
        const allHistories = data.histories || [];
        const counts = data.counts || {};

        // 1. Header Info
        const titleEl = document.getElementById('fhist-app-title');
        const subtitleEl = document.getElementById('fhist-app-subtitle');

        titleEl.textContent = `${app.application_number} • ${app.customer_name}`;
        subtitleEl.innerHTML = `
            <span>Service: <strong>${escapeHtml(app.service_name)}</strong></span>
            ${app.sub_service ? `<span>(${escapeHtml(app.sub_service)})</span>` : ''}
            <span>• Created: ${escapeHtml(app.created_at || 'Recently')}</span>
        `;

        // 2. Tab Bar
        const tabsEl = document.getElementById('fhist-tabs');
        const tabDefinitions = [
            { key: 'all', label: 'All Changes', icon: 'fa-list-check' },
            { key: 'work_status', label: 'Status', icon: 'fa-signal' },
            { key: 'assigned_employee_id', label: 'Operator', icon: 'fa-user-gear' },
            { key: 'rate', label: 'Rate (₹)', icon: 'fa-indian-rupee-sign' },
            { key: 'follow_up_date', label: 'Follow-Up Date', icon: 'fa-calendar-days' },
            { key: 'sub_service', label: 'Sub Service', icon: 'fa-layer-group' },
        ];

        // If target field is not valid, default to 'all'
        const hasMatchingKey = tabDefinitions.some(t => t.key === currentField);
        if (!hasMatchingKey) {
            currentField = 'all';
        }

        let tabsHtml = '';
        tabDefinitions.forEach(t => {
            const count = t.key === 'all' ? allHistories.length : (counts[t.key] || 0);
            const isActive = t.key === currentField;
            tabsHtml += `
                <button type="button" class="field-history-tab ${isActive ? 'active' : ''}" data-tab="${t.key}" onclick="window._switchHistoryTab('${t.key}')">
                    <i class="fa-solid ${t.icon}"></i>
                    <span>${t.label}</span>
                    <span class="tab-badge">${count}</span>
                </button>
            `;
        });
        tabsEl.innerHTML = tabsHtml;

        // 3. Render Timeline items for currentField
        renderTimelineCards(allHistories, app);
    }

    function renderTimelineCards(allHistories, app) {
        const body = document.getElementById('fhist-body');
        const footerInfo = document.getElementById('fhist-footer-info');

        let filtered = allHistories;
        if (currentField && currentField !== 'all') {
            filtered = allHistories.filter(h => h.field_name === currentField);
        }

        footerInfo.textContent = `${filtered.length} change record${filtered.length === 1 ? '' : 's'} found`;

        if (filtered.length === 0) {
            let fieldFriendlyName = 'this field';
            let currentVal = '';
            if (currentField === 'work_status') {
                fieldFriendlyName = 'Status';
                currentVal = app.current_status || 'PENDING';
            } else if (currentField === 'assigned_employee_id') {
                fieldFriendlyName = 'Operator';
                currentVal = app.current_operator || 'Unassigned';
            } else if (currentField === 'rate') {
                fieldFriendlyName = 'Rate';
                currentVal = '₹ ' + (app.current_rate || '0.00');
            } else if (currentField === 'follow_up_date') {
                fieldFriendlyName = 'Follow-Up Date';
                currentVal = app.current_followup || 'Not Set';
            } else if (currentField === 'sub_service') {
                fieldFriendlyName = 'Sub Service';
                currentVal = app.sub_service || 'Standard';
            }

            body.innerHTML = `
                <div class="field-history-empty">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <h5>No Past Changes Recorded</h5>
                    <p>
                        No previous modifications found for <strong>${escapeHtml(fieldFriendlyName)}</strong>.
                        ${currentVal ? `<br><span style="display:inline-block; margin-top:8px; font-weight:700; color:#1e293b; background:#e2e8f0; padding:3px 8px; border-radius:5px;">Current: ${escapeHtml(currentVal)}</span>` : ''}
                    </p>
                </div>
            `;
            return;
        }

        let html = '';
        filtered.forEach(item => {
            const isCreation = item.field_name === 'created';
            html += `
                <div class="history-item-card">
                    <div class="history-card-header">
                        <span class="history-field-pill field-${escapeHtml(item.field_name)}">
                            <i class="fa-solid ${getFieldIcon(item.field_name)}"></i>
                            ${escapeHtml(item.field_label || item.field_name)}
                        </span>
                        <div class="history-time-meta">
                            <span class="history-user-tag" title="Changed by staff">
                                <i class="fa-solid fa-user-circle"></i> ${escapeHtml(item.user_name)}
                            </span>
                            <span>•</span>
                            <span title="${escapeHtml(item.created_at)}">${escapeHtml(item.created_at)}</span>
                            ${item.time_ago ? `<span style="color: #94a3b8; font-size: 10.5px;">(${escapeHtml(item.time_ago)})</span>` : ''}
                        </div>
                    </div>

                    ${isCreation ? `
                        <div class="history-diff-grid" style="background: #f0fdf4; border-color: #86efac;">
                            <div class="history-val-box">
                                <span class="history-val-title" style="color: #166534;">Registration Event</span>
                                <div class="history-val-text" style="color: #166534; font-size: 13px;">
                                    ${escapeHtml(item.new_display)}
                                </div>
                            </div>
                        </div>
                    ` : `
                        <div class="history-diff-grid">
                            <div class="history-val-box history-val-old">
                                <span class="history-val-title">Previous Value</span>
                                <div class="history-val-text">
                                    ${escapeHtml(item.old_display || item.old_value || '-')}
                                </div>
                            </div>
                            <div class="history-diff-arrow">
                                <i class="fa-solid fa-arrow-right"></i>
                            </div>
                            <div class="history-val-box history-val-new">
                                <span class="history-val-title">Updated Value</span>
                                <div class="history-val-text">
                                    ${escapeHtml(item.new_display || item.new_value || '-')}
                                </div>
                            </div>
                        </div>
                    `}

                    ${item.remarks ? `
                        <div class="history-item-remarks">
                            <i class="fa-solid fa-comment-dots" style="margin-right: 4px; color: #94a3b8;"></i>
                            ${escapeHtml(item.remarks)}
                        </div>
                    ` : ''}
                </div>
            `;
        });

        body.innerHTML = html;
    }

    function getFieldIcon(fieldName) {
        switch (fieldName) {
            case 'work_status':
            case 'status':
                return 'fa-signal';
            case 'assigned_employee_id':
            case 'operator':
                return 'fa-user-gear';
            case 'rate':
            case 'total_amount':
                return 'fa-indian-rupee-sign';
            case 'follow_up_date':
            case 'followup_date':
                return 'fa-calendar-days';
            case 'sub_service':
                return 'fa-layer-group';
            case 'created':
                return 'fa-circle-plus';
            default:
                return 'fa-pen-to-square';
        }
    }

    function showError(msg) {
        const body = document.getElementById('fhist-body');
        if (body) {
            body.innerHTML = `
                <div class="field-history-empty" style="color: #dc2626;">
                    <i class="fa-solid fa-triangle-exclamation" style="color: #f87171;"></i>
                    <h5 style="color: #991b1b;">Unable to load history</h5>
                    <p style="color: #b91c1c;">${escapeHtml(msg)}</p>
                </div>
            `;
        }
    }

    window._switchHistoryTab = function (tabKey) {
        currentField = tabKey;
        const tabs = document.querySelectorAll('.field-history-tab');
        tabs.forEach(t => {
            if (t.dataset.tab === tabKey) {
                t.classList.add('active');
            } else {
                t.classList.remove('active');
            }
        });

        if (currentAppId && cachedData[currentAppId]) {
            renderTimelineCards(cachedData[currentAppId].histories || [], cachedData[currentAppId].application || {});
        }
    };

    // Public API
    window.openFieldHistory = openFieldHistory;
    window.closeFieldHistoryModal = closeFieldHistoryModal;
})();
