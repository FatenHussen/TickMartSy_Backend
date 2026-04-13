<?php

namespace App\Services;

use App\Models\PopupCampaign;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class PopupCampaignSelector
{
    public function getActiveCampaignForRequest(Request $request): ?PopupCampaign
    {
        $pageType = $this->normalizePageType($request->get('page_type', 'home'));
        $currentUrl = $request->get('current_url', $request->fullUrl());
        $visitorType = $this->detectVisitorType();
        $isGuest = !$this->determineAuthenticatedUser();

        foreach (PopupCampaign::active()->orderedByPriority()->cursor() as $campaign) {
            if (
                $this->matchesAudience($campaign, $isGuest, $visitorType) &&
                $this->matchesPage($campaign, $pageType, $currentUrl, $request)
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

    protected function matchesPage(PopupCampaign $campaign, string $pageType, string $currentUrl, Request $request): bool
    {
        $showOn = $campaign->show_on_pages ?? [];

        if (empty($showOn)) {
            return true; // no restriction
        }

        [$includes, $customUrls, $excludes] = $this->normalizeShowOnPages($showOn);

        $path = '/'.ltrim($request->path(), '/');

        if ($this->matchesAnyUrl($path, $excludes, $currentUrl)) {
            return false;
        }

        if (in_array($pageType, $includes, true)) {
            return true;
        }

        if ($this->matchesAnyUrl($currentUrl, $customUrls)) {
            return true;
        }

        return false;
    }

    /**
     * @return array{0: array<string>, 1: array<string>, 2: array<string>}
     */
    protected function normalizeShowOnPages(array $showOn): array
    {
        $includes = [];
        $customUrls = [];
        $excludes = [];

        foreach ($showOn as $key => $value) {
            if (is_string($key)) {
                if (Str::contains($key, 'exclude')) {
                    $excludes = array_merge($excludes, Arr::wrap($value));
                    continue;
                }

                if ($key === 'custom_url') {
                    $customUrls = array_merge($customUrls, Arr::wrap($value));
                    continue;
                }
            }

            if (is_string($value)) {
                $includes[] = $this->normalizePageType($value);
            } elseif (is_array($value)) {
                if (isset($value['name']) && $value['name'] === 'custom_url') {
                    $customUrls = array_merge($customUrls, Arr::wrap($value['value'] ?? []));
                } elseif (isset($value['name'])) {
                    $includes[] = $this->normalizePageType($value['name']);
                }
            }
        }

        return [
            array_unique($includes),
            array_unique($customUrls),
            array_unique($excludes),
        ];
    }

    protected function matchesAnyUrl(string $haystack, array $urls, string $currentUrl = ''): bool
    {
        foreach ($urls as $url) {
            if (empty($url)) {
                continue;
            }

            if (str_contains($haystack, $url) || str_contains($currentUrl, $url)) {
                return true;
            }
        }

        return false;
    }

    protected function normalizePageType(?string $value): string
    {
        return Str::of($value ?? 'home')->trim()->lower()->replace(' ', '_')->__toString();
    }
}
