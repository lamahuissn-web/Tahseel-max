<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppMessageLog;
use App\Services\WhatsApp\WhatsAppTemplateService;
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
        // Get OpenWA connection status
        $connectionStatus = DB::table('app_config')->where('key', 'whatsapp_auto_enabled')->value('value');
        $emergencyStop = DB::table('app_config')->where('key', 'whatsapp_emergency_stop')->value('value');

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
            ->whereNotNull('phone1')
            ->where('phone1', '!=', '')
            ->count();

        // Last successful send
        $lastSent = WhatsAppMessageLog::where('status', 'sent')
            ->orderBy('created_at', 'desc')
            ->first();

        return view('dashbord.whatsapp.dashboard', compact(
            'connectionStatus', 'emergencyStop',
            'messagesToday', 'messagesThisMonth', 'failuresToday',
            'totalClients', 'clientsWithPhone', 'lastSent'
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

        // Send via existing WhatsApp service
        $service = app(WhatsAppService::class);
        $result = $service->sendMessage($request->phone, $message);

        return response()->json([
            'success' => isset($result['status']) && $result['status'] === 'success',
            'message' => isset($result['status']) && $result['status'] === 'success'
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
                  ->orWhere('phone1', 'like', "%{$term}%")
                  ->orWhere('id', 'like', "%{$term}%");
            })
            ->select('id', 'name', 'phone1 as phone')
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
        // Preview mode — return matching clients without sending
        if ($request->boolean('preview')) {
            $query = DB::table('tbl_clients')->whereNull('deleted_at')
                ->whereNotNull('phone1')->where('phone1', '!=', '');

            if ($request->filled('unpaid')) {
                $minUnpaid = (int)$request->unpaid;
                $query->whereIn('id', function ($q) use ($minUnpaid) {
                    $q->select('client_id')
                      ->from('tbl_invoices')
                      ->whereIn('status', ['unpaid', 'partial'])
                      ->groupBy('client_id')
                      ->havingRaw('COUNT(*) >= ?', [$minUnpaid]);
                });
            }
            if ($request->filled('area')) {
                $query->where('area_id', $request->area);
            }
            if ($request->filled('subscription')) {
                $query->where('subscription_id', $request->subscription);
            }
            if ($request->filled('last_payment')) {
                $query->whereNotIn('id', function ($q) use ($request) {
                    $q->select('client_id')
                      ->from('tbl_invoices')
                      ->where('status', 'paid')
                      ->where('paid_date', '>=', $request->last_payment);
                });
            }

            $clients = $query->limit(500)->get(['id', 'name', 'phone1 as phone']);
            return response()->json(['clients' => $clients]);
        }

        // Actual send mode
        $request->validate([
            'template_type' => 'required|string',
            'client_ids' => 'required|array',
            'client_ids.*' => 'integer|exists:tbl_clients,id',
        ]);

        $body = WhatsAppTemplateService::getBody($request->template_type);

        // Override template body if custom message provided
        if ($request->template_type === 'custom' && $request->custom_message) {
            $body = $request->custom_message;
        }

        $service = app(WhatsAppService::class);
        $results = ['sent' => 0, 'failed' => 0, 'errors' => []];

        foreach ($request->client_ids as $clientId) {
            $client = DB::table('tbl_clients')->find($clientId);
            if (!$client || empty($client->phone1)) {
                $results['failed']++;
                continue;
            }

            $message = str_replace(
                ['{name}', '{message_body}'],
                [$client->name, $request->custom_message ?? ''],
                $body
            );

            $result = $service->sendMessage($client->phone1, $message);
            $status = (isset($result['status']) && $result['status'] === 'success') ? 'sent' : 'failed';

            WhatsAppMessageLog::create([
                'client_id' => $client->id,
                'client_name' => $client->name,
                'phone' => $client->phone1,
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
        $result = $service->sendMessage($log->phone, $log->message);

        $log->update([
            'status' => (isset($result['status']) && $result['status'] === 'success') ? 'sent' : 'failed',
            'error' => isset($result['status']) && $result['status'] === 'success' ? null : ($result['error'] ?? 'Unknown'),
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
            $result = $service->sendMessage($log->phone, $log->message);
            if (isset($result['status']) && $result['status'] === 'success') {
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