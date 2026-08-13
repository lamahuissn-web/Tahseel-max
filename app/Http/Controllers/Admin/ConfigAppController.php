<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AppConfigRequest;
use App\Interfaces\BasicRepositoryInterface;
use App\Models\Admin\AreaSetting;
use App\Models\Admin\Branch;
use App\Models\Admin\SarfBand;
use App\Models\AppConfig;
use App\Traits\ImageProcessing;
use App\Traits\ValidationMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ConfigAppController extends Controller
{
    use ImageProcessing;
    use ValidationMessage;

    /***********************************************************/
    protected $admin_view = 'dashbord.config_app';
    protected $AppConfigRepository;
    public function __construct(BasicRepositoryInterface $basicRepository)
    {
        $this->AppConfigRepository = createRepository($basicRepository, new AppConfig());
    }
    /***********************************************************/
    public function index()
    {
        $configs=$this->AppConfigRepository->getAll();
        $data['all_data'] = $configs->pluck('value', 'key')->toArray();
        return view($this->admin_view.'.form',$data);
    }
    /**********************************************************/
    public function store(AppConfigRequest $request)
    {
        try {

            $data = $request->except('_token', '_method');

            // معالجة checkbox - إذا لم يتم إرساله، قم بتعيينه إلى 0
            if (!isset($data['auto_backup_enabled'])) {
                $data['auto_backup_enabled'] = '0';
            } else {
                $data['auto_backup_enabled'] = '1';
            }

            foreach ($data as $key => $value) {
                $config = $this->AppConfigRepository->getBywhere(['key'=>$key]);
               // dd($config);
                if (!$config->isEmpty()) {
                    $this->AppConfigRepository->update($config[0]->id, ['value' => $value, 'updated_by' => auth()->user()->id]);
                } else {
                    $this->AppConfigRepository->create([
                        'key' => $key,
                        'value' => $value,
                        'created_by' => auth()->user()->id
                    ]);
                }
            }
            toastr()->addSuccess(trans('forms.success'));
            return redirect()->route('admin.app_config');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function downloadDatabaseBackup(): BinaryFileResponse
    {
        $dbConfig = config('database.connections.mysql');
        $directory = storage_path('app');
        $baseName = 'tahseel-backup-' . now()->format('Y-m-d_H-i-s');
        $sqlFile = $directory . DIRECTORY_SEPARATOR . $baseName . '.sql';
        $downloadFile = $sqlFile . '.gz';
        $credentialsFile = tempnam($directory, '.telegram-mysqldump-');

        if ($credentialsFile === false) {
            abort(500, 'Could not prepare database backup.');
        }

        try {
            chmod($credentialsFile, 0600);
            file_put_contents($credentialsFile, sprintf(
                "[client]\nhost=%s\nport=%s\nuser=%s\npassword=%s\n",
                $dbConfig['host'] ?? '127.0.0.1',
                $dbConfig['port'] ?? 3306,
                $dbConfig['username'] ?? 'root',
                $dbConfig['password'] ?? ''
            ));

            $dumpCommand = sprintf(
                'mysqldump --defaults-extra-file=%s --single-transaction --routines --triggers --events --hex-blob --default-character-set=utf8mb4 %s > %s 2>/dev/null',
                escapeshellarg($credentialsFile),
                escapeshellarg($dbConfig['database'] ?? 'tahseel'),
                escapeshellarg($sqlFile)
            );

            exec($dumpCommand, $output, $exitCode);
            if ($exitCode !== 0 || !is_file($sqlFile) || filesize($sqlFile) === 0) {
                throw new \RuntimeException('Database dump failed.');
            }

            $compressed = gzopen($downloadFile, 'wb9');
            $source = fopen($sqlFile, 'rb');
            if ($compressed === false || $source === false) {
                if (is_resource($compressed)) gzclose($compressed);
                if (is_resource($source)) fclose($source);
                throw new \RuntimeException('Backup compression failed.');
            }

            while (!feof($source)) {
                gzwrite($compressed, fread($source, 1024 * 1024));
            }
            fclose($source);
            gzclose($compressed);
            @unlink($sqlFile);

            if (!is_file($downloadFile) || filesize($downloadFile) === 0) {
                throw new \RuntimeException('Compressed backup is empty.');
            }

            return response()->download(
                $downloadFile,
                basename($downloadFile),
                ['Content-Type' => 'application/gzip']
            )->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            @unlink($sqlFile);
            @unlink($downloadFile);
            Log::error('Manual database backup download failed: ' . $e->getMessage());
            abort(500, 'Could not create database backup.');
        } finally {
            @unlink($credentialsFile);
        }
    }
}
