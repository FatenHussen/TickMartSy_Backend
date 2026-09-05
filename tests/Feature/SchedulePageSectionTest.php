<?php

namespace Tests\Feature;

use App\Enums\VariantSection;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Schedule;
use App\Models\Section;
use App\Support\DisplayTypeCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchedulePageSectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\DisplayTypeSeeder::class);
    }

    public function test_api_schedule_section_returns_active_category_cards(): void
    {
        $page = Page::create(['title' => 'Home', 'slug' => 'home-schedules']);

        Schedule::create([
            'name' => ['en' => 'Weekly', 'ar' => 'أسبوعي'],
            'description' => ['en' => 'Every week', 'ar' => 'كل أسبوع'],
            'interval_days' => 7,
            'discount_type' => 'percentage',
            'discount_value' => 20,
            'is_active' => true,
        ]);

        Schedule::create([
            'name' => ['en' => 'Hidden', 'ar' => 'مخفي'],
            'interval_days' => 3,
            'is_active' => false,
        ]);

        $section = Section::create([
            'name' => ['en' => 'Schedule categories', 'ar' => 'فئات الجدولة الزمنية'],
            'type' => 'api',
            'api_method' => 'schedules',
        ]);

        PageSection::create([
            'page_id' => $page->id,
            'section_id' => $section->id,
            'display_type_id' => DisplayTypeCatalog::idFor('schedule'),
            'position' => 'after',
            'order' => 1,
            'variant' => VariantSection::Vertical->value,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/user/sections?page_slug=home-schedules');

        $response->assertOk()
            ->assertJsonPath('data.0.content_type', 'schedule')
            ->assertJsonPath('data.0.api_method', 'schedules')
            ->assertJsonPath('data.0.display_type_id', 11);

        $items = $response->json('data.0.items');
        $this->assertCount(1, $items);
        $this->assertSame(7, $items[0]['interval_days']);
        $this->assertArrayHasKey('image', $items[0]);
        $this->assertArrayHasKey('top_badges', $items[0]);
        $this->assertSame(20.0, (float) $items[0]['discount_value']);
    }

    public function test_manual_schedule_section_hides_inactive_cards(): void
    {
        $page = Page::create(['title' => 'Home', 'slug' => 'home-manual-schedules']);

        $weekly = Schedule::create([
            'name' => ['en' => 'Weekly', 'ar' => 'أسبوعي'],
            'interval_days' => 7,
            'is_active' => true,
        ]);

        $hidden = Schedule::create([
            'name' => ['en' => 'Hidden', 'ar' => 'مخفي'],
            'interval_days' => 30,
            'is_active' => false,
        ]);

        $section = Section::create([
            'name' => ['en' => 'Picked schedules', 'ar' => 'فئات مختارة'],
            'type' => 'manual',
            'manual_model' => 'schedule',
        ]);

        $section->sectionItems()->create([
            'item_type' => Schedule::class,
            'item_id' => $weekly->id,
            'order' => 1,
        ]);
        $section->sectionItems()->create([
            'item_type' => Schedule::class,
            'item_id' => $hidden->id,
            'order' => 2,
        ]);

        PageSection::create([
            'page_id' => $page->id,
            'section_id' => $section->id,
            'display_type_id' => DisplayTypeCatalog::idFor('schedule'),
            'position' => 'after',
            'order' => 1,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/user/sections?page_slug=home-manual-schedules');

        $response->assertOk()
            ->assertJsonPath('data.0.content_type', 'schedule')
            ->assertJsonPath('data.0.manual_model', 'schedule');

        $items = $response->json('data.0.items');
        $this->assertCount(1, $items);
        $this->assertSame($weekly->id, $items[0]['item']['id']);
        $this->assertSame(7, $items[0]['item']['interval_days']);
    }

    public function test_display_type_catalog_includes_schedule(): void
    {
        $this->assertSame(11, DisplayTypeCatalog::idFor('schedule'));
        $this->assertTrue(
            \App\Models\DisplayType::query()->whereKey(11)->where('manual_model', 'schedule')->exists()
        );
    }

    public function test_scheduled_baskets_content_type_is_accepted(): void
    {
        $this->assertContains('schedule-basket', Section::CONTENT_TYPES);
        $this->assertSame('schedule-basket', Section::canonicalizeContentType('scheduled-baskets'));
        $this->assertSame('schedule-basket', Section::canonicalizeContentType('Scheduled baskets'));
        $this->assertSame('schedule', Section::canonicalizeContentType('schedules'));

        $request = \App\Http\Requests\Admin\Section\StoreRequest::create('/api/admin/sections', 'POST', [
            'name' => ['ar' => 'weekly', 'en' => 'weekly'],
            'type' => 'api',
            'content_type' => 'schedule-basket',
        ]);
        $request->setContainer($this->app)->setRedirector($this->app->make('redirect'));
        $request->validateResolved();

        $this->assertSame('schedule-basket', $request->input('content_type'));
        $this->assertSame('schedule-basket', $request->input('api_method'));
    }

    public function test_manual_schedule_section_fills_item_type(): void
    {
        $request = \App\Http\Requests\Admin\Section\StoreRequest::create('/api/admin/sections', 'POST', [
            'name' => ['ar' => 'weekly', 'en' => 'weekly'],
            'type' => 'manual',
            'content_type' => 'schedule',
            'manual_model' => 'schedule',
            'item_ids' => [
                ['item_id' => 1, 'order' => 0],
                ['item_id' => 2, 'order' => 1],
            ],
        ]);
        $request->setContainer($this->app)->setRedirector($this->app->make('redirect'));
        $request->validateResolved();

        $this->assertSame('schedule', $request->input('content_type'));
        $this->assertSame('schedule', $request->input('manual_model'));
        $this->assertSame(\App\Models\Schedule::class, $request->input('item_ids.0.item_type'));
        $this->assertSame(\App\Models\Schedule::class, $request->input('item_ids.1.item_type'));
    }

    public function test_schedule_update_accepts_blank_discount_and_saves_name(): void
    {
        $schedule = Schedule::create([
            'name' => ['ar' => 'أسبوعي', 'en' => 'Weekly'],
            'interval_days' => 7,
            'is_active' => true,
            'discount_type' => 'percentage',
            'discount_value' => 20,
        ]);

        $request = \App\Http\Requests\Admin\Schedule\UpdateRequest::create('/api/admin/schedules/'.$schedule->id, 'POST', [
            'name' => ['ar' => 'شهري', 'en' => 'Monthly'],
            'description' => ['ar' => 'وصف', 'en' => 'Desc'],
            'interval_days' => 30,
            'discount_type' => '',
            'discount_value' => '',
            'is_active' => '0',
        ]);
        $request->setContainer($this->app)->setRedirector($this->app->make('redirect'));
        $request->validateResolved();

        $this->assertNull($request->input('discount_type'));
        $this->assertNull($request->input('discount_value'));

        app(\App\Services\Admin\ScheduleService::class)->update($schedule->id, $request->validated());

        $schedule->refresh();
        $this->assertSame('شهري', $schedule->getTranslation('name', 'ar'));
        $this->assertSame('Monthly', $schedule->getTranslation('name', 'en'));
        $this->assertSame(30, (int) $schedule->interval_days);
        $this->assertNull($schedule->discount_type);
        $this->assertFalse($schedule->is_active);
    }
}
