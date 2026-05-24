<?php

namespace App\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\RolesRequest;
use App\Models\Permissions;
use App\Models\Roles;
use Illuminate\Http\Request;
use App\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RolesController extends Controller
{

    public function index()
    {
        $roles = Roles::where('name','!=','Super-Admin')->get();
        $permissions = Permissions::all();
        return view('dashbord.UserManagement.roles.roles', compact('roles', 'permissions'));
    }

    public function get_permission($id)
    {
        $role = Roles::findorfail($id);

        $permission = Permissions::all();
        return view('dashbord.UserManagement.roles.permission', compact('permission', 'role'));
    }

    public function create()
    {
    }

    public function store(RolesRequest $request)
    {
        try {
            $request->validated();

            /*$permission = new Roles();
            $permission->title = ['ar' => $request->title_ar, 'en' => $request->title_en];
            $permission->save();*/

            $insert_data = $request->all();
            $insert_data['title'] = ['ar' => $request->title_ar, 'en' => $request->title_en];
            $insert_data['name'] = preg_replace('/\s+/', '_',  $request->title_en);
            $insert_data['guard_name'] =  'admin';
            $roles = Roles::create($insert_data);
            $role= Role::findorfail($roles->id);
            $role->syncPermissions($request->input('permissions'));

            toastr()->addSuccess(trans('forms.success'));
            return redirect()->route('admin.UserManagement.roles.index');

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(Request $request,$id)
    {
        $id=$request->get('id');
        $one_data = Roles::findorfail($id);
        $permissions = Permissions::all();

        $role_permissions = DB::table('role_has_permissions')
            ->where('role_id', '=', $id)
            ->get()->pluck('permission_id')->all();

//        dd($role_permissions);
        return view('dashbord.UserManagement.roles.edite', compact('permissions', 'one_data','role_permissions'));
    }
 /*   public function load_edit(Request $request)
    {
        $id=$request->get('id');
        $one_data = Roles::findorfail($id);
        $permissions = Permissions::all();

        $role_permissions = DB::table('role_has_permissions')
            ->where('role_id', '=', $id)
            ->get()->pluck('permission_id');
        return view('dashbord.UserManagement.roles.edite', compact('permissions', 'one_data','role_permissions'));

    }*/
    public function edit(Request $request,$id)
    {

    }

    public function update(RolesRequest $request, $id)
    {
        try {
            $role_id = $id;
            $role = Roles::findorfail($role_id);
            /*$permission->name = ['ar' => $request->title_ar, 'en' => $request->title_en];
            $permission->update();*/

            $insert_data = $request->all();
            $insert_data['title'] = ['ar' => $request->title_ar, 'en' => $request->title_en];
            $role->update($insert_data);
            $roles= Role::findorfail($role_id);
            $roles->syncPermissions($request->input('permissions'));

            toastr()->addSuccess(trans('forms.Update'));
            return redirect()->route('admin.UserManagement.roles.index');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function delete($id)
    {
        try {
            Roles::destroy($id);
            toastr()->addSuccess(trans('forms.Delete'));

            return redirect()->route('admin.UserManagement.roles.index');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}





