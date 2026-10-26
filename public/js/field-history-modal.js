/**
 * Maha E-Seva ERP - Field Change History & Audit Trail Popover
 * Handles cell-level history icons and field-specific audit popups anchored directly to the clicked cell.
 */

(function () {
    'use strict';

    let currentAppId = null;
    let currentField = 'all';
    let cachedData = {};
    let activeTriggerBtn = null;
    let repositionListener = null;

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
                                <span id="fhist-app-title">Change History</span>
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
                            0 changes recorded
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

    function positionHistoryPopover(dialog, triggerBtn) {
        if (!dialog) return;
        // Keep dialog centered inside flex backdrop
        dialog.style.top = '';
        dialog.style.left = '';
        dialog.style.transform = '';
        dialog.style.maxHeight = '85vh';
    }

    function bindPopoverReposition(dialog, triggerBtn) {
        unbindPopoverReposition();
        // Centered modal doesn't need reposition listeners on scroll
    }

    function unbindPopoverReposition() {
        if (repositionListener) {
            window.removeEventListener('scroll', repositionListener, true);
            window.removeEventListener('resize', repositionListener);
            repositionListener = null;
        }
    }

    function openFieldHistory(appId, fieldName, event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (!appId) return;

        currentAppId = appId;
        activeTriggerBtn = event ? (event.currentTarget || (event.target ? event.target.closest('.btn-cell-history') : null)) : null;

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
        const dialog = backdrop.querySelector('.field-history-dialog');
        backdrop.classList.add('active');

        // Initial anchored position
        positionHistoryPopover(dialog, activeTriggerBtn);
        bindPopoverReposition(dialog, activeTriggerBtn);

        // Show loading state
        const body = document.getElementById('fhist-body');
        body.innerHTML = `
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 30px; color: #64748b; gap: 8px;">
                <i class="fa-solid fa-spinner fa-spin" style="font-size: 24px; color: #0284c7;"></i>
                <span style="font-size: 12px; font-weight: 600;">Fetching change history...</span>
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
            showError('Unable to load history. Please try again.');
        });
    }

    function closeFieldHistoryModal() {
        unbindPopoverReposition();
        activeTriggerBtn = null;
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
        `;

        // 2. Tab Bar
        const tabsEl = document.getElementById('fhist-tabs');
        const tabDefinitions = [
            { key: 'all', label: 'All', icon: 'fa-list-check' },
            { key: 'work_status', label: 'Status', icon: 'fa-signal' },
            { key: 'assigned_employee_id', label: 'Operator', icon: 'fa-user-gear' },
            { key: 'rate', label: 'Rate', icon: 'fa-indian-rupee-sign' },
            { key: 'follow_up_date', label: 'Follow-Up', icon: 'fa-calendar-days' },
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

        // Reposition accurately after DOM rendered
        setTimeout(() => {
            const dialog = document.querySelector('.field-history-dialog');
            positionHistoryPopover(dialog, activeTriggerBtn);
        }, 15);
    }

    function renderTimelineCards(allHistories, app) {
        const body = document.getElementById('fhist-body');
        const footerInfo = document.getElementById('fhist-footer-info');

        let filtered = allHistories;
        if (currentField && currentField !== 'all') {
            filtered = allHistories.filter(h => h.field_name === currentField);
        }

        footerInfo.textContent = `${filtered.length} change record${filtered.length === 1 ? '' : 's'}`;

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
                <div class="field-history-empty" style="padding: 24px 14px;">
                    <i class="fa-solid fa-clock-rotate-left" style="font-size: 26px;"></i>
                    <h5 style="font-size: 13px;">No Past Changes Recorded</h5>
                    <p style="font-size: 11.5px;">
                        No previous modifications for <strong>${escapeHtml(fieldFriendlyName)}</strong>.
                        ${currentVal ? `<br><span style="display:inline-block; margin-top:6px; font-weight:700; color:#1e293b; background:#e2e8f0; padding:2px 7px; border-radius:4px;">Current: ${escapeHtml(currentVal)}</span>` : ''}
                    </p>
                </div>
            `;
            return;
        }

        let html = '';
        filtered.forEach(item => {
            const isCreation = item.field_name === 'created';
            html += `
                <div class="history-item-card" style="padding: 9px 11px;">
                    <div class="history-card-header">
                        <span class="history-field-pill field-${escapeHtml(item.field_name)}">
                            <i class="fa-solid ${getFieldIcon(item.field_name)}"></i>
                            ${escapeHtml(item.field_label || item.field_name)}
                        </span>
                        <div class="history-time-meta" style="font-size: 11px;">
                            <span class="history-user-tag" title="Changed by staff">
                                <i class="fa-solid fa-user-circle"></i> ${escapeHtml(item.user_name)}
                            </span>
                            <span>•</span>
                            <span title="${escapeHtml(item.created_at)}">${escapeHtml(item.created_at)}</span>
                        </div>
                    </div>

                    ${isCreation ? `
                        <div class="history-diff-vertical" style="background: #f0fdf4; border: 1px solid #86efac; border-radius: 8px; padding: 8px 12px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span class="history-val-title" style="color: #166534; font-weight: 700; font-size: 10.5px;">Registration Event</span>
                                <span style="color: #166534; font-size: 11.5px; font-weight: 700; background: #dcfce7; padding: 2px 8px; border-radius: 4px; border: 1px solid #86efac;">
                                    ${escapeHtml(item.new_display)}
                                </span>
                            </div>
                        </div>
                    ` : `
                        <div class="history-diff-vertical" style="display: flex; flex-direction: column; gap: 6px; padding: 8px 12px; background: #f8fafc; border-radius: 8px; border: 1px dashed #cbd5e1;">
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                                <span class="history-val-title" style="font-size: 10.5px; font-weight: 700; color: #64748b; text-transform: uppercase;">Previous</span>
                                <span style="font-size: 11.5px; font-weight: 600; color: #dc2626; text-decoration: line-through; background: #fee2e2; padding: 2px 8px; border-radius: 4px; border: 1px solid #fca5a5;">
                                    ${escapeHtml(item.old_display || item.old_value || '-')}
                                </span>
                            </div>
                            <div style="text-align: center; color: #94a3b8; font-size: 11px; line-height: 1; margin: -1px 0;">
                                <i class="fa-solid fa-arrow-down"></i>
                            </div>
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                                <span class="history-val-title" style="font-size: 10.5px; font-weight: 700; color: #166534; text-transform: uppercase;">Updated</span>
                                <span style="font-size: 11.5px; font-weight: 700; color: #166534; background: #dcfce7; padding: 2px 8px; border-radius: 4px; border: 1px solid #86efac;">
                                    ${escapeHtml(item.new_display || item.new_value || '-')}
                                </span>
                            </div>
                        </div>
                    `}

                    ${item.remarks ? `
                        <div class="history-item-remarks" style="font-size: 11px;">
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
                <div class="field-history-empty" style="color: #dc2626; padding: 20px;">
                    <i class="fa-solid fa-triangle-exclamation" style="color: #f87171;"></i>
                    <h5 style="color: #991b1b; font-size: 13px;">Unable to load history</h5>
                    <p style="color: #b91c1c; font-size: 11px;">${escapeHtml(msg)}</p>
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
            setTimeout(() => {
                const dialog = document.querySelector('.field-history-dialog');
                positionHistoryPopover(dialog, activeTriggerBtn);
            }, 10);
        }
    };

    // Public API
    window.openFieldHistory = openFieldHistory;
    window.closeFieldHistoryModal = closeFieldHistoryModal;
})();
