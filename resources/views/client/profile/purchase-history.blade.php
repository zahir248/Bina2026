@extends('layouts.client.app')

@section('title', 'Purchase History - BINA')

@section('content')
<div class="profile-page-container">
    <div class="container">
        <div class="profile-page-content-wrapper profile-purchase-history-wrapper">
            <div class="profile-page-header">
                <h1 class="profile-page-title">Purchase History</h1>
                <p class="profile-page-subtitle">View your past orders.</p>
            </div>

            <nav class="purchase-history-tabs" aria-label="Order status">
                <a href="{{ route('profile.purchaseHistory', ['tab' => 'to_pay']) }}" class="purchase-history-tab {{ $activeTab === 'to_pay' ? 'active' : '' }}">
                    To Pay
                    @if(($counts['to_pay'] ?? 0) > 0)
                        <span class="purchase-history-tab-count">{{ $counts['to_pay'] }}</span>
                    @endif
                </a>
                <a href="{{ route('profile.purchaseHistory', ['tab' => 'completed']) }}" class="purchase-history-tab {{ $activeTab === 'completed' ? 'active' : '' }}">
                    Completed
                    @if(($counts['completed'] ?? 0) > 0)
                        <span class="purchase-history-tab-count">{{ $counts['completed'] }}</span>
                    @endif
                </a>
                <a href="{{ route('profile.purchaseHistory', ['tab' => 'refund']) }}" class="purchase-history-tab {{ $activeTab === 'refund' ? 'active' : '' }}">
                    Refund
                    @if(($counts['refund'] ?? 0) > 0)
                        <span class="purchase-history-tab-count">{{ $counts['refund'] }}</span>
                    @endif
                </a>
                <a href="{{ route('profile.purchaseHistory', ['tab' => 'cancelled']) }}" class="purchase-history-tab {{ $activeTab === 'cancelled' ? 'active' : '' }}">
                    Cancelled
                    @if(($counts['cancelled'] ?? 0) > 0)
                        <span class="purchase-history-tab-count">{{ $counts['cancelled'] }}</span>
                    @endif
                </a>
            </nav>

            <div class="profile-section-card">
                <div class="profile-section-body">
                    @if($orders->isNotEmpty())
                        <div class="purchase-history-table-wrap">
                            <table class="purchase-history-table">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Reference</th>
                                        <th>Date</th>
                                        <th>Total (RM)</th>
                                        <th>Status</th>
                                        <th>Payment</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                        <tr>
                                            <td>{{ $orders->firstItem() + $loop->index }}</td>
                                            <td class="purchase-ref">{{ $order->stripe_payment_intent_id ?? '-' }}</td>
                                            <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                                            <td>{{ number_format($order->total_amount_cents / 100, 2) }}</td>
                                            <td>
                                                @if($activeTab === 'refund' && $order->refund_status)
                                                    <span class="purchase-status-badge {{ $order->refund_status === 'pending' ? 'purchase-status-pending' : 'purchase-status-refunded' }}">
                                                        {{ $order->refund_status === 'pending' ? 'Reviewing' : ucfirst($order->refund_status) }}
                                                    </span>
                                                @else
                                                    <span class="purchase-status-badge purchase-status-{{ $order->status }}">
                                                        {{ ucfirst($order->status) }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>{{ $order->payment_method ? ucfirst($order->payment_method) : '-' }}</td>
                                            <td>
                                                <div class="purchase-history-actions-cell">
                                                    <button type="button" class="purchase-history-action-link purchase-history-btn-view" title="View order" aria-label="View order"
                                                        data-reference="{{ $order->stripe_payment_intent_id ?? $order->id }}"
                                                        data-modal-url="{{ route('profile.orderModal', $order) }}">View Detail</button>
                                                    @if($activeTab === 'to_pay')
                                                        <button type="button" class="purchase-history-action-link purchase-history-btn-repay" title="Make payment" aria-label="Make payment"
                                                            data-create-repay-url="{{ route('profile.order.createRepayIntent', $order) }}"
                                                            data-order-subtotal-cents="{{ $order->total_amount_cents }}"
                                                            data-order-amount-excludes-fee="{{ $order->amount_excludes_fee ? '1' : '0' }}"
                                                            data-order-payment-method="{{ strtolower($order->payment_method ?? '') }}">Make payment</button>
                                                        <button type="button" class="purchase-history-action-link purchase-history-btn-cancel-open" title="Cancel order" aria-label="Cancel order"
                                                            data-cancel-url="{{ route('profile.order.cancel', $order) }}">Cancel</button>
                                                    @endif
                                                    @if($activeTab === 'completed')
                                                        <a href="{{ route('profile.order.receipt', $order) }}" class="purchase-history-action-link" title="Download Receipt" aria-label="Download Receipt" download>Download Receipt</a>
                                                        @php
                                                            $holders = $order->ticket_holders_snapshot ?? [];
                                                            $qrUrls = [];
                                                            for ($i = 0; $i < count($holders); $i++) {
                                                                $qrUrls[] = route('profile.order.qrCode', ['order' => $order, 'index' => $i]);
                                                            }
                                                        @endphp
                                                        @if(count($qrUrls) > 0)
                                                            <button type="button" class="purchase-history-action-link purchase-history-btn-download-qr-codes" title="Download all attendance QR codes (one per ticket holder)" aria-label="Download QR Codes"
                                                                data-qr-urls="{{ json_encode($qrUrls) }}">Download QR Codes</button>
                                                        @endif
                                                        <button type="button" class="purchase-history-action-link purchase-history-btn-refund-open" title="Request Refund" aria-label="Request Refund"
                                                            data-refund-url="{{ route('profile.order.refund', $order) }}">Request Refund</button>
                                                    @elseif($activeTab === 'refund')
                                                        <a href="{{ route('profile.order.receipt', $order) }}" class="purchase-history-action-link" title="Download Receipt" aria-label="Download Receipt" download>Download Receipt</a>
                                                        @if($order->refund_status === 'rejected')
                                                            @php
                                                                $holders = $order->ticket_holders_snapshot ?? [];
                                                                $qrUrls = [];
                                                                for ($i = 0; $i < count($holders); $i++) {
                                                                    $qrUrls[] = route('profile.order.qrCode', ['order' => $order, 'index' => $i]);
                                                                }
                                                            @endphp
                                                            @if(count($qrUrls) > 0)
                                                                <button type="button" class="purchase-history-action-link purchase-history-btn-download-qr-codes" title="Download all attendance QR codes (one per ticket holder)" aria-label="Download QR Codes"
                                                                    data-qr-urls="{{ json_encode($qrUrls) }}">Download QR Codes</button>
                                                            @endif
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="purchase-history-pagination">
                            {{ $orders->links() }}
                        </div>
                    @else
                        <p class="purchase-history-empty">
                            @switch($activeTab)
                                @case('to_pay')
                                    No orders to pay.
                                    @break
                                @case('completed')
                                    No completed orders.
                                    @break
                                @case('refund')
                                    No refunded orders.
                                    @break
                                @case('cancelled')
                                    No cancelled orders.
                                    @break
                                @default
                                    You have no orders yet.
                            @endswitch
                        </p>
                    @endif
                </div>
            </div>

            <!-- Order detail modal (client) -->
            <div id="clientOrderDetailModal" class="client-order-modal" role="dialog" aria-modal="true" aria-labelledby="clientOrderDetailModalTitle" hidden>
                <div class="client-order-modal-overlay" data-close="clientOrderDetailModal"></div>
                <div class="client-order-modal-dialog">
                    <div class="client-order-modal-content">
                        <div class="client-order-modal-header">
                            <h2 class="client-order-modal-title" id="clientOrderDetailModalTitle">Reference: <span id="clientOrderDetailModalReference">-</span></h2>
                            <button type="button" class="client-order-modal-close" aria-label="Close" data-close="clientOrderDetailModal">&times;</button>
                        </div>
                        <div class="client-order-modal-body-wrap">
                            <div class="client-order-modal-loading" id="clientOrderDetailModalLoading">Loading…</div>
                            <div class="client-order-modal-body-content" id="clientOrderDetailModalContent"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Repay modal (To Pay) – same layout as checkout "Choose payment method" -->
            <div id="repay-modal" class="repay-modal" aria-hidden="true" role="dialog" aria-labelledby="repay-modal-title">
                <div class="repay-modal-backdrop"></div>
                <div class="repay-modal-content">
                    <button type="button" class="repay-modal-close" id="repay-modal-close" aria-label="Close">&times;</button>
                    <div class="repay-modal-panels">
                        <div class="repay-modal-left">
                            <h2 id="repay-modal-title" class="repay-modal-title">Choose payment method</h2>
                            <div id="repay-modal-step-choose" class="repay-modal-step">
                                <p class="repay-modal-text">Continue to Pay with:</p>
                                <div class="repay-modal-buttons repay-modal-primary-methods">
                                    <button type="button" class="repay-modal-btn" data-method="fpx">
                                        <span class="repay-modal-btn-icon">🏦</span>
                                        <span>FPX Online Banking</span>
                                    </button>
                                    <button type="button" class="repay-modal-btn" data-method="card">
                                        <span class="repay-modal-btn-icon">💳</span>
                                        <span>Credit / Debit Card</span>
                                    </button>
                                </div>
                                <p class="repay-modal-text repay-modal-change-label" id="repay-modal-change-label">Or change payment method</p>
                                <p class="repay-modal-change-alert" id="repay-modal-change-alert">If you select a different payment method, the current order will be cancelled and a new order will be created automatically with a new reference number. All order details (items, amounts, and buyer information) will be preserved.</p>
                                <div class="repay-modal-buttons repay-modal-change-methods" id="repay-modal-change-methods">
                                    <button type="button" class="repay-modal-btn" data-method="fpx" data-change="1">
                                        <span class="repay-modal-btn-icon">🏦</span>
                                        <span>FPX Online Banking</span>
                                    </button>
                                    <button type="button" class="repay-modal-btn" data-method="card" data-change="1">
                                        <span class="repay-modal-btn-icon">💳</span>
                                        <span>Credit / Debit Card</span>
                                    </button>
                                </div>
                            </div>
                            <div id="repay-modal-step-pay" class="repay-modal-step" style="display: none;">
                                <p id="repay-modal-continue-text" class="repay-modal-continue-text">Click the button below to continue to the secure payment page.</p>
                                <div id="repay-element-container"></div>
                                <div class="repay-modal-actions">
                                    <button type="button" id="repay-modal-back" class="repay-modal-back-btn">Back</button>
                                    <button type="button" id="repay-modal-pay-now" class="repay-modal-pay-btn">Continue to payment</button>
                                </div>
                                <p id="repay-modal-error" class="repay-modal-error" style="display: none;"></p>
                            </div>
                        </div>
                        <div class="repay-modal-right">
                            <p class="repay-modal-right-title">Order summary</p>
                            <div id="repay-modal-order-summary" class="repay-modal-order-summary">
                                <div class="repay-modal-summary-row">
                                    <span>Subtotal</span>
                                    <span id="repay-modal-summary-subtotal">—</span>
                                </div>
                                <div class="repay-modal-summary-row" id="repay-modal-summary-fee-row">
                                    <span title="Payment gateway and card processing charges">Payment processing fee</span>
                                    <span id="repay-modal-summary-fee">—</span>
                                </div>
                                <div id="repay-modal-fee-breakdown" class="repay-modal-fee-breakdown">
                                    @if($feeDomesticLabel ?? null)
                                        <p class="repay-modal-fee-breakdown-title">Payment processing fee:</p>
                                        <ul class="repay-modal-fee-list">
                                            <li data-method="card">{{ $feeDomesticLabel }} for domestic cards</li>
                                            @if($feeInternationalExtra ?? null)
                                                <li data-method="card">{{ $feeInternationalExtra }}</li>
                                            @endif
                                            <li data-method="card">{{ $feeCurrencyNote ?? '+ 2% if currency conversion is required' }}</li>
                                            <li data-method="fpx">{{ $feeFpxLabel ?? $feeDomesticLabel }} FPX</li>
                                        </ul>
                                    @endif
                                </div>
                                <div class="repay-modal-summary-row repay-modal-summary-total">
                                    <span>Total</span>
                                    <span id="repay-modal-summary-total">—</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cancel order modal (To Pay tab) – reason required -->
            <div id="cancel-order-modal" class="cancel-order-modal" aria-hidden="true" role="dialog" aria-labelledby="cancel-order-modal-title">
                <div class="cancel-order-modal-backdrop"></div>
                <div class="cancel-order-modal-content">
                    <button type="button" class="cancel-order-modal-close" id="cancel-order-modal-close" aria-label="Close">&times;</button>
                    <h2 id="cancel-order-modal-title" class="cancel-order-modal-title">Cancel order</h2>
                    <p class="cancel-order-modal-text">Please select a reason for cancellation. Your choice helps us improve our service.</p>
                    <form id="cancel-order-form" method="POST" action="" class="cancel-order-form">
                        @csrf
                        <input type="hidden" name="reason" id="cancel-order-reason-hidden">
                        <p class="cancel-order-label">Reason for cancellation <span class="cancel-order-required">*</span></p>
                        <div class="cancel-order-options">
                            <label class="cancel-order-option">
                                <input type="radio" name="reason_choice" value="Changed my plans / no longer attending">
                                <span>Changed my plans / no longer attending</span>
                            </label>
                            <label class="cancel-order-option">
                                <input type="radio" name="reason_choice" value="Entered wrong details (name, email, etc.)">
                                <span>Entered wrong details (name, email, etc.)</span>
                            </label>
                            <label class="cancel-order-option">
                                <input type="radio" name="reason_choice" value="Duplicate order / purchased more than once">
                                <span>Duplicate order / purchased more than once</span>
                            </label>
                            <label class="cancel-order-option">
                                <input type="radio" name="reason_choice" value="Prefer to use a different payment method">
                                <span>Prefer to use a different payment method</span>
                            </label>
                            <label class="cancel-order-option">
                                <input type="radio" name="reason_choice" value="OTHER">
                                <span>Other</span>
                            </label>
                        </div>
                        <div class="cancel-order-other" id="cancel-order-other" style="display: none;">
                            <textarea id="cancel-order-reason-other" class="cancel-order-textarea" rows="3" maxlength="500" placeholder="Please tell us your reason (max 500 characters)."></textarea>
                        </div>
                        <div class="cancel-order-actions">
                            <button type="button" class="cancel-order-btn-secondary" id="cancel-order-modal-cancel-btn">Close</button>
                            <button type="submit" class="cancel-order-btn-primary">Cancel order</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Refund request modal (Completed tab) – reason and admin approval -->
            <div id="refund-order-modal" class="cancel-order-modal" aria-hidden="true" role="dialog" aria-labelledby="refund-order-modal-title">
                <div class="cancel-order-modal-backdrop"></div>
                <div class="cancel-order-modal-content">
                    <button type="button" class="cancel-order-modal-close" id="refund-order-modal-close" aria-label="Close">&times;</button>
                    <h2 id="refund-order-modal-title" class="cancel-order-modal-title">Request refund</h2>
                    <p class="cancel-order-modal-text">Please select a reason for your refund request. Your reason will be reviewed by our admin team.</p>
                    <form id="refund-order-form" method="POST" action="" class="cancel-order-form" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="reason" id="refund-order-reason-hidden">
                        <p class="cancel-order-label">Reason for refund <span class="cancel-order-required">*</span></p>
                        <div class="cancel-order-options">
                            <label class="cancel-order-option">
                                <input type="radio" name="refund_reason_choice" value="Unable to attend the event">
                                <span>Unable to attend the event</span>
                            </label>
                            <label class="cancel-order-option">
                                <input type="radio" name="refund_reason_choice" value="Purchased the wrong ticket type or quantity">
                                <span>Purchased the wrong ticket type or quantity</span>
                            </label>
                            <label class="cancel-order-option">
                                <input type="radio" name="refund_reason_choice" value="Technical or payment issue during checkout">
                                <span>Technical or payment issue during checkout</span>
                            </label>
                            <label class="cancel-order-option">
                                <input type="radio" name="refund_reason_choice" value="Event information or date has changed">
                                <span>Event information or date has changed</span>
                            </label>
                            <label class="cancel-order-option">
                                <input type="radio" name="refund_reason_choice" value="OTHER">
                                <span>Other</span>
                            </label>
                        </div>
                        <div class="cancel-order-other" id="refund-order-other" style="display: none;">
                            <textarea id="refund-order-reason-other" class="cancel-order-textarea" rows="3" maxlength="500" placeholder="Please tell us your reason (max 500 characters)."></textarea>
                        </div>
                        <div class="cancel-order-label" style="margin-top: 0.75rem;">Attach images as proof (optional)</div>
                        <input type="file" name="proof_images[]" multiple accept="image/*" class="form-control form-control-sm" style="margin: 0.25rem 0 0.75rem; max-width: 100%;">
                        <div class="cancel-order-actions">
                            <button type="button" class="cancel-order-btn-secondary" id="refund-order-modal-cancel-btn">Close</button>
                            <button type="submit" class="cancel-order-btn-primary">Submit refund request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.profile-page,
.profile-page main {
    background: #F9FAFB;
}

.profile-page-container {
    padding: 4rem 0 3rem;
    min-height: calc(100vh - 60px);
    background: #F9FAFB;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.profile-page-content-wrapper.profile-purchase-history-wrapper {
    max-width: 900px;
    margin: 0 auto;
    padding: 0 2.5rem 2.5rem;
}

.profile-page-header {
    text-align: center;
    margin-bottom: 1.5rem;
    padding-top: 1rem;
}

.profile-page-title {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 0.5rem;
    font-family: 'Playfair Display', serif;
    line-height: 1.2;
}

.profile-page-subtitle {
    font-size: 0.9375rem;
    color: #6B7280;
    font-family: 'Inter', sans-serif;
    line-height: 1.4;
}

.purchase-history-tabs {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.25rem;
    margin-bottom: 1.25rem;
    border-bottom: 1px solid #E5E7EB;
}

.purchase-history-tab {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem;
    font-size: 0.9375rem;
    font-weight: 500;
    color: #6B7280;
    text-decoration: none;
    border-bottom: 2px solid transparent;
    margin-bottom: -1px;
    transition: color 0.2s, border-color 0.2s;
}

.purchase-history-tab:hover {
    color: var(--text-dark, #111827);
}

.purchase-history-tab.active {
    color: var(--primary-color, #ff9800);
    border-bottom-color: var(--primary-color, #ff9800);
}

.purchase-history-tab-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 1.25rem;
    height: 1.25rem;
    padding: 0 0.35rem;
    font-size: 0.75rem;
    font-weight: 600;
    color: #fff;
    background: #9CA3AF;
    border-radius: 9999px;
}

.purchase-history-tab.active .purchase-history-tab-count {
    background: var(--primary-color, #ff9800);
}

.profile-section-card {
    background: #fff;
    border-radius: 0.5rem;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border: 1px solid #E5E7EB;
    font-family: 'Inter', sans-serif;
}

.profile-section-body {
    margin: 0;
}

.purchase-history-table-wrap {
    overflow-x: auto;
}

.purchase-history-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9375rem;
}

.purchase-history-table th,
.purchase-history-table td {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #E5E7EB;
    text-align: center;
}

.purchase-history-table th {
    font-weight: 600;
    color: #374151;
    background: #F9FAFB;
}

.purchase-history-table tbody tr:hover {
    background: #F9FAFB;
}


.purchase-ref {
    word-break: break-all;
    font-family: monospace;
    font-size: 0.875rem;
}

.purchase-status-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.8125rem;
    font-weight: 500;
}

.purchase-status-paid {
    background: #D1FAE5;
    color: #065F46;
}

.purchase-status-pending {
    background: #FEF3C7;
    color: #92400E;
}

.purchase-status-failed {
    background: #FEE2E2;
    color: #991B1B;
}

.purchase-status-refunded {
    background: #E0E7FF;
    color: #3730A3;
}

.purchase-status-cancelled {
    background: #F3F4F6;
    color: #4B5563;
}

.purchase-history-empty {
    text-align: center;
    color: #6B7280;
    padding: 2rem;
    margin: 0;
}

.purchase-history-pagination {
    margin-top: 1.5rem;
    display: flex;
    justify-content: center;
}

.purchase-history-pagination nav {
    display: flex;
    gap: 0.25rem;
}

.purchase-history-actions-cell {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    gap: 0.5rem 1rem;
}

.purchase-history-action-link {
    padding: 0;
    border: none;
    background: none;
    font-size: 0.9375rem;
    color: #374151;
    text-decoration: underline;
    cursor: pointer;
    transition: color 0.2s;
}

.purchase-history-action-link:hover {
    color: var(--primary-color, #ff9800);
}

.purchase-history-cancel-form,
.purchase-history-refund-form {
    display: inline-flex;
}

/* Repay modal – same layout and style as checkout "Choose payment method" */
.repay-modal {
    position: fixed;
    inset: 0;
    z-index: 1001;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.25s ease, visibility 0.25s ease;
}

.repay-modal.is-open {
    opacity: 1;
    visibility: visible;
}

.repay-modal-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
}

.repay-modal-content {
    position: relative;
    background: #fff;
    border-radius: 12px;
    padding: 0;
    max-width: 740px;
    width: 100%;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.repay-modal-panels {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
    align-items: stretch;
    min-height: 320px;
}

.repay-modal-left {
    min-width: 0;
    padding: 2rem 2rem 2rem 2.25rem;
}

.repay-modal-right {
    min-width: 0;
    background: linear-gradient(180deg, #F8FAFC 0%, #F1F5F9 100%);
    padding: 2rem;
    border-left: 1px solid #E2E8F0;
}

.repay-modal-right-title {
    margin: 0 0 1rem;
    font-size: 0.6875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #64748B;
}

.repay-modal-title {
    margin: 0 0 1.5rem;
    font-size: 1.375rem;
    font-weight: 700;
    color: #0F172A;
    letter-spacing: -0.02em;
}

.repay-modal-text {
    margin: 0 0 1rem;
    font-size: 0.9375rem;
    color: #475569;
}

.repay-modal-buttons {
    display: flex;
    flex-direction: column;
    gap: 0.625rem;
}

.repay-modal-change-label {
    margin-top: 1.25rem;
    margin-bottom: 0.5rem;
}

.repay-modal-change-alert {
    margin: 0 0 0.75rem;
    padding: 0.75rem 1rem;
    font-size: 0.8125rem;
    line-height: 1.45;
    color: #475569;
    background: #F1F5F9;
    border: 1px solid #E2E8F0;
    border-radius: 8px;
    text-align: justify;
}

.repay-modal-change-methods {
    margin-bottom: 0;
}

.repay-modal-btn {
    display: flex;
    align-items: center;
    gap: 0.875rem;
    padding: 1rem 1.25rem;
    border: 1px solid #E2E8F0;
    border-radius: 8px;
    background: #fff;
    font-size: 0.9375rem;
    font-weight: 500;
    color: #0F172A;
    cursor: pointer;
    transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
}

.repay-modal-btn:hover {
    border-color: var(--primary-color, #ff9800);
    background: #F0FDF4;
    box-shadow: 0 2px 8px rgba(22, 101, 52, 0.12);
}

.repay-modal-btn-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    line-height: 1;
    width: 2.25rem;
    height: 2.25rem;
    background: #F1F5F9;
    border-radius: 6px;
}

.repay-modal-continue-text {
    margin: 0 0 1rem;
    font-size: 0.9375rem;
    color: #475569;
}
.repay-modal-continue-text.is-hidden {
    display: none;
}
.repay-modal-step-pay .repay-modal-actions {
    display: flex;
    gap: 0.75rem;
    margin-top: 1.25rem;
}

.repay-modal-back-btn {
    padding: 0.5rem 1rem;
    border: 1px solid #E2E8F0;
    border-radius: 8px;
    background: #fff;
    font-size: 0.875rem;
    font-weight: 500;
    color: #475569;
    cursor: pointer;
    transition: border-color 0.2s, background 0.2s;
}

.repay-modal-back-btn:hover {
    border-color: #CBD5E1;
    background: #F8FAFC;
}

.repay-modal-pay-btn {
    flex: 1;
    padding: 0.75rem 1.25rem;
    border: none;
    border-radius: 8px;
    background: var(--primary-color, #ff9800);
    color: #fff;
    font-size: 0.9375rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
}

.repay-modal-pay-btn:hover {
    background: var(--primary-dark, #e68900);
}

.repay-modal-pay-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.repay-modal-error {
    margin: 0.75rem 0 0;
    font-size: 0.875rem;
    color: #DC2626;
}

.repay-modal-close {
    position: absolute;
    top: 1rem;
    right: 1rem;
    width: 2.25rem;
    height: 2.25rem;
    border: none;
    background: #F1F5F9;
    border-radius: 8px;
    font-size: 1.25rem;
    line-height: 1;
    color: #64748B;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s, color 0.2s;
    z-index: 1;
}

.repay-modal-close:hover {
    background: #E2E8F0;
    color: #0F172A;
}

#repay-element-container {
    min-height: 120px;
}

.repay-modal-order-summary {
    background: #fff;
    border: 1px solid #E2E8F0;
    border-radius: 10px;
    padding: 1.25rem 1.5rem;
    font-size: 0.875rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
}

.repay-modal-summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}

.repay-modal-summary-row + .repay-modal-summary-row {
    margin-top: 0.5rem;
}

.repay-modal-summary-row.repay-modal-summary-total {
    margin-top: 0.875rem;
    padding-top: 0.875rem;
    border-top: 1px solid #E2E8F0;
    font-weight: 700;
    font-size: 1.0625rem;
    color: #0F172A;
}

.repay-modal-fee-breakdown {
    margin: 1.25rem 0 0;
}

.repay-modal-fee-breakdown-title {
    margin: 0 0 0.25rem;
    font-size: 0.6875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748B;
}

.repay-modal-fee-list {
    margin: 0;
    padding-left: 1.25rem;
    font-size: 0.75rem;
    color: #64748B;
    line-height: 1.5;
}

.repay-modal-fee-list li {
    margin-bottom: 0.125rem;
}

@media (max-width: 640px) {
    .repay-modal-panels {
        grid-template-columns: 1fr;
        min-height: 0;
    }
    .repay-modal-right {
        border-left: none;
        border-top: 1px solid #E2E8F0;
    }
    .repay-modal-left,
    .repay-modal-right {
        padding: 1.5rem;
    }
}

/* Cancel order modal */
.cancel-order-modal {
    position: fixed;
    inset: 0;
    z-index: 1001;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.25s ease, visibility 0.25s ease;
}
.cancel-order-modal.is-open {
    opacity: 1;
    visibility: visible;
}
.cancel-order-modal-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
}
.cancel-order-modal-content {
    position: relative;
    background: #fff;
    border-radius: 12px;
    padding: 2rem 2rem 2rem 2.25rem;
    max-width: 440px;
    width: 100%;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(0, 0, 0, 0.05);
}
.cancel-order-modal-title {
    margin: 0 0 0.75rem;
    font-size: 1.375rem;
    font-weight: 700;
    color: #0F172A;
}
.cancel-order-modal-text {
    margin: 0 0 1.25rem;
    font-size: 0.9375rem;
    color: #475569;
    line-height: 1.5;
}
.cancel-order-form {
    display: block;
}
.cancel-order-label {
    display: block;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #334155;
}
.cancel-order-required {
    color: #DC2626;
}
.cancel-order-options {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}
.cancel-order-option {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    font-size: 0.9375rem;
    color: #0F172A;
}
.cancel-order-option input[type="radio"] {
    margin-top: 0.15rem;
}
.cancel-order-option span {
    display: inline-block;
}
.cancel-order-other {
    margin: 0.5rem 0 0.75rem;
}
.cancel-order-textarea {
    width: 100%;
    padding: 0.75rem 1rem;
    font-size: 0.9375rem;
    font-family: inherit;
    line-height: 1.5;
    color: #0F172A;
    border: 1px solid #E2E8F0;
    border-radius: 8px;
    resize: vertical;
    min-height: 90px;
    box-sizing: border-box;
}
.cancel-order-textarea:focus {
    outline: none;
    border-color: var(--primary-color, #ff9800);
    box-shadow: 0 0 0 3px rgba(22, 101, 52, 0.15);
}
.cancel-order-hint {
    margin: 0.5rem 0 1.25rem;
    font-size: 0.8125rem;
    color: #64748B;
}
.cancel-order-actions {
    display: flex;
    gap: 0.75rem;
    justify-content: flex-end;
}
.cancel-order-btn-secondary {
    padding: 0.5rem 1.25rem;
    font-size: 0.9375rem;
    font-weight: 500;
    color: #475569;
    background: #fff;
    border: 1px solid #E2E8F0;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.2s, border-color 0.2s;
}
.cancel-order-btn-secondary:hover {
    background: #F8FAFC;
    border-color: #CBD5E1;
}
.cancel-order-btn-primary {
    padding: 0.5rem 1.25rem;
    font-size: 0.9375rem;
    font-weight: 600;
    color: #fff;
    background: var(--primary-color, #ff9800);
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.2s;
}
.cancel-order-btn-primary:hover {
    background: var(--primary-dark, #e68900);
}
.cancel-order-modal-close {
    position: absolute;
    top: 1rem;
    right: 1rem;
    width: 2.25rem;
    height: 2.25rem;
    border: none;
    background: #F1F5F9;
    border-radius: 8px;
    font-size: 1.25rem;
    line-height: 1;
    color: #64748B;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s, color 0.2s;
}
.cancel-order-modal-close:hover {
    background: #E2E8F0;
    color: #0F172A;
}

/* Client order detail modal */
.client-order-modal {
    position: fixed;
    inset: 0;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.2s, visibility 0.2s;
}

.client-order-modal[data-open="true"] {
    opacity: 1;
    visibility: visible;
}

.client-order-modal-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    cursor: pointer;
}

.client-order-modal-dialog {
    /* Match admin orders modal (Bootstrap modal-lg = 800px) */
    position: relative;
    width: 100%;
    max-width: 800px;
    max-height: calc(100vh - 2rem);
    display: flex;
    flex-direction: column;
}

.client-order-modal-content {
    background: #fff;
    border-radius: 0.5rem;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    max-height: calc(100vh - 2rem);
}

.client-order-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #E5E7EB;
    flex-shrink: 0;
}

.client-order-modal-title {
    font-size: 1.125rem;
    font-weight: 600;
    margin: 0;
    color: var(--text-dark, #111827);
}

.client-order-modal-close {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    padding: 0;
    border: none;
    background: transparent;
    font-size: 1.5rem;
    line-height: 1;
    color: #6B7280;
    cursor: pointer;
    border-radius: 0.25rem;
    transition: color 0.2s, background 0.2s;
}

.client-order-modal-close:hover {
    color: #111827;
    background: #F3F4F6;
}

.client-order-modal-body-wrap {
    padding: 1.25rem;
    overflow-y: auto;
    min-height: 120px;
}

.client-order-modal-loading {
    color: #6B7280;
    font-size: 0.9375rem;
}

.client-order-modal-body-content {
    display: none;
}

.client-order-modal-body-content.is-loaded {
    display: block;
}

/* Content loaded from partial */
.client-order-modal-body .client-order-modal-section {
    margin-bottom: 1.25rem;
}

.client-order-modal-body .client-order-modal-section:last-child {
    margin-bottom: 0;
}

.client-order-modal-body .client-order-modal-heading {
    font-size: 1rem;
    font-weight: 600;
    margin: 0 0 0.5rem 0;
    color: var(--text-dark, #111827);
}

.client-order-modal-body .client-order-modal-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
}

.client-order-modal-body .client-order-modal-table td {
    padding: 0.35rem 0.5rem 0.35rem 0;
    vertical-align: top;
}

.client-order-modal-body .client-order-modal-table .client-order-modal-muted {
    color: #6B7280;
    width: 140px;
}

.client-order-modal-body .client-order-modal-table-bordered td,
.client-order-modal-body .client-order-modal-table-bordered th {
    border: 1px solid #E5E7EB;
    padding: 0.5rem 0.75rem;
}

.client-order-modal-body .client-order-modal-table-bordered th {
    font-weight: 600;
    background: #F9FAFB;
    color: #374151;
}

.client-order-modal-body .client-order-modal-table-wrap {
    overflow-x: auto;
    margin-bottom: 0.5rem;
}

.client-order-modal-body .client-order-modal-table-order-items th,
.client-order-modal-body .client-order-modal-table-order-items td {
    text-align: center;
}

.client-order-modal-body .text-break {
    word-break: break-all;
}

.client-order-modal-body .client-order-modal-fee-breakdown {
    padding: 0.5rem 0.75rem;
    background: #F9FAFB;
    border-top: none;
    vertical-align: top;
}

.client-order-modal-body .client-order-modal-fee-breakdown .client-order-modal-fee-list {
    margin: 0.25rem 0 0 1.25rem;
    padding: 0;
    font-size: 0.8125rem;
    color: #6B7280;
    line-height: 1.5;
}

.client-order-modal-body .client-order-modal-fee-breakdown .client-order-modal-fee-list li {
    margin-bottom: 0.125rem;
}

@media (max-width: 768px) {
    .purchase-history-table th,
    .purchase-history-table td {
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }
}
</style>
@endpush

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('clientOrderDetailModal');
    var refEl = document.getElementById('clientOrderDetailModalReference');
    var contentEl = document.getElementById('clientOrderDetailModalContent');
    var loadingEl = document.getElementById('clientOrderDetailModalLoading');

    function openModal() {
        modal.removeAttribute('hidden');
        modal.setAttribute('data-open', 'true');
    }

    function closeModal() {
        modal.setAttribute('hidden', '');
        modal.removeAttribute('data-open');
    }

    document.querySelectorAll('.purchase-history-btn-view').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var reference = btn.getAttribute('data-reference') || '-';
            var modalUrl = btn.getAttribute('data-modal-url');
            if (!modalUrl) return;
            refEl.textContent = reference;
            contentEl.classList.remove('is-loaded');
            contentEl.innerHTML = '';
            loadingEl.style.display = 'block';
            openModal();
            fetch(modalUrl, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } })
                .then(function(r) { return r.text(); })
                .then(function(html) {
                    loadingEl.style.display = 'none';
                    contentEl.innerHTML = html;
                    contentEl.classList.add('is-loaded');
                })
                .catch(function() {
                    loadingEl.textContent = 'Failed to load order details.';
                });
        });
    });

    modal.querySelectorAll('[data-close="clientOrderDetailModal"]').forEach(function(el) {
        el.addEventListener('click', closeModal);
    });

    modal.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeModal();
    });

    // Cancel order modal – open on Cancel click, set form action, require reason
    var cancelOrderModal = document.getElementById('cancel-order-modal');
    var cancelOrderForm = document.getElementById('cancel-order-form');
    var cancelOrderReasonHidden = document.getElementById('cancel-order-reason-hidden');
    var cancelOrderReasonOther = document.getElementById('cancel-order-reason-other');
    var cancelOrderOtherWrapper = document.getElementById('cancel-order-other');
    var cancelOrderModalClose = document.getElementById('cancel-order-modal-close');
    var cancelOrderModalCancelBtn = document.getElementById('cancel-order-modal-cancel-btn');

    function openCancelOrderModal(url) {
        if (cancelOrderForm && url) {
            cancelOrderForm.action = url;
            // Clear any previously selected reason
            var reasonInputs = cancelOrderForm.querySelectorAll('input[name="reason_choice"]');
            reasonInputs.forEach(function(input) { input.checked = false; });
            if (cancelOrderReasonHidden) cancelOrderReasonHidden.value = '';
            if (cancelOrderReasonOther) cancelOrderReasonOther.value = '';
            if (cancelOrderOtherWrapper) cancelOrderOtherWrapper.style.display = 'none';
            if (cancelOrderModal) {
                cancelOrderModal.classList.add('is-open');
                cancelOrderModal.setAttribute('aria-hidden', 'false');
            }
        }
    }

    function closeCancelOrderModal() {
        if (cancelOrderModal) {
            cancelOrderModal.classList.remove('is-open');
            cancelOrderModal.setAttribute('aria-hidden', 'true');
        }
    }

    document.querySelectorAll('.purchase-history-btn-cancel-open').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var url = btn.getAttribute('data-cancel-url');
            if (url) openCancelOrderModal(url);
        });
    });
    if (cancelOrderModalClose) cancelOrderModalClose.addEventListener('click', closeCancelOrderModal);
    if (cancelOrderModalCancelBtn) cancelOrderModalCancelBtn.addEventListener('click', closeCancelOrderModal);

    if (cancelOrderForm) {
        var reasonRadios = cancelOrderForm.querySelectorAll('input[name="reason_choice"]');
        reasonRadios.forEach(function(radio) {
            radio.addEventListener('change', function() {
                if (this.value === 'OTHER') {
                    if (cancelOrderOtherWrapper) cancelOrderOtherWrapper.style.display = '';
                    if (cancelOrderReasonOther) cancelOrderReasonOther.focus();
                } else {
                    if (cancelOrderOtherWrapper) cancelOrderOtherWrapper.style.display = 'none';
                    if (cancelOrderReasonOther) cancelOrderReasonOther.value = '';
                }
            });
        });

        cancelOrderForm.addEventListener('submit', function(e) {
            var selected = cancelOrderForm.querySelector('input[name="reason_choice"]:checked');
            if (!selected) {
                e.preventDefault();
                return;
            }
            if (selected.value === 'OTHER') {
                var text = cancelOrderReasonOther ? cancelOrderReasonOther.value.trim() : '';
                if (!text) {
                    e.preventDefault();
                    if (cancelOrderReasonOther) cancelOrderReasonOther.focus();
                    return;
                }
                if (cancelOrderReasonHidden) cancelOrderReasonHidden.value = text;
            } else {
                if (cancelOrderReasonHidden) cancelOrderReasonHidden.value = selected.value;
            }
        });
    }

    // Refund order modal – open on Request refund click, require reason, pending admin approval
    var refundOrderModal = document.getElementById('refund-order-modal');
    var refundOrderForm = document.getElementById('refund-order-form');
    var refundOrderReasonHidden = document.getElementById('refund-order-reason-hidden');
    var refundOrderReasonOther = document.getElementById('refund-order-reason-other');
    var refundOrderOtherWrapper = document.getElementById('refund-order-other');
    var refundOrderModalClose = document.getElementById('refund-order-modal-close');
    var refundOrderModalCancelBtn = document.getElementById('refund-order-modal-cancel-btn');

    function openRefundOrderModal(url) {
        if (refundOrderForm && url) {
            refundOrderForm.action = url;
            var reasonInputs = refundOrderForm.querySelectorAll('input[name="refund_reason_choice"]');
            reasonInputs.forEach(function(input) { input.checked = false; });
            if (refundOrderReasonHidden) refundOrderReasonHidden.value = '';
            if (refundOrderReasonOther) refundOrderReasonOther.value = '';
            if (refundOrderOtherWrapper) refundOrderOtherWrapper.style.display = 'none';
            if (refundOrderModal) {
                refundOrderModal.classList.add('is-open');
                refundOrderModal.setAttribute('aria-hidden', 'false');
            }
        }
    }

    function closeRefundOrderModal() {
        if (refundOrderModal) {
            refundOrderModal.classList.remove('is-open');
            refundOrderModal.setAttribute('aria-hidden', 'true');
        }
    }

    document.querySelectorAll('.purchase-history-btn-refund-open').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var url = btn.getAttribute('data-refund-url');
            if (url) openRefundOrderModal(url);
        });
    });

    // Download all QR codes (one per ticket holder) one by one on single button click
    document.querySelectorAll('.purchase-history-btn-download-qr-codes').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var urlsJson = this.getAttribute('data-qr-urls');
            if (!urlsJson) return;
            var urls = [];
            try {
                urls = JSON.parse(urlsJson);
            } catch (e) {
                return;
            }
            urls.forEach(function(url, i) {
                setTimeout(function() {
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = '';
                    a.style.display = 'none';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                }, i * 300);
            });
        });
    });

    if (refundOrderModalClose) refundOrderModalClose.addEventListener('click', closeRefundOrderModal);
    if (refundOrderModalCancelBtn) refundOrderModalCancelBtn.addEventListener('click', closeRefundOrderModal);

    if (refundOrderForm) {
        var refundReasonRadios = refundOrderForm.querySelectorAll('input[name="refund_reason_choice"]');
        refundReasonRadios.forEach(function(radio) {
            radio.addEventListener('change', function() {
                if (this.value === 'OTHER') {
                    if (refundOrderOtherWrapper) refundOrderOtherWrapper.style.display = '';
                    if (refundOrderReasonOther) refundOrderReasonOther.focus();
                } else {
                    if (refundOrderOtherWrapper) refundOrderOtherWrapper.style.display = 'none';
                    if (refundOrderReasonOther) refundOrderReasonOther.value = '';
                }
            });
        });

        refundOrderForm.addEventListener('submit', function(e) {
            var selected = refundOrderForm.querySelector('input[name="refund_reason_choice"]:checked');
            if (!selected) {
                e.preventDefault();
                return;
            }
            if (selected.value === 'OTHER') {
                var text = refundOrderReasonOther ? refundOrderReasonOther.value.trim() : '';
                if (!text) {
                    e.preventDefault();
                    if (refundOrderReasonOther) refundOrderReasonOther.focus();
                    return;
                }
                if (refundOrderReasonHidden) refundOrderReasonHidden.value = text;
            } else {
                if (refundOrderReasonHidden) refundOrderReasonHidden.value = selected.value;
            }
        });
    }

    // Repay modal (Stripe) – same as checkout choose payment method (fees, order summary)
    @php
        $repayConfigForJs = [
            'stripeKey' => config('services.stripe.key'),
            'paymentSuccessUrl' => route('checkout.paymentSuccess'),
            'csrfToken' => csrf_token(),
            'feeConfig' => $repayFeeConfig ?? ['fee_percentage' => 0, 'fee_percentage_international' => 0, 'fee_fixed_cents' => 0],
        ];
    @endphp
    var repayConfig = @json($repayConfigForJs);
    var repayModal = document.getElementById('repay-modal');
    var repayStepChoose = document.getElementById('repay-modal-step-choose');
    var repayStepPay = document.getElementById('repay-modal-step-pay');
    var repayElementContainer = document.getElementById('repay-element-container');
    var repayModalBack = document.getElementById('repay-modal-back');
    var repayModalPayNow = document.getElementById('repay-modal-pay-now');
    var repayModalError = document.getElementById('repay-modal-error');
    var repayModalClose = document.getElementById('repay-modal-close');
    var currentRepayUrl = null;
    var currentRepayMethod = null;
    var repayIntentCreated = false;
    var repayStripe = null;
    var repayElements = null;
    var repayCardElement = null;
    var repayClientSecret = null;
    var repayModalContinueText = document.getElementById('repay-modal-continue-text');

    var repayModalSummarySubtotal = document.getElementById('repay-modal-summary-subtotal');
    var repayModalSummaryFee = document.getElementById('repay-modal-summary-fee');
    var repayModalSummaryTotal = document.getElementById('repay-modal-summary-total');
    var repayModalSummaryFeeRow = document.getElementById('repay-modal-summary-fee-row');
    var repayModalFeeBreakdown = document.getElementById('repay-modal-fee-breakdown');
    var repayOrderSummary = { subtotal: 0, feeDomestic: 0, feeInternational: 0, totalMin: 0, totalMax: 0 };

    function formatRm(amount) {
        return 'RM ' + (typeof amount === 'number' ? amount.toFixed(2) : amount);
    }

    function updateRepayOrderSummary(method) {
        var s = repayOrderSummary;
        if (repayModalSummarySubtotal) repayModalSummarySubtotal.textContent = formatRm(s.subtotal);
        var hasFee = s.feeDomestic > 0 || s.feeInternational > 0;
        if (repayModalSummaryFeeRow) repayModalSummaryFeeRow.style.display = hasFee ? '' : 'none';
        if (repayModalFeeBreakdown) repayModalFeeBreakdown.style.display = hasFee ? '' : 'none';
        if (method === 'fpx') {
            if (repayModalSummaryFee) repayModalSummaryFee.textContent = hasFee ? formatRm(s.feeDomestic) : '—';
            if (repayModalSummaryTotal) repayModalSummaryTotal.textContent = formatRm(s.totalMin);
        } else if (method === 'card') {
            if (repayModalSummaryFee) repayModalSummaryFee.textContent = '—';
            if (repayModalSummaryTotal) repayModalSummaryTotal.textContent = hasFee ? ('Up to ' + formatRm(s.totalMax)) : formatRm(s.subtotal);
        } else {
            if (repayModalSummaryFee) repayModalSummaryFee.textContent = '—';
            if (repayModalSummaryTotal) repayModalSummaryTotal.textContent = hasFee ? 'Select payment method' : formatRm(s.subtotal);
        }
        var items = repayModalFeeBreakdown ? repayModalFeeBreakdown.querySelectorAll('.repay-modal-fee-list li[data-method]') : [];
        items.forEach(function(li) {
            li.style.display = !method ? '' : (li.getAttribute('data-method') === method ? '' : 'none');
        });
    }

    function openRepayModal(subtotalCents, amountExcludesFee, orderPaymentMethod) {
        repayStepChoose.style.display = '';
        repayStepPay.style.display = 'none';
        repayElementContainer.innerHTML = '';
        repayModalError.style.display = 'none';
        repayModalError.textContent = '';
        repayClientSecret = null;
        currentRepayMethod = null;
        repayIntentCreated = false;
        var methodFilter = (orderPaymentMethod === 'fpx' || orderPaymentMethod === 'card') ? orderPaymentMethod : '';
        var primaryContainer = repayStepChoose.querySelector('.repay-modal-primary-methods');
        var changeLabel = document.getElementById('repay-modal-change-label');
        var changeContainer = document.getElementById('repay-modal-change-methods');
        if (primaryContainer) {
            primaryContainer.querySelectorAll('.repay-modal-btn[data-method]').forEach(function(btn) {
                var btnMethod = btn.getAttribute('data-method');
                btn.style.display = !methodFilter || btnMethod === methodFilter ? '' : 'none';
            });
        }
        var changeAlert = document.getElementById('repay-modal-change-alert');
        if (changeLabel && changeContainer) {
            if (methodFilter) {
                changeLabel.style.display = '';
                if (changeAlert) changeAlert.style.display = '';
                changeContainer.style.display = '';
                changeContainer.querySelectorAll('.repay-modal-btn[data-method]').forEach(function(btn) {
                var btnMethod = btn.getAttribute('data-method');
                btn.style.display = btnMethod !== methodFilter ? '' : 'none';
                });
            } else {
                changeLabel.style.display = 'none';
                if (changeAlert) changeAlert.style.display = 'none';
                changeContainer.style.display = 'none';
            }
        }
        var subtotal = (subtotalCents || 0) / 100;
        var cfg = repayConfig.feeConfig || {};
        var feePctDomestic = parseFloat(cfg.fee_percentage) || 0;
        var feePctIntl = parseFloat(cfg.fee_percentage_international) || 0;
        if (feePctIntl <= 0) feePctIntl = feePctDomestic;
        var feeFixedCents = parseInt(cfg.fee_fixed_cents, 10) || 0;
        var subtotalC = Math.round(subtotal * 100);
        var feeDomesticCents = 0, feeInternationalCents = 0;
        if (amountExcludesFee) {
            feeDomesticCents = feePctDomestic > 0 ? Math.round(subtotalC * feePctDomestic / 100) + feeFixedCents : feeFixedCents;
            feeInternationalCents = feePctIntl > 0 ? Math.round(subtotalC * feePctIntl / 100) + feeFixedCents : feeFixedCents;
        }
        repayOrderSummary.subtotal = subtotal;
        repayOrderSummary.feeDomestic = feeDomesticCents / 100;
        repayOrderSummary.feeInternational = feeInternationalCents / 100;
        repayOrderSummary.totalMin = (subtotalC + feeDomesticCents) / 100;
        repayOrderSummary.totalMax = (subtotalC + feeInternationalCents) / 100;
        updateRepayOrderSummary(null);
        if (repayModal) {
            repayModal.classList.add('is-open');
            repayModal.setAttribute('aria-hidden', 'false');
        }
    }

    function closeRepayModal() {
        if (repayModal) {
            repayModal.classList.remove('is-open');
            repayModal.setAttribute('aria-hidden', 'true');
        }
        currentRepayUrl = null;
        currentRepayMethod = null;
        repayIntentCreated = false;
        repayClientSecret = null;
        repayElements = null;
        repayCardElement = null;
    }

    function showRepayStepPay() {
        repayStepChoose.style.display = 'none';
        repayStepPay.style.display = '';
        repayModalError.style.display = 'none';
    }

    function showRepayStepChoose() {
        repayStepPay.style.display = 'none';
        repayStepChoose.style.display = '';
        repayElementContainer.innerHTML = '';
        repayClientSecret = null;
        repayElements = null;
        repayCardElement = null;
        repayIntentCreated = false;
        currentRepayMethod = null;
        updateRepayOrderSummary(null);
        if (repayModalPayNow) {
            repayModalPayNow.textContent = 'Continue to payment';
            repayModalPayNow.disabled = false;
        }
        if (repayModalContinueText) repayModalContinueText.classList.remove('is-hidden');
    }

    function createRepayIntent(method, callback) {
        if (!currentRepayUrl) return callback(new Error('No order selected'));
        var xhr = new XMLHttpRequest();
        xhr.open('POST', currentRepayUrl);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('X-CSRF-TOKEN', repayConfig.csrfToken);
        xhr.onload = function() {
            try {
                var json = JSON.parse(xhr.responseText);
                if (xhr.status >= 200 && xhr.status < 300) {
                    callback(null, json.clientSecret);
                } else {
                    callback(new Error(json.error || 'Failed to create payment'));
                }
            } catch (e) {
                callback(new Error('Invalid response'));
            }
        };
        xhr.onerror = function() { callback(new Error('Network error')); };
        xhr.withCredentials = true;
        xhr.send(JSON.stringify({ payment_method_type: method, _token: repayConfig.csrfToken }));
    }

    function initRepayStripeAndMount(clientSecret, method) {
        if (!repayConfig.stripeKey) {
            repayModalError.textContent = 'Payment is not configured.';
            repayModalError.style.display = 'block';
            return;
        }
        repayStripe = window.Stripe ? window.Stripe(repayConfig.stripeKey) : null;
        if (!repayStripe) {
            repayModalError.textContent = 'Payment provider failed to load.';
            repayModalError.style.display = 'block';
            return;
        }
        repayModalPayNow.disabled = true;
        repayCardElement = null;
        var appearance = { theme: 'stripe', variables: { colorPrimary: '#ff9800' } };
        repayElements = repayStripe.elements({ clientSecret: clientSecret, appearance: appearance });
        if (method === 'card') {
            repayCardElement = repayElements.create('card', { style: { base: { fontSize: '16px' } } });
            repayCardElement.on('ready', function() { repayModalPayNow.disabled = false; });
            repayCardElement.mount('#repay-element-container');
        } else {
            var paymentElement = repayElements.create('payment', {
                layout: { type: 'tabs', defaultCollapsed: false, radios: true, spacedAccent: true }
            });
            paymentElement.on('ready', function() { repayModalPayNow.disabled = false; });
            paymentElement.mount('#repay-element-container');
        }
    }

    function confirmRepayPayment() {
        var returnUrl = repayConfig.paymentSuccessUrl;
        if (returnUrl && returnUrl.indexOf('http') !== 0) {
            returnUrl = window.location.origin + returnUrl;
        }
        repayModalPayNow.disabled = true;
        repayModalError.style.display = 'none';
        repayModalError.textContent = '';

        if (currentRepayMethod === 'card' && repayCardElement && repayStripe && repayClientSecret) {
            repayStripe.createPaymentMethod({ type: 'card', card: repayCardElement }).then(function(pmResult) {
                if (pmResult.error) {
                    repayModalError.textContent = pmResult.error.message || 'Card details invalid.';
                    repayModalError.style.display = 'block';
                    repayModalPayNow.disabled = false;
                    return;
                }
                return repayStripe.confirmCardPayment(repayClientSecret, { payment_method: pmResult.paymentMethod.id });
            }).then(function(result) {
                if (result && result.error) {
                    repayModalError.textContent = result.error.message || 'Payment failed.';
                    repayModalError.style.display = 'block';
                    repayModalPayNow.disabled = false;
                    return;
                }
                if (result && result.paymentIntent && result.paymentIntent.status === 'succeeded') {
                    var successUrl = returnUrl;
                    if (result.paymentIntent.id) {
                        var sep = successUrl.indexOf('?') >= 0 ? '&' : '?';
                        successUrl = successUrl + sep + 'payment_intent=' + encodeURIComponent(result.paymentIntent.id);
                    }
                    window.location.href = successUrl;
                }
            }).catch(function(err) {
                repayModalError.textContent = err.message || 'Something went wrong. Please try again.';
                repayModalError.style.display = 'block';
                repayModalPayNow.disabled = false;
            });
            return;
        }

        if (!repayStripe || !repayElements || !repayClientSecret) {
            repayModalError.textContent = 'Payment form is not ready. Please wait or go back and try again.';
            repayModalError.style.display = 'block';
            repayModalPayNow.disabled = false;
            return;
        }
        repayElements.submit().then(function() {
            return repayStripe.confirmPayment({
                elements: repayElements,
                clientSecret: repayClientSecret,
                confirmParams: { return_url: returnUrl }
            });
        }).then(function(result) {
            if (result.error) {
                repayModalError.textContent = result.error.message || 'Payment failed.';
                repayModalError.style.display = 'block';
                repayModalPayNow.disabled = false;
            }
            if (result.paymentIntent && result.paymentIntent.status === 'succeeded') {
                var successUrl = returnUrl;
                if (result.paymentIntent.id) {
                    var sep = successUrl.indexOf('?') >= 0 ? '&' : '?';
                    successUrl = successUrl + sep + 'payment_intent=' + encodeURIComponent(result.paymentIntent.id);
                }
                window.location.href = successUrl;
            }
        }).catch(function(err) {
            repayModalError.textContent = err.message || 'Something went wrong. Please try again.';
            repayModalError.style.display = 'block';
            repayModalPayNow.disabled = false;
        });
    }

    document.querySelectorAll('.purchase-history-btn-repay').forEach(function(btn) {
        btn.addEventListener('click', function() {
            currentRepayUrl = this.getAttribute('data-create-repay-url');
            var subtotalCents = parseInt(this.getAttribute('data-order-subtotal-cents'), 10) || 0;
            var amountExcludesFee = this.getAttribute('data-order-amount-excludes-fee') === '1';
            var orderPaymentMethod = (this.getAttribute('data-order-payment-method') || '').toLowerCase();
            if (!currentRepayUrl) return;
            openRepayModal(subtotalCents, amountExcludesFee, orderPaymentMethod);
        });
    });

    if (repayModalClose) repayModalClose.addEventListener('click', closeRepayModal);
    /* Modal closes only via the X button; backdrop click does not close (same as checkout) */
    function onRepayPayButtonClick() {
        if (!repayIntentCreated) {
            if (!currentRepayMethod) {
                repayModalError.textContent = 'Please choose a payment method first.';
                repayModalError.style.display = 'block';
                return;
            }
            repayModalPayNow.disabled = true;
            repayModalError.style.display = 'none';
            repayModalError.textContent = '';
            createRepayIntent(currentRepayMethod, function(err, clientSecret) {
                if (err) {
                    repayModalError.textContent = err.message;
                    repayModalError.style.display = 'block';
                    repayModalPayNow.disabled = false;
                    return;
                }
                repayClientSecret = clientSecret;
                repayIntentCreated = true;
                if (repayModalPayNow) repayModalPayNow.textContent = 'Pay now';
                if (repayModalContinueText) repayModalContinueText.classList.add('is-hidden');
                showRepayStepPay();
                initRepayStripeAndMount(clientSecret, currentRepayMethod);
            });
            return;
        }
        confirmRepayPayment();
    }

    if (repayModalBack) repayModalBack.addEventListener('click', showRepayStepChoose);
    if (repayModalPayNow) repayModalPayNow.addEventListener('click', onRepayPayButtonClick);

    document.querySelectorAll('.repay-modal-btn[data-method]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var method = this.getAttribute('data-method');
            repayModalError.style.display = 'none';
            repayModalError.textContent = '';
            currentRepayMethod = method;
            repayIntentCreated = false;
            repayElementContainer.innerHTML = '';
            repayClientSecret = null;
            repayElements = null;
            updateRepayOrderSummary(method);
            if (repayModalPayNow) {
                repayModalPayNow.textContent = 'Continue to payment';
                repayModalPayNow.disabled = false;
            }
            if (repayModalContinueText) repayModalContinueText.classList.remove('is-hidden');
            showRepayStepPay();
        });
    });
});
</script>
@endpush
@endsection
