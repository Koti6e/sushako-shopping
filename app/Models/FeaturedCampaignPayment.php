<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['featured_campaign_id', 'provider', 'provider_order_id', 'provider_payment_id', 'amount', 'currency', 'status', 'event_id', 'verified_at', 'metadata'])]
class FeaturedCampaignPayment extends Model
{
    public function campaign(): BelongsTo { return $this->belongsTo(FeaturedCampaign::class, 'featured_campaign_id'); }

    protected function casts(): array { return ['metadata' => 'array', 'verified_at' => 'datetime']; }
}
