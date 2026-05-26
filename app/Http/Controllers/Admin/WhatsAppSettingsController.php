<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class WhatsAppSettingsController extends Controller
{
    protected $whatsapp;

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
                ?? "مرحباً {name}،\nنود تذكيرك بوجود مبالغ مستحقة غير مدفوعة لحسابك بإجمالي {total_amount}$.\n\nتفاصيل الفواتير المستحقة:\n{invoice_details_list}\n\nيرجى التكرم بتسوية الرصيد المستحق في أقرب وقت ممكن. إذا كنت قد سددت هذا المبلغ مؤخراً، يرجى تجاهل هذه الرسالة. شكراً لتفهمك.",
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
            exec('supervisorctl restart whatsapp-service 2>&1', $output, $returnCode);
            if ($returnCode === 0) {
                return response()->json(['success' => true, 'message' => trans('clients.whatsapp_restarted')]);
            }
            return response()->json(['success' => false, 'message' => implode("\n", $output)]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
