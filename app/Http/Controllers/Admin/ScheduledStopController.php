<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScheduledStopController extends Controller
{
    public function index()
    {
        $clients = DB::table('tbl_clients')
            ->join('tbl_subscriptions', 'tbl_subscriptions.id', '=', 'tbl_clients.subscription_id')
            ->whereNotNull('tbl_clients.radius_stop_at')
            ->select(
                'tbl_clients.id',
                'tbl_clients.name',
                'tbl_clients.phone',
                'tbl_clients.sas_username',
                'tbl_clients.radius_stop_at',
                'tbl_clients.is_active',
                'tbl_subscriptions.name as plan_name'
            )
            ->orderBy('tbl_clients.radius_stop_at', 'asc')
            ->get();

        $totalScheduled = $clients->count();
        $overdue = $clients->where('radius_stop_at', '<=', now()->format('Y-m-d'))->count();
        $upcoming = $clients->where('radius_stop_at', '>', now()->format('Y-m-d'))->count();

        return view('dashbord.sessions.scheduled-stops', compact(
            'clients', 'totalScheduled', 'overdue', 'upcoming'
        ));
    }
}
