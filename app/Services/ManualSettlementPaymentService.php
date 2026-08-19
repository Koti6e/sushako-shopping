<?php

namespace App\Services;

use App\Models\SellerSettlement;

class ManualSettlementPaymentService implements SettlementPaymentService
{
    public function __construct(private readonly SettlementBatchService $batches) {}

    public function markPaid(SellerSettlement $settlement, array $data, int $adminUserId): SellerSettlement
    {
        return $this->batches->transition($settlement, 'paid', $adminUserId, $data['admin_note'] ?? null, [
            'paid_at' => $data['payment_date'],
            'payment_reference' => $data['payment_reference'],
            'payment_mode' => $data['payment_mode'],
            'admin_note' => $data['admin_note'] ?? null,
            'payment_marked_by' => $adminUserId,
        ]);
    }
}
