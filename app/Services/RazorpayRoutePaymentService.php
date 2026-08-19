<?php

namespace App\Services;

use App\Models\SellerSettlement;

class RazorpayRoutePaymentService implements SettlementPaymentService
{
    public function markPaid(SellerSettlement $settlement, array $data, int $adminUserId): SellerSettlement
    {
        abort(422, 'Razorpay Route payout is not configured. Use Manual Settlement.');
    }
}
