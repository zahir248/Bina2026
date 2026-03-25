<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    private function hideTestPaymentDataEnabled(): bool
    {
        return Setting::get(SettingsController::KEY_HIDE_TEST_PAYMENT_DATA_IN_ADMIN, '0') === '1';
    }

    public function index()
    {
        // Check if user is authenticated and is admin
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $paidOrders = Order::with(['user', 'items'])
            ->where('status', 'paid')
            ->where(function ($q) {
                $q->whereNull('refund_status')->orWhere('refund_status', 'rejected');
            })
            ->get();

        if ($this->hideTestPaymentDataEnabled()) {
            $paidOrders = $paidOrders->where('stripe_test_mode', false)->values();
        }

        $genderDistribution = $this->buildGenderDistribution($paidOrders);
        $buyerCategoryDistribution = $this->buildBuyerCategoryDistribution($paidOrders);
        $buyerCountryDistribution = $this->buildBuyerCountryDistribution($paidOrders);

        $recentOrders = Order::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();

        if ($this->hideTestPaymentDataEnabled()) {
            $recentOrders = $recentOrders->where('stripe_test_mode', false)->values();
        }

        $ordersCountQuery = Order::query();
        if ($this->hideTestPaymentDataEnabled()) {
            $ordersCountQuery->where('stripe_test_mode', false);
        }
        $totalOrdersCount = $ordersCountQuery->count();

        $revenueQuery = Order::query()->where('status', 'paid')
            ->where(function ($q) {
                $q->whereNull('refund_status')->orWhere('refund_status', 'rejected');
            });

        if ($this->hideTestPaymentDataEnabled()) {
            $revenueQuery->where('stripe_test_mode', false);
        }

        $totalRevenueCents = $revenueQuery->sum('total_amount_cents');

        return view('admin.dashboard', [
            'genderDistribution' => $genderDistribution,
            'buyerCategoryDistribution' => $buyerCategoryDistribution,
            'buyerCountryDistribution' => $buyerCountryDistribution,
            'recentOrders' => $recentOrders,
            'totalOrdersCount' => $totalOrdersCount,
            'totalRevenueCents' => $totalRevenueCents,
        ]);
    }

    /**
     * Build gender counts from all ticket holders (participants) across paid orders.
     *
     * @return array<string, int>
     */
    private function buildGenderDistribution(Collection $orders): array
    {
        $counts = ['male' => 0, 'female' => 0];

        foreach ($orders as $order) {
            $holders = $order->ticket_holders_snapshot ?? [];
            foreach ($holders as $holder) {
                $gender = isset($holder['gender']) ? strtolower((string) $holder['gender']) : '';
                if ($gender === 'male') {
                    $counts['male']++;
                } elseif ($gender === 'female') {
                    $counts['female']++;
                }
            }
        }

        return $counts;
    }

    /**
     * Build buyer category counts from paid orders (one category per order).
     *
     * @return array<string, int>
     */
    private function buildBuyerCategoryDistribution(Collection $orders): array
    {
        $counts = [];

        foreach ($orders as $order) {
            $buyer = $order->buyer_snapshot ?? [];
            $category = isset($buyer['buyer_category']) ? trim((string) $buyer['buyer_category']) : 'other';
            if ($category === '') {
                $category = 'other';
            }
            $counts[$category] = ($counts[$category] ?? 0) + 1;
        }

        return $counts;
    }

    /**
     * Build buyer country counts from paid orders (one country per order).
     *
     * @return array<string, int>
     */
    private function buildBuyerCountryDistribution(Collection $orders): array
    {
        $counts = [];

        foreach ($orders as $order) {
            $buyer = $order->buyer_snapshot ?? [];
            $country = isset($buyer['buyer_country']) ? trim((string) $buyer['buyer_country']) : 'Unknown';
            if ($country === '') {
                $country = 'Unknown';
            }
            $counts[$country] = ($counts[$country] ?? 0) + 1;
        }

        return $counts;
    }
}
