<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Radius\RadiusService;
use App\Services\Radius\RouterOSService;
use App\Services\Radius\ProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Clients;

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

        // Build client name lookup from sas_username
        $clients = Clients::whereNotNull('sas_username')
            ->where('sas_username', '!=', '')
            ->pluck('name', 'sas_username');

        $nasList = $radiusDb->table("nas")->orderBy("nasname")->get()->keyBy("nasname");

        // Live RouterOS stats from CHR
        $routerStats = null;
        try {
            $routeros = app(RouterOSService::class);
            if ($routeros->connect()) {
                $routerStats = $routeros->getRouterStats();
                $routeros->disconnect();
            }
        } catch (\Exception $e) {
            // CHR might be unreachable, ignore
        }

        return view("dashbord.sessions.index", compact(
            "sessions", "disconnectedSessions", "totalOnline", "totalDisconnected",
            "totalDown", "totalUp", "nasList", "routerStats", "clients"
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

        // Get available profiles
        $profiles = app(ProfileService::class)->getAvailableProfiles();
        $currentProfile = app(ProfileService::class)->getClientProfile($username);

        return view("dashbord.sessions.change-speed", compact("username", "session", "profiles", "currentProfile"));
    }

    public function changeSpeed(Request $request, $username)
    {
        $request->validate([
            "profile" => "required|string|max:50",
        ]);

        $profileName = $request->profile;

        // Apply profile via ProfileService (updates radusergroup + radreply + CoA)
        $profileService = app(ProfileService::class);
        $success = $profileService->applyProfile($username, $profileName);

        if ($success) {
            // Get the profile display name for the message
            $profile = collect($profileService->getAvailableProfiles())
                ->firstWhere('name', $profileName);

            $displayName = $profile ? $profile->name : $profileName;

            return redirect()->route("admin.sessions.index")
                ->with("success", "\u062a\u0645 \u062a\u063a\u064a\u064a\u0631 \u0633\u0631\u0639\u0629 {$username} \u0625\u0644\u0649 {$displayName} \u0628\u0646\u062c\u0627\u062d");
        }

        return redirect()->route("admin.sessions.index")
            ->with("error", "\u0641\u0634\u0644 \u062a\u063a\u064a\u064a\u0631 \u0627\u0644\u0633\u0631\u0639\u0629 \u0644\u0644\u0645\u0633\u062a\u062e\u062f\u0645 {$username}");
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

            // Build client name lookup
            $clients = Clients::whereNotNull('sas_username')
                ->where('sas_username', '!=', '')
                ->pluck('name', 'sas_username');

            $nasList = $radiusDb->table('nas')->get()->keyBy('nasname');

            $html = view('dashbord.sessions.partials.table', compact('sessions', 'nasList', 'clients'))->render();

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

        // Build client name lookup
        $clients = Clients::whereNotNull('sas_username')
            ->where('sas_username', '!=', '')
            ->pluck('name', 'sas_username');

        $nasList = $radiusDb->table('nas')->get()->keyBy('nasname');

        $html = view('dashbord.sessions.partials.disconnected-table', compact('disconnectedSessions', 'nasList', 'clients'))->render();

        return response()->json([
            "html" => $html,
            "total" => $disconnectedSessions->count(),
            "tab" => "disconnected",
        ]);
    }

    public function routerHealth()
    {
        $stats = null;
        try {
            $routeros = app(RouterOSService::class);
            if ($routeros->connect()) {
                $stats = $routeros->getRouterStats();
                $routeros->disconnect();
            }
        } catch (\Exception $e) {
            return response()->json(["success" => false, "message" => $e->getMessage()]);
        }

        return response()->json([
            "success" => true,
            "stats" => $stats,
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