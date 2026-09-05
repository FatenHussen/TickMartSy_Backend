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
}
