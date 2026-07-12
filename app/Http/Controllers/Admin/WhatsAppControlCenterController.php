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

            if ($request->filled('unpaid')) {
                $unpaidCount = (int) $request->unpaid;
                $query->whereRaw('(SELECT COUNT(*) FROM tbl_invoices WHERE tbl_invoices.client_id = tbl_clients.id AND tbl_invoices.paid = 0) >= ?', [$unpaidCount]);
            }

            if ($request->filled('address')) {
                $addr = $request->address;
                $query->where(function ($q) use ($addr) {
                    $q->where('address1', 'like', "%{$addr}%")->orWhere('address2', 'like', "%{$addr}%");
                });
            }

            if ($request->filled('subscription')) {
                $query->where('subscription_id', $request->subscription);
            }

            if ($request->filled('last_payment')) {
                $query->whereRaw('(SELECT MAX(created_at) FROM tbl_payments WHERE tbl_payments.client_id = tbl_clients.id) <= ?', [$request->last_payment . ' 23:59:59']);
            }

            $clients = $query->select('id', 'name', 'phone')->limit(200)->get();

            return response()->json([
                'clients' => $clients->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'phone' => $c->phone]),
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

    /**
     * 🤖 Automation — List and manage rules. (P2)
     */
    public function automation()
    {
        // Read automation rules from app_config
        $rules = [];

        $ruleNames = [
            'whatsapp_remind_before' => [
                'label' => 'تذكير قبل القطع',
                'label_en' => 'Reminder Before Disconnection',
                'command' => 'whatsapp:reminders',
            ],
        ];

        foreach ($ruleNames as $key => $config) {
            $value = DB::table('app_config')->where('key', $key)->value('value');
            $enabled = DB::table('app_config')->where('key', 'whatsapp_auto_enabled')->value('value');
            $rules[] = [
                'id' => $key,
                'label' => $config['label'],
                'label_en' => $config['label_en'],
                'command' => $config['command'],
                'enabled' => $enabled == '1',
                'value' => $value,
            ];
        }

        return view('dashbord.whatsapp.automation', compact('rules'));
    }

    /**
     * 🔄 Toggle automation rule on/off. (P2)
     */
    public function toggleAutomationRule($id)
    {
        $current = DB::table('app_config')->where('key', 'whatsapp_auto_enabled')->value('value');
        $new = $current == '1' ? '0' : '1';
        DB::table('app_config')->where('key', 'whatsapp_auto_enabled')->update(['value' => $new]);

        return response()->json(['success' => true, 'enabled' => $new == '1']);
    }

    /**
     * ▶️ Run an automation rule immediately. (P2)
     */
    public function runAutomationRule($id)
    {
        try {
            Artisan::call($id);
            return response()->json(['success' => true, 'output' => Artisan::output()]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

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
