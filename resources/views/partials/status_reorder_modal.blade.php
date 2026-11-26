<!-- Drag & Drop Status Reorder Modal -->
<div class="modal fade" id="statusReorderModal" tabindex="-1" aria-labelledby="statusReorderModalLabel" aria-hidden="true" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 1060; overflow-x: hidden; overflow-y: auto; outline: 0;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px; margin: 1.75rem auto;">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25), 0 10px 10px -5px rgba(0, 0, 0, 0.08); overflow: hidden; background: #ffffff;">
            <div style="padding: 16px 20px; color: #ffffff; display: flex; align-items: center; justify-content: space-between; background: #4f46e5;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-arrow-down-up-across-line" style="font-size: 18px;"></i>
                    <h5 class="modal-title" id="statusReorderModalLabel" style="font-size: 15px; font-weight: 700; margin: 0;">Reorder Status Sequence (स्टेटसचा क्रम बदला)</h5>
                </div>
                <button type="button" onclick="closeStatusReorderModal()" aria-label="Close" style="filter: brightness(0) invert(1); opacity: 0.85; cursor: pointer; border: none; background: transparent; font-size: 18px; color: #fff;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="modal-body" style="padding: 18px 20px; background: #f8fafc;">
                <p style="font-size: 12px; color: #64748b; margin-bottom: 14px; line-height: 1.4;">
                    <i class="fa-solid fa-hand-pointer" style="color: #4f46e5;"></i> माऊसने ड्रॅग करून (Drag & Drop) किंवा <strong>▲ / ▼</strong> बटनांनी स्टेटस वर-खाली सरकवून हवा तो क्रम ठरवा.
                </p>

                <!-- Sortable Status List Container -->
                <div id="status-reorder-container" style="max-height: 380px; overflow-y: auto; padding-right: 4px;">
                    <!-- Rendered via JS -->
                </div>
            </div>

            <div class="modal-footer" style="padding: 12px 20px 16px; background: #ffffff; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; gap: 10px;">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetStatusOrderToDefault()" style="font-size: 11.5px; font-weight: 600; padding: 6px 12px; border-radius: 6px; border: 1px solid #cbd5e1; color: #64748b; background: transparent; cursor: pointer;">
                    <i class="fa-solid fa-rotate-left"></i> मूळ क्रमाने ठेवा
                </button>
                <div style="display: flex; gap: 8px;">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="closeStatusReorderModal()" style="font-size: 12.5px; font-weight: 600; padding: 7px 14px; border-radius: 6px;">
                        रद्द करा
                    </button>
                    <button type="button" class="btn btn-sm" onclick="saveStatusOrderFromModal()" style="background: #4f46e5; color: #ffffff; font-weight: 700; font-size: 12.5px; padding: 7px 18px; border-radius: 6px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-check"></i> Save Order
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="statusReorderBackdrop" onclick="closeStatusReorderModal()" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.6); z-index: 1055; backdrop-filter: blur(2px);"></div>

<style>
.status-sortable-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 9px 12px;
    margin-bottom: 7px;
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    cursor: grab;
    user-select: none;
    transition: background 0.15s, border-color 0.15s, transform 0.15s, box-shadow 0.15s;
}
.status-sortable-item:hover {
    border-color: #818cf8;
    background: #f5f3ff;
    box-shadow: 0 2px 4px rgba(79, 70, 229, 0.08);
}
.status-sortable-item.dragging {
    opacity: 0.45;
    background: #e0e7ff;
    border-color: #6366f1;
    cursor: grabbing;
}
.status-sortable-item .btn-move-btn {
    width: 28px;
    height: 28px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    font-size: 11px;
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    border-radius: 5px;
    color: #475569;
    cursor: pointer;
    transition: all 0.15s;
}
.status-sortable-item .btn-move-btn:hover {
    background: #4f46e5;
    color: #ffffff;
    border-color: #4f46e5;
}
</style>

<script>
const SMART_STATUS_DEFAULT_LIST = [
    'IN_PROGRESS',
    'PENDING',
    'WAITING FOR DOCS',
    'SUBMITTED',
    'READY',
    'DELIVERED',
    'COMPLETED',
    'REJECTED'
];

function getGlobalStatusOrder() {
    try {
        const raw = localStorage.getItem('smart_crm_status_order');
        if (raw) {
            const parsed = JSON.parse(raw);
            if (Array.isArray(parsed) && parsed.length > 0) {
                const set = new Set(parsed);
                SMART_STATUS_DEFAULT_LIST.forEach(st => set.add(st));
                return Array.from(set);
            }
        }
    } catch (e) {}
    return [...SMART_STATUS_DEFAULT_LIST];
}

