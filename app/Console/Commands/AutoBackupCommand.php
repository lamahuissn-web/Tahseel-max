<?php

namespace App\Console\Commands;

use App\Exports\AllDataExport;
use App\Models\AppConfig;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class AutoBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:auto';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'إنشاء نسخة احتياطية تلقائية لجميع البيانات';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // التحقق من تفعيل النسخ الاحتياطي التلقائي
        $autoBackupEnabled = AppConfig::where('key', 'auto_backup_enabled')->value('value');
        
        if ($autoBackupEnabled != '1') {
            $this->info('النسخ الاحتياطي التلقائي معطل.');
            return 0;
        }

        try {
            // الحصول على مسار النسخ الاحتياطي
            $backupPath = AppConfig::where('key', 'backup_path')->value('value');
            
            if (empty($backupPath)) {
                $backupPath = storage_path('app/backups');
            } else {
                $backupPath = trim($backupPath);
            }

            // التأكد من وجود المجلد
            if (!is_dir($backupPath)) {
                mkdir($backupPath, 0755, true);
            }

            // إنشاء اسم الملف
            $fileName = 'auto_backup_' . date('Y-m-d_His') . '.xlsx';
            $filePath = $backupPath . DIRECTORY_SEPARATOR . $fileName;

            // تصدير البيانات
            $this->info('جاري إنشاء النسخة الاحتياطية...');
            Excel::store(new AllDataExport(), $fileName, 'local');
            
            // نسخ الملف إلى المسار المحدد
            $storedPath = storage_path('app/' . $fileName);
            if (file_exists($storedPath)) {
                copy($storedPath, $filePath);
                unlink($storedPath); // حذف الملف المؤقت
                
                $this->info("تم إنشاء النسخة الاحتياطية بنجاح في: {$filePath}");
                
                // حذف الملفات القديمة (احتفظ بآخر 10 ملفات)
                $this->cleanOldBackups($backupPath);
                
                return 0;
            } else {
                $this->error('فشل إنشاء النسخة الاحتياطية.');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('حدث خطأ أثناء إنشاء النسخة الاحتياطية: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * حذف الملفات القديمة (احتفظ بآخر 10 ملفات)
     */
    private function cleanOldBackups($backupPath)
    {
        $files = glob($backupPath . DIRECTORY_SEPARATOR . 'auto_backup_*.xlsx');
        
        if (count($files) > 10) {
            // ترتيب الملفات حسب تاريخ التعديل
            usort($files, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            
            // حذف الملفات القديمة
            $filesToDelete = array_slice($files, 0, count($files) - 10);
            foreach ($filesToDelete as $file) {
                unlink($file);
                $this->info("تم حذف الملف القديم: " . basename($file));
            }
        }
    }
}

