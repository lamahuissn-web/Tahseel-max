<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Invoice;
use App\Models\Clients;
use App\Services\WhatsAppMessageBuilder;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class WhatsAppSettingsController extends Controller
{
    protected $whatsapp;

    protected $arabicMonths = [
        1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
        5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
        9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
    ];

    protected function isValidPhone($phone)
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (empty($clean)) return false;
        if (preg_match('/^0+$/', $clean)) return false;
        if (strlen($clean) < 7) return false;
        return true;
    }

    protected function isSuspiciousPhone($phone)
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (preg_match('/^0+$/', $clean)) return true;
        if (strlen($clean) < 7) return true;
        if (preg_match('/^9610{5,}/', $clean)) return true;
        return false;
    }

    public function __construct(WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    public function index()
    {
        $status = $this->whatsapp->status();
        $qr = $this->whatsapp->getQR();
        $logs = $this->whatsapp->getLogs(50);

        $settings = [
            'whatsapp_enabled' => DB::table('app_config')->where('key', 'whatsapp_enabled')->value('value') ?? '0',
            'whatsapp_remind_before' => DB::table('app_config')->where('key', 'whatsapp_remind_before')->value('value') ?? '3',
            'whatsapp_remind_on_due' => DB::table('app_config')->where('key', 'whatsapp_remind_on_due')->value('value') ?? '1',
            'whatsapp_remind_after' => DB::table('app_config')->where('key', 'whatsapp_remind_after')->value('value') ?? '1,3,7',
            'whatsapp_message_template' => DB::table('app_config')->where('key', 'whatsapp_message_template')->value('value')
                ?? "👋 مرحباً {name}،\n\n📋 نود تذكيرك بوجود مبالغ مستحقة غير مدفوعة لحسابك بإجمالي {total_amount}$.\n\n📄 تفاصيل الفواتير المستحقة:\n{invoice_details_list}\n\n💳 يرجى التكرم بتسوية الرصيد المستحق في أقرب وقت ممكن.\nإذا كنت قد سددت هذا المبلغ مؤخراً، يرجى تجاهل هذه الرسالة.\n\n🙏 شكراً لتفهمك.",
            'whatsapp_auto_enabled' => DB::table('app_config')->where('key', 'whatsapp_auto_enabled')->value('value') ?? '0',
            'whatsapp_auto_time' => DB::table('app_config')->where('key', 'whatsapp_auto_time')->value('value') ?? '09:00',
            'whatsapp_auto_days' => DB::table('app_config')->where('key', 'whatsapp_auto_days')->value('value') ?? '1,2,3,4,5,6,7',
            'whatsapp_auto_client_type' => DB::table('app_config')->where('key', 'whatsapp_auto_client_type')->value('value') ?? 'all',
            'whatsapp_auto_min_amount' => DB::table('app_config')->where('key', 'whatsapp_auto_min_amount')->value('value') ?? '0',
            'whatsapp_auto_status' => DB::table('app_config')->where('key', 'whatsapp_auto_status')->value('value') ?? 'unpaid,partial',
            'whatsapp_auto_delay' => DB::table('app_config')->where('key', 'whatsapp_auto_delay')->value('value') ?? '10',
            'whatsapp_auto_skip_hours' => DB::table('app_config')->where('key', 'whatsapp_auto_skip_hours')->value('value') ?? '24',
        ];

        return view('dashbord.settings.whatsapp', compact('status', 'qr', 'logs', 'settings'));
    }

    public function update(Request $request)
    {
        $fields = [
            'whatsapp_enabled' => $request->whatsapp_enabled ? '1' : '0',
            'whatsapp_remind_before' => $request->whatsapp_remind_before,
            'whatsapp_remind_on_due' => $request->whatsapp_remind_on_due ? '1' : '0',
            'whatsapp_remind_after' => $request->whatsapp_remind_after,
            'whatsapp_message_template' => $request->whatsapp_message_template,
            'whatsapp_auto_enabled' => $request->whatsapp_auto_enabled ? '1' : '0',
            'whatsapp_auto_time' => $request->whatsapp_auto_time ?? '09:00',
            'whatsapp_auto_days' => $request->whatsapp_auto_days ? implode(',', $request->whatsapp_auto_days) : '',
            'whatsapp_auto_client_type' => $request->whatsapp_auto_client_type ?? 'all',
            'whatsapp_auto_min_amount' => $request->whatsapp_auto_min_amount ?? '0',
            'whatsapp_auto_status' => $request->whatsapp_auto_status ? implode(',', $request->whatsapp_auto_status) : '',
            'whatsapp_auto_delay' => $request->whatsapp_auto_delay ?? '10',
            'whatsapp_auto_skip_hours' => $request->whatsapp_auto_skip_hours ?? '24',
        ];

        foreach ($fields as $key => $value) {
            DB::table('app_config')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'updated_at' => now()]
            );
        }

        return redirect()->back()->with('success', trans('clients.whatsapp_settings_saved'));
    }

    public function preview(Request $request)
    {
        $template = $request->template ?? '';
        $sampleDetails = "فاتورة شهر أبريل (رقم 12345) بمبلغ 25.00$\nفاتورة شهر مايو (رقم 12346) بمبلغ 25.00$";
        $sample = [
            'name' => 'أحمد محمد',
            'total_amount' => number_format(50.00, 2),
            'invoice_details_list' => $sampleDetails,
        ];

        $preview = str_replace('{name}', $sample['name'], $template);
        $preview = str_replace('{total_amount}', $sample['total_amount'], $preview);
        $preview = str_replace('{invoice_details_list}', $sample['invoice_details_list'], $preview);

        return response()->json(['preview' => nl2br(e($preview))]);
    }

    public function testSend(Request $request)
    {
        $request->validate([
            'test_phone' => 'required|string',
            'test_message' => 'required|string',
        ]);

        $phone = $request->test_phone;
        $message = $request->test_message;

        $result = $this->whatsapp->send($phone, $message);

        DB::table('whatsapp_message_logs')->insert([
            'client_id' => null,
            'invoice_id' => null,
            'phone' => $phone,
            'message' => $message,
            'status' => $result['success'] ? 'sent' : 'failed',
            'error' => $result['error'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($result['success']) {
            return response()->json(['success' => true, 'message' => trans('clients.whatsapp_test_sent')]);
        }

        return response()->json(['success' => false, 'message' => $result['error'] ?? trans('clients.whatsapp_test_failed')]);
    }

    public function restartService()
    {
        try {
            $sessionId = env('OPENWA_SESSION_ID', '');
            $baseUrl = rtrim(env('OPENWA_API_URL', ''), '/');
            $apiKey = env('OPENWA_API_KEY', '');

            Http::withHeaders(['X-API-Key' => $apiKey])
                ->timeout(10)
                ->post("{$baseUrl}/sessions/{$sessionId}/stop");

            sleep(2);

            $response = Http::withHeaders(['X-API-Key' => $apiKey])
                ->timeout(10)
                ->post("{$baseUrl}/sessions/{$sessionId}/start");

            if ($response->successful()) {
                return response()->json(['success' => true, 'message' => trans('clients.whatsapp_restarted')]);
            }
            return response()->json(['success' => false, 'message' => 'Failed to restart session']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function apiStatus()
    {
        return response()->json($this->whatsapp->status());
    }

    public function apiQR()
    {
        return response()->json($this->whatsapp->getQR());
    }

    public function remindersPreview()
    {
        $enabled = DB::table('app_config')->where('key', 'whatsapp_enabled')->value('value');
        if ($enabled != '1') {
            return response()->json(['error' => 'WhatsApp reminders are disabled']);
        }

        $status = $this->whatsapp->status();
        if (!$status['connected']) {
            return response()->json(['error' => 'WhatsApp is not connected']);
        }

        $template = DB::table('app_config')->where('key', 'whatsapp_message_template')->value('value')
            ?? WhatsAppMessageBuilder::defaultTemplate();

        $today = Carbon::today();
        $targetDates = [];

        $beforeDays = (int) (DB::table('app_config')->where('key', 'whatsapp_remind_before')->value('value') ?? 3);
        if ($beforeDays > 0) {
            $targetDates[] = $today->copy()->addDays($beforeDays)->format('Y-m-d');
        }

        $onDue = DB::table('app_config')->where('key', 'whatsapp_remind_on_due')->value('value');
        if ($onDue == '1') {
            $targetDates[] = $today->format('Y-m-d');
        }

        $afterDays = DB::table('app_config')->where('key', 'whatsapp_remind_after')->value('value') ?? '1,3,7';
        $afterDaysArray = array_map('intval', array_filter(explode(',', $afterDays)));
        foreach ($afterDaysArray as $days) {
            if ($days > 0) {
                $targetDates[] = $today->copy()->subDays($days)->format('Y-m-d');
            }
        }

        $targetDates = array_unique($targetDates);

        $invoices = Invoice::with(['client'])
            ->whereIn('due_date', $targetDates)
            ->whereIn('status', ['unpaid', 'partial'])
            ->whereHas('client', function ($q) {
                $q->whereNotNull('phone')->where('phone', '!=', '');
            })
            ->orderBy('due_date')
            ->get()
            ->groupBy('client_id');

        if ($invoices->isEmpty()) {
            return response()->json(['clients' => [], 'total' => 0, 'grandTotal' => 0]);
        }

        $previewData = [];
        $grandTotal = 0;

        foreach ($invoices as $clientId => $clientInvoices) {
            $client = $clientInvoices->first()->client;
            if (!$client || !$client->phone) continue;
            if (!$this->isValidPhone($client->phone)) continue;

            $alreadyNotifiedToday = Invoice::where('client_id', $clientId)
                ->whereNotNull('last_notified_at')
                ->whereDate('last_notified_at', $today)
                ->exists();

            if ($alreadyNotifiedToday) {
                continue;
            }

            $suspicious = $this->isSuspiciousPhone($client->phone);
            $totalAmount = $clientInvoices->sum('remaining_amount');
            $invoiceDetailsList = WhatsAppMessageBuilder::buildInvoiceDetailsList($clientInvoices);
            $message = WhatsAppMessageBuilder::buildMessage($template, $client->name, $totalAmount, $invoiceDetailsList);
            $phone = preg_replace('/[^0-9]/', '', $client->phone);

            $subscriptionLines = [];
            $serviceLines = [];
            foreach ($clientInvoices as $inv) {
                $dateFormatted = Carbon::parse($inv->due_date)->format('Y-m');
                $amount = number_format($inv->remaining_amount, 2);
                if ($inv->invoice_type === 'service') {
                    $label = !empty($inv->notes) ? preg_replace('/\s+/', ' ', trim($inv->notes)) : 'خدمة';
                    $serviceLines[] = "🔧 {$label} {$dateFormatted} ({$inv->invoice_number}) - {$amount}$";
                } else {
                    if (!empty($inv->notes)) {
                        $noteLabel = preg_replace('/\s+/', ' ', trim($inv->notes));
                        $subscriptionLines[] = "📅 {$noteLabel} {$dateFormatted} ({$inv->invoice_number}) - {$amount}$";
                    } else {
                        $subscriptionLines[] = "📅 {$dateFormatted} ({$inv->invoice_number}) - {$amount}$";
                    }
                }
            }
            $invoiceLines = [];
            if (!empty($subscriptionLines)) {
                $invoiceLines[] = '🌐 فواتير الاشتراك:';
                $invoiceLines = array_merge($invoiceLines, $subscriptionLines);
            }
            if (!empty($serviceLines)) {
                $invoiceLines[] = '🔧 فواتير الخدمات:';
                $invoiceLines = array_merge($invoiceLines, $serviceLines);
            }

            $grandTotal += $totalAmount;

            $previewData[] = [
                'client_id' => $clientId,
                'client_name' => $client->name,
                'phone' => $phone,
                'total_amount' => number_format($totalAmount, 2),
                'invoice_details_list' => $invoiceDetailsList,
                'invoice_lines' => $invoiceLines,
                'message' => $message,
                'invoices' => $clientInvoices->pluck('id')->toArray(),
                'suspicious_phone' => $suspicious,
            ];
        }

        return response()->json([
            'clients' => $previewData,
            'total' => count($previewData),
            'grandTotal' => number_format($grandTotal, 2),
        ]);
    }

    public function sendReminders(Request $request)
    {
        $enabled = DB::table('app_config')->where('key', 'whatsapp_enabled')->value('value');
        if ($enabled != '1') {
            return response()->json(['error' => 'WhatsApp reminders are disabled']);
        }

        $status = $this->whatsapp->status();
        if (!$status['connected']) {
            return response()->json(['error' => 'WhatsApp is not connected']);
        }

        $template = DB::table('app_config')->where('key', 'whatsapp_message_template')->value('value')
            ?? WhatsAppMessageBuilder::defaultTemplate();

        $today = Carbon::today();
        $targetDates = [];

        $beforeDays = (int) (DB::table('app_config')->where('key', 'whatsapp_remind_before')->value('value') ?? 3);
        if ($beforeDays > 0) {
            $targetDates[] = $today->copy()->addDays($beforeDays)->format('Y-m-d');
        }

        $onDue = DB::table('app_config')->where('key', 'whatsapp_remind_on_due')->value('value');
        if ($onDue == '1') {
            $targetDates[] = $today->format('Y-m-d');
        }

        $afterDays = DB::table('app_config')->where('key', 'whatsapp_remind_after')->value('value') ?? '1,3,7';
        $afterDaysArray = array_map('intval', array_filter(explode(',', $afterDays)));
        foreach ($afterDaysArray as $days) {
            if ($days > 0) {
                $targetDates[] = $today->copy()->subDays($days)->format('Y-m-d');
            }
        }

        $targetDates = array_unique($targetDates);

        $invoices = Invoice::with(['client'])
            ->whereIn('due_date', $targetDates)
            ->whereIn('status', ['unpaid', 'partial'])
            ->whereHas('client', function ($q) {
                $q->whereNotNull('phone')->where('phone', '!=', '');
            })
            ->orderBy('due_date')
            ->get()
            ->groupBy('client_id');

        if ($invoices->isEmpty()) {
            return response()->json(['error' => 'No unpaid invoices found']);
        }

        $sentCount = 0;
        $failedCount = 0;
        $results = [];
        $totalClients = 0;

        foreach ($invoices as $clientId => $clientInvoices) {
            $client = $clientInvoices->first()->client;
            if (!$client || !$client->phone) continue;
            if (!$this->isValidPhone($client->phone)) continue;

            $alreadyNotifiedToday = Invoice::where('client_id', $clientId)
                ->whereNotNull('last_notified_at')
                ->whereDate('last_notified_at', $today)
                ->exists();

            if ($alreadyNotifiedToday) {
                continue;
            }

            $totalClients++;
        }

        $currentIndex = 0;
        foreach ($invoices as $clientId => $clientInvoices) {
            $client = $clientInvoices->first()->client;
            if (!$client || !$client->phone) continue;
            if (!$this->isValidPhone($client->phone)) continue;

            $alreadyNotifiedToday = Invoice::where('client_id', $clientId)
                ->whereNotNull('last_notified_at')
                ->whereDate('last_notified_at', $today)
                ->exists();

            if ($alreadyNotifiedToday) {
                continue;
            }

            if ($currentIndex > 0) {
                sleep(10);
            }

            $totalAmount = $clientInvoices->sum('remaining_amount');
            $invoiceDetailsList = WhatsAppMessageBuilder::buildInvoiceDetailsList($clientInvoices);
            $message = WhatsAppMessageBuilder::buildMessage($template, $client->name, $totalAmount, $invoiceDetailsList);
            $phone = preg_replace('/[^0-9]/', '', $client->phone);
            $invoiceIds = $clientInvoices->pluck('id')->toArray();

            $result = $this->whatsapp->send($phone, $message);

            DB::table('whatsapp_message_logs')->insert([
                'client_id' => $clientId,
                'invoice_id' => $clientInvoices->first()->id,
                'invoice_ids' => json_encode($invoiceIds),
                'phone' => $phone,
                'message' => $message,
                'status' => $result['success'] ? 'sent' : 'failed',
                'error' => $result['error'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($result['success']) {
                Invoice::where('client_id', $clientId)
                    ->whereIn('status', ['unpaid', 'partial'])
                    ->update(['last_notified_at' => now()]);
                $sentCount++;
                $results[] = ['client' => $client->name, 'phone' => $phone, 'status' => 'sent'];
            } else {
                $failedCount++;
                $results[] = ['client' => $client->name, 'phone' => $phone, 'status' => 'failed', 'error' => $result['error']];
            }

            $currentIndex++;
        }

        return response()->json([
            'success' => true,
            'sent' => $sentCount,
            'failed' => $failedCount,
            'results' => $results,
        ]);
    }


    public function monthlyPreview(Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');

        if (!$month || !$year) {
            return response()->json(['error' => 'Month and year are required']);
        }

        $enabled = DB::table('app_config')->where('key', 'whatsapp_enabled')->value('value');
        if ($enabled != '1') {
            return response()->json(['error' => 'WhatsApp reminders are disabled']);
        }

        $status = $this->whatsapp->status();
        if (!$status['connected']) {
            return response()->json(['error' => 'WhatsApp is not connected']);
        }

        $template = DB::table('app_config')->where('key', 'whatsapp_message_template')->value('value')
            ?? WhatsAppMessageBuilder::defaultTemplate();

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');

        $invoices = Invoice::with(['client'])
            ->whereBetween('due_date', [$startDate, $endDate])
            ->whereIn('status', ['unpaid', 'partial'])
            ->whereHas('client', function ($q) {
                $q->whereNotNull('phone')->where('phone', '!=', '');
            })
            ->orderBy('due_date')
            ->get()
            ->groupBy('client_id');

        if ($invoices->isEmpty()) {
            return response()->json([
                'month_name' => $this->arabicMonths[(int)$month] ?? Carbon::createFromDate($year, $month, 1)->format('F'),
                'year' => $year,
                'clients' => [],
                'total' => 0,
                'grandTotal' => 0,
            ]);
        }

        $previewData = [];
        $grandTotal = 0;

        foreach ($invoices as $clientId => $clientInvoices) {
            $client = $clientInvoices->first()->client;
            if (!$client || !$client->phone) continue;
            if (!$this->isValidPhone($client->phone)) continue;

            $suspicious = $this->isSuspiciousPhone($client->phone);
            $totalAmount = $clientInvoices->sum('remaining_amount');
            $invoiceDetailsList = WhatsAppMessageBuilder::buildInvoiceDetailsList($clientInvoices);
            $message = WhatsAppMessageBuilder::buildMessage($template, $client->name, $totalAmount, $invoiceDetailsList);
            $phone = preg_replace('/[^0-9]/', '', $client->phone);

            $subscriptionLines = [];
            $serviceLines = [];
            foreach ($clientInvoices as $inv) {
                $dateFormatted = Carbon::parse($inv->due_date)->format('Y-m');
                $amount = number_format($inv->remaining_amount, 2);
                if ($inv->invoice_type === 'service') {
                    $label = !empty($inv->notes) ? preg_replace('/\s+/', ' ', trim($inv->notes)) : 'خدمة';
                    $serviceLines[] = "🔧 {$label} {$dateFormatted} ({$inv->invoice_number}) - {$amount}$";
                } else {
                    if (!empty($inv->notes)) {
                        $noteLabel = preg_replace('/\s+/', ' ', trim($inv->notes));
                        $subscriptionLines[] = "📅 {$noteLabel} {$dateFormatted} ({$inv->invoice_number}) - {$amount}$";
                    } else {
                        $subscriptionLines[] = "📅 {$dateFormatted} ({$inv->invoice_number}) - {$amount}$";
                    }
                }
            }
            $invoiceLines = [];
            if (!empty($subscriptionLines)) {
                $invoiceLines[] = '🌐 فواتير الاشتراك:';
                $invoiceLines = array_merge($invoiceLines, $subscriptionLines);
            }
            if (!empty($serviceLines)) {
                $invoiceLines[] = '🔧 فواتير الخدمات:';
                $invoiceLines = array_merge($invoiceLines, $serviceLines);
            }

            $grandTotal += $totalAmount;

            $previewData[] = [
                'client_id' => $clientId,
                'client_name' => $client->name,
                'phone' => $phone,
                'total_amount' => number_format($totalAmount, 2),
                'invoice_details_list' => $invoiceDetailsList,
                'invoice_lines' => $invoiceLines,
                'message' => $message,
                'invoices' => $clientInvoices->pluck('id')->toArray(),
                'suspicious_phone' => $suspicious,
            ];
        }

        $monthName = $this->arabicMonths[(int)$month] ?? Carbon::createFromDate($year, $month, 1)->format('F');

        $daysWithInvoices = [];
        foreach ($invoices as $clientId => $clientInvoices) {
            foreach ($clientInvoices as $inv) {
                $dayNum = (int) Carbon::parse($inv->due_date)->format('j');
                if (!isset($daysWithInvoices[$dayNum])) {
                    $daysWithInvoices[$dayNum] = ['count' => 0, 'total' => 0];
                }
                $daysWithInvoices[$dayNum]['count']++;
                $daysWithInvoices[$dayNum]['total'] += $inv->remaining_amount;
            }
        }
        foreach ($daysWithInvoices as $day => $data) {
            $daysWithInvoices[$day]['total'] = number_format($data['total'], 2);
        }

        return response()->json([
            'month_name' => $monthName,
            'year' => $year,
            'clients' => $previewData,
            'total' => count($previewData),
            'grandTotal' => number_format($grandTotal, 2),
            'days_with_invoices' => $daysWithInvoices,
        ]);
    }

    public function dailyPreview(Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');
        $day = $request->input('day');

        if (!$month || !$year || !$day) {
            return response()->json(['error' => 'Month, year, and day are required']);
        }

        $enabled = DB::table('app_config')->where('key', 'whatsapp_enabled')->value('value');
        if ($enabled != '1') {
            return response()->json(['error' => 'WhatsApp reminders are disabled']);
        }

        $status = $this->whatsapp->status();
        if (!$status['connected']) {
            return response()->json(['error' => 'WhatsApp is not connected']);
        }

        $template = DB::table('app_config')->where('key', 'whatsapp_message_template')->value('value')
            ?? WhatsAppMessageBuilder::defaultTemplate();

        $targetDate = Carbon::createFromDate($year, $month, $day)->format('Y-m-d');

        $clientIdsOnDate = Invoice::where('due_date', $targetDate)
            ->whereIn('status', ['unpaid', 'partial'])
            ->pluck('client_id')
            ->unique();

        if ($clientIdsOnDate->isEmpty()) {
            $monthName = $this->arabicMonths[(int)$month] ?? Carbon::createFromDate($year, $month, 1)->format('F');
            return response()->json([
                'month_name' => $monthName,
                'year' => $year,
                'day' => $day,
                'date' => $targetDate,
                'clients' => [],
                'total' => 0,
                'grandTotal' => 0,
            ]);
        }

        $invoices = Invoice::with(['client'])
            ->whereIn('client_id', $clientIdsOnDate)
            ->whereIn('status', ['unpaid', 'partial'])
            ->whereHas('client', function ($q) {
                $q->whereNotNull('phone')->where('phone', '!=', '');
            })
            ->orderBy('due_date')
            ->get()
            ->groupBy('client_id');

        $previewData = [];
        $grandTotal = 0;

        foreach ($invoices as $clientId => $clientInvoices) {
            $client = $clientInvoices->first()->client;
            if (!$client || !$client->phone) continue;
            if (!$this->isValidPhone($client->phone)) continue;

            $suspicious = $this->isSuspiciousPhone($client->phone);
            $totalAmount = $clientInvoices->sum('remaining_amount');
            $invoiceDetailsList = WhatsAppMessageBuilder::buildInvoiceDetailsList($clientInvoices);
            $message = WhatsAppMessageBuilder::buildMessage($template, $client->name, $totalAmount, $invoiceDetailsList);
            $phone = preg_replace('/[^0-9]/', '', $client->phone);

            $subscriptionLines = [];
            $serviceLines = [];
            foreach ($clientInvoices as $inv) {
                $dateFormatted = Carbon::parse($inv->due_date)->format('Y-m');
                $amount = number_format($inv->remaining_amount, 2);
                if ($inv->invoice_type === 'service') {
                    $label = !empty($inv->notes) ? preg_replace('/\s+/', ' ', trim($inv->notes)) : 'خدمة';
                    $serviceLines[] = "🔧 {$label} {$dateFormatted} ({$inv->invoice_number}) - {$amount}$";
                } else {
                    if (!empty($inv->notes)) {
                        $noteLabel = preg_replace('/\s+/', ' ', trim($inv->notes));
                        $subscriptionLines[] = "📅 {$noteLabel} {$dateFormatted} ({$inv->invoice_number}) - {$amount}$";
                    } else {
                        $subscriptionLines[] = "📅 {$dateFormatted} ({$inv->invoice_number}) - {$amount}$";
                    }
                }
            }
            $invoiceLines = [];
            if (!empty($subscriptionLines)) {
                $invoiceLines[] = '🌐 فواتير الاشتراك:';
                $invoiceLines = array_merge($invoiceLines, $subscriptionLines);
            }
            if (!empty($serviceLines)) {
                $invoiceLines[] = '🔧 فواتير الخدمات:';
                $invoiceLines = array_merge($invoiceLines, $serviceLines);
            }

            $grandTotal += $totalAmount;

            $previewData[] = [
                'client_id' => $clientId,
                'client_name' => $client->name,
                'phone' => $phone,
                'total_amount' => number_format($totalAmount, 2),
                'invoice_details_list' => $invoiceDetailsList,
                'invoice_lines' => $invoiceLines,
                'message' => $message,
                'invoices' => $clientInvoices->pluck('id')->toArray(),
                'suspicious_phone' => $suspicious,
            ];
        }

        $monthName = $this->arabicMonths[(int)$month] ?? Carbon::createFromDate($year, $month, 1)->format('F');

        return response()->json([
            'month_name' => $monthName,
            'year' => $year,
            'day' => $day,
            'date' => $targetDate,
            'clients' => $previewData,
            'total' => count($previewData),
            'grandTotal' => number_format($grandTotal, 2),
        ]);
    }

    public function sendDaily(Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');
        $day = $request->input('day');

        if (!$month || !$year || !$day) {
            return response()->json(['error' => 'Month, year, and day are required']);
        }

        $enabled = DB::table('app_config')->where('key', 'whatsapp_enabled')->value('value');
        if ($enabled != '1') {
            return response()->json(['error' => 'WhatsApp reminders are disabled']);
        }

        $status = $this->whatsapp->status();
        if (!$status['connected']) {
            return response()->json(['error' => 'WhatsApp is not connected']);
        }

        $template = DB::table('app_config')->where('key', 'whatsapp_message_template')->value('value')
            ?? WhatsAppMessageBuilder::defaultTemplate();

        $targetDate = Carbon::createFromDate($year, $month, $day)->format('Y-m-d');

        $clientIdsOnDate = Invoice::where('due_date', $targetDate)
            ->whereIn('status', ['unpaid', 'partial'])
            ->pluck('client_id')
            ->unique();

        if ($clientIdsOnDate->isEmpty()) {
            return response()->json(['error' => 'No unpaid invoices found for this date']);
        }

        $invoices = Invoice::with(['client'])
            ->whereIn('client_id', $clientIdsOnDate)
            ->whereIn('status', ['unpaid', 'partial'])
            ->whereHas('client', function ($q) {
                $q->whereNotNull('phone')->where('phone', '!=', '');
            })
            ->orderBy('due_date')
            ->get()
            ->groupBy('client_id');

        $sentCount = 0;
        $failedCount = 0;
        $results = [];
        $currentIndex = 0;

        foreach ($invoices as $clientId => $clientInvoices) {
            $client = $clientInvoices->first()->client;
            if (!$client || !$client->phone) continue;
            if (!$this->isValidPhone($client->phone)) continue;

            if ($currentIndex > 0) {
                sleep(10);
            }

            $totalAmount = $clientInvoices->sum('remaining_amount');
            $invoiceDetailsList = WhatsAppMessageBuilder::buildInvoiceDetailsList($clientInvoices);
            $message = WhatsAppMessageBuilder::buildMessage($template, $client->name, $totalAmount, $invoiceDetailsList);
            $phone = preg_replace('/[^0-9]/', '', $client->phone);
            $invoiceIds = $clientInvoices->pluck('id')->toArray();

            $result = $this->whatsapp->send($phone, $message);

            DB::table('whatsapp_message_logs')->insert([
                'client_id' => $clientId,
                'invoice_id' => $clientInvoices->first()->id,
                'invoice_ids' => json_encode($invoiceIds),
                'phone' => $phone,
                'message' => $message,
                'status' => $result['success'] ? 'sent' : 'failed',
                'error' => $result['error'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($result['success']) {
                Invoice::where('client_id', $clientId)
                    ->whereIn('status', ['unpaid', 'partial'])
                    ->update(['last_notified_at' => now()]);
                $sentCount++;
                $results[] = ['client' => $client->name, 'phone' => $phone, 'status' => 'sent'];
            } else {
                $failedCount++;
                $results[] = ['client' => $client->name, 'phone' => $phone, 'status' => 'failed', 'error' => $result['error']];
            }

            $currentIndex++;
        }

        $monthName = $this->arabicMonths[(int)$month] ?? Carbon::createFromDate($year, $month, 1)->format('F');

        return response()->json([
            'success' => true,
            'month_name' => $monthName,
            'year' => $year,
            'day' => $day,
            'date' => $targetDate,
            'sent' => $sentCount,
            'failed' => $failedCount,
            'results' => $results,
        ]);
    }

    public function sendSelected(Request $request)
    {
        $selectedIds = $request->input('clients');
        $type = $request->input('type');
        $month = $request->input('month');
        $year = $request->input('year');
        $day = $request->input('day');

        if (empty($selectedIds) || !is_array($selectedIds)) {
            return response()->json(['error' => 'No clients selected']);
        }

        $enabled = DB::table('app_config')->where('key', 'whatsapp_enabled')->value('value');
        if ($enabled != '1') {
            return response()->json(['error' => 'WhatsApp reminders are disabled']);
        }

        $status = $this->whatsapp->status();
        if (!$status['connected']) {
            return response()->json(['error' => 'WhatsApp is not connected']);
        }

        $template = DB::table('app_config')->where('key', 'whatsapp_message_template')->value('value')
            ?? WhatsAppMessageBuilder::defaultTemplate();

        $query = Invoice::with(['client'])
            ->whereIn('client_id', $selectedIds)
            ->whereIn('status', ['unpaid', 'partial'])
            ->whereHas('client', function ($q) {
                $q->whereNotNull('phone')->where('phone', '!=', '');
            });

        if ($type === 'daily' && $day) {
            $targetDate = Carbon::createFromDate($year, $month, $day)->format('Y-m-d');
            $clientIdsOnDate = Invoice::whereIn('client_id', $selectedIds)
                ->where('due_date', $targetDate)
                ->whereIn('status', ['unpaid', 'partial'])
                ->pluck('client_id')
                ->unique();
            $query->whereIn('client_id', $clientIdsOnDate);
        } elseif ($type === 'monthly') {
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');
            $query->whereBetween('due_date', [$startDate, $endDate]);
        }

        $invoices = $query->orderBy('due_date')->get()->groupBy('client_id');

        if ($invoices->isEmpty()) {
            return response()->json(['error' => 'No unpaid invoices found for selected clients']);
        }

        $sentCount = 0;
        $failedCount = 0;
        $results = [];
        $currentIndex = 0;

        foreach ($invoices as $clientId => $clientInvoices) {
            $client = $clientInvoices->first()->client;
            if (!$client || !$client->phone) continue;
            if (!$this->isValidPhone($client->phone)) continue;

            if ($currentIndex > 0) {
                sleep(10);
            }

            $totalAmount = $clientInvoices->sum('remaining_amount');
            $invoiceDetailsList = WhatsAppMessageBuilder::buildInvoiceDetailsList($clientInvoices);
            $message = WhatsAppMessageBuilder::buildMessage($template, $client->name, $totalAmount, $invoiceDetailsList);
            $phone = preg_replace('/[^0-9]/', '', $client->phone);
            $invoiceIds = $clientInvoices->pluck('id')->toArray();

            $result = $this->whatsapp->send($phone, $message);

            DB::table('whatsapp_message_logs')->insert([
                'client_id' => $clientId,
                'invoice_id' => $clientInvoices->first()->id,
                'invoice_ids' => json_encode($invoiceIds),
                'phone' => $phone,
                'message' => $message,
                'status' => $result['success'] ? 'sent' : 'failed',
                'error' => $result['error'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($result['success']) {
                Invoice::where('client_id', $clientId)
                    ->whereIn('status', ['unpaid', 'partial'])
                    ->update(['last_notified_at' => now()]);
                $sentCount++;
                $results[] = ['client' => $client->name, 'phone' => $phone, 'status' => 'sent'];
            } else {
                $failedCount++;
                $results[] = ['client' => $client->name, 'phone' => $phone, 'status' => 'failed', 'error' => $result['error']];
            }

            $currentIndex++;
        }

        return response()->json([
            'success' => true,
            'sent' => $sentCount,
            'failed' => $failedCount,
            'results' => $results,
        ]);
    }

    public function sendClientReminder($id)
    {
        $enabled = DB::table('app_config')->where('key', 'whatsapp_enabled')->value('value');
        if ($enabled != '1') {
            return response()->json(['success' => false, 'error' => trans('clients.whatsapp_disabled')]);
        }

        $status = $this->whatsapp->status();
        if (!$status['connected']) {
            return response()->json(['success' => false, 'error' => trans('clients.whatsapp_not_connected')]);
        }

        $client = Clients::find($id);
        if (!$client) {
            return response()->json(['success' => false, 'error' => trans('clients.client_not_found')]);
        }

        if (empty($client->phone)) {
            return response()->json(['success' => false, 'error' => trans('clients.whatsapp_no_phone')]);
        }

        if (!$this->isValidPhone($client->phone)) {
            return response()->json(['success' => false, 'error' => trans('clients.whatsapp_invalid_phone')]);
        }

        $unpaidInvoices = Invoice::where('client_id', $id)
            ->whereIn('status', ['unpaid', 'partial'])
            ->orderBy('due_date', 'asc')
            ->get();

        if ($unpaidInvoices->isEmpty()) {
            return response()->json(['success' => false, 'error' => trans('clients.no_unpaid_invoices')]);
        }

        $template = DB::table('app_config')->where('key', 'whatsapp_message_template')->value('value')
            ?? WhatsAppMessageBuilder::defaultTemplate();

        $totalAmount = $unpaidInvoices->sum('remaining_amount');
        $invoiceDetailsList = WhatsAppMessageBuilder::buildInvoiceDetailsList($unpaidInvoices);
        $message = WhatsAppMessageBuilder::buildMessage($template, $client->name, $totalAmount, $invoiceDetailsList);
        $phone = preg_replace('/[^0-9]/', '', $client->phone);
        $invoiceIds = $unpaidInvoices->pluck('id')->toArray();

        $result = $this->whatsapp->send($phone, $message);

        DB::table('whatsapp_message_logs')->insert([
            'client_id' => $id,
            'invoice_id' => $unpaidInvoices->first()->id,
            'invoice_ids' => json_encode($invoiceIds),
            'phone' => $phone,
            'message' => $message,
            'status' => $result['success'] ? 'sent' : 'failed',
            'error' => $result['error'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($result['success']) {
            Invoice::where('client_id', $id)
                ->whereIn('status', ['unpaid', 'partial'])
                ->update(['last_notified_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => trans('clients.whatsapp_reminder_sent'),
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error'] ?? trans('clients.whatsapp_send_failed'),
        ]);
    }
}
