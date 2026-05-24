<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\projects\SaveRequest;
use App\Interfaces\BasicRepositoryInterface;
use App\Models\Admin\AreaSetting;
use App\Models\Clients;
use App\Models\ClientsCompanies;
use App\Models\ClientsProjects;
use App\Services\CompanyService;
use App\Services\ProjectsService;
use App\Traits\ImageProcessing;
use App\Traits\ValidationMessage;
use Illuminate\Http\Request;
use DataTables;
class ProjectController extends Controller
{
    use ImageProcessing;
    use ValidationMessage;

    protected $admin_view = 'dashbord.projects';
    protected $AreasSettingRepository;
    protected $ClientsRepository;
    protected $clientService;
    protected $projectsService;
    protected $CompanyRepository;
    protected $ProjectsRepository;

    public function __construct(BasicRepositoryInterface $basicRepository,ProjectsService $projectsService)
    {
        $this->AreasSettingRepository = createRepository($basicRepository, new AreaSetting());
        $this->ClientsRepository = createRepository($basicRepository, new Clients());
        $this->CompanyRepository   = createRepository($basicRepository, new ClientsCompanies());
        $this->ProjectsRepository   = createRepository($basicRepository, new ClientsProjects());
        $this->projectsService   = $projectsService;


    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $allData = ClientsProjects::select('*');
            return Datatables::of($allData)
                ->editColumn('client', function ($row) {
                    return $row->client->name;
                })
                ->editColumn('company', function ($row) {
                    return $row->company->name;
                })
                ->addColumn('action', function ($row) {
                    return '
    <div class="btn-group btn-group-sm">
        <a href="' . route('admin.project.edit', $row->id) . '" class="btn btn-sm btn-primary" title="' . trans('clients.edit') . '" style="font-size: 16px;">
            <i class="bi bi-pencil-square"></i>
        </a>
        <a onclick="return confirm(\'Are You Sure To Delete?\')"  href="' . route('admin.delete_project', $row->id) . '"  class="btn btn-sm btn-danger" title="' . trans('clients.delete') . '" style="font-size: 16px;" onclick="return confirm(\'' . trans('employees.confirm_delete') . '\')">
            <i class="bi bi-trash3"></i>
        </a>


    </div>
';
                })
                ->rawColumns(['image', 'action'])
                ->make(true);
        }
        return view($this->admin_view . '.index');
    }

    /********************************************/
    public function create()
    {
        $data['project_code'] = $this->ProjectsRepository->getLastFieldValue('project_code');
        $data['clients']      = $this->ClientsRepository->getAll();
        return view($this->admin_view . '.form', $data);
    }

    /********************************************/
    public function store(SaveRequest $request)
    {
        try {
            $this->projectsService->store($request);
            toastr()->addSuccess(trans('forms.success'));
            return redirect()->route('admin.project.index');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /********************************************/
    public function show(string $id)
    {
        //
    }

    /********************************************/
    public function edit(string $id)
    {
        $data['all_data']     = $this->ProjectsRepository->getById($id);
        $data['clients']      = $this->ClientsRepository->getAll();
        return view($this->admin_view . '.edit', $data);
    }

    /********************************************/
    public function update(SaveRequest $request, string $id)
    {
        try {
            $this->projectsService->update($request,$id);
            toastr()->addSuccess(trans('forms.success'));
            return redirect()->route('admin.project.index');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /********************************************/
    public function destroy(string $id)
    {
        try {
            $this->ProjectsRepository->delete($id);
            toastr()->addSuccess(trans('forms.success'));
            return redirect()->route('admin.project.index');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    /********************************************/
    public function get_company($id)
    {
        $data['companies']=$this->CompanyRepository->getBywhere(['client_id'=>$id]);
        //dd($data['companies']);
        return view($this->admin_view . '.get_company_list',$data);
    }
}
