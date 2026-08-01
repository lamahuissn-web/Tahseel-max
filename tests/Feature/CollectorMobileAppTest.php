<?php

namespace Tests\Feature;

use Tests\TestCase;

class CollectorMobileAppTest extends TestCase
{
    public function test_manifest_launches_the_localized_collector_interface(): void
    {
        $manifest = $this->readJsonFile('public/collector-manifest.json');

        $this->assertSame('Tahseel Collector', $manifest['name']);
        $this->assertSame('Tahseel', $manifest['short_name']);
        $this->assertSame('/ar/admin/mobile-view', $manifest['start_url']);
        $this->assertSame('/ar/admin/', $manifest['scope']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertSame('portrait', $manifest['orientation']);
        $this->assertSame('#0ea5e9', $manifest['theme_color']);
        $this->assertManifestHasIcon($manifest, '192x192');
        $this->assertManifestHasIcon($manifest, '512x512');
    }

    public function test_mobile_layout_registers_the_collector_pwa(): void
    {
        $layout = $this->readProjectFile('resources/views/dashbord/layouts/mobile_master.blade.php');
        $sharedHead = $this->readProjectFile('resources/views/dashbord/layouts/head.blade.php');
        $desktopManifest = $this->readJsonFile('public/manifest.json');

        $this->assertSame('Tahseel App', $desktopManifest['name']);
        $this->assertSame('/admin/dashboard', $desktopManifest['start_url']);
        $this->assertStringContainsString("['manifestUrl' => asset('collector-manifest.json')]", $layout);
        $this->assertStringContainsString("{{ \$manifestUrl ?? asset('manifest.json') }}", $sharedHead);
        $this->assertStringContainsString('name="theme-color" content="#0ea5e9"', $layout);
        $this->assertStringContainsString('name="tahseel-service-worker" content="{{ asset(\'service-worker.js\') }}"', $layout);
        $this->assertStringContainsString("navigator.serviceWorker.register(workerUrl, { scope: '/ar/admin/' })", $layout);
    }

    public function test_service_worker_never_caches_dynamic_or_financial_requests(): void
    {
        $serviceWorker = $this->readProjectFile('public/service-worker.js');

        $this->assertStringContainsString("request.method !== 'GET'", $serviceWorker);
        $this->assertStringContainsString("request.mode === 'navigate'", $serviceWorker);
        $this->assertStringContainsString('event.respondWith(fetch(request));', $serviceWorker);
        $this->assertStringContainsString('caches.match(OFFLINE_URL)', $serviceWorker);
        $this->assertStringContainsString('cacheName.startsWith(CACHE_PREFIX)', $serviceWorker);
        $this->assertStringNotContainsString('cache.put(request', $serviceWorker);
    }

    public function test_offline_page_explains_that_financial_work_requires_a_connection(): void
    {
        $offlinePage = $this->readProjectFile('public/offline.html');

        $this->assertStringContainsString('لا يوجد اتصال بالإنترنت', $offlinePage);
        $this->assertStringContainsString('لا يمكن تسجيل الدفعات بدون اتصال', $offlinePage);
    }

    public function test_android_wrapper_uses_the_official_twa_launcher(): void
    {
        $buildFile = $this->readProjectFile('mobile/collector-app/app/build.gradle');
        $androidManifest = $this->readProjectFile('mobile/collector-app/app/src/main/AndroidManifest.xml');

        $this->assertStringContainsString('com.google.androidbrowserhelper:androidbrowserhelper:2.7.2', $buildFile);
        $this->assertStringContainsString('applicationId "live.meganet.tahseel.collector"', $buildFile);
        $this->assertStringContainsString('compileSdk 36', $buildFile);
        $this->assertStringContainsString('keyAlias releaseKeyAlias', $buildFile);
        $this->assertStringContainsString('com.google.androidbrowserhelper.trusted.LauncherActivity', $androidManifest);
        $this->assertStringContainsString('android.support.customtabs.trusted.DEFAULT_URL', $androidManifest);
        $this->assertStringContainsString('https://tahseel.meganet.live/ar/admin/mobile-view', $androidManifest);
    }

    public function test_digital_asset_links_match_the_android_package_and_a_release_certificate(): void
    {
        $statements = $this->readJsonFile('public/.well-known/assetlinks.json');
        $target = $statements[0]['target'];

        $this->assertSame(['delegate_permission/common.handle_all_urls'], $statements[0]['relation']);
        $this->assertSame('android_app', $target['namespace']);
        $this->assertSame('live.meganet.tahseel.collector', $target['package_name']);
        $this->assertMatchesRegularExpression(
            '/^(?:[0-9A-F]{2}:){31}[0-9A-F]{2}$/',
            $target['sha256_cert_fingerprints'][0]
        );
    }

    public function test_apk_workflow_uses_protected_signing_secrets(): void
    {
        $workflow = $this->readProjectFile('.github/workflows/build-collector-apk.yml');
        $gitignore = $this->readProjectFile('.gitignore');

        $this->assertStringContainsString('TAHSEEL_ANDROID_KEYSTORE_BASE64', $workflow);
        $this->assertStringContainsString('assembleRelease', $workflow);
        $this->assertStringContainsString('apksigner" verify', $workflow);
        $this->assertStringContainsString('*.jks', $gitignore);
        $this->assertStringContainsString('*.p12', $gitignore);
    }

    private function assertManifestHasIcon(array $manifest, string $size): void
    {
        $sizes = array_column($manifest['icons'], 'sizes');

        $this->assertContains($size, $sizes);
    }

    private function readJsonFile(string $relativePath): array
    {
        return json_decode($this->readProjectFile($relativePath), true, flags: JSON_THROW_ON_ERROR);
    }

    private function readProjectFile(string $relativePath): string
    {
        $path = dirname(__DIR__, 2).'/'.$relativePath;
        $content = file_get_contents($path);

        $this->assertNotFalse($content, "Unable to read {$relativePath}");

        return $content;
    }
}
