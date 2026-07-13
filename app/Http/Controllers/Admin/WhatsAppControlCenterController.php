<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppMessageLog;
use App\Services\WhatsApp\WhatsAppTemplateService;
use App\Services\WhatsAppMessageBuilder;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class WhatsAppControlCenterController extends Controller
{
    /**
     * 📊 Dashboard — Pulse metrics at a glance.
     */
    public function dashboard()
    {
        $emergencyStop = DB::table('app_config')->where('key', 'whatsapp_emergency_stop')->value('value');

        $connectionStatus = false;
        $devicePhone = null;
        $lastConnectedAt = null;

        if ($emergencyStop != '1') {
            try {
                $service = app(WhatsAppService::class);
                $device = $service->status();
                if ($device && ($device['connected'] ?? false)) {
                    $connectionStatus = true;
                    $devicePhone = $device['phone'] ?? null;
                }
            } catch (\Exception $e) {
                // OpenWA not reachable — stay as disconnected
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

        return view('dashbord.whatsapp.dashboard', compact(
            'connectionStatus', 'emergencyStop',
            'messagesToday', 'messagesThisMonth', 'failuresToday',
            'totalClients', 'clientsWithPhone', 'lastSent',
            'devicePhone'
        ));
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

        foreach ($request->client_ids as $clientId) {
            $client = DB::table('tbl_clients')->find($clientId);
            if (!$client || empty($client->phone)) {
                $results['failed']++;
                continue;
            }

            $message = $body;
            $message = str_replace('{name}', $client->name, $message);
            $message = str_replace('{message_body}', $request->custom_message ?? '', $message);

            $unpaidInvoices = DB::table('tbl_invoices')
                ->where('client_id', $client->id)
                ->whereIn('status', ['unpaid', 'partial'])
                ->get();

            $totalAmount = $unpaidInvoices->sum('remaining_amount');

            if ($unpaidInvoices->isNotEmpty()) {
                $invoiceDetailsList = WhatsAppMessageBuilder::buildInvoiceDetailsList($unpaidInvoices);
                $message = WhatsAppMessageBuilder::buildMessage($message, $client->name, $totalAmount, $invoiceDetailsList);
            }

            $message = str_replace('{total_amount}', number_format($totalAmount, 2), $message);
            $message = str_replace('{invoice_details_list}', 'لا توجد فواتير مستحقة', $message);
            $message = str_replace('{due_date}', now()->addDays(3)->format('Y-m-d'), $message);
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

        // Filter settings (reminder-specific)
        if ($id === 'whatsapp_remind_before') {
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
        if ($id === 'whatsapp_remind_before' && !empty($request->filter_client_type) && $request->filter_client_type !== 'all') {
            $filtersSummary .= ($request->filter_client_type === 'internet' ? 'إنترنت' : 'ساتلايت');
        }
        if ($id === 'whatsapp_remind_before') {
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
    private function getAutomationRulesConfig(): array
    {
        $defaults = $this->getDefaultAutomationRules();
        $stored = DB::table('app_config')->where('key', 'whatsapp_automation_rules')->value('value');

        if ($stored) {
            $storedRules = json_decode($stored, true) ?? [];
            $rules = array_merge($defaults, $storedRules);

            foreach ($defaults as $key => $defaultRule) {
                if (!isset($rules[$key])) {
                    $rules[$key] = $defaultRule;
                } else {
                    foreach ($defaultRule as $field => $value) {
                        if (!array_key_exists($field, $rules[$key])) {
                            $rules[$key][$field] = $value;
                        }
                    }
                }
            }

            // Strip rules that are no longer in defaults (e.g. removed receipt/disconnection)
            $rules = array_intersect_key($rules, $defaults);

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
            $entry = [
                'enabled' => $rule['enabled'] ?? false,
                'time' => $rule['time'] ?? '09:00',
                'days' => $rule['days'] ?? [0,1,2,3,4,5,6],
                'template' => $rule['template'] ?? 'reminder',
                'days_offset' => $rule['days_offset'] ?? 0,
            ];

            // Save filter settings for reminder rule
            if ($key === 'whatsapp_remind_before') {
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
            'whatsapp_custom' => [
                'id' => 'whatsapp_custom',
                'label' => 'رسالة مخصصة',
                'label_en' => 'Custom Message',
                'command' => 'whatsapp:custom',
                'icon' => 'bi bi-envelope',
                'color' => 'info',
                'enabled' => false,
                'time' => '10:00',
                'days' => [0,1,2,3,4,5,6],
                'template' => 'custom',
                'days_offset' => 0,
                'days_offset_label' => 'كل',
                'days_offset_unit' => 'أيام',
            ],
        ];
    }

    // ═══════════════════════════════════════════════════════════════
    //  ⏳ QUEUE
    // ═══════════════════════════════════════════════════════════════

    /**
     * ⏳ Queue — View pending/recent messages. (P2)
     */
    public function queue()
    {
        $pending = WhatsAppMessageLog::where('status', 'pending')->count();
        $failed = WhatsAppMessageLog::where('status', 'failed')->count();
        $recent = WhatsAppMessageLog::orderBy('created_at', 'desc')->limit(50)->get();

        $queuePaused = DB::table('app_config')->where('key', 'whatsapp_auto_enabled')->value('value') == '0';

        return view('dashbord.whatsapp.queue', compact('pending', 'failed', 'recent', 'queuePaused'));
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

        foreach ($request->client_ids as $clientId) {
            $client = DB::table('tbl_clients')->find($clientId);
            if (!$client || empty($client->phone)) {
                $results['failed']++;
                continue;
            }

            $message = $body;
            $message = str_replace('{name}', $client->name, $message);
            $message = str_replace('{message_body}', '', $message);

            $unpaidInvoices = DB::table('tbl_invoices')
                ->where('client_id', $client->id)
                ->whereIn('status', ['unpaid', 'partial'])
                ->whereNull('deleted_at')
                ->get();

            $totalAmount = $unpaidInvoices->sum('remaining_amount');

            if ($unpaidInvoices->isNotEmpty()) {
                $invoiceDetailsList = WhatsAppMessageBuilder::buildInvoiceDetailsList($unpaidInvoices);
                $message = WhatsAppMessageBuilder::buildMessage($message, $client->name, $totalAmount, $invoiceDetailsList);
            }

            $message = str_replace('{total_amount}', number_format($totalAmount, 2), $message);
            $message = str_replace('{invoice_details_list}', 'لا توجد فواتير مستحقة', $message);
            $message = str_replace('{due_date}', now()->addDays(3)->format('Y-m-d'), $message);
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


    // ═══════════════════════════════════════════════════════════════
    //  👥 AUTOMATION — Clients list (Tab 1)
    // ═══════════════════════════════════════════════════════════════

    /**
     * 👥 Get filtered clients with unpaid invoice summary.
     */
    public function getAutomationClients(Request $request)
    {
        $clientType = $request->input('client_type', 'all');
        $status = $request->input('status', 'all');
        $subscriptionId = $request->input('subscription_id');
        $minUnpaid = (int) ($request->input('min_unpaid', 0));
        $search = $request->input('search');

        $query = DB::table('tbl_clients')
            ->whereNull('deleted_at')
            ->whereNotNull('phone')
            ->where('phone', '!=', '');

        if ($clientType !== 'all') {
            $query->where('client_type', $clientType);
        }

        if ($status !== 'all') {
            $query->where('is_active', $status === 'active' ? '1' : '0');
        }

        if ($subscriptionId) {
            $query->where('subscription_id', $subscriptionId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $clients = $query->select(
                'id', 'name', 'phone', 'client_type', 'is_active',
                DB::raw("(SELECT COUNT(*) FROM tbl_invoices WHERE tbl_invoices.client_id = tbl_clients.id AND tbl_invoices.status IN ('unpaid','partial') AND tbl_invoices.deleted_at IS NULL) as invoice_count"),
                DB::raw("(SELECT COALESCE(SUM(remaining_amount), 0) FROM tbl_invoices WHERE tbl_invoices.client_id = tbl_clients.id AND tbl_invoices.status IN ('unpaid','partial') AND tbl_invoices.deleted_at IS NULL) as total_unpaid"),
                DB::raw("(SELECT MAX(due_date) FROM tbl_invoices WHERE tbl_invoices.client_id = tbl_clients.id AND tbl_invoices.status IN ('unpaid','partial') AND tbl_invoices.deleted_at IS NULL) as last_due_date")
            )
            ->orderBy('name')
            ->paginate(50);

        $clients->getCollection()->transform(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'phone' => $c->phone,
                'client_type' => $c->client_type,
                'is_active' => $c->is_active,
                'invoice_count' => (int) $c->invoice_count,
                'total_unpaid' => (float) $c->total_unpaid,
                'last_due_date' => $c->last_due_date,
            ];
        });

        return response()->json($clients);
    }

    /**
     * 📨 Send now — immediate send to selected clients.
     */
    public function sendNow(Request $request)
    {
        $request->validate([
            'client_ids' => 'required|array',
            'client_ids.*' => 'integer|exists:tbl_clients,id',
            'template_type' => 'nullable|string',
        ]);

        $templateType = $request->template_type ?? 'reminder';
        $body = WhatsAppTemplateService::getBody($templateType);

        if (empty($body)) {
            return response()->json(['sent' => 0, 'failed' => 0, 'errors' => ['القالب غير موجود']]);
        }

        $service = app(WhatsAppService::class);
        $results = ['sent' => 0, 'failed' => 0, 'errors' => []];
        $failCount = 0;

        foreach ($request->client_ids as $clientId) {
            $client = DB::table('tbl_clients')->find($clientId);
            if (!$client || empty($client->phone)) {
                $results['failed']++;
                continue;
            }

            $message = $body;
            $message = str_replace('{name}', $client->name, $message);
            $message = str_replace('{message_body}', '', $message);

            $unpaidInvoices = DB::table('tbl_invoices')
                ->where('client_id', $client->id)
                ->whereIn('status', ['unpaid', 'partial'])
                ->whereNull('deleted_at')
                ->get();

            $totalAmount = $unpaidInvoices->sum('remaining_amount');

            if ($unpaidInvoices->isNotEmpty()) {
                $invoiceDetailsList = WhatsAppMessageBuilder::buildInvoiceDetailsList($unpaidInvoices);
                $message = WhatsAppMessageBuilder::buildMessage($message, $client->name, $totalAmount, $invoiceDetailsList);
            }

            $message = str_replace('{total_amount}', number_format($totalAmount, 2), $message);
            $message = str_replace('{invoice_details_list}', 'لا توجد فواتير مستحقة', $message);
            $message = str_replace('{due_date}', now()->addDays(3)->format('Y-m-d'), $message);
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
                'sent_by' => 'automation:' . auth('admin')->id(),
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

    /**
     * ⏰ Schedule a new automation task from selected clients.
     */
    public function scheduleTask(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'client_ids' => 'required|array',
            'client_ids.*' => 'integer|exists:tbl_clients,id',
            'time' => 'required|date_format:H:i',
            'days' => 'required|array',
            'days.*' => 'integer|between:0,6',
            'template_type' => 'required|string',
        ]);

        $rules = $this->getAutomationRulesConfig();

        $ruleId = 'whatsapp_scheduled_' . uniqid();
        $rules[$ruleId] = [
            'id' => $ruleId,
            'label' => $request->name,
            'label_en' => $request->name,
            'command' => 'whatsapp:reminders',
            'icon' => 'bi bi-clock',
            'color' => 'primary',
            'enabled' => true,
            'time' => $request->time,
            'days' => $request->days,
            'template' => $request->template_type,
            'days_offset' => 0,
            'days_offset_label' => 'كل',
            'days_offset_unit' => 'أيام',
            'client_ids' => $request->client_ids,
        ];

        $this->saveAutomationRulesConfig($rules);

        return response()->json([
            'success' => true,
            'rule_id' => $ruleId,
            'message' => 'تم إنشاء المهمة المجدولة بنجاح',
        ]);
    }

    /**
     * ⏰ Get all scheduled tasks with stats.
     */
    public function getAutomationTasks()
    {
        $rules = $this->getAutomationRulesConfig();
        $templates = WhatsAppTemplateService::getAll();

        $result = [];
        foreach ($rules as $ruleId => $rule) {
            $lastRun = WhatsAppMessageLog::where(function ($q) use ($ruleId, $rule) {
                    $q->where('sent_by', 'LIKE', "%{$ruleId}%")
                      ->orWhere('template_type', $rule['template']);
                })
                ->orderBy('created_at', 'desc')
                ->first();

            $totalSent = WhatsAppMessageLog::where(function ($q) use ($ruleId, $rule) {
                    $q->where('sent_by', 'LIKE', "%{$ruleId}%")
                      ->orWhere('template_type', $rule['template']);
                })
                ->where('status', 'sent')
                ->count();

            $result[] = [
                'id' => $ruleId,
                'label' => $rule['label'] ?? $rule['label_en'] ?? 'Unknown',
                'label_en' => $rule['label_en'] ?? $rule['label'] ?? 'Unknown',
                'enabled' => $rule['enabled'] ?? false,
                'time' => $rule['time'] ?? '09:00',
                'days' => $rule['days'] ?? [],
                'template' => $rule['template'] ?? 'reminder',
                'days_offset' => $rule['days_offset'] ?? 0,
                'days_offset_label' => $rule['days_offset_label'] ?? '',
                'filter_client_type' => $rule['filter_client_type'] ?? 'all',
                'filter_subscription_id' => $rule['filter_subscription_id'] ?? null,
                'filter_min_unpaid' => $rule['filter_min_unpaid'] ?? 0,
                'filter_client_status' => $rule['filter_client_status'] ?? 'all',
                'client_ids' => $rule['client_ids'] ?? null,
                'last_run' => $lastRun ? $lastRun->created_at->format('Y-m-d h:i A') : null,
                'last_run_status' => $lastRun ? $lastRun->status : null,
                'total_sent' => $totalSent,
                'total_failed' => WhatsAppMessageLog::where(function ($q) use ($ruleId, $rule) {
                        $q->where('sent_by', 'LIKE', "%{$ruleId}%")
                          ->orWhere('template_type', $rule['template']);
                    })
                    ->where('status', 'failed')
                    ->count(),
            ];
        }

        return response()->json([
            'tasks' => $result,
            'templates' => $templates,
        ]);
    }

    /**
     * 🗑️ Delete a scheduled task.
     */
    public function deleteTask($id)
    {
        $rules = $this->getAutomationRulesConfig();

        if (!isset($rules[$id])) {
            return response()->json(['success' => false, 'error' => 'Task not found'], 404);
        }

        unset($rules[$id]);
        $this->saveAutomationRulesConfig($rules);

        return response()->json(['success' => true, 'message' => 'تم حذف المهمة بنجاح']);
    }

}
