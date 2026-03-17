@extends('layouts.scanner')

@section('title', 'Ticket Scanner')

@push('styles')
<style>
    .scanner-page {
        max-width: 560px;
        margin: 0 auto;
        padding: 0 0 2rem;
    }
    .scanner-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .scanner-card-header {
        padding: 1rem 1.25rem;
        background: #0f172a;
        color: #e2e8f0;
        font-weight: 600;
        font-size: 1rem;
    }
    .scanner-card-body {
        padding: 1rem 1.25rem;
    }
    #reader {
        border: none !important;
        padding: 0 !important;
    }
    #reader__scan_region {
        background: #000 !important;
    }
    #reader__scan_region video {
        width: 100% !important;
        max-height: 50vh !important;
        object-fit: cover !important;
    }
    #reader__dashboard {
        padding: 0.75rem 1rem !important;
        background: #f8fafc !important;
        border-top: 1px solid #e2e8f0 !important;
    }
    #reader__dashboard_section_csr button {
        border-radius: 8px !important;
        font-weight: 500 !important;
    }
    .scanner-result {
        margin-top: 1rem;
        padding: 1rem 1.25rem;
        border-radius: 10px;
        font-size: 0.9375rem;
        display: none;
    }
    .scanner-result.show {
        display: block;
    }
    .scanner-result.valid {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }
    .scanner-result.invalid {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
    .scanner-result.pending {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }
    .scanner-result-title {
        font-weight: 700;
        margin-bottom: 0.35rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .scanner-result-holder {
        margin-top: 0.5rem;
        padding-top: 0.5rem;
        border-top: 1px solid rgba(0,0,0,0.08);
        font-size: 0.875rem;
    }
    .scanner-holder-field-label {
        font-weight: 600;
        font-size: 0.8rem;
        color: #475569;
        margin-right: 0.25rem;
    }
    .scanner-holder-field-value {
        font-size: 0.85rem;
    }
    .scanner-events-list {
        margin-top: 0.5rem;
        padding-top: 0.5rem;
        border-top: 1px dashed rgba(15,23,42,0.15);
        font-size: 0.85rem;
    }
    .scanner-events-list-title {
        font-weight: 600;
        margin-bottom: 0.25rem;
        color: #0f172a;
    }
    .scanner-events-list-item {
        margin-bottom: 0.35rem;
    }
    @media (max-width: 576px) {
        .scanner-page {
            padding-left: 0.25rem;
            padding-right: 0.25rem;
        }
        .scanner-card-header,
        .scanner-card-body {
            padding: 0.875rem 1rem;
        }
        #reader__scan_region video {
            max-height: 45vh !important;
        }
    }
</style>
@endpush

@section('content')
    <div class="scanner-page">
        <div class="scanner-card">
            <div class="scanner-card-header">
                <i class="bi bi-qr-code-scan"></i> Scan participant ticket
            </div>
            <div class="scanner-card-body">
                <p class="text-muted small mb-3">Point the camera at the participant's QR code. Validation is instant and not stored.</p>
                <button type="button" id="btnStartScan" class="btn btn-primary mb-3">
                    <i class="bi bi-camera-video"></i> Start scanning
                </button>
                <div id="reader"></div>
                <div id="scannerResult" class="scanner-result" role="alert"></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const resultEl = document.getElementById('scannerResult');
            const startButton = document.getElementById('btnStartScan');
            const validateUrl = '{{ route("admin.scanner.validate") }}';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

            let lastScannedPayload = null;
            let cooldownUntil = 0;
            let scanner = null;
            let scannerStarted = false;

            function showResult(valid, message, holder, order) {
                resultEl.classList.remove('valid', 'invalid', 'pending', 'show');
                if (valid === true) {
                    resultEl.classList.add('valid', 'show');
                    let html = '<div class="scanner-result-title"><i class="bi bi-check-circle-fill"></i> Valid ticket</div>' +
                        '<div>' + escapeHtml(message) + '</div>';

                    if (order && order.reference) {
                        html += '<div class="scanner-result-holder">';
                        html += '<div><span class="scanner-holder-field-label">Reference:</span><span class="scanner-holder-field-value">' + escapeHtml(order.reference) + '</span></div>';
                        html += '</div>';
                    }

                    if (holder) {
                        html += '<div class="scanner-result-holder">';
                        html += '<div><span class="scanner-holder-field-label">Name:</span><span class="scanner-holder-field-value">' + escapeHtml(holder.full_name || '') + '</span></div>';
                        if (holder.email) {
                            html += '<div><span class="scanner-holder-field-label">Email:</span><span class="scanner-holder-field-value">' + escapeHtml(holder.email) + '</span></div>';
                        }
                        if (holder.gender) {
                            html += '<div><span class="scanner-holder-field-label">Gender:</span><span class="scanner-holder-field-value">' + escapeHtml(holder.gender) + '</span></div>';
                        }
                        if (holder.nric_passport) {
                            html += '<div><span class="scanner-holder-field-label">NRIC/Passport:</span><span class="scanner-holder-field-value">' + escapeHtml(holder.nric_passport) + '</span></div>';
                        }
                        if (holder.contact_number) {
                            html += '<div><span class="scanner-holder-field-label">Contact:</span><span class="scanner-holder-field-value">' + escapeHtml(holder.contact_number) + '</span></div>';
                        }
                        if (holder.company_name) {
                            html += '<div><span class="scanner-holder-field-label">Company:</span><span class="scanner-holder-field-value">' + escapeHtml(holder.company_name) + '</span></div>';
                        }
                        html += '</div>';
                    }

                    if (order && Array.isArray(order.events) && order.events.length > 0) {
                        html += '<div class="scanner-events-list">';
                        if (Array.isArray(order.events) && order.events.length > 0) {
                            html += '<div class="scanner-events-list-title">Event participation</div>';
                        }
                        (order.events || []).forEach(function(ev, idx) {
                            if (!ev) return;
                            const eventName = ev.event_name || '-';
                            const eventCategory = ev.event_category || '-';
                            const ticketType = ev.ticket_type || '-';
                            const qty = ev.quantity || 0;
                            html += '<div class="scanner-events-list-item">';
                            html += '<div><span class="scanner-holder-field-label">Event name:</span><span class="scanner-holder-field-value">' + escapeHtml(eventName) + '</span></div>';
                            html += '<div><span class="scanner-holder-field-label">Event category:</span><span class="scanner-holder-field-value">' + escapeHtml(eventCategory) + '</span></div>';
                            html += '<div><span class="scanner-holder-field-label">Ticket type:</span><span class="scanner-holder-field-value">' + escapeHtml(ticketType) + ' (x' + qty + ')</span></div>';
                            html += '</div>';
                        });
                        html += '</div>';
                    }

                    resultEl.innerHTML = html;
                } else if (valid === false) {
                    resultEl.classList.add('invalid', 'show');
                    resultEl.innerHTML = '<div class="scanner-result-title"><i class="bi bi-x-circle-fill"></i> Invalid</div><div>' + escapeHtml(message) + '</div>';
                } else {
                    resultEl.classList.add('pending', 'show');
                    resultEl.innerHTML = '<div class="scanner-result-title"><i class="bi bi-hourglass-split"></i> Checking...</div><div>' + escapeHtml(message) + '</div>';
                }
            }

            function escapeHtml(text) {
                if (!text) return '';
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            function validatePayload(payload) {
                payload = (payload || '').trim();
                if (!payload) {
                    showResult(false, 'No data to validate.');
                    return;
                }
                if (Date.now() < cooldownUntil && payload === lastScannedPayload) {
                    return;
                }
                lastScannedPayload = payload;
                showResult(null, 'Validating...');

                fetch(validateUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ payload: payload })
                })
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        showResult(data.valid, data.message || (data.valid ? 'Ticket is valid.' : 'Invalid ticket.'), data.holder || null, data.order || null);
                        cooldownUntil = Date.now() + 2500;
                    })
                    .catch(function() {
                        showResult(false, 'Network error. Please try again.');
                    });
            }

            startButton.addEventListener('click', function() {
                if (scannerStarted) {
                    return;
                }
                scannerStarted = true;
                showResult(null, 'Camera starting...');

                const config = {
                    fps: 10,
                    qrbox: { width: 220, height: 220 },
                    aspectRatio: 1
                };
                scanner = new Html5QrcodeScanner('reader', config, false);
                scanner.render(function(decodedText) {
                    validatePayload(decodedText);
                }, function() {});
            });
        });
    </script>
@endpush
