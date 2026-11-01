<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['featured_campaign_id', 'event_type', 'fingerprint', 'user_id'])]
class FeaturedCampaignEvent extends Model
{
    public function campaign(): BelongsTo { return $this->belongsTo(FeaturedCampaign::class, 'featured_campaign_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
