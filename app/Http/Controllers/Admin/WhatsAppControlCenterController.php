<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppMessageLog;
use App\Services\WhatsApp\WhatsAppTemplateService;
use App\Services\WhatsAppMessageBuilder;
use App\Services\WhatsAppService;
use App\Services\WhatsApp\InvoiceEligibilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WhatsAppControlCenterController extends Controller
{
    /**
     * 📊 Dashboard — Pulse metrics at a glance.
     */
    public function dashboard()
    {
        $dashboardData = $this->buildDashboardMonitorData();

        return view('dashbord.whatsapp.dashboard', $dashboardData);
    }

    public function monitor()
    {
        $dashboardData = $this->buildDashboardMonitorData();

        return view('dashbord.whatsapp.monitor', $dashboardData);
    }

    private function buildDashboardMonitorData(): array
    {
        $emergencyStop = DB::table('app_config')->where('key', 'whatsapp_emergency_stop')->value('value');

        $connectionStatus = false;
        $devicePhone = null;
        $service = app(WhatsAppService::class);
        $device = [
            'reachable' => false,
            'connected' => false,
            'phone' => null,
            'status' => 'unchecked',
            'message' => null,
        ];
        $qrState = [
            'reachable' => false,
            'connected' => false,
            'qr' => null,
            'status' => 'unchecked',
            'message' => null,
        ];

        if ($emergencyStop != '1') {
            try {
                $device = $service->status();
                $qrState = $service->getQR();
                if ($device && ($device['connected'] ?? false)) {
                    $connectionStatus = true;
                    $devicePhone = $device['phone'] ?? null;
                }
            } catch (\Exception $e) {
                // OpenWA not reachable — stay disconnected and use fallback states.
            }
        }

        $messagesToday = WhatsAppMessageLog::whereDate('created_at', today())->count();
        $messagesThisMonth = WhatsAppMessageLog::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $failuresToday = WhatsAppMessageLog::whereDate('created_at', today())
            ->where('status', 'failed')
            ->count();

        $totalClients = DB::table('tbl_clients')->whereNull('deleted_at')->count();
        $clientsWithPhone = DB::table('tbl_clients')
            ->whereNull('deleted_at')
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->count();

        $lastSent = WhatsAppMessageLog::where('status', 'sent')
            ->orderBy('created_at', 'desc')
            ->first();
        $lastFailed = WhatsAppMessageLog::where('status', 'failed')
            ->orderBy('created_at', 'desc')
            ->first();
        $pendingQueueCount = WhatsAppMessageLog::where('status', 'pending')->count();
        $sendingQueueCount = WhatsAppMessageLog::where('status', 'sending')->count();
        $oldestPending = WhatsAppMessageLog::where('status', 'pending')->orderBy('created_at')->first();

        $apiReachable = (bool) ($device['reachable'] ?? false);
        $sessionConnected = (bool) ($device['connected'] ?? false);
        $sessionStatus = (string) ($device['status'] ?? 'unknown');
        $qrNeeded = !$connectionStatus && !empty($qrState['qr']);
        $oldestPendingAt = optional($oldestPending)->created_at;
        $lastSuccessAt = optional($lastSent)->created_at;
        $lastFailureAt = optional($lastFailed)->created_at;
        $lastFailureError = $lastFailed->error ?? null;
        $queueLooksStuck = $this->isQueueLikelyStuck($pendingQueueCount, $sendingQueueCount, $oldestPendingAt);
        $failureWarning = $this->hasRecentFailureWarning($failuresToday, $lastFailureAt);
        $overallAlert = $this->buildOverallMonitorAlert(
            $emergencyStop,
            $apiReachable,
            $sessionConnected,
            $sessionStatus,
            $qrNeeded,
            $queueLooksStuck,
            $failureWarning
        );

        $monitor = [
            'api_reachable' => $apiReachable,
            'session_connected' => $sessionConnected,
            'session_status' => $sessionStatus,
            'session_status_label' => $this->formatSessionStatusLabel($sessionStatus),
            'session_message' => $device['message'] ?? $qrState['message'] ?? null,
            'qr_needed' => $qrNeeded,
            'connected_phone' => $devicePhone,
            'last_success_at' => $lastSuccessAt,
            'last_failure_at' => $lastFailureAt,
            'last_failure_error' => $lastFailureError,
            'pending_queue_count' => $pendingQueueCount,
            'sending_queue_count' => $sendingQueueCount,
            'oldest_pending_at' => $oldestPendingAt,
            'queue_looks_stuck' => $queueLooksStuck,
            'failure_warning' => $failureWarning,
            'overall_alert_level' => $overallAlert['level'],
            'overall_alert_label' => $overallAlert['label'],
            'overall_alert_text' => $overallAlert['text'],
            'status_badges' => $this->buildMonitorStatusBadges(
                $emergencyStop,
                $apiReachable,
                $sessionConnected,
                $sessionStatus,
                $qrNeeded,
                $queueLooksStuck,
                $failureWarning
            ),
            'checked_at' => now(),
            'recommended_action' => $this->buildConnectionRecommendedAction(
                $emergencyStop,
                $apiReachable,
                $sessionConnected,
                $qrNeeded,
                $pendingQueueCount,
                $sendingQueueCount,
                $oldestPendingAt,
                $lastFailureError
            ),
        ];

        return compact(
            'connectionStatus', 'emergencyStop',
            'messagesToday', 'messagesThisMonth', 'failuresToday',
            'totalClients', 'clientsWithPhone', 'lastSent',
            'devicePhone', 'monitor'
        );
    }

    /**
     * 📝 Templates — List editable message templates.
     */
    public function templates()
    {
        $templates = WhatsAppTemplateService::getAll();
        return view('dashbord.whatsapp.templates', compact('templates'));
    }

    /**
     * 💾 Save a template body.
     */
    public function saveTemplate(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'body' => 'required|string',
        ]);

        WhatsAppTemplateService::saveBody($request->type, $request->body, auth('admin')->id());

        return response()->json(['success' => true, 'message' => trans('clients.whatsapp_settings_saved')]);
    }

    /**
     * 📨 Test send a template to a phone number.
     */
    public function testTemplate(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'phone' => 'required|string',
        ]);

        $body = WhatsAppTemplateService::getBody($request->type);

        $sampleData = [
            '{name}' => 'زبون تجريبي',
            '{total_amount}' => '50.00',
            '{amount}' => '15.00',
            '{month}' => '07',
            '{year}' => '2026',
            '{collector}' => 'أحمد',
            '{datetime}' => now()->format('Y-m-d h:i A'),
            '{balance_status}' => 'الرصيد الحالي: $0.00',
            '{due_date}' => now()->addDays(3)->format('Y-m-d'),
            '{support_phone}' => '96170781562',
            '{invoice_details_list}' => "❌ 07 / 2026      \$20.00\n❌ 06 / 2026      \$20.00",
            '{message_body}' => 'هذه رسالة تجريبية',
        ];

        $message = str_replace(array_keys($sampleData), array_values($sampleData), $body);

        $service = app(WhatsAppService::class);
        $result = $service->send($request->phone, $message);

        return response()->json([
            'success' => isset($result['success']) && $result['success'] === true,
            'message' => isset($result['success']) && $result['success'] === true
                ? 'تم الإرسال بنجاح'
                : 'فشل الإرسال: ' . ($result['error'] ?? 'خطأ غير معروف'),
        ]);
    }

    /**
     * 📨 Send — Broadcast to selected or filtered clients.
     */
    public function send()
    {
        $templates = WhatsAppTemplateService::getAll();
        return view('dashbord.whatsapp.send', compact('templates'));
    }

    /**
     * 🎯 Search clients for manual selection.
     */
    public function searchClients(Request $request)
    {
        $term = $request->q;
        $clients = DB::table('tbl_clients')
            ->whereNull('deleted_at')
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('phone', 'like', "%{$term}%")
                  ->orWhere('id', 'like', "%{$term}%");
            })
            ->select('id', 'name', 'phone')
            ->limit(20)
            ->get();

        return response()->json([
            'results' => $clients->map(fn($c) => [
                'id' => $c->id,
                'text' => "{$c->name} | {$c->phone}",
            ]),
        ]);
    }

    /**
     * 📨 Execute broadcast to selected clients.
     */
    public function broadcast(Request $request)
    {
        if ($request->boolean('preview')) {
            $query = DB::table('tbl_clients')->whereNull('deleted_at')
                ->whereNotNull('phone')->where('phone', '!=', '');

            if ($request->filled('q')) {
                $q = $request->q;
                $query->where(function ($qry) use ($q) {
                    $qry->where('name', 'like', "%{$q}%")
                      ->orWhere('phone', 'like', "%{$q}%")
                      ->orWhere('id', 'like', "%{$q}%");
                });
            }

            if ($request->filled('client_type')) {
                $query->where('client_type', $request->client_type);
            }

            if ($request->filled('status')) {
                $query->where('is_active', $request->status);
            }

            if ($request->filled('unpaid')) {
                $unpaidCount = (int) $request->unpaid;
                $query->whereRaw('(SELECT COUNT(*) FROM tbl_invoices WHERE tbl_invoices.client_id = tbl_clients.id AND tbl_invoices.status IN ("unpaid","partial")) >= ?', [$unpaidCount]);
            }

            if ($request->filled('subscription')) {
                $query->where('subscription_id', $request->subscription);
            }

            if ($request->filled('last_payment')) {
                $query->whereRaw('(SELECT MAX(created_at) FROM tbl_payments WHERE tbl_payments.client_id = tbl_clients.id) <= ?', [$request->last_payment . ' 23:59:59']);
            }

            $clients = $query->select(
                    'id', 'name', 'phone', 'is_active',
                    DB::raw('(SELECT COUNT(*) FROM tbl_invoices WHERE tbl_invoices.client_id = tbl_clients.id AND tbl_invoices.status IN ("unpaid","partial")) as unpaid_count')
                )
                ->limit(200)
                ->get();

            return response()->json([
                'clients' => $clients->map(fn($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'phone' => $c->phone,
                    'is_active' => $c->is_active,
                    'unpaid_count' => (int) $c->unpaid_count,
                ]),
            ]);
        }

        $request->validate([
            'template_type' => 'required|string',
            'client_ids' => 'required|array',
            'client_ids.*' => 'integer|exists:tbl_clients,id',
        ]);

        $body = WhatsAppTemplateService::getBody($request->template_type);

        if (empty($body)) {
            return response()->json([
                'sent' => 0,
                'failed' => 0,
                'errors' => [trans('clients.whatsapp_template_not_found') ?? 'القالب غير موجود'],
            ]);
        }

        if ($request->template_type === 'custom' && $request->custom_message) {
            $body = $request->custom_message;
        }

        $service = app(WhatsAppService::class);
        $results = ['sent' => 0, 'failed' => 0, 'errors' => []];

        if (count($request->client_ids) > 1) {
            $batchId = (string) Str::uuid();
            $queued = 0;

            foreach ($request->client_ids as $clientId) {
                $client = DB::table('tbl_clients')->find($clientId);
                if (!$client || empty($client->phone)) {
                    $results['failed']++;
                    $results['errors'][] = 'Client #' . $clientId . ': missing client or phone';
                    continue;
                }

                $message = $body;
                $message = str_replace('{name}', $client->name, $message);
                $message = str_replace('{message_body}', $request->custom_message ?? '', $message);

                $unpaidInvoices = InvoiceEligibilityService::getEligibleInvoices($client->id);
                $totalAmount = $unpaidInvoices->sum('remaining_amount');

                if ($unpaidInvoices->isNotEmpty()) {
                    $invoiceDetailsList = WhatsAppMessageBuilder::buildInvoiceDetailsList($unpaidInvoices);
                    $message = WhatsAppMessageBuilder::buildMessage($message, $client->name, $totalAmount, $invoiceDetailsList);
                    $dueDate = $unpaidInvoices->last()->due_date
                        ? Carbon::parse($unpaidInvoices->last()->due_date)->format('Y-m-d')
                        : Carbon::today()->format('Y-m-d');
                } else {
                    $dueDate = Carbon::today()->format('Y-m-d');
                }

                $message = str_replace('{total_amount}', number_format($totalAmount, 2), $message);
                $message = str_replace('{invoice_details_list}', 'لا توجد فواتير مستحقة', $message);
                $message = str_replace('{due_date}', $dueDate, $message);
                $message = str_replace('{support_phone}', '96170781562', $message);
                $message = str_replace('{amount}', number_format($totalAmount > 0 ? $totalAmount : 0, 2), $message);
                $message = str_replace('{month}', now()->format('m'), $message);
                $message = str_replace('{year}', now()->format('Y'), $message);
                $message = str_replace('{collector}', auth('admin')->user()->name ?? 'الإدارة', $message);
                $message = str_replace('{datetime}', now()->format('Y-m-d h:i A'), $message);
                $message = str_replace('{balance_status}', 'الرصيد الحالي: $' . number_format($totalAmount, 2), $message);

                WhatsAppMessageLog::create([
                    'client_id' => $client->id,
                    'client_name' => $client->name,
                    'phone' => $client->phone,
                    'message' => $message,
                    'template_type' => $request->template_type,
                    'status' => 'pending',
                    'error' => null,
                    'sent_by' => 'admin:manual|batch:' . $batchId,
                ]);

                $queued++;
            }

            if ($queued > 0) {
                $delay = (int) (DB::table('app_config')->where('key', 'whatsapp_auto_delay')->value('value') ?? 10);
                $this->startQueuedBatchProcessor($batchId, $delay);
            }

            return response()->json([
                'success' => true,
                'queued' => $queued,
                'failed' => $results['failed'],
                'errors' => $results['errors'],
                'total' => count($request->client_ids),
                'batch_id' => $batchId,
                'redirect_url' => route('admin.whatsapp.queue'),
            ]);
        }

        foreach ($request->client_ids as $clientId) {
            $client = DB::table('tbl_clients')->find($clientId);
            if (!$client || empty($client->phone)) {
                $results['failed']++;
                continue;
            }

            $message = $body;
            $message = str_replace('{name}', $client->name, $message);
            $message = str_replace('{message_body}', $request->custom_message ?? '', $message);

            // Use centralized eligibility check — only due/overdue invoices
            $unpaidInvoices = InvoiceEligibilityService::getEligibleInvoices($client->id);

            $totalAmount = $unpaidInvoices->sum('remaining_amount');

            if ($unpaidInvoices->isNotEmpty()) {
                $invoiceDetailsList = WhatsAppMessageBuilder::buildInvoiceDetailsList($unpaidInvoices);
                $message = WhatsAppMessageBuilder::buildMessage($message, $client->name, $totalAmount, $invoiceDetailsList);
                // Use the most recent invoice's actual due date
                $dueDate = $unpaidInvoices->last()->due_date
                    ? Carbon::parse($unpaidInvoices->last()->due_date)->format('Y-m-d')
                    : Carbon::today()->format('Y-m-d');
            } else {
                $dueDate = Carbon::today()->format('Y-m-d');
            }

            $message = str_replace('{total_amount}', number_format($totalAmount, 2), $message);
            $message = str_replace('{invoice_details_list}', 'لا توجد فواتير مستحقة', $message);
            $message = str_replace('{due_date}', $dueDate, $message);
            $message = str_replace('{support_phone}', '96170781562', $message);
            $message = str_replace('{amount}', number_format($totalAmount > 0 ? $totalAmount : 0, 2), $message);
            $message = str_replace('{month}', now()->format('m'), $message);
            $message = str_replace('{year}', now()->format('Y'), $message);
            $message = str_replace('{collector}', auth('admin')->user()->name ?? 'الإدارة', $message);
            $message = str_replace('{datetime}', now()->format('Y-m-d h:i A'), $message);
            $message = str_replace('{balance_status}', 'الرصيد الحالي: $' . number_format($totalAmount, 2), $message);

            $result = $service->send($client->phone, $message);
            $status = (isset($result['success']) && $result['success'] === true) ? 'sent' : 'failed';

            WhatsAppMessageLog::create([
                'client_id' => $client->id,
                'client_name' => $client->name,
                'phone' => $client->phone,
                'message' => $message,
                'template_type' => $request->template_type,
                'status' => $status,
                'error' => $status === 'failed' ? ($result['error'] ?? 'Unknown') : null,
                'sent_by' => 'admin:' . auth('admin')->id(),
            ]);

            if ($status === 'sent') {
                $results['sent']++;
            } else {
                $results['failed']++;
                $results['errors'][] = $client->name . ': ' . ($result['error'] ?? 'Unknown');
            }

            usleep(1000000);
        }

        return response()->json($results);
    }

    /**
     * 📋 Message Log — View history.
     */
    public function log()
    {
        return view('dashbord.whatsapp.log');
    }

    /**
     * 📊 Message Log — DataTables server-side data.
     */
    public function logData(Request $request)
    {
        $query = WhatsAppMessageLog::query();

        if ($request->search) {
            $query->search($request->search);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->template_type) {
            $query->where('template_type', $request->template_type);
        }
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $total = $query->count();

        $perPage = $request->length ?? 25;
        $page = ($request->start ?? 0) / $perPage + 1;

        $logs = $query->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'draw' => $request->draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $logs->map(fn($log) => [
                'id' => $log->id,
                'client_name' => $log->client_name,
                'phone' => $log->phone,
                'template_type' => $log->template_type,
                'status' => $log->status,
                'message_preview' => mb_substr($log->message, 0, 100),
                'message_full' => $log->message,
                'error' => $log->error,
                'created_at' => $log->created_at->format('Y-m-d h:i A'),
                'sent_by' => $log->sent_by,
                'source_label' => $this->getMessageSourceMeta($log->sent_by)['label'],
                'source_badge' => $this->getMessageSourceMeta($log->sent_by)['badge'],
                'source_detail' => $this->getMessageSourceMeta($log->sent_by)['detail'],
            ]),
        ]);
    }

    /**
     * 🔄 Resend a failed message.
     */
    public function resendMessage($id)
    {
        $log = WhatsAppMessageLog::findOrFail($id);
        $service = app(WhatsAppService::class);
        $result = $service->send($log->phone, $log->message);

        $log->update([
            'status' => (isset($result['success']) && $result['success'] === true) ? 'sent' : 'failed',
            'error' => isset($result['success']) && $result['success'] === true ? null : ($result['error'] ?? 'Unknown'),
        ]);

        return response()->json([
            'success' => $log->status === 'sent',
            'message' => $log->status === 'sent' ? 'تمت إعادة الإرسال بنجاح' : 'فشلت إعادة الإرسال',
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    //  🤖 AUTOMATION RULES
    // ═══════════════════════════════════════════════════════════════

    /**
     * 📱 QR Code — Fetch QR from OpenWA for re-authentication.
     */
    public function getQRCode()
    {
        try {
            $service = app(WhatsAppService::class);

            // First check if already connected
            $status = $service->status();
            if ($status['connected']) {
                return response()->json([
                    'success' => true,
                    'connected' => true,
                    'phone' => $status['phone'] ?? null,
                    'message' => 'Session already connected',
                ]);
            }

            // Fetch QR code from OpenWA
            $qr = $service->getQR();

            if (!empty($qr['qr'])) {
                return response()->json([
                    'success' => true,
                    'connected' => false,
                    'qr' => $qr['qr'],
                ]);
            }

            return response()->json([
                'success' => false,
                'connected' => false,
                'message' => 'QR code not available. The session may need to be restarted.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'connected' => false,
                'message' => 'Failed to fetch QR code: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * 📱 QR Code — Check connection status (for polling).
     */
    public function checkConnection()
    {
        try {
            $service = app(WhatsAppService::class);
            $status = $service->status();

            return response()->json([
                'connected' => $status['connected'] ?? false,
                'phone' => $status['phone'] ?? null,
                'status' => $status['status'] ?? 'unknown',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'connected' => false,
                'phone' => null,
                'status' => 'error',
            ]);
        }
    }

    /**
     * 🤖 Automation — List and manage rules.
     */
    public function automation()
    {
        $rules = $this->getAutomationRulesConfig();
        $templates = WhatsAppTemplateService::getAll();
        $subscriptions = \App\Models\Admin\Subscription::all();

        $calendarData = DB::table('tbl_invoices')
            ->selectRaw("DATE(due_date) as due_day, COUNT(DISTINCT client_id) as client_count")
            ->whereMonth('due_date', now()->month)
            ->whereYear('due_date', now()->year)
            ->whereIn('status', ['unpaid', 'partial'])
            ->whereNull('deleted_at')
            ->groupByRaw("DATE(due_date)")
            ->orderBy('due_day')
            ->get();

        return view('dashbord.whatsapp.automation', compact('rules', 'templates', 'subscriptions', 'calendarData'));
    }

    /**
     * 🔄 Toggle a single automation rule on/off.
     */
    public function toggleAutomationRule($id)
    {
        $id = $this->normalizeAutomationRuleId($id);
        $rules = $this->getAutomationRulesConfig();

        if (!isset($rules[$id])) {
            return response()->json(['success' => false, 'error' => 'Rule not found'], 404);
        }

        $rules[$id]['enabled'] = !($rules[$id]['enabled'] ?? false);
        $this->saveAutomationRulesConfig($rules);

        return response()->json([
            'success' => true,
            'enabled' => $rules[$id]['enabled'],
        ]);
    }

    /**
     * 💾 Save settings for a single automation rule.
     */
    public function saveAutomationRule(Request $request, $id)
    {
        $id = $this->normalizeAutomationRuleId($id);
        $rules = $this->getAutomationRulesConfig();

        if (!isset($rules[$id])) {
            return response()->json(['success' => false, 'error' => 'Rule not found'], 404);
        }

        $request->validate([
            'time' => 'required|date_format:H:i',
            'days' => 'required|array',
            'days.*' => 'integer|between:0,6',
            'template' => 'required|string',
            'days_offset' => 'nullable|integer|min:-30|max:30',
        ]);

        $rules[$id]['time'] = $request->time;
        $rules[$id]['days'] = $request->days;
        $rules[$id]['template'] = $request->template;
        $rules[$id]['days_offset'] = (int) ($request->days_offset ?? $rules[$id]['days_offset'] ?? 0);

        // Filter settings (shared by both reminder rules)
        if (in_array($id, ['whatsapp_remind_before', 'whatsapp_overdue'], true)) {
            $rules[$id]['filter_client_type'] = $request->filter_client_type ?? 'all';
            $rules[$id]['filter_subscription_id'] = $request->filter_subscription_id ?? null;
            $rules[$id]['filter_min_unpaid'] = (int) ($request->filter_min_unpaid ?? 0);
            $rules[$id]['filter_client_status'] = $request->filter_client_status ?? 'all';
        }

        $this->saveAutomationRulesConfig($rules);

        // Build day names summary
        $dayNames = ['سبت', 'أحد', 'اثنين', 'ثلاثاء', 'أربعاء', 'خميس', 'جمعة'];
        $selectedDays = array_intersect($request->days, [0,1,2,3,4,5,6]);
        if (count($selectedDays) === 7) {
            $daysSummary = 'كل الأيام';
        } else {
            $daysSummary = implode('، ', array_map(fn($d) => $dayNames[$d] ?? '', $selectedDays));
        }

        // Build filter summary
        $filtersSummary = '';
        if (in_array($id, ['whatsapp_remind_before', 'whatsapp_overdue'], true) && !empty($request->filter_client_type) && $request->filter_client_type !== 'all') {
            $filtersSummary .= ($request->filter_client_type === 'internet' ? 'إنترنت' : 'ساتلايت');
        }
        if (in_array($id, ['whatsapp_remind_before', 'whatsapp_overdue'], true)) {
            $parts = [];
            if ($request->filter_client_type && $request->filter_client_type !== 'all') {
                $parts[] = $request->filter_client_type === 'internet' ? 'إنترنت' : 'ساتلايت';
            }
            if ($request->filter_client_status && $request->filter_client_status !== 'all') {
                $parts[] = $request->filter_client_status === 'active' ? 'نشط' : 'غير نشط';
            }
            if (!empty($request->filter_min_unpaid) && (int)$request->filter_min_unpaid > 0) {
                $parts[] = '≥ ' . (int)$request->filter_min_unpaid . ' unpaid';
            }
            $filtersSummary = !empty($parts) ? implode('، ', $parts) : 'الكل';
        }

        return response()->json([
            'success' => true,
            'rule' => $rules[$id],
            'days_summary' => $daysSummary,
            'filters_summary' => $filtersSummary,
        ]);
    }

    /**
     * ▶️ Run an automation rule immediately.
     */
    public function runAutomationRule($id)
    {
        $id = $this->normalizeAutomationRuleId($id);
        $rules = $this->getAutomationRulesConfig();
        $command = $rules[$id]['command'] ?? null;

        if (!$command) {
            return response()->json(['success' => false, 'error' => 'No command configured for this rule']);
        }

        try {
            Artisan::call($command, ['--rule' => $id]);
            return response()->json(['success' => true, 'output' => Artisan::output()]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    //  🛠️ HELPERS
    // ═══════════════════════════════════════════════════════════════

    /**
     * Get automation rules config from app_config.
     */
    // ═══════════════════════════════════════════════════════════════
    //  📋 PREVIEW & SEND
    // ═══════════════════════════════════════════════════════════════

    /**
     * 📋 Preview an automation rule — returns who will receive messages.
     */
    public function previewAutomationRule($id)
    {
        $id = $this->normalizeAutomationRuleId($id);
        $rules = $this->getAutomationRulesConfig();
        if (!isset($rules[$id])) {
            return response()->json(['success' => false, 'error' => 'Rule not found'], 404);
        }

        $rule = $rules[$id];
        $filters = [
            'client_type' => $rule['filter_client_type'] ?? 'all',
            'subscription_id' => $rule['filter_subscription_id'] ?? null,
            'min_unpaid' => $rule['filter_min_unpaid'] ?? 0,
            'client_status' => $rule['filter_client_status'] ?? 'all',
        ];

        $service = app(\App\Services\WhatsApp\ReminderService::class);

        if ($id === 'whatsapp_remind_before') {
            $days = abs((int) ($rule['days_offset'] ?? 3));
            $preview = $service->getBeforeDisconnectionPreview($days, $filters);
        } elseif ($id === 'whatsapp_overdue') {
            $preview = $service->getOverduePreview($filters);
        } else {
            return response()->json(['success' => false, 'error' => 'Rule not supported for preview'], 400);
        }

        // Return preview data directly (not wrapped) for JS compatibility
        return response()->json($preview);
    }

    /**
     * 📨 Send from preview — sends to the client IDs shown in preview.
     */
    public function sendFromPreview(Request $request, $id)
    {
        $id = $this->normalizeAutomationRuleId($id);
        $request->validate([
            'client_ids' => 'required|array',
            'client_ids.*' => 'integer',
        ]);

        $rules = $this->getAutomationRulesConfig();
        if (!isset($rules[$id])) {
            return response()->json(['success' => false, 'error' => 'Rule not found'], 404);
        }

        $template = $rules[$id]['template'] ?? 'payment_reminder';
        $clientIds = $request->client_ids;
        $days = abs((int) ($rules[$id]['days_offset'] ?? 3));
        $delay = (int) (DB::table('app_config')->where('key', 'whatsapp_auto_delay')->value('value') ?? 10);

        $service = app(\App\Services\WhatsApp\ReminderService::class);
        $result = $service->enqueueReminders($id, $clientIds, $template, [
            'days' => $days,
            'delay_seconds' => $delay,
            'sent_by' => 'admin:automation',
        ]);

        if (($result['queued'] ?? 0) > 0) {
            $this->startQueuedBatchProcessor($result['batch_id'], $delay);
        }

        return response()->json([
            'success' => true,
            'queued' => $result['queued'],
            'failed' => $result['failed'],
            'skipped' => $result['skipped'],
            'total' => $result['total'],
            'batch_id' => $result['batch_id'],
            'redirect_url' => route('admin.whatsapp.queue'),
        ]);
    }

    private function startQueuedBatchProcessor(string $batchId, int $delay): void
    {
        $phpBinary = is_executable('/usr/bin/php') ? '/usr/bin/php' : PHP_BINARY;
        $php = escapeshellarg($phpBinary);
        $artisan = escapeshellarg(base_path('artisan'));
        $batchArg = escapeshellarg($batchId);
        $delayArg = (int) max(0, $delay);
        $logFile = '/tmp/whatsapp-batch-' . preg_replace('/[^A-Za-z0-9_-]/', '-', $batchId) . '.log';

        $command = "{$php} {$artisan} whatsapp:process-pending {$batchArg} --delay={$delayArg} > " . escapeshellarg($logFile) . " 2>&1 &";
        exec($command);
    }

    private function getAutomationRulesConfig(): array
    {
        $defaults = $this->getDefaultAutomationRules();
        $stored = DB::table('app_config')->where('key', 'whatsapp_automation_rules')->value('value');

        if ($stored) {
            $storedRules = json_decode($stored, true) ?? [];
            $storedRules = $this->normalizeStoredAutomationRules($storedRules);
            $rules = array_merge($defaults, $storedRules);
            $needsSave = false;

            foreach ($defaults as $key => $defaultRule) {
                if (!isset($rules[$key])) {
                    $rules[$key] = $defaultRule;
                    $needsSave = true;
                } else {
                    foreach ($defaultRule as $field => $value) {
                        if (!array_key_exists($field, $rules[$key])) {
                            $rules[$key][$field] = $value;
                            $needsSave = true;
                        }
                    }
                }
            }

            // Strip rules that are no longer in defaults (e.g. removed receipt/disconnection)
            if (count(array_diff_key($rules, $defaults)) > 0) {
                $needsSave = true;
            }
            $rules = array_intersect_key($rules, $defaults);

            foreach ($rules as $key => &$rule) {
                if (($rule['id'] ?? null) !== $key) {
                    $rule['id'] = $key;
                    $needsSave = true;
                }
            }
            unset($rule);

            if ($needsSave) {
                $this->saveAutomationRulesConfig($rules);
            }

            return $rules;
        }

        // First run — migrate old keys
        $oldEnabled = DB::table('app_config')->where('key', 'whatsapp_auto_enabled')->value('value');
        $oldRemindBefore = DB::table('app_config')->where('key', 'whatsapp_remind_before')->value('value');

        if ($oldEnabled !== null) {
            $defaults['whatsapp_remind_before']['enabled'] = ($oldEnabled == '1');
        }
        if ($oldRemindBefore !== null) {
            $defaults['whatsapp_remind_before']['days_offset'] = -1 * abs((int) $oldRemindBefore);
        }

        $this->saveAutomationRulesConfig($defaults);

        return $defaults;
    }

    /**
     * Save automation rules config to app_config.
     */
    private function saveAutomationRulesConfig(array $rules): void
    {
        $lean = [];
        foreach ($rules as $key => $rule) {
            $key = $this->normalizeAutomationRuleId($key);
            $entry = [
                'id' => $key,
                'enabled' => $rule['enabled'] ?? false,
                'time' => $rule['time'] ?? '09:00',
                'days' => $rule['days'] ?? [0,1,2,3,4,5,6],
                'template' => $rule['template'] ?? 'reminder',
                'days_offset' => $rule['days_offset'] ?? 0,
            ];

            // Save filter settings for reminder rules
            if (in_array($key, ['whatsapp_remind_before', 'whatsapp_overdue'], true)) {
                $entry['filter_client_type'] = $rule['filter_client_type'] ?? 'all';
                $entry['filter_subscription_id'] = $rule['filter_subscription_id'] ?? null;
                $entry['filter_min_unpaid'] = (int) ($rule['filter_min_unpaid'] ?? 0);
                $entry['filter_client_status'] = $rule['filter_client_status'] ?? 'all';
            }

            $lean[$key] = $entry;
        }

        DB::table('app_config')->updateOrInsert(
            ['key' => 'whatsapp_automation_rules'],
            ['value' => json_encode($lean, JSON_UNESCAPED_UNICODE)]
        );
    }

    /**
     * Default automation rules definitions — only cron-based rules.
     */
    private function getDefaultAutomationRules(): array
    {
        return [
            'whatsapp_remind_before' => [
                'id' => 'whatsapp_remind_before',
                'label' => 'تذكير قبل القطع',
                'label_en' => 'Reminder Before Disconnection',
                'command' => 'whatsapp:reminders',
                'icon' => 'bi bi-bell',
                'color' => 'warning',
                'enabled' => false,
                'time' => '09:00',
                'days' => [0,1,2,3,4,5,6],
                'template' => 'reminder',
                'days_offset' => -3,
                'days_offset_label' => 'قبل القطع بـ',
                'days_offset_unit' => 'أيام',
                // Filter settings
                'filter_client_type' => 'all',       // 'all', 'internet', 'satellite'
                'filter_subscription_id' => null,    // null = all subscriptions
                'filter_min_unpaid' => 0,            // minimum unpaid invoices
                'filter_client_status' => 'all',     // 'all', 'active', 'inactive'
                'filter_summary' => 'الكل',
            ],
            'whatsapp_overdue' => [
                'id' => 'whatsapp_overdue',
                'label' => 'تذكير متأخر',
                'label_en' => 'Overdue Reminder',
                'command' => 'whatsapp:reminders',
                'icon' => 'bi bi-envelope',
                'color' => 'info',
                'enabled' => false,
                'time' => '10:00',
                'days' => [0,1,2,3,4,5,6],
                'template' => 'reminder',
                'days_offset' => 0,
                'days_offset_label' => 'كل',
                'days_offset_unit' => 'أيام',
                'description' => 'إرسال تذكير للزبائن الذين تأخر سداد فواتيرهم',
                'filter_client_type' => 'all',
                'filter_subscription_id' => null,
                'filter_min_unpaid' => 0,
                'filter_client_status' => 'all',
                'filter_summary' => 'الكل',
            ],
        ];
    }

    private function normalizeAutomationRuleId(string $id): string
    {
        return $id === 'whatsapp_custom' ? 'whatsapp_overdue' : $id;
    }

    private function normalizeStoredAutomationRules(array $rules): array
    {
        if (isset($rules['whatsapp_custom']) && !isset($rules['whatsapp_overdue'])) {
            $rules['whatsapp_overdue'] = $rules['whatsapp_custom'];
        }

        unset($rules['whatsapp_custom']);

        if (isset($rules['whatsapp_overdue'])) {
            $rules['whatsapp_overdue']['id'] = 'whatsapp_overdue';
            $rules['whatsapp_overdue']['label'] = $rules['whatsapp_overdue']['label'] ?? 'تذكير متأخر';
            $rules['whatsapp_overdue']['label_en'] = $rules['whatsapp_overdue']['label_en'] ?? 'Overdue Reminder';
            $rules['whatsapp_overdue']['command'] = 'whatsapp:reminders';
            $rules['whatsapp_overdue']['template'] = $rules['whatsapp_overdue']['template'] ?? 'reminder';
            $rules['whatsapp_overdue']['filter_subscription_id'] = $rules['whatsapp_overdue']['filter_subscription_id']
                ?? $rules['whatsapp_overdue']['filter_subscription']
                ?? null;
            $rules['whatsapp_overdue']['filter_client_status'] = $rules['whatsapp_overdue']['filter_client_status']
                ?? $rules['whatsapp_overdue']['filter_status']
                ?? 'all';
        }

        if (isset($rules['whatsapp_remind_before'])) {
            $rules['whatsapp_remind_before']['id'] = 'whatsapp_remind_before';
            $rules['whatsapp_remind_before']['filter_subscription_id'] = $rules['whatsapp_remind_before']['filter_subscription_id']
                ?? $rules['whatsapp_remind_before']['filter_subscription']
                ?? null;
            $rules['whatsapp_remind_before']['filter_client_status'] = $rules['whatsapp_remind_before']['filter_client_status']
                ?? $rules['whatsapp_remind_before']['filter_status']
                ?? 'all';
        }

        return $rules;
    }

    // ═══════════════════════════════════════════════════════════════
    //  ⏳ QUEUE
    // ═══════════════════════════════════════════════════════════════

    /**
     * ⏳ Queue — View pending/recent messages. (P2)
     */
    public function queue(Request $request)
    {
        $pending = WhatsAppMessageLog::where('status', 'pending')->count();
        $sending = WhatsAppMessageLog::where('status', 'sending')->count();
        $failed = WhatsAppMessageLog::where('status', 'failed')->count();

        $statusFilter = trim((string) $request->input('status', ''));
        $sourceFilter = trim((string) $request->input('source', ''));

        $recentQuery = WhatsAppMessageLog::query();

        if ($statusFilter !== '') {
            $recentQuery->where('status', $statusFilter);
        }

        $recent = $recentQuery->orderByRaw("CASE WHEN status = 'sending' THEN 0 WHEN status = 'pending' THEN 1 WHEN status = 'failed' THEN 2 ELSE 3 END")
            ->orderBy('created_at', 'desc')
            ->limit(180)
            ->get();

        $recent = $recent->filter(function ($log) use ($sourceFilter) {
            if ($sourceFilter === '') {
                return true;
            }

            $source = $this->getMessageSourceMeta($log->sent_by)['key'];
            return $source === $sourceFilter;
        })->values();

        $batchRows = WhatsAppMessageLog::query()
            ->whereNotNull('sent_by')
            ->where('sent_by', 'like', '%|batch:%')
            ->orderBy('created_at', 'desc')
            ->limit(500)
            ->get(['sent_by', 'status', 'created_at']);

        $batchSummaries = $batchRows
            ->groupBy('sent_by')
            ->map(function ($rows, $sentBy) {
                $meta = $this->getMessageSourceMeta($sentBy);
                return [
                    'sent_by' => $sentBy,
                    'source_key' => $meta['key'],
                    'source_label' => $meta['label'],
                    'source_badge' => $meta['badge'],
                    'batch_label' => $meta['detail'],
                    'total' => $rows->count(),
                    'pending' => $rows->where('status', 'pending')->count(),
                    'sending' => $rows->where('status', 'sending')->count(),
                    'sent' => $rows->where('status', 'sent')->count(),
                    'failed' => $rows->where('status', 'failed')->count(),
                    'last_activity' => $rows->max('created_at'),
                ];
            })
            ->filter(function ($batch) use ($sourceFilter) {
                return $sourceFilter === '' || $batch['source_key'] === $sourceFilter;
            })
            ->sortByDesc(function ($batch) {
                return (string) $batch['last_activity'];
            })
            ->take(12)
            ->values();

        $queuePaused = DB::table('app_config')->where('key', 'whatsapp_auto_enabled')->value('value') == '0';
        $sourceOptions = [
            '' => 'All Sources',
            'manual_bulk' => 'Manual Bulk',
            'manual_single' => 'Manual Single',
            'automation' => 'Automation',
            'autoreceipt' => 'Auto Receipt',
            'calendar' => 'Calendar',
            'cron' => 'Cron',
            'hermes' => 'Hermes/Test',
            'system' => 'System',
            'other' => 'Other',
        ];

        return view('dashbord.whatsapp.queue', compact(
            'pending',
            'sending',
            'failed',
            'recent',
            'queuePaused',
            'statusFilter',
            'sourceFilter',
            'sourceOptions',
            'batchSummaries'
        ));
    }

    /**
     * 🔄 Resend all failed messages. (P2)
     */
    public function resendAllFailed()
    {
        $failed = WhatsAppMessageLog::where('status', 'failed')->limit(50)->get();
        $service = app(WhatsAppService::class);
        $results = ['resent' => 0, 'still_failed' => 0];

        foreach ($failed as $log) {
            $result = $service->send($log->phone, $log->message);
            if (isset($result['success']) && $result['success'] === true) {
                $log->update(['status' => 'sent', 'error' => null]);
                $results['resent']++;
            } else {
                $log->update(['error' => $result['error'] ?? 'Unknown']);
                $results['still_failed']++;
            }
            usleep(500000);
        }

        return response()->json($results);
    }

    /**
     * ⏸️ Toggle queue pause. (P2)
     */
    public function toggleQueuePause()
    {
        $current = DB::table('app_config')->where('key', 'whatsapp_auto_enabled')->value('value');
        $new = $current == '1' ? '0' : '1';
        DB::table('app_config')->where('key', 'whatsapp_auto_enabled')->update(['value' => $new]);

        return response()->json(['success' => true, 'paused' => $new == '0']);
    }


    // ═══════════════════════════════════════════════════════════════
    //  📅 CALENDAR — Monthly unpaid invoice calendar
    // ═══════════════════════════════════════════════════════════════

    /**
     * 📅 Calendar — Get month data (days with unpaid bills).
     */
    public function calendarData(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $clientType = $request->input('client_type', 'all');

        $query = DB::table('tbl_invoices')
            ->selectRaw("DATE(due_date) as due_day, COUNT(DISTINCT tbl_invoices.client_id) as client_count")
            ->whereMonth('due_date', $month)
            ->whereYear('due_date', $year)
            ->whereIn('status', ['unpaid', 'partial'])
            ->whereNull('tbl_invoices.deleted_at');

        if ($clientType !== 'all') {
            $query->join('tbl_clients', 'tbl_clients.id', '=', 'tbl_invoices.client_id')
                  ->where('tbl_clients.client_type', $clientType)
                  ->whereNull('tbl_clients.deleted_at');
        }

        $data = $query->groupByRaw("DATE(due_date)")
            ->orderBy('due_day')
            ->get();

        return response()->json($data);
    }

    /**
     * 📅 Calendar — Get customers with unpaid bills on a specific day.
     */
    public function calendarDay(Request $request)
    {
        $date = $request->input('date');

        if (!$date) {
            return response()->json(['error' => 'Date is required'], 400);
        }

        $clientType = $request->input('client_type', 'all');

        // Step 1: Find clients who have unpaid/partial invoices DUE on this date
        $triggerQuery = DB::table('tbl_invoices as i')
            ->join('tbl_clients as c', 'c.id', '=', 'i.client_id')
            ->whereDate('i.due_date', $date)
            ->whereIn('i.status', ['unpaid', 'partial'])
            ->whereNull('i.deleted_at')
            ->whereNull('c.deleted_at')
            ->whereNotNull('c.phone')
            ->where('c.phone', '!=', '');

        if ($clientType !== 'all') {
            $triggerQuery->where('c.client_type', $clientType);
        }

        $clientIds = $triggerQuery->distinct()->pluck('c.id');

        if ($clientIds->isEmpty()) {
            return response()->json([
                'date' => $date,
                'total_clients' => 0,
                'clients' => [],
            ]);
        }

        // Step 2: Get ALL unpaid/partial invoices for those clients (all dates)
        $clients = DB::table('tbl_clients')
            ->whereIn('id', $clientIds)
            ->select('id', 'name', 'phone', 'client_type')
            ->orderBy('name')
            ->get();

        $allInvoices = DB::table('tbl_invoices')
            ->whereIn('client_id', $clientIds)
            ->whereIn('status', ['unpaid', 'partial'])
            ->whereNull('deleted_at')
            ->orderBy('due_date')
            ->get()
            ->groupBy('client_id');

        $result = $clients->map(function($client) use ($allInvoices) {
            $clientInvoices = $allInvoices->get($client->id, collect());
            return [
                'id' => $client->id,
                'name' => $client->name,
                'phone' => $client->phone,
                'client_type' => $client->client_type,
                'invoice_count' => $clientInvoices->count(),
                'total_amount' => (float) $clientInvoices->sum('remaining_amount'),
                'invoices' => $clientInvoices->map(fn($inv) => [
                    'id' => $inv->id,
                    'amount' => (float) $inv->amount,
                    'remaining_amount' => (float) $inv->remaining_amount,
                    'due_date' => $inv->due_date,
                    'type' => $inv->invoice_type === 'subscription' ? 'اشتراك' : 'خدمة',
                    'notes' => $inv->notes ?? '',
                ])->values()->toArray(),
            ];
        })->values()->toArray();

        return response()->json([
            'date' => $date,
            'total_clients' => count($result),
            'clients' => $result,
        ]);
    }

    /**
     * 📅 Calendar — Send reminders to selected customers.
     */
    public function calendarSend(Request $request)
    {
        $request->validate([
            'client_ids' => 'required|array',
            'client_ids.*' => 'integer|exists:tbl_clients,id',
            'template_type' => 'nullable|string',
        ]);

        $templateType = $request->template_type ?? 'reminder';
        $body = WhatsAppTemplateService::getBody($templateType);

        if (empty($body)) {
            return response()->json([
                'sent' => 0,
                'failed' => 0,
                'errors' => ['القالب غير موجود'],
            ]);
        }

        $service = app(WhatsAppService::class);
        $results = ['sent' => 0, 'failed' => 0, 'errors' => []];
        $failCount = 0;

        if (count($request->client_ids) > 1) {
            $batchId = (string) Str::uuid();
            $queued = 0;

            foreach ($request->client_ids as $clientId) {
                $client = DB::table('tbl_clients')->find($clientId);
                if (!$client || empty($client->phone)) {
                    $results['failed']++;
                    continue;
                }

                $message = $body;
                $message = str_replace('{name}', $client->name, $message);
                $message = str_replace('{message_body}', '', $message);

                $unpaidInvoices = InvoiceEligibilityService::getEligibleInvoices($client->id);
                $totalAmount = $unpaidInvoices->sum('remaining_amount');

                if ($unpaidInvoices->isNotEmpty()) {
                    $invoiceDetailsList = WhatsAppMessageBuilder::buildInvoiceDetailsList($unpaidInvoices);
                    $message = WhatsAppMessageBuilder::buildMessage($message, $client->name, $totalAmount, $invoiceDetailsList);
                    $dueDate = $unpaidInvoices->last()->due_date
                        ? Carbon::parse($unpaidInvoices->last()->due_date)->format('Y-m-d')
                        : Carbon::today()->format('Y-m-d');
                } else {
                    $dueDate = Carbon::today()->format('Y-m-d');
                }

                $message = str_replace('{total_amount}', number_format($totalAmount, 2), $message);
                $message = str_replace('{invoice_details_list}', 'لا توجد فواتير مستحقة', $message);
                $message = str_replace('{due_date}', $dueDate, $message);
                $message = str_replace('{support_phone}', '96170781562', $message);
                $message = str_replace('{amount}', number_format($totalAmount > 0 ? $totalAmount : 0, 2), $message);
                $message = str_replace('{month}', now()->format('m'), $message);
                $message = str_replace('{year}', now()->format('Y'), $message);
                $message = str_replace('{collector}', auth('admin')->user()->name ?? 'الإدارة', $message);
                $message = str_replace('{datetime}', now()->format('Y-m-d h:i A'), $message);
                $message = str_replace('{balance_status}', 'الرصيد الحالي: $' . number_format($totalAmount, 2), $message);

                WhatsAppMessageLog::create([
                    'client_id' => $client->id,
                    'client_name' => $client->name,
                    'phone' => $client->phone,
                    'message' => $message,
                    'template_type' => $templateType,
                    'status' => 'pending',
                    'error' => null,
                    'sent_by' => 'calendar|batch:' . $batchId,
                ]);

                $queued++;
            }

            if ($queued > 0) {
                $delay = (int) (DB::table('app_config')->where('key', 'whatsapp_auto_delay')->value('value') ?? 10);
                $this->startQueuedBatchProcessor($batchId, $delay);
            }

            return response()->json([
                'success' => true,
                'queued' => $queued,
                'failed' => $results['failed'],
                'errors' => $results['errors'],
                'total' => count($request->client_ids),
                'batch_id' => $batchId,
                'redirect_url' => route('admin.whatsapp.queue'),
            ]);
        }

        foreach ($request->client_ids as $clientId) {
            $client = DB::table('tbl_clients')->find($clientId);
            if (!$client || empty($client->phone)) {
                $results['failed']++;
                continue;
            }

            $message = $body;
            $message = str_replace('{name}', $client->name, $message);
            $message = str_replace('{message_body}', '', $message);

            // Use centralized eligibility check — only due/overdue invoices
            $unpaidInvoices = InvoiceEligibilityService::getEligibleInvoices($client->id);

            $totalAmount = $unpaidInvoices->sum('remaining_amount');

            if ($unpaidInvoices->isNotEmpty()) {
                $invoiceDetailsList = WhatsAppMessageBuilder::buildInvoiceDetailsList($unpaidInvoices);
                $message = WhatsAppMessageBuilder::buildMessage($message, $client->name, $totalAmount, $invoiceDetailsList);
                // Use the most recent invoice's actual due date
                $dueDate = $unpaidInvoices->last()->due_date
                    ? Carbon::parse($unpaidInvoices->last()->due_date)->format('Y-m-d')
                    : Carbon::today()->format('Y-m-d');
            } else {
                $dueDate = Carbon::today()->format('Y-m-d');
            }

            $message = str_replace('{total_amount}', number_format($totalAmount, 2), $message);
            $message = str_replace('{invoice_details_list}', 'لا توجد فواتير مستحقة', $message);
            $message = str_replace('{due_date}', $dueDate, $message);
            $message = str_replace('{support_phone}', '96170781562', $message);
            $message = str_replace('{amount}', number_format($totalAmount > 0 ? $totalAmount : 0, 2), $message);
            $message = str_replace('{month}', now()->format('m'), $message);
            $message = str_replace('{year}', now()->format('Y'), $message);
            $message = str_replace('{collector}', auth('admin')->user()->name ?? 'الإدارة', $message);
            $message = str_replace('{datetime}', now()->format('Y-m-d h:i A'), $message);
            $message = str_replace('{balance_status}', 'الرصيد الحالي: $' . number_format($totalAmount, 2), $message);

            $result = $service->send($client->phone, $message);
            $status = (isset($result['success']) && $result['success'] === true) ? 'sent' : 'failed';

            WhatsAppMessageLog::create([
                'client_id' => $client->id,
                'client_name' => $client->name,
                'phone' => $client->phone,
                'message' => $message,
                'template_type' => $templateType,
                'status' => $status,
                'error' => $status === 'failed' ? ($result['error'] ?? 'Unknown') : null,
                'sent_by' => 'calendar:' . auth('admin')->id(),
            ]);

            if ($status === 'sent') {
                $results['sent']++;
            } else {
                $results['failed']++;
                if ($failCount < 5) {
                    $results['errors'][] = $client->name . ': ' . ($result['error'] ?? 'Unknown');
                    $failCount++;
                }
            }

            usleep(1000000);
        }

        return response()->json($results);
    }

    private function formatSessionStatusLabel(string $status): string
    {
        return match (strtolower(trim($status))) {
            'ready', 'connected' => 'Ready',
            'initializing' => 'Initializing',
            'starting' => 'Starting',
            'authenticated' => 'Authenticated',
            'disconnected' => 'Disconnected',
            'auth_failure' => 'Auth Failure',
            default => Str::of($status)->replace('_', ' ')->title()->toString(),
        };
    }

    private function isQueueLikelyStuck(int $pendingQueueCount, int $sendingQueueCount, $oldestPendingAt): bool
    {
        if ($pendingQueueCount <= 0 || !$oldestPendingAt) {
            return false;
        }

        if ($sendingQueueCount > 0) {
            return false;
        }

        return $oldestPendingAt instanceof Carbon && $oldestPendingAt->lt(now()->subMinutes(10));
    }

    private function hasRecentFailureWarning(int $failuresToday, $lastFailureAt): bool
    {
        if ($failuresToday <= 0 || !$lastFailureAt) {
            return false;
        }

        return $failuresToday >= 3 || ($lastFailureAt instanceof Carbon && $lastFailureAt->gt(now()->subHours(2)));
    }

    private function buildOverallMonitorAlert(
        string $emergencyStop,
        bool $apiReachable,
        bool $sessionConnected,
        string $sessionStatus,
        bool $qrNeeded,
        bool $queueLooksStuck,
        bool $failureWarning
    ): array
    {
        if ($emergencyStop === '1') {
            return ['level' => 'danger', 'label' => 'Emergency Stop', 'text' => 'WhatsApp sending is paused by emergency stop.'];
        }

        if (!$apiReachable) {
            return ['level' => 'danger', 'label' => 'API Down', 'text' => 'OpenWA API is unreachable. Admin should check the OpenWA server/container first.'];
        }

        if ($qrNeeded) {
            return ['level' => 'warning', 'label' => 'QR Required', 'text' => 'Session is not authenticated. Admin should open QR and scan it from the WhatsApp phone.'];
        }

        if (!$sessionConnected || in_array(strtolower(trim($sessionStatus)), ['initializing', 'starting', 'disconnected', 'auth_failure'], true)) {
            return ['level' => 'warning', 'label' => 'Session Issue', 'text' => 'OpenWA is reachable but the WhatsApp session is not fully ready yet.'];
        }

        if ($queueLooksStuck) {
            return ['level' => 'warning', 'label' => 'Queue Delay', 'text' => 'Pending queue items look stuck. Admin should inspect Queue and restart the session if needed.'];
        }

        if ($failureWarning) {
            return ['level' => 'warning', 'label' => 'Recent Failures', 'text' => 'Recent WhatsApp failures were detected. Review the latest errors in Monitor/Log.'];
        }

        return ['level' => 'success', 'label' => 'Healthy', 'text' => 'OpenWA, session, and queue look healthy right now.'];
    }

    private function buildMonitorStatusBadges(
        string $emergencyStop,
        bool $apiReachable,
        bool $sessionConnected,
        string $sessionStatus,
        bool $qrNeeded,
        bool $queueLooksStuck,
        bool $failureWarning
    ): array
    {
        $badges = [];

        if ($emergencyStop === '1') {
            $badges[] = ['label' => 'Emergency Stop', 'class' => 'badge-light-danger'];
        }

        $badges[] = [
            'label' => $apiReachable ? 'API Reachable' : 'API Unreachable',
            'class' => $apiReachable ? 'badge-light-success' : 'badge-light-danger',
        ];

        $badges[] = [
            'label' => 'Session ' . $this->formatSessionStatusLabel($sessionStatus),
            'class' => $sessionConnected ? 'badge-light-success' : 'badge-light-warning',
        ];

        if ($qrNeeded) {
            $badges[] = ['label' => 'QR Required', 'class' => 'badge-light-warning'];
        }

        if ($queueLooksStuck) {
            $badges[] = ['label' => 'Queue Looks Stuck', 'class' => 'badge-light-warning'];
        }

        if ($failureWarning) {
            $badges[] = ['label' => 'Recent Failures', 'class' => 'badge-light-danger'];
        }

        return $badges;
    }

    private function buildConnectionRecommendedAction(
        $emergencyStop,
        bool $apiReachable,
        bool $sessionConnected,
        bool $qrNeeded,
        int $pendingQueueCount,
        int $sendingQueueCount,
        $oldestPendingAt,
        ?string $lastFailureError
    ): string {
        if ((string) $emergencyStop === '1') {
            return 'WhatsApp is paused by Emergency Stop. If this was intentional, leave it as-is. Otherwise, restart the service from the dashboard.';
        }

        if (!$apiReachable) {
            return 'OpenWA API is unreachable. Admin should check the OpenWA server/container first, then use Restart Service after the API is back.';
        }

        if (!$sessionConnected && $qrNeeded) {
            return 'OpenWA is reachable but the session needs authentication. Admin should scan the QR code shown on this page.';
        }

        if (!$sessionConnected) {
            return 'OpenWA is reachable but the session is not ready. Admin should try Restart Service and then re-check the QR/session state.';
        }

        if ($pendingQueueCount > 0 && $oldestPendingAt && Carbon::parse($oldestPendingAt)->lt(now()->subMinutes(5)) && $sendingQueueCount === 0) {
            return 'Messages are waiting in Queue longer than expected. Admin should open Queue, confirm new activity, and if needed restart the batch processor/service.';
        }

        if (!empty($lastFailureError)) {
            return 'Connection looks healthy, but there were recent send failures. Admin should review the latest failed log entry and compare it with Queue activity.';
        }

        return 'Connection and queue look healthy. Admin can keep working normally and use Queue/Log for any delivery follow-up.';
    }

    private function getMessageSourceMeta(?string $sentBy): array
    {
        $sentBy = trim((string) $sentBy);

        if ($sentBy === '') {
            return ['key' => 'system', 'label' => 'System', 'badge' => 'badge-light-dark', 'detail' => '-'];
        }

        if (str_contains($sentBy, 'admin:manual|batch:')) {
            return ['key' => 'manual_bulk', 'label' => 'Manual Bulk', 'badge' => 'badge-light-primary', 'detail' => $this->extractBatchShortId($sentBy)];
        }

        if (str_contains($sentBy, 'admin:automation|batch:')) {
            return ['key' => 'automation', 'label' => 'Automation', 'badge' => 'badge-light-success', 'detail' => $this->extractBatchShortId($sentBy)];
        }

        if (str_starts_with($sentBy, 'hermes:test|batch:') || str_starts_with($sentBy, 'hermes:bgtest|batch:') || str_starts_with($sentBy, 'hermes:wwwtest|batch:')) {
            return ['key' => 'hermes', 'label' => 'Test Batch', 'badge' => 'badge-light-info', 'detail' => $this->extractBatchShortId($sentBy)];
        }

        if (str_starts_with($sentBy, 'system:autoreceipt')) {
            return ['key' => 'autoreceipt', 'label' => 'Auto Receipt', 'badge' => 'badge-light-warning', 'detail' => $this->extractBatchShortId($sentBy)];
        }

        if (str_starts_with($sentBy, 'calendar:')) {
            if (str_contains($sentBy, '|batch:')) {
                return ['key' => 'calendar', 'label' => 'Calendar', 'badge' => 'badge-light-warning', 'detail' => $this->extractBatchShortId($sentBy)];
            }
            return ['key' => 'calendar', 'label' => 'Calendar Direct', 'badge' => 'badge-light-warning', 'detail' => $sentBy];
        }

        if (str_starts_with($sentBy, 'admin:') && str_contains($sentBy, '|batch:')) {
            return ['key' => 'manual_bulk', 'label' => 'Admin Batch', 'badge' => 'badge-light-primary', 'detail' => $this->extractBatchShortId($sentBy)];
        }

        if (str_starts_with($sentBy, 'admin:')) {
            return ['key' => 'manual_single', 'label' => 'Manual Single', 'badge' => 'badge-light-primary', 'detail' => $sentBy];
        }

        if (str_starts_with($sentBy, 'cron:')) {
            return ['key' => 'cron', 'label' => 'Cron', 'badge' => 'badge-light-success', 'detail' => $sentBy];
        }

        if (str_starts_with($sentBy, 'hermes:')) {
            return ['key' => 'hermes', 'label' => 'Hermes', 'badge' => 'badge-light-info', 'detail' => $sentBy];
        }

        return ['key' => 'other', 'label' => 'Other', 'badge' => 'badge-light-dark', 'detail' => $sentBy];
    }

    private function extractBatchShortId(string $sentBy): string
    {
        $parts = explode('|batch:', $sentBy, 2);
        $batchId = $parts[1] ?? $sentBy;

        return 'Batch ' . substr($batchId, 0, 8);
    }
}
