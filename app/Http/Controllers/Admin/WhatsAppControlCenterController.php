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

        // Real connection status from OpenWA
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

        // Message counts
        $messagesToday = WhatsAppMessageLog::whereDate('created_at', today())->count();
        $messagesThisMonth = WhatsAppMessageLog::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $failuresToday = WhatsAppMessageLog::whereDate('created_at', today())
            ->where('status', 'failed')
            ->count();

        // Clients with WhatsApp numbers
        $totalClients = DB::table('tbl_clients')->whereNull('deleted_at')->count();
        $clientsWithPhone = DB::table('tbl_clients')
            ->whereNull('deleted_at')
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->count();

        // Last successful send
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

        // Replace placeholders with sample data
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

        // Send via WhatsApp service
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
     * 🎯 Search clients for manual selection (Select2/typeahead).
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
        // 🔍 Filter preview (no actual sending)
        if ($request->boolean('preview')) {
            $query = DB::table('tbl_clients')->whereNull('deleted_at')
                ->whereNotNull('phone')->where('phone', '!=', '');

            // Search query (name, phone, or ID)
            if ($request->filled('q')) {
                $q = $request->q;
                $query->where(function ($qry) use ($q) {
                    $qry->where('name', 'like', "%{$q}%")
                      ->orWhere('phone', 'like', "%{$q}%")
                      ->orWhere('id', 'like', "%{$q}%");
                });
            }

            // Client type filter
            if ($request->filled('client_type')) {
                $query->where('client_type', $request->client_type);
            }

            // Status filter
            if ($request->filled('status')) {
                $query->where('is_active', $request->status);
            }

            // Unpaid bills filter
            if ($request->filled('unpaid')) {
                $unpaidCount = (int) $request->unpaid;
                $query->whereRaw('(SELECT COUNT(*) FROM tbl_invoices WHERE tbl_invoices.client_id = tbl_clients.id AND tbl_invoices.status IN ("unpaid","partial")) >= ?', [$unpaidCount]);
            }

            // Subscription filter
            if ($request->filled('subscription')) {
                $query->where('subscription_id', $request->subscription);
            }

            // Last payment before
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

        // 📨 Actual send validation
        $request->validate([
            'template_type' => 'required|string',
            'client_ids' => 'required|array',
            'client_ids.*' => 'integer|exists:tbl_clients,id',
        ]);

        $body = WhatsAppTemplateService::getBody($request->template_type);

        // If template body not found, return error
        if (empty($body)) {
            return response()->json([
                'sent' => 0,
                'failed' => 0,
                'errors' => [trans('clients.whatsapp_template_not_found') ?? 'القالب غير موجود'],
            ]);
        }

        // Override template body if custom message provided
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

            // Build message with dynamic replacements
            $message = $body;

            // Always replace {name} and {message_body}
            $message = str_replace('{name}', $client->name, $message);
            $message = str_replace('{message_body}', $request->custom_message ?? '', $message);

            // Look up unpaid invoices for template variables
            $unpaidInvoices = DB::table('tbl_invoices')
                ->where('client_id', $client->id)
                ->whereIn('status', ['unpaid', 'partial'])
                ->get();

            $totalAmount = $unpaidInvoices->sum('remaining_amount');

            if ($unpaidInvoices->isNotEmpty()) {
                $invoiceDetailsList = WhatsAppMessageBuilder::buildInvoiceDetailsList($unpaidInvoices);
                $message = WhatsAppMessageBuilder::buildMessage($message, $client->name, $totalAmount, $invoiceDetailsList);
            }

            // Replace remaining common variables (fallback for clients with no unpaid invoices)
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

            // Rate limit: 1s between sends
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

        // Search
        if ($request->search) {
            $query->search($request->search);
        }

        // Filters
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

        // Pagination
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

        return view('dashbord.whatsapp.automation', compact('rules', 'templates'));
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

        $this->saveAutomationRulesConfig($rules);

        // Build day names summary
        $dayNames = ['سبت', 'أحد', 'اثنين', 'ثلاثاء', 'أربعاء', 'خميس', 'جمعة'];
        $selectedDays = array_intersect($request->days, [0,1,2,3,4,5,6]);
        if (count($selectedDays) === 7) {
            $daysSummary = 'كل الأيام';
        } else {
            $daysSummary = implode('، ', array_map(fn($d) => $dayNames[$d] ?? '', $selectedDays));
        }

        return response()->json([
            'success' => true,
            'rule' => $rules[$id],
            'days_summary' => $daysSummary,
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
            Artisan::call($command);
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
     * Migrates old keys if this is the first call after upgrade.
     */
    private function getAutomationRulesConfig(): array
    {
        $defaults = $this->getDefaultAutomationRules();
        $stored = DB::table('app_config')->where('key', 'whatsapp_automation_rules')->value('value');

        if ($stored) {
            // Merge stored with defaults (adds new rules if they were added to defaults)
            $storedRules = json_decode($stored, true) ?? [];
            $rules = array_merge($defaults, $storedRules);

            // Ensure each rule has all expected fields from defaults
            foreach ($defaults as $key => $defaultRule) {
                if (!isset($rules[$key])) {
                    $rules[$key] = $defaultRule;
                } else {
                    // Fill missing fields from defaults
                    foreach ($defaultRule as $field => $value) {
                        if (!array_key_exists($field, $rules[$key])) {
                            $rules[$key][$field] = $value;
                        }
                    }
                }
            }

            return $rules;
        }

        // First run after upgrade — migrate old keys
        $oldEnabled = DB::table('app_config')->where('key', 'whatsapp_auto_enabled')->value('value');
        $oldRemindBefore = DB::table('app_config')->where('key', 'whatsapp_remind_before')->value('value');

        if ($oldEnabled !== null) {
            $defaults['whatsapp_remind_before']['enabled'] = ($oldEnabled == '1');
        }
        if ($oldRemindBefore !== null) {
            $defaults['whatsapp_remind_before']['days_offset'] = -1 * abs((int) $oldRemindBefore);
        }

        // Save initial config
        $this->saveAutomationRulesConfig($defaults);

        return $defaults;
    }

    /**
     * Save automation rules config to app_config.
     */
    private function saveAutomationRulesConfig(array $rules): void
    {
        // Strip labels and commands from stored config (they're defined in code)
        $lean = [];
        foreach ($rules as $key => $rule) {
            $lean[$key] = [
                'enabled' => $rule['enabled'] ?? false,
                'time' => $rule['time'] ?? '09:00',
                'days' => $rule['days'] ?? [0,1,2,3,4,5,6],
                'template' => $rule['template'] ?? 'reminder',
                'days_offset' => $rule['days_offset'] ?? 0,
            ];
        }

        DB::table('app_config')->updateOrInsert(
            ['key' => 'whatsapp_automation_rules'],
            ['value' => json_encode($lean, JSON_UNESCAPED_UNICODE)]
        );
    }

    /**
     * Default automation rules definitions.
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
            ],
            'whatsapp_receipt' => [
                'id' => 'whatsapp_receipt',
                'label' => 'إيصال الدفع',
                'label_en' => 'Payment Receipt',
                'command' => 'whatsapp:receipt',
                'icon' => 'bi bi-receipt',
                'color' => 'success',
                'enabled' => false,
                'time' => '12:00',
                'days' => [0,1,2,3,4,5,6],
                'template' => 'receipt',
                'days_offset' => 0,
                'days_offset_label' => 'بعد الدفع بـ',
                'days_offset_unit' => 'أيام',
            ],
            'whatsapp_disconnection' => [
                'id' => 'whatsapp_disconnection',
                'label' => 'إشعار القطع',
                'label_en' => 'Disconnection Notice',
                'command' => 'whatsapp:disconnection',
                'icon' => 'bi bi-plug',
                'color' => 'danger',
                'enabled' => false,
                'time' => '08:00',
                'days' => [0,1,2,3,4,5,6],
                'template' => 'disconnection',
                'days_offset' => -1,
                'days_offset_label' => 'قبل القطع بـ',
                'days_offset_unit' => 'أيام',
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
}
