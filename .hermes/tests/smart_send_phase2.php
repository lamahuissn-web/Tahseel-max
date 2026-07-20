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

$dueClient = DB::table('tbl_clients as c')
    ->join('tbl_invoices as i', 'i.client_id', '=', 'c.id')
    ->whereNull('c.deleted_at')
    ->whereNull('i.deleted_at')
    ->whereNotNull('c.phone')
    ->where('c.phone', '!=', '')
    ->where('c.is_active', '1')
    ->whereIn('i.status', ['unpaid', 'partial'])
    ->whereDate('i.due_date', '<=', today())
    ->distinct()
    ->select('c.id', 'c.name')
    ->first();

assert_true((bool) $dueClient, 'Need at least one due/overdue customer fixture');

$search = json_decode($controller->searchClients(Request::create('/search', 'GET', ['q' => (string) $dueClient->id]))->getContent(), true);
$result = collect($search['results'])->firstWhere('id', $dueClient->id);
assert_true((bool) $result, 'Search should include the due customer');

$auto = $result['auto_template'] ?? [];
assert_true(($auto['state'] ?? null) === 'overdue_due', 'Overdue customer should map to overdue_due state, got: ' . json_encode($auto));
assert_true(($auto['template'] ?? null) === 'reminder', 'Overdue customer should get reminder template, got: ' . json_encode($auto));
assert_true(($auto['reason'] ?? '') !== '', 'Overdue customer should have a reason');

// ── Future-invoice scenario ──
$futureClient = DB::table('tbl_clients as c')
    ->join('tbl_invoices as i', 'i.client_id', '=', 'c.id')
    ->whereNull('c.deleted_at')
    ->whereNull('i.deleted_at')
    ->whereNotNull('c.phone')
    ->where('c.phone', '!=', '')
    ->where('c.is_active', '1')
    ->whereIn('i.status', ['unpaid', 'partial'])
    ->whereDate('i.due_date', '>', today())
    ->distinct()
    ->select('c.id', 'c.name')
    ->first();

if ($futureClient) {
    $search2 = json_decode($controller->searchClients(Request::create('/search', 'GET', ['q' => (string) $futureClient->id]))->getContent(), true);
    $r2 = collect($search2['results'])->firstWhere('id', $futureClient->id);
    if ($r2) {
        $a2 = $r2['auto_template'] ?? [];
        assert_true(in_array($a2['state'] ?? null, ['future_invoice', 'overdue_due']), 'Future-invoice customer should map to future_invoice (or also overdue), got: ' . json_encode($a2));
    }
}

// ── Skip/no-state scenario (a client with no unpaid/partial invoices) ──
$noInvoiceClient = DB::table('tbl_clients')
    ->whereNull('deleted_at')
    ->whereNotNull('phone')
    ->where('phone', '!=', '')
    ->where('is_active', '1')
    ->whereNotExists(function ($q) {
        $q->select(DB::raw(1))->from('tbl_invoices')
          ->whereColumn('client_id', 'tbl_clients.id')
          ->whereNull('deleted_at')
          ->whereIn('status', ['unpaid', 'partial']);
    })
    ->select('id', 'name')
    ->first();

if ($noInvoiceClient) {
    $search3 = json_decode($controller->searchClients(Request::create('/search', 'GET', ['q' => (string) $noInvoiceClient->id]))->getContent(), true);
    $r3 = collect($search3['results'])->firstWhere('id', $noInvoiceClient->id);
    if ($r3) {
        $a3 = $r3['auto_template'] ?? [];
        assert_true(in_array($a3['state'] ?? null, ['paid_receipt', 'skip_no_state', 'blocked']), 'Client with no invoices should be paid_receipt, skip_no_state, or blocked, got: ' . json_encode($a3));
    }
}

echo "smart_send_phase2_ok\n";