function openStatusReorderModal() {
    const modal = document.getElementById('statusReorderModal');
    const backdrop = document.getElementById('statusReorderBackdrop');
    const container = document.getElementById('status-reorder-container');
    if (!modal || !container) return;

    const list = getGlobalStatusOrder();
    container.innerHTML = '';

    list.forEach(st => {
        const item = document.createElement('div');
        item.className = 'status-sortable-item';
        item.draggable = true;
        item.setAttribute('data-status', st);
        item.innerHTML = `
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="color: #94a3b8; font-size: 14px; cursor: grab;"><i class="fa-solid fa-grip-vertical"></i></span>
                <span style="font-weight: 800; font-size: 12px; color: #1e293b; letter-spacing: 0.3px;">${escapeHtml(st)}</span>
            </div>
            <div style="display: flex; align-items: center; gap: 4px;">
                <button type="button" class="btn-move-btn" onclick="moveStatusDOMItem(this, -1)" title="Move Up (वर घ्या)">
                    <i class="fa-solid fa-arrow-up"></i>
                </button>
                <button type="button" class="btn-move-btn" onclick="moveStatusDOMItem(this, 1)" title="Move Down (खाली घ्या)">
                    <i class="fa-solid fa-arrow-down"></i>
                </button>
            </div>
        `;

        item.addEventListener('dragstart', handleStatusDragStart);
        item.addEventListener('dragend', handleStatusDragEnd);
        item.addEventListener('dragover', handleStatusDragOver);

        container.appendChild(item);
    });

    modal.style.display = 'block';
    if (backdrop) backdrop.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeStatusReorderModal() {
    const modal = document.getElementById('statusReorderModal');
    const backdrop = document.getElementById('statusReorderBackdrop');
    if (modal) modal.style.display = 'none';
    if (backdrop) backdrop.style.display = 'none';
    document.body.style.overflow = '';
}

let draggedStatusElement = null;

function handleStatusDragStart(e) {
    draggedStatusElement = this;
    this.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
    try {
        e.dataTransfer.setData('text/plain', this.getAttribute('data-status'));
    } catch (err) {}
}

function handleStatusDragEnd() {
    if (draggedStatusElement) {
        draggedStatusElement.classList.remove('dragging');
    }
    draggedStatusElement = null;
}

function handleStatusDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    if (!draggedStatusElement || draggedStatusElement === this) return;

    const container = document.getElementById('status-reorder-container');
    if (!container) return;

    const rect = this.getBoundingClientRect();
    const next = (e.clientY - rect.top) / (rect.bottom - rect.top) > 0.5;
    container.insertBefore(draggedStatusElement, next && this.nextSibling || this);
}

function moveStatusDOMItem(btn, direction) {
    const item = btn.closest('.status-sortable-item');
    if (!item) return;
    const parent = item.parentNode;
    if (!parent) return;

    if (direction === -1) {
        const prev = item.previousElementSibling;
        if (prev) {
            parent.insertBefore(item, prev);
        }
    } else if (direction === 1) {
        const next = item.nextElementSibling;
        if (next) {
            parent.insertBefore(item, next.nextSibling);
        }
    }
}

function resetStatusOrderToDefault() {
    if (!confirm('मूळ डीफॉल्ट क्रम पुन्हा सेट करायचा आहे का?')) return;
    localStorage.removeItem('smart_crm_status_order');
    openStatusReorderModal();
    if (typeof refreshAllStatusDropdowns === 'function') {
        refreshAllStatusDropdowns();
    }
}

function saveStatusOrderFromModal() {
    const container = document.getElementById('status-reorder-container');
    if (!container) return;

    const items = container.querySelectorAll('.status-sortable-item');
    const newOrder = [];
    items.forEach(el => {
        const st = el.getAttribute('data-status');
        if (st && !newOrder.includes(st)) {
            newOrder.push(st);
        }
    });

    if (newOrder.length > 0) {
        localStorage.setItem('smart_crm_status_order', JSON.stringify(newOrder));
    }

    if (typeof refreshAllStatusDropdowns === 'function') {
        refreshAllStatusDropdowns();
    }

    closeStatusReorderModal();

    if (typeof showToast === 'function') {
        showToast('स्टेटसचा क्रम यशस्वीरीत्या बदलला!', 'success');
    } else {
        const msg = document.createElement('div');
        msg.textContent = '✅ स्टेटसचा क्रम यशस्वीरीत्या बदलला!';
        msg.style.cssText = 'position: fixed; bottom: 20px; right: 20px; background: #10b981; color: #fff; padding: 10px 18px; border-radius: 8px; font-weight: 700; font-size: 13px; z-index: 9999; box-shadow: 0 4px 6px rgba(0,0,0,0.15);';
        document.body.appendChild(msg);
        setTimeout(() => msg.remove(), 2500);
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeStatusReorderModal();
    }
});
</script>
