<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PopupCampaignEvent extends Model
{
    public const EVENT_VIEW = 'view';
    public const EVENT_CLICK = 'click';

    protected $fillable = [
        'popup_campaign_id',
        'event_type',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function campaign()
    {
        return $this->belongsTo(PopupCampaign::class);
    }
}
