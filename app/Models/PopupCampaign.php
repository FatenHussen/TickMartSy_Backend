<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class PopupCampaign extends Model
{
    use HasTranslations;

    public const TYPE_MODAL = 'modal';
    public const TYPE_SLIDE_IN = 'slide_in';
    public const TYPE_FULLSCREEN = 'fullscreen';

    public const TYPES = [
        self::TYPE_MODAL,
        self::TYPE_SLIDE_IN,
        self::TYPE_FULLSCREEN,
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_ARCHIVED = 'archived';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_ACTIVE,
        self::STATUS_PAUSED,
        self::STATUS_ARCHIVED,
    ];

    public const BUTTON_CLAIM_OFFER = 'Claim Offer';
    public const BUTTON_SHOP_NOW = 'Shop Now';
    public const BUTTON_SUBSCRIBE = 'Subscribe';
    public const BUTTON_REVEAL_MY_DEAL = 'Reveal My Deal';

    public const BUTTON_OPTIONS = [
        self::BUTTON_CLAIM_OFFER,
        self::BUTTON_SHOP_NOW,
        self::BUTTON_SUBSCRIBE,
        self::BUTTON_REVEAL_MY_DEAL,
    ];

    // Kept for backward compatibility with existing seed data.
    public const CTA_URL = 'url';
    public const CTA_PRODUCT = 'product';
    public const CTA_CATEGORY = 'category';
    public const CTA_FORM = 'form';
    public const CTA_COUPON = 'coupon';

    public const CTA_TYPES = [
        self::CTA_URL,
        self::CTA_PRODUCT,
        self::CTA_CATEGORY,
        self::CTA_FORM,
        self::CTA_COUPON,
    ];

    public const MEDIA_IMAGE = 'image';
    public const MEDIA_VIDEO = 'video';
    public const MEDIA_GIF = 'gif';

    public const MEDIA_TYPES = [
        self::MEDIA_IMAGE,
        self::MEDIA_VIDEO,
        self::MEDIA_GIF,
    ];

    public const AUDIENCE_ALL = 'all_visitors';
    public const AUDIENCE_GUESTS = 'guests_only';
    public const AUDIENCE_LOGGED_IN = 'logged_in_only';
    public const AUDIENCE_NEW = 'new_visitors';
    public const AUDIENCE_RETURNING = 'returning_visitors';

    public const AUDIENCE_TYPES = [
        self::AUDIENCE_ALL,
        self::AUDIENCE_GUESTS,
        self::AUDIENCE_LOGGED_IN,
        self::AUDIENCE_NEW,
        self::AUDIENCE_RETURNING,
    ];

    public const TRIGGER_ON_LOAD = 'on_load';
    public const TRIGGER_DELAY = 'delay';
    public const TRIGGER_SCROLL = 'scroll';
    public const TRIGGER_EXIT_INTENT = 'exit_intent';

    public const TRIGGER_TYPES = [
        self::TRIGGER_ON_LOAD,
        self::TRIGGER_DELAY,
        self::TRIGGER_SCROLL,
        self::TRIGGER_EXIT_INTENT,
    ];

    protected $fillable = [
        'title',
        'headline',
        'subheadline',
        'description',
        'slug',
        'type',
        'status',
        'priority',

        'button_text',
        'button_url',
        'secondary_button_text',
        'media_type',
        'media_path',
        'form_enabled',
        'form_fields',
        'show_on_pages',
        'audience_type',
        'trigger_type',
        'trigger_value',
        'show_every',
        'max_impressions',
    ];

    protected $casts = [
        'title' => 'array',
        'headline' => 'array',
        'subheadline' => 'array',
        'description' => 'array',
        'form_fields' => 'array',
        'show_on_pages' => 'array',
        'form_enabled' => 'boolean',
        'priority' => 'integer',
        'trigger_value' => 'integer',
        'show_every' => 'integer',
        'max_impressions' => 'integer',
    ];

    public array $translatable = [
        'title',
        'headline',
        'subheadline',
        'description',
    ];

    public function getMediaPathAttribute(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $value)) {
            return $value;
        }

        return asset('storage/' . $value);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeOrderedByPriority(Builder $query): Builder
    {
        return $query->orderByDesc('priority');
    }
}
