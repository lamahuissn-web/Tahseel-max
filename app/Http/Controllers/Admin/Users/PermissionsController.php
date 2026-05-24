<?php

namespace App\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\PermissionsRequest;
use App\Models\Permissions;

class PermissionsController extends Controller
{

    public function index()
    {
        $permission = Permissions::all();
        return view('dashbord.UserManagement.permissions', compact('permission'));
    }

    public function create()
    {
    }

    public function store(PermissionsRequest $request)
    {
        try {
            $request->validated();

            /*$permission = new Permissions();
            $permission->title = ['ar' => $request->title_ar, 'en' => $request->title_en];
            $permission->save();*/

            $insert_data = $request->all();
            $insert_data['title'] = ['ar' => $request->title_ar, 'en' => $request->title_en];
            Permissions::create($insert_data);

            toastr()->addSuccess(trans('forms.success'));
            return redirect()->route('admin.UserManagement.permission.index');

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }


    public function edit($id)
    {

    }

    public function update(PermissionsRequest $request, $id)
    {
        try {
            $permission_id = $id;
            $permission = Permissions::findorfail($permission_id);
            /*$permission->name = ['ar' => $request->title_ar, 'en' => $request->title_en];
            $permission->update();*/

            $insert_data = $request->all();
            $insert_data['title'] = ['ar' => $request->title_ar, 'en' => $request->title_en];
            $permission->update($insert_data);


            toastr()->addSuccess(trans('forms.Update'));
            return redirect()->route('admin.UserManagement.permission.index');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function delete($id)
    {
        try {
            Permissions::destroy($id);
            toastr()->addSuccess(trans('forms.Delete'));

            return redirect()->route('admin.UserManagement.permission.index');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}





