<?php

namespace App\Services;

use App\Models\PopupCampaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class PopupCampaignSelector
{
    public function getActiveCampaignForRequest(Request $request): ?PopupCampaign
    {
        $pageType = $this->normalizePageType($request->get('page_type', 'home'));
        $visitorType = $this->detectVisitorType();
        $isGuest = !$this->determineAuthenticatedUser();

        foreach (
            PopupCampaign::active()
                ->orderedByPriority()
                ->with(['pages'])
                ->withCount('attachableLinks')
                ->cursor() as $campaign
        ) {
            if (
                $this->matchesAudience($campaign, $isGuest, $visitorType) &&
                $this->matchesPage($campaign, $pageType) &&
                $campaign->matchesAttachableContext($request)
            ) {
                return $campaign;
            }
        }

        return null;
    }

    protected function determineAuthenticatedUser()
    {
        return Auth::guard('user')->user()
            ?? Auth::user();
    }

    protected function detectVisitorType(): string
    {
        if (!Session::has('popup_campaign_first_visit')) {
            Session::put('popup_campaign_first_visit', now()->toDateTimeString());
            return PopupCampaign::AUDIENCE_NEW;
        }

        return PopupCampaign::AUDIENCE_RETURNING;
    }

    protected function matchesAudience(PopupCampaign $campaign, bool $isGuest, string $visitorType): bool
    {
        return match ($campaign->audience_type) {
            PopupCampaign::AUDIENCE_ALL => true,
            PopupCampaign::AUDIENCE_GUESTS => $isGuest,
            PopupCampaign::AUDIENCE_LOGGED_IN => !$isGuest,
            PopupCampaign::AUDIENCE_NEW => $visitorType === PopupCampaign::AUDIENCE_NEW,
            PopupCampaign::AUDIENCE_RETURNING => $visitorType === PopupCampaign::AUDIENCE_RETURNING,
            default => true,
        };
    }

    protected function matchesPage(PopupCampaign $campaign, string $pageType): bool
    {
        if (!$campaign->relationLoaded('pages')) {
            $campaign->load('pages');
        }

        if ($campaign->pages->isEmpty()) {
            return true;
        }

        $normalized = $this->normalizePageType($pageType);
        $slugs = $campaign->pages->pluck('slug')
            ->map(fn (string $slug) => $this->normalizePageType($slug))
            ->unique()
            ->all();

        return in_array($normalized, $slugs, true);
    }

    protected function normalizePageType(?string $value): string
    {
        return Str::of($value ?? 'home')->trim()->lower()->replace(' ', '_')->__toString();
    }
}
