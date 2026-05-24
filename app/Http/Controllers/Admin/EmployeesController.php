<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Employee\EmployeeStoreRequest;
use App\Http\Requests\Admin\Employees\AddEmployeeRequest;
use App\Interfaces\BasicRepositoryInterface;
use App\Models\Admin;
use App\Models\Admin\Account;
use App\Models\Admin\AreaSetting;
use App\Models\Admin\Branch;
use App\Models\Admin\Employee;
use App\Models\Admin\EmployeeFiles;
use App\Models\Admin\FinancialTransaction;
use App\Models\Admin\Masrofat;
use App\Models\Admin\Revenue;
use App\Models\Admin\SarfBand;
use App\Traits\ImageProcessing;
use App\Traits\ValidationMessage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\Storage;

class EmployeesController extends Controller
{
    use ImageProcessing;
    use ValidationMessage;

    /*---------------------------------------------------*/

    protected $GeneralSettingRepository;
    protected $UsersRepository;
    protected $BranchRepository;
    protected $AreasSettingRepository;
    protected $EmployeeRepository;
    protected $EmployeeFilesRepository;
    protected $MasrofatRepository;
    protected $RevenuesRepository;
    protected $bandsRepository;
    public function __construct(BasicRepositoryInterface $basicRepository)
    {
        $this->middleware('can:view_employees')->only('index', 'get_ajax_employee');
        $this->middleware('can:add_employee')->only('add_employee', 'save_employee');
        $this->middleware('can:edit_employee')->only('edit_employee', 'update_employee');

        $this->middleware('can:view_employee_files')->only('employee_files');
        $this->middleware('can:add_employee_files')->only('employee_add_files');
        $this->middleware('can:read_employee_file')->only('read_file');
        $this->middleware('can:download_employee_file')->only('download_file');
        $this->middleware('can:delete_employee_file')->only('delete_file');

        $this->middleware('can:view_employee_details')->only('employee_details');
        $this->middleware('can:view_employee_masrofat')->only('employee_masrofat');
        $this->middleware('can:add_employee_masrofat')->only('employee_add_masrofat');
        $this->middleware('can:delete_employee_masrofat')->only('employee_delete_masrofat');
        $this->middleware('can:view_employee_revenues')->only('employee_revenues');

        $this->AreasSettingRepository   = createRepository($basicRepository, new AreaSetting());
        $this->BranchRepository         = createRepository($basicRepository, new Branch());
        $this->EmployeeRepository       = createRepository($basicRepository, new Employee());
        $this->EmployeeFilesRepository  = createRepository($basicRepository, new EmployeeFiles());
        $this->MasrofatRepository  = createRepository($basicRepository, new Masrofat());
        $this->RevenuesRepository  = createRepository($basicRepository, new Revenue());
        $this->bandsRepository = createRepository($basicRepository, new SarfBand());
    }

