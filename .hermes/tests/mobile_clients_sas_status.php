<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function assert_true($condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$admin = App\Models\Admin::find(1);
Illuminate\Support\Facades\Auth::setUser($admin);
Illuminate\Support\Facades\Auth::guard('admin')->setUser($admin);
Illuminate\Support\Facades\View::share('errors', new Illuminate\Support\ViewErrorBag);

$client = App\Models\Clients::where('is_active', 1)
    ->whereNotNull('sas_username')
    ->where('sas_username', '!=', '')
    ->orderBy('id', 'desc')
    ->first();

assert_true((bool) $client, 'Test requires at least one active client with sas_username');

$controller = app(App\Http\Controllers\Admin\MobileController::class);

$html = $controller->clients(Illuminate\Http\Request::create('/ar/admin/mobile-clients', 'GET'))->render();
assert_true(str_contains($html, 'id="client_search"'), 'Mobile clients page should still include search input');
assert_true(str_contains($html, 'loadMobileSasStatuses'), 'Mobile clients page should load SAS statuses asynchronously');
assert_true(str_contains($html, 'sasStatusUrl') && str_contains($html, '/sas4/online-status'), 'Mobile SAS status should reuse existing online-status route');
assert_true(!str_contains($html, 'sas4-control'), 'Mobile client list should not expose SAS control actions');

$partial = view('dashbord.mobile_view.partials.clients_list', ['clients' => collect([$client])])->render();
assert_true(str_contains($partial, 'mobile-sas-indicator'), 'Client card should include mobile SAS indicator');
assert_true(str_contains($partial, 'data-username="' . e($client->sas_username) . '"'), 'SAS indicator should expose username for async status lookup');
assert_true(str_contains($partial, e($client->sas_username)), 'Client card should display SAS username');
assert_true(str_contains($partial, 'جاري الفحص'), 'Linked SAS username should show checking state before async status resolves');

$clientWithoutSas = App\Models\Clients::where('is_active', 1)
    ->where(function ($query) {
        $query->whereNull('sas_username')->orWhere('sas_username', '');
    })
    ->first();

if ($clientWithoutSas) {
    $noSasPartial = view('dashbord.mobile_view.partials.clients_list', ['clients' => collect([$clientWithoutSas])])->render();
    assert_true(str_contains($noSasPartial, 'غير مربوط'), 'Client card without SAS username should show not-linked state');
}

$request = Illuminate\Http\Request::create('/ar/admin/mobile-clients', 'GET', [
    'page' => 1,
    'search' => $client->sas_username,
]);
$request->headers->set('X-Requested-With', 'XMLHttpRequest');
app()->instance('request', $request);
$response = $controller->clients($request);
$json = json_decode($response->getContent(), true);
assert_true(($json['total'] ?? 0) >= 1, 'Mobile search by sas_username should return matching clients');
assert_true(str_contains($json['html'] ?? '', e($client->sas_username)), 'AJAX search result should include the matching SAS username');

$defaultRequest = Illuminate\Http\Request::create('/ar/admin/mobile-clients', 'GET', ['page' => 1]);
$defaultRequest->headers->set('X-Requested-With', 'XMLHttpRequest');
app()->instance('request', $defaultRequest);
$defaultJson = json_decode($controller->clients($defaultRequest)->getContent(), true);
assert_true(($defaultJson['total'] ?? 0) === 1170, 'Mobile default active client count should remain unchanged');

echo "mobile_clients_sas_status_ok\n";
