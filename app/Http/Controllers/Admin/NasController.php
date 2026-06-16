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
            $online = false;
            $rtt = 0;
            $packetLoss = 0;
            
            // Ping with RTT + Packet Loss measurement (3 packets, 2s timeout)
            $pingCmd = "ping -c 3 -W 2 " . escapeshellarg($ip) . " 2>&1";
            $pingOutput = @shell_exec($pingCmd);
            
            if ($pingOutput) {
                // Extract packet loss
                if (preg_match('/(\d+)% packet loss/', $pingOutput, $m)) {
                    $packetLoss = (int)$m[1];
                }
                // Extract RTT (avg)
                if (preg_match('/rtt min\/avg\/max\/(\w+) = [\d.]+\/([\d.]+)\/[\d.]+\/[\d.]+/', $pingOutput, $m)) {
                    $rtt = round((float)$m[2], 1);
                } elseif (preg_match('/round-trip (.*) avg\/max = [\d.]+\/([\d.]+)/', $pingOutput, $m)) {
                    $rtt = round((float)$m[2], 1);
                }
                if ($packetLoss < 100) {
                    $online = true;
                }
            }

            // Fallback: TCP ping on port 22
            if (!$online) {
                $conn = @fsockopen($ip, 22, $errno, $errstr, 2);
                if ($conn) {
                    $online = true;
                    fclose($conn);
                }
            }

            $activeSessions = DB::connection("radius")->table("radacct")
                ->where("nasipaddress", $ip)
                ->whereNull("acctstoptime")
                ->count();

            // OMS = Online Monitoring Status
            if (!$online) {
                $oms = "offline";
            } elseif ($packetLoss > 20 || $rtt > 500) {
                $oms = "poor";
            } elseif ($packetLoss > 5 || $rtt > 200) {
                $oms = "fair";
            } elseif ($packetLoss > 0 || $rtt > 80) {
                $oms = "good";
            } else {
                $oms = "excellent";
            }

            $statuses[$nas->id] = [
                "online" => $online,
                "active_sessions" => $activeSessions,
                "rtt" => $rtt,
                "packet_loss" => $packetLoss,
                "oms" => $oms,
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
            "shortname" => "nullable|string|max:100",
            "secret" => "required|string|min:6",
            "type" => "required|string",
            "description" => "nullable|string|max:200",
            "ports" => "nullable|integer",
            "coa_port" => "nullable|integer",
            "http_port" => "nullable|integer",
            "ssh_port" => "nullable|integer",
            "community" => "nullable|string|max:100",
            "pool_name" => "nullable|string|max:100",
            "mikrotik_version" => "nullable|string|max:50",
            "site" => "nullable|string|max:50",
            "ssh_username" => "nullable|string|max:50",
            "ssh_password" => "nullable|string|max:255",
            "enabled" => "nullable|boolean",
            "ip_accounting" => "nullable|boolean",
            "ping_monitor" => "nullable|boolean",
        ]);

        DB::connection("radius")->table("nas")->insert([
            "nasname" => $request->nasname,
            "shortname" => $request->shortname ?: $request->nasname,
            "type" => $request->type ?: "other",
            "secret" => $request->secret,
            "description" => $request->description ?: "MikroTik Router",
            "ports" => $request->ports ?: 0,
            "coa_port" => $request->coa_port ?? 3799,
            "http_port" => $request->http_port ?? 80,
            "ssh_port" => $request->ssh_port ?? 22,
            "community" => $request->community,
            "pool_name" => $request->pool_name,
            "mikrotik_version" => $request->mikrotik_version,
            "site" => $request->site,
            "ssh_username" => $request->ssh_username,
            "ssh_password" => $request->ssh_password,
            "enabled" => $request->boolean("enabled", true),
            "ip_accounting" => $request->boolean("ip_accounting", false),
            "ping_monitor" => $request->boolean("ping_monitor", true),
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
            "shortname" => "nullable|string|max:100",
            "secret" => "required|string|min:6",
            "type" => "required|string",
            "description" => "nullable|string|max:200",
            "ports" => "nullable|integer",
            "coa_port" => "nullable|integer",
            "http_port" => "nullable|integer",
            "ssh_port" => "nullable|integer",
            "community" => "nullable|string|max:100",
            "pool_name" => "nullable|string|max:100",
            "mikrotik_version" => "nullable|string|max:50",
            "site" => "nullable|string|max:50",
            "ssh_username" => "nullable|string|max:50",
            "ssh_password" => "nullable|string|max:255",
            "enabled" => "nullable|boolean",
            "ip_accounting" => "nullable|boolean",
            "ping_monitor" => "nullable|boolean",
        ]);

        DB::connection("radius")->table("nas")->where("id", $id)->update([
            "nasname" => $request->nasname,
            "shortname" => $request->shortname ?: $request->nasname,
            "type" => $request->type ?: "other",
            "secret" => $request->secret,
            "description" => $request->description ?: "MikroTik Router",
            "ports" => $request->ports ?: 0,
            "coa_port" => $request->coa_port ?? 3799,
            "http_port" => $request->http_port ?? 80,
            "ssh_port" => $request->ssh_port ?? 22,
            "community" => $request->community,
            "pool_name" => $request->pool_name,
            "mikrotik_version" => $request->mikrotik_version,
            "site" => $request->site,
            "ssh_username" => $request->ssh_username,
            "ssh_password" => $request->ssh_password,
            "enabled" => $request->boolean("enabled", true),
            "ip_accounting" => $request->boolean("ip_accounting", false),
            "ping_monitor" => $request->boolean("ping_monitor", true),
        ]);

        return redirect()->route("admin.nas.index")->with("success", "تم تحديث جهاز NAS بنجاح");
    }

    public function destroy($id)
    {
        DB::connection("radius")->table("nas")->where("id", $id)->delete();
        return redirect()->route("admin.nas.index")->with("success", "تم حذف جهاز NAS بنجاح");
    }
}
