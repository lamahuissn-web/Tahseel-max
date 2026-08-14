<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

class WhatsAppTabNavLayeringTest extends TestCase
{
    public function test_mobile_tab_navigation_stays_below_keen_drawer_and_overlay(): void
    {
        $blade = file_get_contents(dirname(__DIR__, 2).'/resources/views/dashbord/whatsapp/_partials/tab-nav.blade.php');

        $this->assertStringNotContainsString('style="z-index: 1020;"', $blade);
        $this->assertMatchesRegularExpression(
            '/\.whatsapp-tab-nav\s*\{[^}]*z-index:\s*1020;/s',
            $blade
        );
        $this->assertMatchesRegularExpression(
            '/@media\s*\(max-width:\s*991\.98px\)\s*\{\s*\.whatsapp-tab-nav\s*\{[^}]*z-index:\s*100\s*!important;/s',
            $blade
        );
    }
}
