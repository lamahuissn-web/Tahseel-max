<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\masrofat\SaveRequest;
use App\Interfaces\BasicRepositoryInterface;
use App\Models\Admin\Employee;
use App\Models\Admin\Masrofat;
use App\Models\Admin\SarfBand;
use App\Services\MasrofatService;
use App\Traits\ImageProcessing;
use App\Traits\ValidationMessage;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class MasrofatController extends Controller
{
    use ImageProcessing;
    use ValidationMessage;

    protected $bandsRepository;
    protected $masrofatService;
    protected $employeesRepository;
    protected $masrofatRepository;

    public function __construct(BasicRepositoryInterface $basicRepository, MasrofatService $masrofatService)
    {
        $this->middleware('can:list_masrofat')->only('index');
        $this->middleware('can:create_masrofat')->only('create', 'store');
        $this->middleware('can:update_masrofat')->only('edit', 'update');
        $this->middleware('can:delete_masrofat')->only('destroy');

        $this->bandsRepository = createRepository($basicRepository, new SarfBand());
        $this->employeesRepository   = createRepository($basicRepository, new Employee());
        $this->masrofatRepository   = createRepository($basicRepository, new Masrofat());
        $this->masrofatService   = $masrofatService;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            // $allData = Masrofat::with(['employee', 'sarf_band', 'user'])->orderBy('created_at', 'desc')->get();
            $query = Masrofat::with(['employee', 'sarf_band', 'user'])
                ->orderBy('created_at', 'desc');

            if ($request->has('band_id') && $request->band_id != '') {
                $query->where('band_id', $request->band_id);
            }

            if ($request->has('from_date') && $request->from_date != '') {
                $query->whereDate('created_at', '>=', $request->from_date);
            }

            if ($request->has('to_date') && $request->to_date != '') {
                $query->whereDate('created_at', '<=', $request->to_date);
            }

            if ($request->has('value') && $request->value != '') {
                $query->where('value', 'like', '%' . $request->value . '%');
            }

            if ($request->has('notes') && $request->notes != '') {
                $query->where('notes', 'like', '%' . $request->notes . '%');
            }

            if ($request->has('created_by') && $request->created_by != '') {
                $query->whereHas('user', function($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->created_by . '%');
                });
            }

            return DataTables::of($query)
                ->editColumn('emp_id', function ($row) {
                    return $row->employee ? $row->employee->first_name . ' ' . $row->employee->last_name : 'N/A';
                })
                ->editColumn('band_id', function ($row) {
                    return $row->sarf_band ? $row->sarf_band->title : 'N/A';
                })
                ->editColumn('value', function ($row) {
                    return $row->value;
                })
                ->editColumn('notes', function ($row) {
                    return $row->notes ?? 'N/A';
                })
                ->editColumn('created_by', function ($row) {
                    return $row->user ? $row->user->name : 'N/A';
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at->format('Y-m-d H:i:s');
                })
                // ->addColumn('action', function ($row) {
                //     $actionButtons = '<div class="btn-group btn-group-sm">';

                //     if (auth()->user()->can('update_masrofat')) {
                //         $actionButtons .=  '<a href="' . route('admin.masrofat.edit', $row->id) . '" class="btn btn-sm btn-primary" title="' . trans('masrofat.edit') . '" style="font-size: 16px;">
                //                             <i class="bi bi-pencil-square"></i>
                //                         </a>';
                //     }

                //     if (auth()->user()->can('delete_masrofat')) {
                //         $actionButtons .= '<a onclick="return confirm(\'Are You Sure To Delete?\')"  href="' . route('admin.delete_masrofat', $row->id) . '"  class="btn btn-sm btn-danger" title="' . trans('masrofat.delete') . '" style="font-size: 16px;" onclick="return confirm(\'' . trans('masrofat.confirm_delete') . '\')">
                //                         <i class="bi bi-trash3"></i>
                //                     </a>';
                //     }


                //     $actionButtons .= '</div>';

                //     return $actionButtons;
                // })
                ->rawColumns(['action'])
                ->make(true);
        }
        $data['bands'] = SarfBand::all();
        return view('dashbord.masrofat.index', $data);
    }

    /********************************************/
    public function create()
    {
        $data['employees']      = $this->employeesRepository->getAll();
        $data['bands']      = $this->bandsRepository->getAll();
        return view('dashbord.masrofat.form', $data);
    }

    /********************************************/
    public function store(SaveRequest $request)
    {
        try {
            // dd($request->all());
            $masrofat = $this->masrofatService->store($request);
            $message = sprintf(
                'تم إضافة مصروف جديد: %s - المبلغ: %s %s - تم الإضافة بواسطة %s',
                $masrofat->description,
                number_format($masrofat->amount, 2),
                get_app_config_data('currency'),
                auth()->user()->name
            );

            log_helper(
                'massrofat_created',
                $message,
                [
                    'model' => $masrofat
                ]
            );
            toastr()->addSuccess(trans('forms.success'));
            return redirect()->route('admin.masrofat.index');
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
        $data['all_data']     = $this->masrofatRepository->getById($id);
        $data['employees']      = $this->employeesRepository->getAll();
        $data['bands']      = $this->bandsRepository->getAll();
        return view('dashbord.masrofat.edit', $data);
    }

    /********************************************/
    public function update(SaveRequest $request, string $id)
    {
        try {
            $this->masrofatService->update($request, $id);
            toastr()->addSuccess(trans('forms.success'));
            return redirect()->route('admin.masrofat.index');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /********************************************/
    public function destroy(string $id)
    {
        try {
            $this->masrofatRepository->delete($id);
            toastr()->addSuccess(trans('forms.success'));
            return redirect()->route('admin.masrofat.index');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    /********************************************/
}
