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

        $statuses = [];
        foreach ($nasDevices as $nas) {
            $ip = $nas->nasname;

            // TCP ping on port 22 (SSH) - 2s timeout
            $online = false;
            $conn = @fsockopen($ip, 22, $errno, $errstr, 2);
            if ($conn) {
                $online = true;
                fclose($conn);
            }

            // Active sessions count
            $activeSessions = DB::connection("radius")->table("radacct")
                ->where("nasipaddress", $ip)
                ->whereNull("acctstoptime")
                ->count();

            $statuses[$nas->id] = [
                "online" => $online,
                "active_sessions" => $activeSessions,
            ];
        }

        return view("dashbord.nas.index", compact("nasDevices", "statuses"));
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

        return redirect()->route("admin.nas.index")->with("success", "تم إضافة جهاز NAS بنجاح");
    }

    public function edit($id)
    {
        $nas = DB::connection("radius")->table("nas")->where("id", $id)->first();

        if (!$nas) {
            abort(404, "جهاز NAS غير موجود");
        }
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
