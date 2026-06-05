<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NasController extends Controller
{
    public function index()
    {
        $nasDevices = DB::connection("radius")->table("nas")->orderBy("nasname")->get();
        return view("dashbord.nas.index", compact("nasDevices"));
    }

    public function create()
    {
        return view("dashbord.nas.form");
    }

    public function store(Request $request)
    {
        $request->validate([
            "nasname" => "required|ip",
            "shortname" => "nullable|string|max:32",
            "secret" => "required|string|min:6",
            "type" => "required|string",
            "description" => "nullable|string|max:200",
            "ports" => "nullable|integer",
        ]);

        DB::connection("radius")->table("nas")->insert([
            "nasname" => $request->nasname,
            "shortname" => $request->shortname ?: $request->nasname,
            "type" => $request->type ?: "other",
            "secret" => $request->secret,
            "description" => $request->description ?: "MikroTik Router",
            "ports" => $request->ports ?: 0,
        ]);

        // Reload FreeRADIUS to pick up new NAS
        // exec("systemctl reload freeradius 2>/dev/null &");

        return redirect()->route("admin.nas.index")->with("success", "تم إضافة جهاز NAS بنجاح");
    }

    public function edit($id)
    {
        $nas = DB::connection("radius")->table("nas")->where("id", $id)->firstOrFail();
        return view("dashbord.nas.form", compact("nas"));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            "nasname" => "required|ip",
            "shortname" => "nullable|string|max:32",
            "secret" => "required|string|min:6",
            "type" => "required|string",
            "description" => "nullable|string|max:200",
            "ports" => "nullable|integer",
        ]);

        DB::connection("radius")->table("nas")->where("id", $id)->update([
            "nasname" => $request->nasname,
            "shortname" => $request->shortname ?: $request->nasname,
            "type" => $request->type ?: "other",
            "secret" => $request->secret,
            "description" => $request->description ?: "MikroTik Router",
            "ports" => $request->ports ?: 0,
        ]);

        return redirect()->route("admin.nas.index")->with("success", "تم تحديث جهاز NAS بنجاح");
    }

    public function destroy($id)
    {
        DB::connection("radius")->table("nas")->where("id", $id)->delete();
        return redirect()->route("admin.nas.index")->with("success", "تم حذف جهاز NAS بنجاح");
    }
}
