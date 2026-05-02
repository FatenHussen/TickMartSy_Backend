<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PopupCampaignAttachable extends Model
{
    protected $table = 'popup_campaign_attachables';

    protected $fillable = [
        'popup_campaign_id',
        'attachable_type',
        'attachable_id',
    ];

    public function popupCampaign(): BelongsTo
    {
        return $this->belongsTo(PopupCampaign::class);
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }
}
