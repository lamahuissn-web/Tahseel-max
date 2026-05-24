<?php

namespace App\Imports;

use App\Models\Admin\Subscription;
use App\Models\Clients;
use App\Services\ClientService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ClientsImport implements ToCollection
{
    protected $subscriptionDate;
    protected $successCount = 0;
    protected $failures = [];
    protected $subscriptionCache = [];
    protected $skipRows = 2;
    public function __construct($subscriptionDate, $skipRows = 2)
    {
        $this->subscriptionDate = $subscriptionDate;
        $this->skipRows = $skipRows;
        $this->cacheSubscriptions();
    }

    protected function cacheSubscriptions()
    {
        $subscriptions = Subscription::all();
        foreach ($subscriptions as $subscription) {
            $this->subscriptionCache[$subscription->name] = $subscription;
        }
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {

            if ($index < $this->skipRows) {
                continue;
            }

            DB::beginTransaction();
            try {
                $this->processRow($row, $index);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                // Log::error('Import error for row ' . ($index + 2), [
                //     'error' => $e->getMessage(),
                //     'row_data' => $row->toArray()
                // ]);

                $this->failures[] = [
                    'row' => $index + 2,
                    'errors' => ['System error: ' . $e->getMessage()],
                    'data' => $row->toArray()
                ];
            }
        }
    }

    protected function processRow($row, $index)
    {
        $data = $this->prepareData($row);

        if ($this->isEmptyRow($data)) {
            return;
        }

        if ($this->isDuplicateClient($data)) {
            // Log::info('Duplicate client skipped', ['row' => $index + 2, 'name' => $data['name']]);
            return;
        }

        $validator = $this->validateData($data);

        if ($validator->fails()) {
            $this->failures[] = [
                'row' => $index + 2,
                'errors' => $validator->errors()->all(),
                'data' => $data
            ];
            return;
        }

        $subscription = $this->findSubscription($data['subscription_name']);

        if (!$subscription) {
            $this->failures[] = [
                'row' => $index + 2,
                'errors' => ['Subscription not found: ' . $data['subscription_name']],
                'data' => $data
            ];
            return;
        }

        $clientData = $this->buildClientData($data, $subscription);

        app(ClientService::class)->storeFromImport($clientData);
        $this->successCount++;
    }

    protected function isDuplicateClient($data)
    {
        $subscription = $this->findSubscription($data['subscription_name']);
        if (!$subscription) return false;

        return Clients::where('name', $data['name'])
            ->where('subscription_id', $subscription->id)
            ->where('price', $data['price'])
            ->where('start_date', $data['start_date'])
            ->exists();
    }

    protected function prepareData($row)
    {
        if ($row instanceof Collection) {
            $row = $row->toArray();
        }

        // Log::debug('Processing row data', ['row' => $row]);

        $mappings = [
            'name' => ['الاسم', 'name', 'client_name'],
            'price' => ['الإشتراك الشهري', 'الاشتراك الشهري', 'الإشتراك_الشهري', 'price', 'monthly_subscription'],
            'end_date' => ['تاريخ الانتهاء', 'تاريخ_الانتهاء', 'end_date', 'expiry_date'],
            'notes' => ['ملاحظات', 'notes'],
            'subscription_name' => ['نوع الاشتراك', 'نوع_الاشتراك', 'subscription_type', 'subscription_name'],
            'address' => ['العنوان', 'address', 'address1'],
            'is_active' => ['الحالة', 'status', 'is_active'],
            'client_type' => ['النوع', 'type', 'client_type'],
        ];

        $data = [];

        foreach ($mappings as $key => $columns) {
            $data[$key] = null;
            foreach ($columns as $column) {
                if (isset($row[$column]) && !is_null($row[$column]) && trim($row[$column]) !== '') {
                    $data[$key] = trim($row[$column]);
                    break;
                }
            }
        }

        if ($this->isEmptyRow($data) && is_array($row) && count($row) >= 8) {
            $data = [
                'name' => isset($row[0]) ? trim($row[0]) : null,
                'price' => isset($row[1]) ? trim($row[1]) : null,
                'end_date' => isset($row[2]) ? $row[2] : null,
                'notes' => isset($row[3]) ? trim($row[3]) : null,
                'subscription_name' => isset($row[4]) ? trim($row[4]) : null,
                'address' => isset($row[5]) ? trim($row[5]) : null,
                'is_active' => isset($row[6]) ? trim($row[6]) : 'فعال',
                'client_type' => isset($row[7]) ? trim($row[7]) : 'انترنت',
            ];
        }

        $data['start_date'] = $this->parseDate($data['end_date']);

        // Log::debug('Processed row data', ['processed' => $data]);

        return $data;
    }

    protected function isEmptyRow($data)
    {
        $requiredFields = ['name', 'price', 'subscription_name'];
        foreach ($requiredFields as $field) {
            if (!empty($data[$field])) {
                return false;
            }
        }
        return true;
    }

    protected function validateData($data)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'subscription_name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'is_active' => 'required',
            'client_type' => 'required',
            'notes' => 'nullable|string|max:1000',
        ];

        $messages = [
            'name.required' => 'Client name is required',
            'price.required' => 'Monthly subscription price is required',
            'price.numeric' => 'Price must be a valid number',
            'price.min' => 'Price cannot be negative',
            'start_date.required' => 'End date is required',
            'start_date.date' => 'End date must be a valid date',
            'subscription_name.required' => 'Subscription type is required',
        ];

        return Validator::make($data, $rules, $messages);
    }

    protected function findSubscription($subscriptionName)
    {
        return $this->subscriptionCache[$subscriptionName] ?? null;
    }

    protected function buildClientData($data, $subscription)
    {
        return [
            'name' => $data['name'],
            'price' => (float) $data['price'],
            'subscription_id' => $subscription->id,
            'subscription_date' => $this->subscriptionDate,
            'start_date' => $data['start_date'],
            'box_switch' => $data['address'] ?? '',
            'is_active' => $this->mapActiveStatus($data['is_active']),
            'client_type' => $this->mapClientType($data['client_type']),
            'notes' => $data['notes'] ?? '',
        ];
    }

    protected function parseDate1($date)
    {
        if (!$date) return null;

        try {
            if (is_numeric($date)) {
                $excelEpoch = Carbon::create(1900, 1, 1);
                $parsedDate = $excelEpoch->addDays($date - 2);
                return $parsedDate->format('Y-m-d');
            }

            if (is_string($date)) {
                $formats = ['Y/m/d', 'Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y', 'm-d-Y'];

                foreach ($formats as $format) {
                    try {
                        $parsed = Carbon::createFromFormat($format, $date);
                        if ($parsed && $parsed->format($format) === $date) {
                            return $parsed->format('Y-m-d');
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                }

                try {
                    return Carbon::parse($date)->format('Y-m-d');
                } catch (\Exception $e) {
                    // Log::warning('Carbon parse failed', ['date' => $date, 'error' => $e->getMessage()]);
                }
            }

        } catch (\Exception $e) {
            // Log::warning('Date parsing failed', ['date' => $date, 'error' => $e->getMessage()]);
        }

        return null;
    }

    protected function parseDate($date)
    {
        if (!$date) return null;

        try {
            $parsedDate = null;

            if (is_numeric($date)) {
                $excelEpoch = Carbon::create(1900, 1, 1);
                $parsedDate = $excelEpoch->addDays($date - 2);
            }
            elseif (is_string($date)) {
                $formats = ['Y/m/d', 'Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y', 'm-d-Y'];

                foreach ($formats as $format) {
                    try {
                        $parsed = Carbon::createFromFormat($format, $date);
                        if ($parsed && $parsed->format($format) === $date) {
                            $parsedDate = $parsed;
                            break;
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                }

                if (!$parsedDate) {
                    try {
                        $parsedDate = Carbon::parse($date);
                    } catch (\Exception $e) {
                        // Log::warning('Carbon parse failed', ['date' => $date, 'error' => $e->getMessage()]);
                        return null;
                    }
                }
            }

            if ($parsedDate) {
                if ($parsedDate->month != 1) {
                    $parsedDate = $parsedDate->subMonth();
                }

                return $parsedDate->format('Y-m-d');
            }

        } catch (\Exception $e) {
            // Log::warning('Date parsing failed', ['date' => $date, 'error' => $e->getMessage()]);
        }

        return null;
    }

    protected function mapActiveStatus($status)
    {
        if (is_null($status)) return 1;

        $activeStatuses = ['فعال', 'active', '1', 1, true];
        return in_array($status, $activeStatuses, true) ? 1 : 0;
    }

    protected function mapClientType($type)
    {
        if (is_null($type)) return 'internet';

        $type = strtolower(trim($type));

        $typeMap = [
            'انترنت' => 'internet',
            'internet' => 'internet',
            'ساتلايت' => 'satellite',
            'satellite' => 'satellite',
        ];

        return $typeMap[$type] ?? 'internet';
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }

    public function getFailures()
    {
        return $this->failures;
    }

    public function batchSize(): int
    {
        return 50;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
