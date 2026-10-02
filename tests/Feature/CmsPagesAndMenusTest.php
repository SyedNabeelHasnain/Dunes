<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\User;
use App\Services\CmsContentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsPagesAndMenusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure migrations have run and seed default pages/menus if needed
        $this->artisan('migrate');
    }

    public function test_admin_can_view_pages_index_and_edit_page(): void
    {
        $admin = User::factory()->create();

        $page = Page::create([
            'slug' => 'test-page',
            'name' => 'Test Page',
            'title' => ['en' => 'Test Page Title', 'ar' => 'عنوان صفحة تجريبية'],
            'status' => 'published',
        ]);

        $section = PageSection::create([
            'page_id' => $page->id,
            'section_key' => 'hero_banner',
            'name' => 'Hero Banner',
            'title' => ['en' => 'Hero Title', 'ar' => 'عنوان البانر'],
            'is_active' => true,
            'order' => 1,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.pages.index'));
        $response->assertStatus(200);
        $response->assertSee('Test Page');

        $response = $this->actingAs($admin)->get(route('admin.pages.edit', $page));
        $response->assertStatus(200);
        $response->assertSee('Hero Title');
    }

    public function test_admin_can_update_page_and_manage_sections(): void
    {
        $admin = User::factory()->create();

        $page = Page::create([
            'slug' => 'home-custom',
            'name' => 'Old Name',
            'title' => ['en' => 'Old Title'],
            'status' => 'published',
        ]);

        // Update page
        $response = $this->actingAs($admin)->put(route('admin.pages.update', $page), [
            'name' => 'Updated Home Name',
            'title' => ['en' => 'Updated Home Title', 'ar' => 'عنوان محدث'],
            'status' => 'published',
        ]);
        $response->assertRedirect();

        $page->refresh();
        $this->assertEquals('Updated Home Name', $page->name);
        $this->assertEquals('Updated Home Title', $page->getTranslation('title', 'en'));
        $this->assertEquals('عنوان محدث', $page->getTranslation('title', 'ar'));

        // Add section
        $response = $this->actingAs($admin)->post(route('admin.pages.sections.add', $page), [
            'section_key' => 'features',
            'name' => 'Features Section',
            'title' => ['en' => 'Features Title', 'ar' => 'الميزات'],
            'subtitle' => ['en' => 'Sub features'],
            'body' => ['en' => '<p>Feature content</p>'],
            'order' => 10,
        ]);
        $response->assertRedirect();

        $this->assertDatabaseHas('page_sections', [
            'page_id' => $page->id,
            'section_key' => 'features',
        ]);

        $section = PageSection::where('page_id', $page->id)->where('section_key', 'features')->first();

        // Update section
        $response = $this->actingAs($admin)->put(route('admin.pages.sections.update', [$page, $section]), [
            'name' => 'Updated Features',
            'title' => ['en' => 'Updated Features Title', 'ar' => 'الميزات المحدثة'],
            'order' => 20,
            'is_active' => 1,
        ]);
        $response->assertRedirect();
        $section->refresh();
        $this->assertEquals('Updated Features Title', $section->getTranslation('title', 'en'));
        $this->assertEquals(20, $section->order);

        // Toggle section status
        $response = $this->actingAs($admin)->post(route('admin.pages.sections.toggle-status', [$page, $section]));
        $response->assertStatus(200);
        $section->refresh();
        $this->assertFalse($section->is_active);

        // Delete section
        $response = $this->actingAs($admin)->delete(route('admin.pages.sections.delete', [$page, $section]));
        $response->assertRedirect();
        $this->assertDatabaseMissing('page_sections', ['id' => $section->id]);
    }

    public function test_admin_can_manage_menu_items(): void
    {
        $admin = User::factory()->create();

        // Create menu item
        $response = $this->actingAs($admin)->post(route('admin.menus.store'), [
            'location' => 'header',
            'label' => ['en' => 'VIP Experiences', 'ar' => 'تجارب كبار الشخصيات'],
            'url' => '/vip-tours',
            'target' => '_self',
            'order' => 5,
        ]);
        $response->assertRedirect();

        $this->assertDatabaseHas('menu_items', [
            'location' => 'header',
            'url' => '/vip-tours',
        ]);

        $item = MenuItem::where('url', '/vip-tours')->first();
        $this->assertEquals('VIP Experiences', $item->getTranslation('label', 'en'));
        $this->assertEquals('تجارب كبار الشخصيات', $item->getTranslation('label', 'ar'));

        // Update menu item
        $response = $this->actingAs($admin)->put(route('admin.menus.update', $item), [
            'label' => ['en' => 'Ultra VIP Experiences', 'ar' => 'تجارب فائقة الفخامة'],
            'url' => '/ultra-vip',
            'target' => '_blank',
            'order' => 1,
            'is_active' => 1,
        ]);
        $response->assertRedirect();
        $item->refresh();
        $this->assertEquals('Ultra VIP Experiences', $item->getTranslation('label', 'en'));
        $this->assertEquals('/ultra-vip', $item->url);

        // Toggle status
        $response = $this->actingAs($admin)->post(route('admin.menus.toggle-status', $item));
        $response->assertStatus(200);
        $item->refresh();
        $this->assertFalse($item->is_active);

        // Delete
        $response = $this->actingAs($admin)->delete(route('admin.menus.destroy', $item));
        $response->assertRedirect();
        $this->assertDatabaseMissing('menu_items', ['id' => $item->id]);
    }

    public function test_cms_content_service_returns_fallback_when_db_is_empty(): void
    {
        $service = app(CmsContentService::class);

        $section = $service->getSection('non_existent_page', 'need_help');
        $this->assertNull($section);

        $title = $service->getSectionTitle('tours_sidebar', 'non_existent_section', __('ui.tour_sidebar.need_help'));
        $this->assertEquals(__('ui.tour_sidebar.need_help'), $title);

        $footerSafaris = $service->getMenuItems('footer_desert_safaris');
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $footerSafaris);
    }

    public function test_frontend_renders_cms_menu_and_footer_concierge(): void
    {
        MenuItem::create([
            'location' => 'footer_desert_safaris',
            'label' => ['en' => 'Exclusive Luxury Safari', 'ar' => 'سفاري فاخر حصري'],
            'url' => '/exclusive-safari',
            'is_active' => true,
            'order' => 1,
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);

        // Custom menu link should appear in footer
        $response->assertSee('Exclusive Luxury Safari');
        $response->assertSee('/exclusive-safari');

        // Safari Concierge button should be present in footer
        $response->assertSee('Safari Concierge');
    }

    public function test_top_banner_and_welcome_modal_do_not_render_when_disabled_or_inactive(): void
    {
        \App\Models\Setting::updateOrCreate(['setting_key' => 'top_promo_banner_active'], ['setting_value' => '0']);
        \App\Models\Setting::updateOrCreate(['setting_key' => 'promo_top_banner_enabled'], ['setting_value' => '0']);
        \App\Models\Setting::updateOrCreate(['setting_key' => 'welcome_popup_active'], ['setting_value' => '0']);
        \App\Models\Setting::updateOrCreate(['setting_key' => 'promo_welcome_modal_enabled'], ['setting_value' => '0']);

        app(\App\Services\SettingsService::class)->clearCache();

        $response = $this->get('/');
        $response->assertStatus(200);

        // Banner and welcome modal must NOT be rendered in HTML
        $response->assertDontSee('id="dunesTopPromoBanner"', false);
        $response->assertDontSee('id="welcomeOfferModal"', false);
        $response->assertDontSee('Special Online Exclusive: Get 25% OFF');
    }

    public function test_deactivated_coupon_disables_top_banner_and_modal(): void
    {
        \App\Models\Setting::updateOrCreate(['setting_key' => 'top_promo_banner_active'], ['setting_value' => '1']);
        \App\Models\Setting::updateOrCreate(['setting_key' => 'promo_top_banner_enabled'], ['setting_value' => '1']);
        \App\Models\Setting::updateOrCreate(['setting_key' => 'promo_top_banner_code'], ['setting_value' => 'DUNESWELCOME']);

        \App\Models\Coupon::updateOrCreate(
            ['code' => 'DUNESWELCOME'],
            [
                'name' => 'First-Time Welcome 25% Promo (Top Banner)',
                'discount_type' => 'percentage',
                'discount_value' => 25.00,
                'status' => 'inactive',
            ]
        );

        app(\App\Services\SettingsService::class)->clearCache();

        $response = $this->get('/');
        $response->assertStatus(200);

        // Because coupon is inactive, banner must NOT render
        $response->assertDontSee('id="dunesTopPromoBanner"', false);
    }

    public function test_logo_links_to_home_and_home_button_removed_from_menu(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        // Logo has link to home
        $response->assertSee('aria-label="Dunes Discovery Tourism"', false);

        // Header menu starts with All Tours, not a standalone Home button
        $response->assertDontSee('>Home</a>', false);
    }
}
