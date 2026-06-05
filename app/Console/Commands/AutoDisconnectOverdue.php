<?php

namespace App\Console\Commands;

use App\Models\Clients;
use App\Services\Radius\RadiusService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoDisconnectOverdue extends Command
{
    protected $signature = "radius:auto-disconnect
        {--dry-run : عرض النتائج بدون تطبيق}
        {--days=30 : عدد أيام التأخير قبل القطع}";

    protected $description = "قطع المستخدمين المتأخرين عن الدفع تلقائياً";

    public function handle(RadiusService $radius)
    {
        $days = (int) $this->option("days");
        $dryRun = $this->option("dry-run");
        $cutoffDate = Carbon::now()->subDays($days);

        $this->info("🔍 البحث عن المستخدمين المتأخرين أكثر من {$days} يوم...");

        // Find clients with overdue invoices (unpaid/partial with due_date older than cutoff)
        $overdueClients = Clients::query()
            ->where("is_active", "1")
            ->whereNotNull("sas_username")
            ->whereHas("invoices", function ($q) use ($cutoffDate) {
                $q->whereIn("status", ["unpaid", "partial"])
                  ->where("remaining_amount", ">", 0)
                  ->where("due_date", "<", $cutoffDate->format("Y-m-d"));
            })
            ->get();

        if ($overdueClients->isEmpty()) {
            $this->info("✅ لا يوجد مستخدمين متأخرين!");
            return Command::SUCCESS;
        }

        $this->info("📋 وجد {$overdueClients->count()} مستخدمين متأخرين:");
        $disconnected = 0;
        $errors = 0;

        foreach ($overdueClients as $client) {
            $username = $client->sas_username;
            $latestInvoice = $client->invoices()
                ->whereIn("status", ["unpaid", "partial"])
                ->where("remaining_amount", ">", 0)
                ->orderBy("due_date", "desc")
                ->first();

            $dueDays = $latestInvoice ? Carbon::parse($latestInvoice->due_date)->diffInDays(now()) : 0;

            $this->line("  - {$client->name} ({$username}) - متأخر {$dueDays} يوم");

            if ($dryRun) {
                continue;
            }

            try {
                // 1. Cut off via CoA (if online)
                $coaResult = $radius->coaDisconnect($username);
                if ($coaResult["success"]) {
                    $this->line("    ✓ قطع: {$coaResult["message"]}");
                }

                // 2. Disable RADIUS login
                DB::connection("radius")->table("radcheck")->updateOrInsert(
                    ["username" => $username, "attribute" => "Auth-Type"],
                    ["op" => ":=", "value" => "Reject"]
                );

                $this->line("    ✓ RADIUS معطل");
                $disconnected++;
            } catch (\Exception $e) {
                $this->error("    ✗ خطأ: {$e->getMessage()}");
                Log::error("AutoDisconnect: Failed for {$username}: {$e->getMessage()}");
                $errors++;
            }
        }

        $this->newLine();

        if ($dryRun) {
            $this->info("🏁 تشغيل تجريبي — لم يتم تطبيق أي تغيير");
            $this->info("   استعمل php artisan radius:auto-disconnect بدون --dry-run للتطبيق");
        } else {
            $this->info("🏁 تم قطع {$disconnected} مستخدم بنجاح");
            if ($errors > 0) {
                $this->warn("⚠️ {$errors} مستخدم فشل قطعهم");
            }
        }

        return Command::SUCCESS;
    }
}
