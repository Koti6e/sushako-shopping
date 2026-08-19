<?php

namespace App\Services;

use App\Models\SellerSettlement;

interface SettlementPaymentService
{
    public function markPaid(SellerSettlement $settlement, array $data, int $adminUserId): SellerSettlement;
}
