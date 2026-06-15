<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Radius\RadiusService;
use App\Services\Radius\RouterOSService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SessionsController extends Controller
{
    protected $radius;

    public function __construct(RadiusService $radius)
    {
        $this->radius = $radius;
    }

    public function index()
    {
        $radiusDb = DB::connection("radius");

        $sessions = $radiusDb->table("radacct")
            ->whereNull("acctstoptime")
            ->orderBy("acctstarttime", "desc")
            ->get();

        $totalOnline = $sessions->count();
        $totalDown = 0;
        $totalUp = 0;
        foreach ($sessions as $s) {
            $totalDown += (int)$s->acctinputoctets;
            $totalUp += (int)$s->acctoutputoctets;
        }

        $disconnectedSessions = $radiusDb->table("radacct")
            ->whereNotNull("acctstoptime")
            ->where("acctstoptime", ">=", now()->subDays(7))
            ->orderBy("acctstoptime", "desc")
            ->get();

        $totalDisconnected = $disconnectedSessions->count();

        $nasList = $radiusDb->table("nas")->orderBy("nasname")->get()->keyBy("nasname");

        return view("dashbord.sessions.index", compact(
            "sessions", "disconnectedSessions", "totalOnline", "totalDisconnected",
            "totalDown", "totalUp", "nasList"
        ));
    }

    public function disconnect($username)
    {
        $result = $this->radius->coaDisconnect($username);

        if ($result["success"]) {
            return redirect()->route("admin.sessions.index")
                ->with("success", $result["message"]);
        }

        return redirect()->route("admin.sessions.index")
            ->with("error", $result["message"]);
    }

    public function changeSpeedForm($username)
    {
        $session = DB::connection("radius")->table("radacct")
            ->where("username", $username)
            ->whereNull("acctstoptime")
            ->first();

        if (!$session) {
            return redirect()->route("admin.sessions.index")
                ->with("error", "\u0627\u0644\u0645\u0633\u062a\u062e\u062f\u0645 \u063a\u064a\u0631 \u0645\u062a\u0635\u0644");
        }

        return view("dashbord.sessions.change-speed", compact("username", "session"));
    }

    public function changeSpeed(Request $request, $username)
    {
        $request->validate([
            "speed" => "required|string|max:20",
        ]);

        $result = $this->radius->coaChangeSpeed($username, $request->speed);

        if ($result["success"]) {
            return redirect()->route("admin.sessions.index")
                ->with("success", $result["message"]);
        }

        return redirect()->route("admin.sessions.index")
            ->with("error", $result["message"]);
    }

    public function refresh(Request $request)
    {
        $tab = $request->get("tab", "active");
        $radiusDb = DB::connection("radius");

        if ($tab === "active") {
            $sessions = $radiusDb->table("radacct")
                ->whereNull("acctstoptime")
                ->orderBy("acctstarttime", "desc")
                ->get();

            $totalOnline = $sessions->count();
            $totalDown = 0;
            $totalUp = 0;
            foreach ($sessions as $s) {
                $totalDown += (int)$s->acctinputoctets;
                $totalUp += (int)$s->acctoutputoctets;
            }

            $nasList = $radiusDb->table("nas")->get()->keyBy("nasname");

            $html = view("dashbord.sessions.partials.table", compact("sessions", "nasList"))->render();

            return response()->json([
                "html" => $html,
                "total" => $totalOnline,
                "totalDown" => $totalDown,
                "totalUp" => $totalUp,
                "tab" => "active",
            ]);
        }

        $disconnectedSessions = $radiusDb->table("radacct")
            ->whereNotNull("acctstoptime")
            ->where("acctstoptime", ">=", now()->subDays(7))
            ->orderBy("acctstoptime", "desc")
            ->get();

        $nasList = $radiusDb->table("nas")->get()->keyBy("nasname");

        $html = view("dashbord.sessions.partials.disconnected-table", compact("disconnectedSessions", "nasList"))->render();

        return response()->json([
            "html" => $html,
            "total" => $disconnectedSessions->count(),
            "tab" => "disconnected",
        ]);
    }

    public function apiSessions()
    {
        $sessions = DB::connection("radius")->table("radacct")
            ->whereNull("acctstoptime")
            ->orderBy("acctstarttime", "desc")
            ->get()
            ->map(function ($s) {
                return [
                    "username" => $s->username,
                    "ip" => $s->framedipaddress,
                    "nas" => $s->nasipaddress,
                    "uptime" => $s->acctstarttime ? now()->diffInSeconds($s->acctstarttime) : 0,
                    "download" => (int)$s->acctinputoctets,
                    "upload" => (int)$s->acctoutputoctets,
                    "session_id" => $s->acctsessionid,
                ];
            });

        return response()->json([
            "success" => true,
            "total" => $sessions->count(),
            "sessions" => $sessions,
        ]);
    }
}
