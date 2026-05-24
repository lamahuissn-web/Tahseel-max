<?php

namespace App\Providers;

use App\Console\Commands\AddNewInvoices;
use App\Models\Admin;
use App\Models\Admin\Invoice;
use App\Models\Clients;
use App\Notifications\InvoiceReminderNotification;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // $this->commands([
        //     AddNewInvoices::class,
        // ]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFour();
        // Lang::handleMissingKeysUsing(function ($key) {
        //     if (strpos($key, 'flasher') !== false) {
        //         return $key;
        //     }

        //     // Add custom logic to handle missing keys
        //     // For example, you can log the missing key
        //     Log::info("Missing translation key: $key");

        //     // You can also add the missing key to the language file dynamically
        //     $keyParts = explode('.', $key);
        //     if (count($keyParts) >= 2) {
        //         $group = $keyParts[0];
        //         $item = $keyParts[1];

        //         $langPath = base_path("lang/" . app()->getLocale() . "/$group.php");

        //         if (File::exists($langPath)) {
        //             $translations = File::getRequire($langPath);
        //             $translations[$item] = $item;
        //             File::put($langPath, '<?php return ' . var_export($translations, true) . ';');
        //         } else {
        //             File::put($langPath, '<?php return ' . var_export([$item => $item], true) . ';');
        //         }
        //     }

        //     // Return the key as the translation (optional)
        //     return $key;
        // });


        // $this->checkAndGenerateInvoices();
        $this->sendOverdueInvoiceNotifications();
    }

    private function checkAndGenerateInvoices()
    {
        $currentYearMonth = Carbon::now()->format('Y-m');
        $invoiceGenerationKey = 'monthly_invoices_generated_' . $currentYearMonth;

        $invoicesGenerated = Cache::get($invoiceGenerationKey, false);

        if (!$invoicesGenerated && Carbon::now()->day <= 5) {
            try {

                $this->generateMonthlyInvoices();

                Cache::put($invoiceGenerationKey, true, now()->addDays(35));

                Log::info('تم إنشاء الفواتير الشهرية بنجاح لشهر: ' . $currentYearMonth);
            } catch (\Exception $e) {
                Log::error('حدث خطأ أثناء إنشاء الفواتير الشهرية: ' . $e->getMessage());
            }
        }
    }

    private function generateMonthlyInvoices()
    {
        $clients = Clients::whereNull('deleted_at')->get();
        $invoicesCreated = 0;

        foreach ($clients as $client) {
            $currentMonth = Carbon::now()->startOfMonth();

            $existingInvoice = Invoice::where('client_id', $client->id)
                ->whereYear('created_at', $currentMonth->year)
                ->whereMonth('created_at', $currentMonth->month)
                ->exists();

            if (!$existingInvoice) {
                $lastInvoice = Invoice::where('client_id', $client->id)->latest()->first();

                if ($lastInvoice) {
                    $dueDate = Carbon::parse($lastInvoice->due_date)->addMonth();
                } else {
                    $dueDate = Carbon::parse($client->start_date ?? $currentMonth)->addMonth();
                }

                $invoiceNumber = $this->getNextInvoiceNumber();

                Invoice::create([
                    'client_id' => $client->id,
                    'invoice_number' => $invoiceNumber,
                    'amount' => $client->price,
                    'remaining_amount' => $client->price,
                    'subscription_id' => $client->subscription_id,
                    'enshaa_date' => $currentMonth,
                    'due_date' => $dueDate,
                    'status' => 'unpaid',
                ]);

                $invoicesCreated++;
            }
        }

        return $invoicesCreated;
    }

    private function getNextInvoiceNumber()
    {
        $lastInvoice = Invoice::orderBy('id', 'desc')->first();

        if ($lastInvoice) {
            if (is_numeric($lastInvoice->invoice_number)) {
                return (int)$lastInvoice->invoice_number + 1;
            }
        }

        return (int)1;
    }

    private function sendOverdueInvoiceNotifications()
    {
        $today = Carbon::today();
        // dd($today);
        if (Carbon::parse(Cache::get('last_invoice_notification_date')) == $today) {
            return;
        }

        $admins = Admin::where('status', '1')
                ->whereNull('deleted_at')
                ->whereHas('roles', function($query) {
                    $query->whereIn('id', [1, 7]);
                })
                ->get();
                
        $overdueInvoices = Invoice::where('status', 'unpaid')
            ->where(function ($query) use ($today) {
                $query->whereNull('last_notified_at')
                    ->orWhereRaw("COALESCE(DATE_FORMAT(last_notified_at, '%Y-%m-%d'), '2000-01-01') < due_date");
            })
            ->get();
        // dd($admins, $overdueInvoices);
        foreach ($overdueInvoices as $invoice) {
            if ($today->toDateString() >= Carbon::parse($invoice->due_date)->toDateString()) {
                // dd('ddd');

                $notificationMessage = sprintf(
                    'فاتورة متأخرة: #%s | العميل: %s | المبلغ: %s %s',
                    $invoice->invoice_number,
                    $invoice->client->name ?? 'غير معروف',
                    number_format($invoice->amount, 2),
                    get_app_config_data('currency') ?? 'جنيه'
                );

                foreach ($admins as $admin) {
                    $admin->notify(new InvoiceReminderNotification($invoice));
                }

                if (!empty($admins)) {
                    sendOneSignalNotification1(
                        $admins,
                        $notificationMessage,
                        [
                            'invoice_id' => $invoice->id,
                            'type' => 'overdue_invoice',
                            'amount' => $invoice->amount,
                            'due_date' => $invoice->due_date,
                            'client' => $invoice->client->name ?? 'غير معروف',
                            'days_overdue' => $today->diffInDays($invoice->due_date)
                        ],
                        null
                    );
                }

                $invoice->updateQuietly(['last_notified_at' => $today]);
            }
        }

        Cache::put('last_invoice_notification_date', $today, now()->endOfDay());
    }


}
