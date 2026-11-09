<?php

namespace Tests\Unit;

use App\Models\CmsMenuItem;
use Tests\TestCase;

class CmsMenuItemUrlTest extends TestCase
{
    public function test_footer_contact_links_are_accepted(): void
    {
        $this->assertTrue(CmsMenuItem::isSafeUrl('tel:+255625003073'));
        $this->assertTrue(CmsMenuItem::isSafeUrl('tel:+255 625 003 073'));
        $this->assertTrue(CmsMenuItem::isSafeUrl('mailto:info@hubafrika.net'));
        $this->assertTrue(CmsMenuItem::isSafeUrl('/pages/contact'));
        $this->assertTrue(CmsMenuItem::isSafeUrl('https://hubafrika.net/shop'));
    }

    public function test_dangerous_or_malformed_links_are_still_rejected(): void
    {
        $this->assertFalse(CmsMenuItem::isSafeUrl('javascript:alert(1)'));
        $this->assertFalse(CmsMenuItem::isSafeUrl('tel:call-us-now'));
        $this->assertFalse(CmsMenuItem::isSafeUrl('mailto:not-an-email'));
        $this->assertFalse(CmsMenuItem::isSafeUrl('mailto:info@hubafrika.net?subject=Hi'));
        $this->assertFalse(CmsMenuItem::isSafeUrl('data:text/html;base64,PHNjcmlwdD4='));
    }
}
