<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Admin\WhatsAppControlCenterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$admin = App\Models\Admin::find(1);
Auth::guard('admin')->setUser($admin);
$controller = app(WhatsAppControlCenterController::class);

function assert_true($condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

function json_response($response): array
{
    return json_decode($response->getContent(), true) ?: [];
}

$dueClient = DB::table('tbl_clients as c')
    ->join('tbl_invoices as i', 'i.client_id', '=', 'c.id')
    ->whereNull('c.deleted_at')
    ->whereNull('i.deleted_at')
    ->whereNotNull('c.phone')
    ->where('c.phone', '!=', '')
    ->whereIn('i.status', ['unpaid', 'partial'])
    ->whereDate('i.due_date', '<=', today())
    ->select('c.id', 'c.name')
    ->first();

assert_true((bool) $dueClient, 'Need at least one due/overdue customer fixture');

$search = json_response($controller->searchClients(Request::create('/search', 'GET', ['q' => $dueClient->id])));
assert_true(isset($search['results'][0]), 'Search should return at least one result');
$result = collect($search['results'])->firstWhere('id', $dueClient->id);
assert_true((bool) $result, 'Search should include the due customer by ID');

foreach (['name', 'phone', 'eligibility', 'due_amount', 'due_date', 'invoice_count', 'recommended_template', 'reason'] as $field) {
    assert_true(array_key_exists($field, $result), "Search result should include {$field}");
}
assert_true(($result['eligibility']['eligible'] ?? false) === true, 'Due customer should be eligible');
assert_true((float) $result['due_amount'] > 0, 'Due customer should have due amount > 0');
assert_true(in_array($result['recommended_template'], ['reminder', 'overdue', 'due_today', 'custom'], true), 'Recommended template should be meaningful');

$preview = json_response($controller->broadcast(Request::create('/broadcast', 'POST', [
    'preview' => true,
    'invoice_scope' => 'due_overdue',
    'status' => '1',
])));
assert_true(isset($preview['clients']), 'Filter preview should return clients key');
assert_true(count($preview['clients']) > 0, 'Due/overdue filter should return clients');
$first = $preview['clients'][0];
foreach (['due_amount', 'due_date', 'invoice_count', 'eligibility', 'recommended_template', 'reason'] as $field) {
    assert_true(array_key_exists($field, $first), "Filter result should include {$field}");
}
assert_true(($first['eligibility']['eligible'] ?? false) === true, 'Due/overdue filter result should be eligible');

$noDue = json_response($controller->broadcast(Request::create('/broadcast', 'POST', [
    'preview' => true,
    'invoice_scope' => 'no_due',
    'status' => '1',
])));
assert_true(isset($noDue['clients']), 'No-due filter should return clients key');
if (count($noDue['clients']) > 0) {
    $firstNoDue = $noDue['clients'][0];
    assert_true(($firstNoDue['eligibility']['eligible'] ?? true) === false, 'No-due customer should not be eligible for invoice reminder');
    $expected = ($firstNoDue['future_invoice_count'] ?? 0) > 0 ? 'invoice_notification' : 'custom';
    assert_true($firstNoDue['recommended_template'] === $expected, 'No-due customer should recommend custom unless a future invoice exists');
}

echo "smart_send_phase1_ok\n";
