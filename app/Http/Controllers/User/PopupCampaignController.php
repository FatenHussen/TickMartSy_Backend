<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\PopupCampaignResource;
use App\Models\PopupCampaign;
use App\Models\PopupCampaignEvent;
use App\Services\PopupCampaignSelector;
use Illuminate\Http\Request;

class PopupCampaignController extends Controller
{
    public function __construct(private PopupCampaignSelector $selector) {}

    public function active(Request $request)
    {
        $campaign = $this->selector->getActiveCampaignForRequest($request);

        if (!$campaign) {
            return response()->json(['data' => null]);
        }

        $campaign->loadAttachablesForUserApi();

        return response()->json(['data' => new PopupCampaignResource($campaign)]);
    }

    public function trackView(PopupCampaign $popupCampaign, Request $request)
    {
        PopupCampaignEvent::create([
            'popup_campaign_id' => $popupCampaign->id,
            'event_type' => PopupCampaignEvent::EVENT_VIEW,
            'payload' => $request->only(['page_type', 'current_url', 'referrer']),
        ]);

        return response()->json([], 204);
    }

    public function trackClick(PopupCampaign $popupCampaign, Request $request)
    {
        PopupCampaignEvent::create([
            'popup_campaign_id' => $popupCampaign->id,
            'event_type' => PopupCampaignEvent::EVENT_CLICK,
            'payload' => $request->only(['page_type', 'current_url', 'referrer']),
        ]);

        return response()->json([], 204);
    }
}
