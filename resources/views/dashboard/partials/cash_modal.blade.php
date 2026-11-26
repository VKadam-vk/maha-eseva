<!-- Quick Cash Receive & Spend Modal -->
<div class="modal fade" id="cashAdjustmentModal" tabindex="-1" aria-labelledby="cashAdjustmentModalLabel" aria-hidden="true" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 1055; overflow-x: hidden; overflow-y: auto; outline: 0;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px; margin: 1.75rem auto;">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25), 0 10px 10px -5px rgba(0, 0, 0, 0.08); overflow: hidden; background: #ffffff;">
            <div id="cashModalHeader" style="padding: 16px 20px; color: #ffffff; display: flex; align-items: center; justify-content: space-between; background: #10b981;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i id="cashModalIcon" class="fa-solid fa-circle-plus" style="font-size: 20px;"></i>
                    <h5 class="modal-title" id="cashAdjustmentModalLabel" style="font-size: 16px; font-weight: 700; margin: 0;">Receive RS (+ पैसे जमा)</h5>
                </div>
                <button type="button" onclick="closeCashModal()" aria-label="Close" style="filter: brightness(0) invert(1); opacity: 0.85; cursor: pointer; border: none; background: transparent; font-size: 18px; color: #fff;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            
            <form id="cashAdjustmentForm" onsubmit="submitCashAdjustment(event)">
                @csrf
                <input type="hidden" name="type" id="cash_adj_type" value="RECEIVE">

                <div class="modal-body" style="padding: 22px 20px 18px;">
                    <!-- Amount Input -->
                    <div style="margin-bottom: 18px;">
                        <label for="cash_adj_amount" style="font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">
                            रक्कम (Amount in ₹) <span style="color: #ef4444;">*</span>
                        </label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); font-weight: 800; font-size: 18px; color: #64748b;">₹</span>
                            <input type="number" step="any" min="0.01" name="amount" id="cash_adj_amount" class="form-control" placeholder="उदा. 5, 10, 100" required
                                   style="padding-left: 35px; height: 46px; font-size: 18px; font-weight: 800; border-radius: 8px; border: 2px solid #cbd5e1; width: 100%;">
                        </div>
                    </div>

                    <!-- Reason Input -->
                    <div style="margin-bottom: 6px;">
                        <label for="cash_adj_reason" style="font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">
                            कारण / तपशील (Reason / Note) <span style="font-size: 11px; font-weight: 500; color: #94a3b8;">(ऐच्छिक)</span>
                        </label>
                        <input type="text" name="reason" id="cash_adj_reason" class="form-control" placeholder="उदा. Xerox / चहा / कागद / इतर..."
                               style="height: 42px; font-size: 13.5px; border-radius: 8px; border: 1.5px solid #cbd5e1; width: 100%;">
                    </div>
                </div>

                <div class="modal-footer" style="padding: 12px 20px 16px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-secondary" onclick="closeCashModal()" style="font-weight: 600; padding: 8px 16px; border-radius: 6px; font-size: 13px;">
                        रद्द करा (Cancel)
                    </button>
                    <button type="submit" id="cashSubmitBtn" class="btn" style="background: #10b981; color: #ffffff; font-weight: 700; padding: 8px 22px; border-radius: 6px; font-size: 13.5px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-check"></i> <span id="cashSubmitBtnText">जतन करा (Save)</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="cashModalBackdrop" onclick="closeCashModal()" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.6); z-index: 1050; backdrop-filter: blur(2px);"></div>

<script>
function openCashModal(type) {
    const modal = document.getElementById('cashAdjustmentModal');
    const backdrop = document.getElementById('cashModalBackdrop');
    const typeInput = document.getElementById('cash_adj_type');
    const header = document.getElementById('cashModalHeader');
    const label = document.getElementById('cashAdjustmentModalLabel');
    const icon = document.getElementById('cashModalIcon');
    const submitBtn = document.getElementById('cashSubmitBtn');
    const amountInput = document.getElementById('cash_adj_amount');
    const reasonInput = document.getElementById('cash_adj_reason');

    if (!modal) return;

    typeInput.value = type;
    amountInput.value = '';
    reasonInput.value = '';

    if (type === 'RECEIVE') {
        header.style.background = '#10b981';
        label.innerHTML = 'Receive RS (+ पैसे जमा)';
        icon.className = 'fa-solid fa-circle-plus';
        submitBtn.style.background = '#10b981';
        amountInput.style.borderColor = '#10b981';
    } else {
        header.style.background = '#ef4444';
        label.innerHTML = 'Spend RS (- खर्च नोंदवा)';
        icon.className = 'fa-solid fa-circle-minus';
        submitBtn.style.background = '#ef4444';
        amountInput.style.borderColor = '#ef4444';
    }

    modal.style.display = 'block';
    backdrop.style.display = 'block';
    document.body.style.overflow = 'hidden';

    setTimeout(() => {
        if (amountInput) amountInput.focus();
    }, 150);
}

function closeCashModal() {
    const modal = document.getElementById('cashAdjustmentModal');
    const backdrop = document.getElementById('cashModalBackdrop');
    if (modal) {
        modal.style.display = 'none';
    }
    if (backdrop) {
        backdrop.style.display = 'none';
    }
    document.body.style.overflow = '';
}

function submitCashAdjustment(e) {
    e.preventDefault();
    const submitBtn = document.getElementById('cashSubmitBtn');
    const btnText = document.getElementById('cashSubmitBtnText');
    const amount = document.getElementById('cash_adj_amount').value;
    const reason = document.getElementById('cash_adj_reason').value;
    const type = document.getElementById('cash_adj_type').value;

    if (!amount || parseFloat(amount) <= 0) {
        alert('कृपया वैध रक्कम टाका.');
        return;
    }

    submitBtn.disabled = true;
    btnText.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> सेव्ह करत आहे...';

    fetch("{{ route('cash_adjustments.store') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            type: type,
            amount: amount,
            reason: reason
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            closeCashModal();
            window.location.reload();
        } else {
            alert(data.message || 'काहीतरी त्रुटी आली. कृपया पुन्हा प्रयत्न करा.');
            submitBtn.disabled = false;
            btnText.innerHTML = 'जतन करा (Save)';
        }
    })
    .catch(err => {
        console.error(err);
        alert('सर्व्हरशी संपर्क होऊ शकला नाही.');
        submitBtn.disabled = false;
        btnText.innerHTML = 'जतन करा (Save)';
    });
}

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCashModal();
    }
});
</script>
