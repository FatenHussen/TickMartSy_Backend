<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\NavMenuItem;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Section;
use App\Models\SectionItem;
use App\Services\Base\CategoryDeleteImpactService;
use App\Support\DisplayTypeCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeletedCategoryUserVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DisplayTypeSeeder::class);
    }

    public function test_user_categories_excludes_soft_deleted_and_inactive(): void
    {
        $alive = Category::create([
            'name' => ['en' => 'Alive', 'ar' => 'حية'],
            'is_active' => true,
        ]);
        $inactive = Category::create([
            'name' => ['en' => 'Inactive', 'ar' => 'موقوفة'],
            'is_active' => false,
        ]);
        $deleted = Category::create([
            'name' => ['en' => 'Deleted', 'ar' => 'محذوفة'],
            'is_active' => true,
        ]);
        $deleted->delete();

        $response = $this->getJson('/api/user/categories');
        $response->assertOk();

        $ids = collect($response->json('data.items'))->pluck('id')->all();

        $this->assertContains($alive->id, $ids);
        $this->assertNotContains($inactive->id, $ids);
        $this->assertNotContains($deleted->id, $ids);
    }

    public function test_children_of_deleted_parent_do_not_appear_as_roots_or_children(): void
    {
        $parent = Category::create([
            'name' => ['en' => 'Parent', 'ar' => 'أب'],
            'is_active' => true,
        ]);
        $child = Category::create([
            'name' => ['en' => 'Child', 'ar' => 'ابن'],
            'parent_id' => $parent->id,
            'is_active' => true,
        ]);

        (new CategoryDeleteImpactService())->executeDelete($parent);

        $roots = $this->getJson('/api/user/categories');
        $roots->assertOk();
        $rootIds = collect($roots->json('data.items'))->pluck('id')->all();
        $this->assertNotContains($parent->id, $rootIds);
        $this->assertNotContains($child->id, $rootIds);

        $byParent = $this->getJson('/api/user/categories?parent_id=' . $parent->id);
        $byParent->assertStatus(422);
    }

    public function test_nav_menu_drops_deleted_category_links(): void
    {
        $category = Category::create([
            'name' => ['en' => 'Nav Cat', 'ar' => 'فئة قائمة'],
            'is_active' => true,
        ]);

        NavMenuItem::create([
            'title' => ['en' => 'Link', 'ar' => 'رابط'],
            'type' => 'category',
            'category_id' => $category->id,
            'order' => 1,
            'is_active' => true,
        ]);

        NavMenuItem::create([
            'title' => ['en' => 'Shops', 'ar' => 'متاجر'],
            'type' => 'route',
            'route_key' => 'shops',
            'order' => 2,
            'is_active' => true,
        ]);

        (new CategoryDeleteImpactService())->executeDelete($category);

        $response = $this->getJson('/api/user/nav-menu');
        $response->assertOk();

        $items = collect($response->json('data'));
        $this->assertFalse($items->contains(fn ($row) => ($row['type'] ?? null) === 'category'));
        $this->assertTrue($items->contains(fn ($row) => ($row['target']['route_key'] ?? null) === 'shops'));
    }

    public function test_sections_filter_out_deleted_category_manual_items(): void
    {
        $keep = Category::create([
            'name' => ['en' => 'Keep', 'ar' => 'تبقى'],
            'is_active' => true,
        ]);
        $remove = Category::create([
            'name' => ['en' => 'Remove', 'ar' => 'تزال'],
            'is_active' => true,
        ]);

        $page = Page::create([
            'title' => 'Home',
            'slug' => 'home-deleted-cat-section',
        ]);

        $section = Section::create([
            'name' => ['en' => 'Cats', 'ar' => 'فئات'],
            'type' => 'manual',
            'manual_model' => 'category',
        ]);

        foreach ([$keep, $remove] as $index => $category) {
            SectionItem::create([
                'section_id' => $section->id,
                'item_type' => Category::class,
                'item_id' => $category->id,
                'order' => $index + 1,
            ]);
        }

        PageSection::create([
            'page_id' => $page->id,
            'section_id' => $section->id,
            'display_type_id' => DisplayTypeCatalog::idFor('category'),
            'position' => 'before',
            'order' => 1,
            'is_active' => true,
        ]);

        (new CategoryDeleteImpactService())->executeDelete($remove);

        $response = $this->getJson('/api/user/sections?page_slug=home-deleted-cat-section');
        $response->assertOk();

        $items = collect($response->json('data.0.items') ?? []);
        $ids = $items->pluck('item.id')->all();

        $this->assertContains($keep->id, $ids);
        $this->assertNotContains($remove->id, $ids);
        $this->assertDatabaseMissing('section_items', [
            'item_type' => Category::class,
            'item_id' => $remove->id,
        ]);
    }

    public function test_category_page_returns_404_for_deleted_category(): void
    {
        $category = Category::create([
            'name' => ['en' => 'Gone', 'ar' => 'راحت'],
            'is_active' => true,
        ]);

        (new CategoryDeleteImpactService())->executeDelete($category);

        $this->getJson("/api/user/categories/{$category->id}/page")
            ->assertNotFound();
    }

    public function test_category_page_returns_404_for_inactive_category(): void
    {
        $category = Category::create([
            'name' => ['en' => 'Off', 'ar' => 'مطفأة'],
            'is_active' => false,
        ]);

        $this->getJson("/api/user/categories/{$category->id}/page")
            ->assertNotFound();
    }
}
