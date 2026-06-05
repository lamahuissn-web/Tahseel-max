<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Radius\RadiusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SessionsController extends Controller
{
    protected $radius;

    public function __construct(RadiusService $radius)
    {
        $this->radius = $radius;
    }

    /**
     * Show live active sessions.
     */
    public function index()
    {
        $sessions = DB::table("radacct")
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

        $nasList = DB::table("nas")->orderBy("nasname")->get()->keyBy("nasname");

        return view("dashbord.sessions.index", compact(
            "sessions", "totalOnline", "totalDown", "totalUp", "nasList"
        ));
    }

    /**
     * Disconnect a user via CoA.
     */
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

    /**
     * Show change speed form.
     */
    public function changeSpeedForm($username)
    {
        $session = DB::table("radacct")
            ->where("username", $username)
            ->whereNull("acctstoptime")
            ->first();

        if (!$session) {
            return redirect()->route("admin.sessions.index")
                ->with("error", "المستخدم غير متصل");
        }

        return view("dashbord.sessions.change-speed", compact("username", "session"));
    }

    /**
     * Change user speed via CoA.
     */
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

    /**
     * Refresh sessions (AJAX).
     */
    public function refresh()
    {
        $sessions = DB::table("radacct")
            ->whereNull("acctstoptime")
            ->orderBy("acctstarttime", "desc")
            ->get();

        $totalOnline = $sessions->count();
        $nasList = DB::table("nas")->get()->keyBy("nasname");

        $html = view("dashbord.sessions.partials.table", compact("sessions", "nasList"))->render();

        return response()->json([
            "html" => $html,
            "total" => $totalOnline,
        ]);
    }

    /**
     * API: Get active sessions JSON.
     */
    public function apiSessions()
    {
        $sessions = DB::table("radacct")
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