    /************************************************************/
    public function index()
    {
        // $data = $this->EmployeeRepository->getWithRelations(['area', 'governate', 'branch']);
        // dd($data);
        // $data = $this->EmployeeRepository->getAll();
        // dd($data);
        return view('dashbord.admin.employees.employee_data');
    }
    /***********************************************************/
    public function get_ajax_employee(Request $request)
    {

        if ($request->ajax()) {
            $data = $this->EmployeeRepository->getAll();
            $counter = 0;
            return DataTables::of($data)
                ->addColumn('id', function () use (&$counter) {
                    $counter++;
                    return $counter;
                })
                ->addColumn('profile_picture', function ($row) {
                    if ($row->profile_picture) {
                        $imagePath = asset('images/' . $row->profile_picture);
                        return '<img src="' . $imagePath . '" alt="Employee Image" class="img-thumbnail" style="width: 50px; height: 50px;">';
                    }
                })
                ->addColumn('name', function ($row) {
                    return  $row->first_name . ' ' . $row->last_name;
                })
                ->addColumn('email', function ($row) {
                    return  $row->email;
                })
                ->addColumn('address', function ($row) {
                    return  $row->address;
                })
                ->addColumn('position', function ($row) {
                    return  $row->position;
                })
                ->addColumn('salary', function ($row) {
                    return  $row->salary;
                })
                ->addColumn('action', function ($row) {
                    $actionButtons = '<div class="btn-group">';
                    $actionButtons .= '<button type="button" style="font-size: 16px" class="btn btn-sm btn-secondary">' . trans('employees.actions') . '</button>';
                    $actionButtons .= '<button type="button" class="btn btn-sm btn-secondary dropdown-toggle dropdown-icon" data-bs-toggle="dropdown" aria-expanded="false"><span class="sr-only">Toggle Dropdown</span></button>';
                    $actionButtons .= '<ul class="dropdown-menu">';

                    if (auth()->user()->can('edit_employee')) {
                        $actionButtons .=  '<li><a style="font-size: 14px" class="hover-effect dropdown-item" target="_blank" href="' . route('admin.edit_employee', $row->id) . '"><i class=" bi bi-pencil"></i> ' . trans('employees.edit_data') . '</a></li>';
                    }


                    if (auth()->user()->can('view_employee_files')) {
                        $actionButtons .= '<li><a style="font-size: 14px" class="hover-effect dropdown-item" target="_blank" href="' . route('admin.employee_files', $row->id) . '"><i class="bi bi-files"></i> ' . trans('employees.employee_file') . '</a></li>';
                    }

                    if (auth()->user()->can('view_employee_masrofat')) {
                        $actionButtons .= '<li><a style="font-size: 14px" class="hover-effect dropdown-item" target="_blank" href="' . route('admin.employee_masrofat', $row->id) . '"><i class="bi bi-cash-stack"></i> ' . trans('employees.employee_masrofat') . '</a></li>';
                    }

                    if (auth()->user()->can('view_employee_revenues')) {
                        $actionButtons .= '<li><a style="font-size: 14px" class="hover-effect dropdown-item" target="_blank" href="' . route('admin.employee_revenues', $row->id) . '"><i class="bi bi-cash-coin"></i> ' . trans('employees.employee_revenues') . '</a></li>';
                    }

                    $actionButtons .= '</ul>';
                    $actionButtons .= '</div>';
                    return $actionButtons;
                })->rawColumns(['profile_picture', 'action'])
                ->make(true);

            return response()->json($data);
        }
    }
    /***********************************************************/
    public function add_employee()
    {
        $data['emp_code']   = $this->EmployeeRepository->getLastFieldValue('emp_code');
        // dd($data);
        return view('dashbord.admin.employees.employee_form', $data);
    }
    //     /*********************************************************/
    public function save_employee(AddEmployeeRequest $request)
    {
        try {
            // dd($request->all());
            $emplyee_model   = new Employee();
            $insert_data     = $emplyee_model->data_to_insert($request);
            if ($request->hasFile('personal_photo')) {
                $file = $request->file('personal_photo');
                $dataX = $this->saveImage($file, 'employees');
                $insert_data['profile_picture'] = $dataX;
            }

            // dd($insert_data);
            $insert_data['created_by'] = auth()->user()->id;
            $employee = $this->EmployeeRepository->create($insert_data);
            $request->session()->flash('toastMessage', trans('added_successfully'));
            return redirect()->route('admin.employee_data');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    /*********************************************************/
    public function edit_employee($id)
    {
        // $data['emp_code']   = $this->EmployeeRepository->getLastFieldValue('emp_code');
        $data['employee']   = $this->EmployeeRepository->getById($id);
        //dd($data['all_data']);
        return view('dashbord.admin.employees.employee_edit', $data);
    }
    /***********************************************************/
    public function update_employee(Request $request, $id)
    {
        try {
            //dd('sss');
            $emplyee_model   = new Employee();
            $insert_data     = $emplyee_model->data_to_insert($request);
            if ($request->hasFile('personal_photo')) {
                $file = $request->file('personal_photo');
                $dataX = $this->saveImage($file, 'employees');
                $insert_data['profile_picture'] = $dataX;
            }
            $insert_data['updated_by'] = auth()->user()->id;

            $employee = $this->EmployeeRepository->update($id, $insert_data);

            $request->session()->flash('toastMessage', trans('updated_successfully'));
            return redirect()->route('admin.employee_data');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    /***********************************************************/
    public function delete_employee($id) {}

    /***********************************************************/
    public function employee_files($id)
    {
        $admin = \App\Models\Admin::where('emp_id', $id)->first();
        $collectedBy = $admin ? $admin->id : $id;
        $data['revenues_data']   =  $this->RevenuesRepository->getBywhereDesc(array('collected_by' => $collectedBy));
        $data['all_data']     =  $this->EmployeeRepository->getById($id);
        $data['files_data']   =  $this->EmployeeFilesRepository->getBywhere(array('emp_id' => $id));
        // dd($data);
        return view('dashbord.admin.employees.employee_files', $data);
    }

    /***********************************************************/
    public function employee_add_files(Request $request, $emp_id)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx,txt|max:2048',
            'file_name' => 'required|string|max:255',
        ]);
        try {
            $emp = $this->EmployeeRepository->getById($emp_id);
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $dataX = $this->saveFile($file, 'employee' . $emp->id);

                $data['file']         = $dataX;
                $data['file_name']    = $request->file_name;
                $data['emp_id']       = $emp->id;
                $data['publisher']    = auth('admin')->user()->id;
                $data['publisher_n']  = auth('admin')->user()->name;
                $file                 = $this->EmployeeFilesRepository->create($data);
            }
            notify()->success(trans('File_added_successfully'), '');
            return redirect()->route('admin.employee_files', $emp_id);
        } catch (\Exception $e) {
            test($e->getMessage());
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    /**********************************************************/
    public function employee_details($id)
    {
        $admin = \App\Models\Admin::where('emp_id', $id)->first();
        $collectedBy = $admin ? $admin->id : $id;
        $data['all_data']     =  $this->EmployeeRepository->getById($id);
        $data['revenues_data']   =  $this->RevenuesRepository->getBywhereDesc(array('collected_by' => $collectedBy));
        return view('dashbord.admin.employees.employee_details', $data);
    }

    public function download_file($file_id)
    {
        try {
            $employee_file = $this->EmployeeFilesRepository->getById($file_id);

            $file_path = Storage::disk('files')->path($employee_file->file);
            $file_extension = pathinfo($employee_file->file, PATHINFO_EXTENSION);
            $file_name_with_extension = $employee_file->file_name;

            if (!str_ends_with($file_name_with_extension, ".$file_extension")) {
                $file_name_with_extension .= ".$file_extension";
            }

            $headers = [
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="' . $file_name_with_extension . '"',
            ];

            return response()->download($file_path, $file_name_with_extension, $headers);
        } catch (\Exception $e) {
            test($e->getMessage());
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function read_file($file_id)
    {
        try {
            $emp_file = $this->EmployeeFilesRepository->getById($file_id);
            $file_path = 'files/' . $emp_file->file;
            // $file_path  = Storage::disk('files')->path($emp_file->file);
            return response()->file($file_path);
        } catch (\Exception $e) {
            test($e->getMessage());
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function delete_file(Request $request, $file_id)
    {
        try {
            $emp_file = $this->EmployeeFilesRepository->getWithRelations('employee')->where('id', $file_id)->first();
            $emp_id = $emp_file->employee->id;
            // dd($emp_id);
            $this->EmployeeFilesRepository->delete($file_id);

            $request->session()->flash('toastMessage', trans('File_deleted_successfully'));
            return redirect()->route('admin.employee_files', $emp_id);
        } catch (\Exception $e) {
            test($e->getMessage());
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**********************************************************/

    public function employee_masrofat($id)
    {
        $admin = \App\Models\Admin::where('emp_id', $id)->first();
        $collectedBy = $admin ? $admin->id : $id;
        $data['all_data']     =  $this->EmployeeRepository->getById($id);
        $data['masrofat_data']   =  $this->MasrofatRepository->getBywhere(array('emp_id' => $id));
        $data['revenues_data']   =  $this->RevenuesRepository->getBywhereDesc(array('collected_by' => $collectedBy));
        $data['bands'] = $this->bandsRepository->getAll();
        // dd($data);
        return view('dashbord.admin.employees.employee_masrofat', $data);
    }
    /***********************************************************/
    public function employee_add_masrofat(Request $request, $emp_id)
    {
        $request->validate([
            'band_id' => 'required|exists:tbl_sarf_bands,id',
            'value' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:255',
        ]);
        try {

            $emp = $this->EmployeeRepository->getById($emp_id);

            $data['emp_id'] = $emp->id;
            $data['band_id'] = $request->band_id;
            $data['value'] = $request->value;
            $data['notes'] = $request->notes;
            $data['created_by'] = auth()->user()->id;

            $this->MasrofatRepository->create($data);

            return redirect()->back()->with('success', trans('employees.masrofat_added'));
        } catch (\Exception $e) {
            test($e->getMessage());
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    /***********************************************************/
    public function employee_delete_masrofat(Request $request, $masrofat_id)
    {
        try {
            $this->MasrofatRepository->delete($masrofat_id);

            return redirect()->back()->with('success', trans('employees.masrofat_deleted'));
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    /**********************************************************/

    public function employee_revenues($id)
    {
        $admin = \App\Models\Admin::where('emp_id', $id)->first();
        $collectedBy = $admin ? $admin->id : $id;
        $data['all_data']     =  $this->EmployeeRepository->getById($id);
        $data['revenues_data']   =  $this->RevenuesRepository->getBywhereDesc(array('collected_by' => $collectedBy));
        // dd($data);
        return view('dashbord.admin.employees.employee_revenues', $data);
    }

    //-----------------------------------------------------------
    public function employee_transactions($id)
    {
        $admin = Admin::where('emp_id', $id)->first();
        $account_id = $admin ? $admin->account_id : null;
        $collectedBy = $admin ? $admin->id : $id;
        
        $account = $account_id ? Account::findOrFail($account_id) : null;
        $transactions = $account ? FinancialTransaction::with(['account', 'admin'])->where('account_id', $account_id)->whereNull('deleted_at')->orderBy('created_at', 'desc')->get() : collect();

        $data['revenues_data']   =  $this->RevenuesRepository->getBywhereDesc(array('collected_by' => $collectedBy));
        $data['all_data']     =  $this->EmployeeRepository->getById($id);
        $data['transactions']   =  $transactions;
        // dd($data);
        return view('dashbord.admin.employees.employee_transactions', $data); 
    }
}
