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
}
