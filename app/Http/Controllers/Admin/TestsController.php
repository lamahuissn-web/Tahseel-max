<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\tests\SaveRequest;
use App\Interfaces\BasicRepositoryInterface;
use App\Models\Admin\Employee;
use App\Models\Admin\Masrofat;
use App\Models\Admin\Test;
use App\Models\Clients;
use App\Models\ClientsCompanies;
use App\Models\ClientsProjects;
use App\Services\TestsService;
use App\Traits\ImageProcessing;
use App\Traits\ValidationMessage;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TestsController extends Controller
{
    use ImageProcessing;
    use ValidationMessage;

    protected $clientsRepository;
    protected $testsRepository;
    protected $testsService;
    protected $masrofatRepository;
    protected $companyRepository;
    protected $projectsRepository;

    public function __construct(BasicRepositoryInterface $basicRepository, TestsService $testsService)
    {
        $this->projectsRepository   = createRepository($basicRepository, new ClientsProjects());
        $this->clientsRepository = createRepository($basicRepository, new Clients());
        $this->companyRepository   = createRepository($basicRepository, new ClientsCompanies());
        $this->testsRepository   = createRepository($basicRepository, new Test());
        $this->testsService   = $testsService;


    }

    public function index(Request $request)
    {
        // $allData = Test::with(['company', 'client', 'project', 'user'])->get();
        // dd($allData);
        if ($request->ajax()) {
            $allData = Test::with(['company', 'client', 'project', 'user'])->get();
            return DataTables::of($allData)
                ->editColumn('client', function ($row) {
                    return $row->client ? $row->client->name : 'N/A';
                })
                ->editColumn('company', function ($row) {
                    return $row->company ? $row->company->name : 'N/A';
                })
                ->editColumn('project', function ($row) {
                    return $row->project ? $row->project->project_name : 'N/A';
                })
                ->editColumn('test_code', function ($row) {
                    return $row->test_code;
                })
                ->editColumn('talab_title', function ($row) {
                    return $row->talab_title;
                })
                ->editColumn('talab_image', function ($row) {
                    if ($row->talab_image) {
                        $imagePath = asset('images/' . $row->talab_image);
                        return '<img src="' . $imagePath . '" alt="Employee Image" class="img-thumbnail" style="width: 50px; height: 50px;">';
                    } else{
                        return 'N\A';
                    }
                })
                ->editColumn('talab_date', function ($row) {
                    return $row->talab_date;
                })
                ->editColumn('talab_end_date', function ($row) {
                    return $row->talab_end_date;
                })
                ->editColumn('created_by', function ($row) {
                    return $row->user ? $row->user->name : 'N/A';
                })
                ->addColumn('action', function ($row) {
                    return '
                        <div class="btn-group btn-group-sm">
                            <a href="' . route('admin.test.edit', $row->id) . '" class="btn btn-sm btn-primary" title="' . trans('tests.edit') . '" style="font-size: 16px;">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <a onclick="return confirm(\'Are You Sure To Delete?\')"  href="' . route('admin.delete_test', $row->id) . '"  class="btn btn-sm btn-danger" title="' . trans('tests.delete') . '" style="font-size: 16px;" onclick="return confirm(\'' . trans('masrofat.confirm_delete') . '\')">
                                <i class="bi bi-trash3"></i>
                            </a>
                        </div>
                    ';
                })
                ->rawColumns(['action', 'talab_image'])
                ->make(true);
        }
        return view('dashbord.tests.index');
    }

    /********************************************/
    public function create()
    {
        $data['test_code'] = $this->testsRepository->getLastFieldValue('test_code');
        $data['clients']      = $this->clientsRepository->getAll();
        $data['companies']      = $this->companyRepository->getAll();
        $data['projects'] = $this->projectsRepository->getAll();
        // dd($data);
        return view('dashbord.tests.form', $data);
    }

    /********************************************/
    public function store(SaveRequest $request)
    {
        try {
            // dd($request->all());
            $this->testsService->store($request);
            toastr()->addSuccess(trans('forms.success'));
            return redirect()->route('admin.test.index');
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
        $data['all_data']     = $this->testsRepository->getById($id);
        $data['clients']      = $this->clientsRepository->getAll();
        $data['companies']    = $this->companyRepository->getAll();
        $data['projects']     = $this->projectsRepository->getAll();
        return view('dashbord.tests.edit', $data);
    }

    /********************************************/
    public function update(SaveRequest $request, string $id)
    {
        try {
            // dd($request->all());
            $this->testsService->update($request,$id);
            toastr()->addSuccess(trans('forms.success'));
            return redirect()->route('admin.test.index');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /********************************************/
    public function destroy(string $id)
    {
        try {
            $test = $this->testsRepository->getById($id);
            if ($test->talab_image) {
                $oldImagePath = public_path('images/' . $test->talab_image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            $this->testsRepository->delete($id);
            toastr()->addSuccess(trans('forms.success'));
            return redirect()->route('admin.test.index');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    /********************************************/

}
