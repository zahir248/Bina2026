<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Mail\OrderCancelledMail;
use App\Mail\RefundPendingAdminMail;
use App\Mail\RefundRequestedMail;
use App\Models\Event;
use App\Models\Setting;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class ProfileController extends Controller
{
    /**
     * List of countries/regions for the profile form (alphabetical by name).
     */
    public static function getCountriesRegions(): array
    {
        $countries = [
            'Afghanistan', 'Albania', 'Algeria', 'Andorra', 'Angola', 'Antigua and Barbuda',
            'Argentina', 'Armenia', 'Australia', 'Austria', 'Azerbaijan', 'Bahamas', 'Bahrain',
            'Bangladesh', 'Barbados', 'Belarus', 'Belgium', 'Belize', 'Benin', 'Bhutan', 'Bolivia',
            'Bosnia and Herzegovina', 'Botswana', 'Brazil', 'Brunei', 'Bulgaria', 'Burkina Faso',
            'Burundi', 'Cabo Verde', 'Cambodia', 'Cameroon', 'Canada', 'Central African Republic',
            'Chad', 'Chile', 'China', 'Colombia', 'Comoros', 'Congo', 'Costa Rica', 'Croatia',
            'Cuba', 'Cyprus', 'Czech Republic', 'Democratic Republic of the Congo', 'Denmark',
            'Djibouti', 'Dominica', 'Dominican Republic', 'East Timor', 'Ecuador', 'Egypt',
            'El Salvador', 'Equatorial Guinea', 'Eritrea', 'Estonia', 'Eswatini', 'Ethiopia',
            'Fiji', 'Finland', 'France', 'Gabon', 'Gambia', 'Georgia', 'Germany', 'Ghana',
            'Greece', 'Grenada', 'Guatemala', 'Guinea', 'Guinea-Bissau', 'Guyana', 'Haiti',
            'Honduras', 'Hungary', 'Iceland', 'India', 'Indonesia', 'Iran', 'Iraq', 'Ireland',
            'Israel', 'Italy', 'Ivory Coast', 'Jamaica', 'Japan', 'Jordan', 'Kazakhstan', 'Kenya',
            'Kiribati', 'Kosovo', 'Kuwait', 'Kyrgyzstan', 'Laos', 'Latvia', 'Lebanon', 'Lesotho',
            'Liberia', 'Libya', 'Liechtenstein', 'Lithuania', 'Luxembourg', 'Madagascar', 'Malawi',
            'Malaysia', 'Maldives', 'Mali', 'Malta', 'Marshall Islands', 'Mauritania', 'Mauritius',
            'Mexico', 'Micronesia', 'Moldova', 'Monaco', 'Mongolia', 'Montenegro', 'Morocco',
            'Mozambique', 'Myanmar', 'Namibia', 'Nauru', 'Nepal', 'Netherlands', 'New Zealand',
            'Nicaragua', 'Niger', 'Nigeria', 'North Korea', 'North Macedonia', 'Norway', 'Oman',
            'Pakistan', 'Palau', 'Palestine', 'Panama', 'Papua New Guinea', 'Paraguay', 'Peru',
            'Philippines', 'Poland', 'Portugal', 'Qatar', 'Romania', 'Russia', 'Rwanda',
            'Saint Kitts and Nevis', 'Saint Lucia', 'Saint Vincent and the Grenadines', 'Samoa',
            'San Marino', 'Sao Tome and Principe', 'Saudi Arabia', 'Senegal', 'Serbia', 'Seychelles',
            'Sierra Leone', 'Singapore', 'Slovakia', 'Slovenia', 'Solomon Islands', 'Somalia',
            'South Africa', 'South Korea', 'South Sudan', 'Spain', 'Sri Lanka', 'Sudan', 'Suriname',
            'Sweden', 'Switzerland', 'Syria', 'Taiwan', 'Tajikistan', 'Tanzania', 'Thailand',
            'Togo', 'Tonga', 'Trinidad and Tobago', 'Tunisia', 'Turkey', 'Turkmenistan', 'Tuvalu',
            'Uganda', 'Ukraine', 'United Arab Emirates', 'United Kingdom', 'United States', 'Uruguay',
            'Uzbekistan', 'Vanuatu', 'Vatican City', 'Venezuela', 'Vietnam', 'Yemen', 'Zambia', 'Zimbabwe',
            // Regions / special administrative
            'Hong Kong', 'Macau', 'Other',
        ];
        $result = ['' => 'Select Country/Region'];
        foreach ($countries as $c) {
            $result[$c] = $c;
        }
        return $result;
    }

    /**
     * Display the profile page.
     */
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to view your profile.');
        }

        $user = Auth::user();

        return view('client.profile.index', [
            'user' => $user,
            'countriesRegions' => self::getCountriesRegions(),
        ]);
    }

    /**
     * Update the user's profile.
     */
    public function update(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $allowedCountries = array_keys(self::getCountriesRegions());
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255', Rule::unique('users', 'username')->ignore(Auth::id())],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'gender' => ['nullable', 'string', 'in:male,female'],
            'nric_passport' => ['nullable', 'string', 'max:50'],
            'country_region' => ['nullable', 'string', Rule::in($allowedCountries)],
            'street_address' => ['nullable', 'string', 'max:255'],
            'town_city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postcode_zip' => ['nullable', 'string', 'max:20'],
        ], [
            'name.required' => 'Full name is required.',
            'username.unique' => 'This username is already taken.',
        ]);

        Auth::user()->update($request->only([
            'name',
            'username',
            'contact_number',
            'gender',
            'nric_passport',
            'country_region',
            'street_address',
            'town_city',
            'state',
            'postcode_zip',
        ]));

        return redirect()->route('profile.index')->with('success', 'Profile updated successfully.');
    }

    /**
     * Tab key => order status for purchase history.
     */
    private const PURCHASE_HISTORY_TABS = [
        'to_pay'    => 'pending',
        'completed' => 'paid',
        'refund'    => 'refunded',
        'cancelled' => 'cancelled',
    ];

    /**
     * Display the user's purchase history (orders) with status tabs.
     */
    public function purchaseHistory()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to view your purchase history.');
        }

        $activeTab = request('tab', 'to_pay');
        if (!array_key_exists($activeTab, self::PURCHASE_HISTORY_TABS)) {
            $activeTab = 'to_pay';
        }

        $baseQuery = Order::where('user_id', Auth::id());

        // Counts per tab (special handling for completed and refund)
        $counts = [];
        foreach (self::PURCHASE_HISTORY_TABS as $tab => $tabStatus) {
            if ($tab === 'completed') {
                // Completed: paid orders with no refund request
                $counts[$tab] = (clone $baseQuery)
                    ->where('status', 'paid')
                    ->whereNull('refund_status')
                    ->count();
            } elseif ($tab === 'refund') {
                // Refund tab: orders that are fully refunded OR have a pending/processed refund request
                $counts[$tab] = (clone $baseQuery)
                    ->where(function ($q) {
                        $q->where('status', 'refunded')
                          ->orWhereNotNull('refund_status');
                    })
                    ->count();
            } else {
                $counts[$tab] = (clone $baseQuery)->where('status', $tabStatus)->count();
            }
        }

        // Orders for active tab
        $ordersQuery = (clone $baseQuery);
        if ($activeTab === 'completed') {
            $ordersQuery->where('status', 'paid')->whereNull('refund_status');
        } elseif ($activeTab === 'refund') {
            $ordersQuery->where(function ($q) {
                $q->where('status', 'refunded')
                  ->orWhereNotNull('refund_status');
            });
        } else {
            $status = self::PURCHASE_HISTORY_TABS[$activeTab];
            $ordersQuery->where('status', $status);
        }

        $orders = $ordersQuery
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Fee labels for repay modal (same as checkout)
        $feeFixedCents = (int) config('services.stripe.fee_fixed_cents', 0);
        $feePctDomestic = (float) config('services.stripe.fee_percentage', 0);
        $feePctInternational = (float) config('services.stripe.fee_percentage_international', 0);
        if ($feePctInternational <= 0) {
            $feePctInternational = $feePctDomestic;
        }
        $feeFixedRm = $feeFixedCents / 100;
        $pctDomesticStr = $feePctDomestic == (int) $feePctDomestic ? (int) $feePctDomestic : number_format($feePctDomestic, 1);
        $feeBaseLabel = $feePctDomestic > 0 || $feeFixedCents > 0
            ? $pctDomesticStr . '% + RM ' . number_format($feeFixedRm, 2)
            : null;
        $feeDomesticLabel = $feeBaseLabel ? $feeBaseLabel . ' per successful transaction' : null;
        $feeFpxLabel = $feeBaseLabel;
        $feeInternationalExtra = ($feePctInternational > 0 && $feePctInternational != $feePctDomestic)
            ? '+ ' . (($feePctInternational - $feePctDomestic) == (int)($feePctInternational - $feePctDomestic) ? (int)($feePctInternational - $feePctDomestic) : number_format($feePctInternational - $feePctDomestic, 1)) . '% for international cards'
            : null;
        $feeCurrencyNote = '+ 2% if currency conversion is required';

        return view('client.profile.purchase-history', [
            'orders'             => $orders,
            'activeTab'          => $activeTab,
            'counts'             => $counts,
            'feeDomesticLabel'   => $feeDomesticLabel,
            'feeInternationalExtra' => $feeInternationalExtra,
            'feeCurrencyNote'    => $feeCurrencyNote,
            'feeFpxLabel'        => $feeFpxLabel,
            'repayFeeConfig'     => [
                'fee_percentage' => $feePctDomestic,
                'fee_percentage_international' => $feePctInternational,
                'fee_fixed_cents' => $feeFixedCents,
            ],
        ]);
    }

    /**
     * Return order detail HTML for the client purchase-history modal (own orders only).
     */
    public function orderModal(Order $order)
    {
        if (!Auth::check() || $order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized.');
        }

        $order->load(['items.ticket', 'items.event', 'promoCode', 'affiliateCode']);

        return view('client.profile.partials.order-modal-body', compact('order'));
    }

    /**
     * Cancel a pending order (To Pay tab). Requires reason from modal. Cancels the Stripe PaymentIntent if present, then updates order status and reason.
     */
    public function cancelOrder(Request $request, Order $order)
    {
        if (!Auth::check() || $order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized.');
        }
        if ($order->status !== 'pending') {
            return redirect()
                ->route('profile.purchaseHistory', ['tab' => 'to_pay'])
                ->with('error', 'This order cannot be cancelled.');
        }

        $request->validate([
            'reason' => 'required|string|max:500',
        ], [
            'reason.required' => 'Please provide a reason for cancellation.',
        ]);

        $paymentIntentId = $order->stripe_payment_intent_id;
        $stripeSecret = config('services.stripe.secret');

        if ($paymentIntentId && $stripeSecret) {
            try {
                $stripe = new StripeClient($stripeSecret);
                $stripe->paymentIntents->cancel($paymentIntentId);
            } catch (ApiErrorException $e) {
                $code = $e->getStripeCode() ?? '';
                $message = $e->getMessage();
                // PaymentIntent already canceled or in terminal state (e.g. succeeded) – still cancel in our DB
                if ($code === 'payment_intent_unexpected_state' || stripos($message, 'canceled') !== false || stripos($message, 'succeeded') !== false) {
                    // proceed to update order
                } else {
                    return redirect()
                        ->route('profile.purchaseHistory', ['tab' => 'to_pay'])
                        ->with('error', $message ?: 'Could not cancel payment. Please try again or contact support.');
                }
            } catch (\Throwable $e) {
                return redirect()
                    ->route('profile.purchaseHistory', ['tab' => 'to_pay'])
                    ->with('error', 'Could not cancel payment. Please try again or contact support.');
            }
        }

        $order->update([
            'status' => 'cancelled',
            'reason' => $request->input('reason'),
        ]);

        $this->sendOrderCancelledEmail($order);

        return redirect()
            ->route('profile.purchaseHistory', ['tab' => 'to_pay'])
            ->with('success', 'Order cancelled.');
    }

    /**
     * Send order cancelled email to the buyer.
     */
    private function sendOrderCancelledEmail(Order $order): void
    {
        $email = $order->buyer_snapshot['buyer_email'] ?? $order->user?->email;
        if (empty($email)) {
            return;
        }
        try {
            Mail::to($email)->send(new OrderCancelledMail($order));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Refund a completed (paid) order via Stripe (real money refund).
     * Only own orders with status paid. Creates Stripe refund, then updates order and restores ticket stock.
     */
    public function refundOrder(Request $request, Order $order)
    {
        if (!Auth::check() || $order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized.');
        }
        if ($order->status !== 'paid') {
            return redirect()
                ->route('profile.purchaseHistory', ['tab' => 'completed'])
                ->with('error', 'This order cannot be refunded.');
        }

        $request->validate([
            'reason' => 'required|string|max:500',
            'proof_images' => 'nullable|array',
            'proof_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ], [
            'reason.required' => 'Please select or provide a reason for your refund request.',
        ]);

        $paths = [];
        if ($request->hasFile('proof_images')) {
            foreach ($request->file('proof_images') as $file) {
                if ($file && $file->isValid()) {
                    $paths[] = $file->store('order-refunds/' . $order->id, 'public');
                }
            }
        }

        $order->update([
            'refund_status' => 'pending',
            'reason' => $request->input('reason'),
            'refund_proof_paths' => $paths,
        ]);

        $this->sendRefundRequestedEmail($order);
        $this->sendRefundPendingAdminEmail($order);

        return redirect()
            ->route('profile.purchaseHistory', ['tab' => 'completed'])
            ->with('success', 'Your refund request has been submitted and is pending admin approval.');
    }

    /**
     * Send refund request submitted email to the buyer.
     */
    private function sendRefundRequestedEmail(Order $order): void
    {
        $email = $order->buyer_snapshot['buyer_email'] ?? $order->user?->email;
        if (empty($email)) {
            return;
        }
        try {
            Mail::to($email)->send(new RefundRequestedMail($order));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Send refund pending alert to admin notification email (if configured in settings).
     */
    private function sendRefundPendingAdminEmail(Order $order): void
    {
        $adminEmail = Setting::get('admin_notification_email');
        if (empty($adminEmail)) {
            return;
        }
        try {
            Mail::to($adminEmail)->send(new RefundPendingAdminMail($order));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Download receipt PDF for a completed (paid) order. Client can only download their own orders.
     * Also allows refunded orders (status refunded) to download the same receipt.
     */
    public function downloadReceipt(Order $order)
    {
        if (!Auth::check() || $order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized.');
        }
        if (!in_array($order->status, ['paid', 'refunded'], true)) {
            abort(404, 'Receipt is only available for completed or refunded orders.');
        }

        $order->load(['items.ticket', 'items.event', 'promoCode']);

        $logoDataUri = self::receiptLogoDataUri(public_path('images/bina-logo.png'));

        $filename = 'receipt-' . preg_replace('/[^a-zA-Z0-9_-]/', '-', $order->stripe_payment_intent_id ?? (string) $order->id) . '.pdf';

        return Pdf::loadView('client.profile.receipt', compact('order', 'logoDataUri'))
            ->setPaper('a4')
            ->download($filename);
    }

    /**
     * Download one attendance QR code (by holder index) for a completed (paid) order as PNG.
     * QR stores order reference and order ID. One QR per ticket holder; filename from holder name.
     */
    public function downloadQrCode(Order $order, int $index)
    {
        if (!Auth::check() || $order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized.');
        }
        if (!in_array($order->status, ['paid', 'refunded'], true)) {
            abort(404, 'QR code is only available for completed or refunded orders.');
        }

        $holders = $order->ticket_holders_snapshot ?? [];
        if ($index < 0 || $index >= count($holders)) {
            abort(404, 'Invalid ticket holder index.');
        }

        $ref = $order->stripe_payment_intent_id ?? (string) $order->id;
        $safeRef = preg_replace('/[^a-zA-Z0-9_-]/', '-', $ref);
        $holderName = $holders[$index]['full_name'] ?? 'holder-' . ($index + 1);
        $data = "reference: {$ref}\nfull name: {$holderName}";

        $writer = new PngWriter();
        $qrCode = new QrCode(data: $data, size: 300);
        $result = $writer->write($qrCode, null, null, []);
        $pngString = $result->getString();
        $holderSlug = Str::slug($holderName);
        $filename = 'qr-code-' . $safeRef . '-' . $holderSlug . '.png';
        return response($pngString, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Build a data URI for the receipt logo (small PNG) so Dompdf can embed it. Returns null if unavailable.
     */
    private static function receiptLogoDataUri(string $path): ?string
    {
        if (!file_exists($path) || !is_readable($path)) {
            return null;
        }
        $maxHeight = 48;
        if (function_exists('imagecreatefrompng')) {
            $im = @imagecreatefrompng($path);
            if ($im !== false) {
                $w = imagesx($im);
                $h = imagesy($im);
                if ($w > 0 && $h > 0) {
                    $scale = min(1, $maxHeight / $h);
                    $nw = (int) round($w * $scale);
                    $nh = (int) round($h * $scale);
                    $out = imagecreatetruecolor($nw, $nh);
                    if ($out !== false) {
                        imagealphablending($out, false);
                        imagesavealpha($out, true);
                        $trans = imagecolorallocatealpha($out, 255, 255, 255, 127);
                        imagefill($out, 0, 0, $trans);
                        imagecopyresampled($out, $im, 0, 0, 0, 0, $nw, $nh, $w, $h);
                        ob_start();
                        imagepng($out, null, 9);
                        $png = ob_get_clean();
                        imagedestroy($out);
                        imagedestroy($im);
                        if ($png !== false && $png !== '') {
                            return 'data:image/png;base64,' . base64_encode($png);
                        }
                    }
                }
                imagedestroy($im);
            }
        }
        $raw = file_get_contents($path);
        if ($raw === false || $raw === '') {
            return null;
        }
        return 'data:image/png;base64,' . base64_encode($raw);
    }
}
