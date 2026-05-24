<?php

namespace App\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;

use App\Http\Requests\Admin\AdminStoreRequest;
use App\Http\Requests\Admin\AdminUpdateRequest;
use App\Interfaces\BasicRepositoryInterface;
use App\Models\Admin;
use App\Models\Roles;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    protected $upload_folder = 'admin/users';

    /* public function __construct(BasicRepositoryInterface $basicRepository)
     {
         $this->basicRepository = $basicRepository;
         $this->basicRepository->set_model(new Admin());
     }*/

    public function index(Request $request)
    {

        if ($request->ajax()) {

            $data = Admin::with('role')->select('*');
            return DataTables::of($data)
                ->addColumn('userCard', function ($row) {
                    return '
<div class="d-flex align-items-center">
<!--begin:: Avatar -->
                    <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                           <div class="symbol-label">
                                    <img src="' . $row->image . '" alt="Emma Smith" class="w-100">
                           </div>
                    </div>
                    <!--end::Avatar-->
                    <!--begin::User details-->
                    <div class="d-flex flex-column">
                        <a  class="text-gray-800 text-hover-primary mb-1">' . $row->name . '</a>
                        <span>' . $row->email . '</span>
                    </div>
                    <!--begin::User details-->
                    </div>';
                })
                ->addColumn('role', function ($row) {
                    return optional($row->role)->title;
                })
                ->addColumn('action', function ($row) {
                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                        <a href="' . route('admin.UserManagement.users.edit', $row->id) . '"
                                               title="Edit"
                                                 class="btn btn-sm btn-icon  btn-light-warning"><i class="fas fa-pencil"></i></a>
                        <a href="' . route('admin.UserManagement.users.delete', $row->id) . '"
                                               title="Edit"
                                                 class="btn btn-sm btn-icon btn-light-danger"><i class="fas fa-trash"></i></a>

                  </div>';
                })
                ->filterColumn('userCard', function ($query, $keyword) {
                    $query->where('admins.name' , 'like', "%{$keyword}%");
                    $query->orWhere('admins.email', 'like', "%{$keyword}%");

                })
               /* ->filterColumn('name', function ($query, $keyword) {
                    $query->where('unites.name->' . app()->getLocale(), 'like', "%{$keyword}%");
                })*/
                ->rawColumns(['action', 'image','userCard'])
                ->make(true);
        }
        return view('dashbord.UserManagement.users.index');

    }

    function create()
    {

        //        $roles=Roles::where('guard_name','admin')->get();
//        $roles = Roles::all();
        $roles = Roles::get()->skip(1);

        return view('dashbord.UserManagement.users.create', compact('roles'));
    }

    public function store(AdminStoreRequest $request)
    {
        try {
            $insert_data = $request->all();
            $insert_data['name'] = $request->user_name;
            $insert_data['address'] = $request->address;
            $insert_data['phone'] = $request->phone;
            $insert_data['email'] = $request->email;
//            $insert_data['password'] = $request->password;
            $insert_data['password'] = Hash::make($request->password);
            $insert_data['group_name'] = $request->roles;
            $insert_data['real_password'] = $request->password;
            $insert_data['status'] = $request->status;
            if ($request->hasFile('user_image')) {
                $file = $request->file('user_image');

                $dataX = $this->saveImage($file, $this->upload_folder);
                $insert_data['image'] = $dataX;
            }
//            $this->basicRepository->create($insert_data);
            $user= Admin::create($insert_data);
            $user->assignRole($request->roles);

            toastr()->addSuccess(trans('forms.success'));

            return redirect()->route('admin.UserManagement.users.index');


        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }


    public function edit(Request $request,$id)
    {
//        $user_data = $this->basicRepository->getById($id);
        $roles = Roles::all();

        $user_data = Admin::find($id);
        return view('dashbord.UserManagement.users.edit', compact('user_data', 'roles'));
    }


    /*------------------------------------------------------------------------------------*/
    public function update(AdminUpdateRequest $request, $id)
    {
        //dd($request);
        try {
//            $data =$this->basicRepository->getById($request->id);

            $data = Admin::find($request->id);
            $insert_data = $request->all();
            $insert_data['user'] = $request->user_name;
            $insert_data['group_name'] = $request->group_name;
            $insert_data['address'] = $request->address;
            $insert_data['phone'] = $request->phone;
            $insert_data['email'] = $request->email;
            $insert_data['group_name'] = $request->roles;

            // $insert_data['password'] = $request->password;
            $insert_data['status'] = $request->status;
            if ($request->hasFile('user_image')) {
                $file = $request->file('user_image');

                $dataX = $this->saveImage($file, $this->upload_folder);
                $insert_data['image'] = $dataX;
            }
//            $this->basicRepository->update($request->id, $insert_data);
            $data->update($insert_data);
            $data->assignRole($request->roles);

            toastr()->addSuccess(trans('forms.success'));

            return redirect()->route('admin.UserManagement.users.index');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }


    /*------------------------------------------------------------------------------*/
    public function destroy(Request $request)
    {
        try {

            $delete_data = Admin::find($request['id'])->delete();
//            $this->basicRepository->delete($request['id']);
            toastr()->addSuccess(trans('forms.success'));

            return redirect()->route('admin.UserManagement.users.index');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

}
