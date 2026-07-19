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

$request = Illuminate\Http\Request::create('/ar/admin/clients', 'GET', [
    'draw' => 1,
    'start' => 0,
    'length' => 10,
]);
$request->headers->set('X-Requested-With', 'XMLHttpRequest');
app()->instance('request', $request);
$start = microtime(true);
$response = $controller->index($request);
$ajaxMs = (microtime(true) - $start) * 1000;
$json = json_decode($response->getContent(), true);

assert_true($ajaxMs < 2000, 'Clients AJAX Phase 1 summary should stay under 2 seconds; got ' . round($ajaxMs, 2) . 'ms');
assert_true(is_array($json), 'Clients AJAX should return JSON');
assert_true(array_key_exists('total_remaining', $json), 'Clients AJAX should include total_remaining for summary cards');
assert_true(is_numeric($json['total_remaining']), 'total_remaining should be numeric');
assert_true(array_key_exists('active_count', $json), 'Clients AJAX should include active_count');
assert_true(array_key_exists('inactive_count', $json), 'Clients AJAX should include inactive_count');

$html = $controller->index(Illuminate\Http\Request::create('/ar/admin/clients', 'GET'))->render();

assert_true(str_contains($html, 'clientsRemainingSummary'), 'Page should include remaining summary target');
assert_true(str_contains($html, 'clientsAvgSummary'), 'Page should include average remaining summary target');
assert_true(str_contains($html, 'clientsActiveSummary'), 'Page should include active summary target');
assert_true(str_contains($html, 'clientsInactiveSummary'), 'Page should include inactive summary target');
assert_true(str_contains($html, 'remaining-amount-pill'), 'Remaining amount should use actionable color pill');
assert_true(str_contains($html, 'showRemainingInvoices('), 'Remaining amount should open remaining invoices modal');

file_put_contents('/tmp/clients-phase1.html', $html);

echo "clients_phase1_ok\n";
