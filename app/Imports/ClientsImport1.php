<?php

namespace App\Imports;

use App\Models\Admin\Subscription;
use App\Services\ClientService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;

class ClientsImport1 implements ToCollection
{
    protected $subscriptionDate;
    protected $successCount = 0;
    protected $failures = [];

    public function __construct($subscriptionDate)
    {
        $this->subscriptionDate = $subscriptionDate;
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                $data = $this->prepareData($row);

                // Validate the data
                $validator = Validator::make($data, [
                    'name' => 'required|string|max:255',
                    'price' => 'required|numeric',
                    'start_date' => 'required|date',
                    'subscription_name' => 'required|string',
                    'box_switch' => 'nullable|string|max:255',
                    'is_active' => 'required|in:فعال,غير فعال,1,0',
                    'client_type' => 'required|in:satellite,internet',
                ]);

                if ($validator->fails()) {
                    $this->failures[] = [
                        'row' => $index + 2,
                        'errors' => $validator->errors()->all(),
                        'data' => $data
                    ];
                    continue;
                }

                $subscription = Subscription::where('name', $data['subscription_name'])->first();

                if (!$subscription) {
                    $this->failures[] = [
                        'row' => $index + 2,
                        'errors' => ['Subscription not found: ' . $data['subscription_name']],
                        'data' => $data
                    ];
                    continue;
                }

                $clientData = [
                    'name' => $data['name'],
                    'price' => $data['price'],
                    'subscription_id' => $subscription->id,
                    'subscription_date' => $this->subscriptionDate,
                    'start_date' => $data['start_date'],
                    'box_switch' => $data['box_switch'] ?? '',
                    'is_active' => $this->mapActiveStatus($data['is_active']),
                    'client_type' => $this->mapClientType($data['client_type']),
                    'notes' => $data['notes'] ?? '',
                ];

                app(ClientService::class)->storeFromImport($clientData);

                $this->successCount++;
            }
            DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->failures[] = [
                    'row' => $index + 2,
                    'errors' => [$e->getMessage()],
                    'data' => $row->toArray()
                ];
            }
    }

    protected function prepareData($row)
    {
        return [
            'name' => $row['الاسم'] ?? null,
            'price' => $row['الإشتراك_الشهري'] ?? null,
            'start_date' => $this->parseArabicDate($row['تاريخ_الانتهاء'] ?? null),
            'notes' => $row['ملاحظات'] ?? null,
            'subscription_name' => $row['نوع_الاشتراك'] ?? null,
            'box_switch' => $row['العنوان'] ?? null,
            'is_active' => $row['الحالة'] ?? 'فعال',
            'client_type' => $row['النوع'] ?? 'انترنت',
        ];
    }

    protected function parseArabicDate($date)
    {
        if (!$date) return null;

        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function mapActiveStatus($status)
    {
        return in_array($status, ['فعال', '1']) ? 1 : 0;
    }

    protected function mapClientType($type)
    {
        return $type === 'انترنت' ? 'internet' : ($type === 'ساتلايت' ? 'satellite' : $type);
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }

    public function getFailures()
    {
        return $this->failures;
    }
}
