<?php

namespace Tests\Feature;

use App\Livewire\Admin\Settings;
use App\Models\BusinessSetting;
use App\Models\CmsMenu;
use App\Models\CmsMenuItem;
use App\Models\CmsPage;
use App\Models\User;
use Database\Seeders\BusinessSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;
use Tests\TestCase;

class ContactAndSocialSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function seedHomePage(): CmsPage
    {
        return CmsPage::query()->create([
            'title' => 'Home',
            'slug' => 'home',
            'content' => '<p>Clean solar energy store</p>',
        ]);
    }

    public function test_guest_topbar_displays_hi_guest_and_hides_unconfigured_contact_social_and_whatsapp(): void
    {
        $this->seedHomePage();
        $this->seed(BusinessSettingSeeder::class);

        $guestResponse = $this->get(route('home'));

        $guestResponse->assertOk()
            ->assertSee('Hi, Guest')
            ->assertSee('Log in')
            ->assertSee('Create account')
            ->assertDontSee('mailto:', false)
            ->assertDontSee('tel:', false)
            ->assertDontSee('class="sf-whatsapp-float', false)
            ->assertDontSee('class="sf-social-link"', false);

        $customer = User::factory()->create(['name' => 'Amina Hassan', 'is_admin' => false]);

        $this->actingAs($customer)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Hi, Amina')
            ->assertDontSee('Hi, Guest');
    }

    public function test_configured_contact_channels_socials_and_whatsapp_render_across_storefront_chrome(): void
    {
        $this->seedHomePage();
        $this->seed(BusinessSettingSeeder::class);

        $settings = [
            'contact_email' => 'sales@hubafrika.test',
            'contact_phone' => '+255 712 345 678',
            'whatsapp_number' => '+255 712 345 678',
            'footer_about' => 'Trusted solar systems across East Africa.',
            'social_facebook' => 'https://facebook.com/hubafrika',
            'social_instagram' => 'https://instagram.com/hubafrika',
            'social_x' => 'https://x.com/hubafrika',
            'social_youtube' => 'https://youtube.com/@hubafrika',
            'social_linkedin' => 'https://linkedin.com/company/hubafrika',
            'social_tiktok' => 'https://tiktok.com/@hubafrika',
        ];

        foreach ($settings as $type => $value) {
            BusinessSetting::query()->updateOrCreate(['type' => $type], ['value' => $value]);
        }

        $homeResponse = $this->get(route('home'));
        $homeResponse->assertOk()
            ->assertSee('href="mailto:sales@hubafrika.test"', false)
            ->assertSee('sales@hubafrika.test')
            ->assertSee('href="tel:+255712345678"', false)
            ->assertSee('+255 712 345 678')
            ->assertSee('Hi, Guest')
            ->assertSee('Log in')
            ->assertSee('href="https://wa.me/255712345678"', false)
            ->assertSee('sf-whatsapp-float', false)
            ->assertSeeInOrder([
                'class="sf-footer-about"',
                'Trusted solar systems across East Africa.',
                'class="sf-socials"',
                'https://facebook.com/hubafrika',
                'https://instagram.com/hubafrika',
                'https://x.com/hubafrika',
                'https://youtube.com/@hubafrika',
                'https://linkedin.com/company/hubafrika',
                'https://tiktok.com/@hubafrika',
            ], false);

        $this->get(route('shop'))
            ->assertOk()
            ->assertSee('href="mailto:sales@hubafrika.test"', false)
            ->assertSee('href="tel:+255712345678"', false)
            ->assertSee('href="https://wa.me/255712345678"', false)
            ->assertSee('Hi, Guest');

        $frontendHtml = Blade::render('<x-layouts.frontend><div>Store preview</div></x-layouts.frontend>');
        $this->assertStringContainsString('href="https://wa.me/255712345678"', $frontendHtml);
        $this->assertStringContainsString('Hi, Guest', $frontendHtml);

        $customer = User::factory()->create(['name' => 'Juma Bakari', 'is_admin' => false]);
        $this->actingAs($customer)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('href="https://wa.me/255712345678"', false)
            ->assertDontSee('Hi, Guest');
    }

    public function test_whatsapp_full_url_is_preserved_and_duplicate_or_unsafe_links_are_ignored(): void
    {
        $this->seedHomePage();
        $this->seed(BusinessSettingSeeder::class);

        BusinessSetting::query()->updateOrCreate(
            ['type' => 'whatsapp_number'],
            ['value' => 'https://wa.me/255712345678?text=Hello'],
        );
        BusinessSetting::query()->updateOrCreate(
            ['type' => 'social_facebook'],
            ['value' => 'https://facebook.com/hubafrika'],
        );
        BusinessSetting::query()->updateOrCreate(
            ['type' => 'social_instagram'],
            ['value' => 'javascript:alert(1)'],
        );

        $footerMenu = CmsMenu::query()->create([
            'name' => 'Footer',
            'slug' => 'footer',
            'location' => 'footer',
            'status' => true,
        ]);

        CmsMenuItem::query()->create([
            'menu_id' => $footerMenu->id,
            'label' => 'Facebook',
            'type' => 'custom',
            'url' => 'https://facebook.com/hubafrika',
            'order' => 1,
            'status' => true,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('href="https://wa.me/255712345678?text=Hello"', false)
            ->assertDontSee('javascript:alert(1)', false);

        $this->assertSame(1, substr_count($response->getContent(), 'class="sf-social-link"'));
    }

    public function test_admin_can_view_update_validate_and_clear_contact_and_social_settings(): void
    {
        $this->seed(BusinessSettingSeeder::class);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Settings::class)
            ->assertDontSee('min-w-full divide-y')
            ->assertSee('Contact & social')
            ->assertSee('Contact email')
            ->assertSee('Contact phone')
            ->assertSee('WhatsApp number')
            ->assertSee('Facebook URL')
            ->assertSee('Instagram URL')
            ->assertSee('X (Twitter) URL')
            ->assertSee('YouTube URL')
            ->assertSee('LinkedIn URL')
            ->assertSee('TikTok URL')
            ->call('edit', 'contact_email')
            ->set('value', 'not-an-email')
            ->call('save')
            ->assertHasErrors(['value'])
            ->set('value', 'support@hubafrika.test')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('business_settings', [
            'type' => 'contact_email',
            'value' => 'support@hubafrika.test',
        ]);

        Livewire::test(Settings::class)
            ->call('edit', 'contact_phone')
            ->set('value', 'invalid-phone')
            ->call('save')
            ->assertHasErrors(['value'])
            ->set('value', '+255 625 003 073')
            ->call('save')
            ->assertHasNoErrors();

        Livewire::test(Settings::class)
            ->call('edit', 'whatsapp_number')
            ->set('value', '123')
            ->call('save')
            ->assertHasErrors(['value'])
            ->set('value', 'ftp://wa.me/255712345678')
            ->call('save')
            ->assertHasErrors(['value'])
            ->set('value', '+255 712 345 678')
            ->call('save')
            ->assertHasNoErrors();

        Livewire::test(Settings::class)
            ->call('edit', 'social_facebook')
            ->set('value', 'javascript:alert(1)')
            ->call('save')
            ->assertHasErrors(['value'])
            ->set('value', 'https://facebook.com/hubafrika')
            ->call('save')
            ->assertHasNoErrors();

        // Admin can clear nullable contact email back to empty to hide it on the storefront.
        Livewire::test(Settings::class)
            ->call('edit', 'contact_email')
            ->set('value', '')
            ->call('save')
            ->assertHasNoErrors();

        $emailValue = BusinessSetting::query()->where('type', 'contact_email')->value('value');
        $this->assertTrue($emailValue === '' || $emailValue === null);
    }
}
