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
$controller = app(App\Http\Controllers\Admin\ClientController::class);

function clients_ajax(App\Http\Controllers\Admin\ClientController $controller, array $params): array
{
    $request = Illuminate\Http\Request::create('/ar/admin/clients', 'GET', array_merge([
        'draw' => 1,
        'start' => 0,
        'length' => 10,
    ], $params));
    $request->headers->set('X-Requested-With', 'XMLHttpRequest');
    app()->instance('request', $request);
    $start = microtime(true);
    $response = $controller->index($request);
    $ms = (microtime(true) - $start) * 1000;
    $json = json_decode($response->getContent(), true);
    assert_true($ms < 2000, 'Filtered clients AJAX should stay under 2 seconds; got ' . round($ms, 2) . 'ms');
    assert_true(is_array($json), 'Filtered clients AJAX should return JSON');
    return $json;
}

$nullFilters = clients_ajax($controller, [
    'subscription_filter' => null,
    'balance_filter' => null,
    'client_type_filter' => null,
]);
assert_true(($nullFilters['recordsFiltered'] ?? 0) === 1170, 'Null filter values should be treated as no filter');

$hasBalance = clients_ajax($controller, ['balance_filter' => 'has_balance']);
assert_true(($hasBalance['recordsFiltered'] ?? 0) > 0, 'Has-balance filter should return matching clients');
foreach ($hasBalance['data'] as $row) {
    assert_true((float) str_replace(',', '', $row['remaining_amount']) > 0, 'Has-balance rows should have remaining amount > 0');
}
assert_true(($hasBalance['total_remaining'] ?? 0) > 0, 'Has-balance summary should have positive total_remaining');

$noBalance = clients_ajax($controller, ['balance_filter' => 'no_balance']);
assert_true(($noBalance['recordsFiltered'] ?? 0) > 0, 'No-balance filter should return matching clients');
foreach ($noBalance['data'] as $row) {
    assert_true((float) str_replace(',', '', $row['remaining_amount']) <= 0, 'No-balance rows should have zero remaining amount');
}
assert_true((float) ($noBalance['total_remaining'] ?? 999) === 0.0, 'No-balance summary should have zero total_remaining');

$subscriptionId = App\Models\Clients::where('is_active', '1')->whereNotNull('subscription_id')->value('subscription_id');
assert_true(!empty($subscriptionId), 'Test needs an active client with subscription_id');
$subscription = clients_ajax($controller, ['subscription_filter' => $subscriptionId]);
assert_true(($subscription['recordsFiltered'] ?? 0) > 0, 'Subscription filter should return matching clients');

$html = $controller->index(Illuminate\Http\Request::create('/ar/admin/clients', 'GET'))->render();
assert_true(str_contains($html, 'id="balanceFilter"'), 'Page should include balance filter');
assert_true(str_contains($html, 'id="subscriptionFilter"'), 'Page should include subscription filter');
assert_true(!str_contains($html, 'id="userFilter"'), 'Page should not include user/collector filter');
assert_true(str_contains($html, 'balance_filter'), 'DataTables AJAX should send balance_filter');
assert_true(str_contains($html, 'subscription_filter'), 'DataTables AJAX should send subscription_filter');
assert_true(!str_contains($html, 'user_filter'), 'DataTables AJAX should not send user_filter');

echo "clients_phase2_ok\n";
