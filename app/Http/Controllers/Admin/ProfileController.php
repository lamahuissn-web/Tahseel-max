<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Radius\ProfileService;
use App\Services\Radius\RadiusService;
use App\Services\Radius\RouterOSService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    protected $profileService;

    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    public function index()
    {
        // Get all RADIUS groups from FreeRADIUS
        $radiusDb = DB::connection('radius');
        $profiles = $radiusDb->table('radgroupcheck')
            ->select('groupname')
            ->distinct()
            ->get()
            ->pluck('groupname');

        // Get RadGroupReply speeds
        $groupSpeeds = $radiusDb->table('radgroupreply')
            ->where('attribute', 'Mikrotik-Rate-Limit')
            ->get()
            ->keyBy('groupname');

        // Get subscriptions linked to profiles
        $subscriptions = DB::table('tbl_subscriptions')
            ->whereNotNull('radius_profile')
            ->get()
            ->keyBy('radius_profile');

        return view('dashbord.profiles.index', compact(
            'profiles', 'groupSpeeds', 'subscriptions'
        ));
    }

    public function create()
    {
        $subscriptions = DB::table('tbl_subscriptions')
            ->select('id', 'name', 'price')
            ->get();

        return view('dashbord.profiles.form', compact('subscriptions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'speed' => 'required|string|max:20',
            'simultaneous_use' => 'nullable|integer|min:1|max:10',
            'subscription_id' => 'nullable|exists:tbl_subscriptions,id',
        ]);

        $radiusDb = DB::connection('radius');
        $profileName = $request->name;

        // Create group in radgroupcheck (simultaneous-use)
        $radiusDb->table('radgroupcheck')->insert([
            'groupname' => $profileName,
            'attribute' => 'Simultaneous-Use',
            'op' => ':=',
            'value' => $request->simultaneous_use ?? 1,
        ]);

        // Create group speed in radgroupreply
        $radiusDb->table('radgroupreply')->insert([
            'groupname' => $profileName,
            'attribute' => 'Mikrotik-Rate-Limit',
            'op' => ':=',
            'value' => $request->speed,
        ]);

        // Link to subscription
        if ($request->subscription_id) {
            DB::table('tbl_subscriptions')
                ->where('id', $request->subscription_id)
                ->update([
                    'radius_profile' => $profileName,
                    'radius_speed' => $request->speed,
                ]);
        }

        return redirect()->route('admin.profiles.index')
            ->with('success', 'تم إنشاء الباقة "' . $profileName . '" بنجاح');
    }

    public function edit($name)
    {
        $radiusDb = DB::connection('radius');
        $checks = $radiusDb->table('radgroupcheck')
            ->where('groupname', $name)
            ->get()
            ->keyBy('attribute');

        $replies = $radiusDb->table('radgroupreply')
            ->where('groupname', $name)
            ->get()
            ->keyBy('attribute');

        $subscription = DB::table('tbl_subscriptions')
            ->where('radius_profile', $name)
            ->first();

        $allSubscriptions = DB::table('tbl_subscriptions')
            ->select('id', 'name', 'price')
            ->get();

        return view('dashbord.profiles.form', compact(
            'name', 'checks', 'replies', 'subscription', 'allSubscriptions'
        ));
    }

    public function update(Request $request, $name)
    {
        $request->validate([
            'speed' => 'required|string|max:20',
            'simultaneous_use' => 'nullable|integer|min:1|max:10',
            'subscription_id' => 'nullable|exists:tbl_subscriptions,id',
        ]);

        $radiusDb = DB::connection('radius');

        // Update simultaneous-use
        $radiusDb->table('radgroupcheck')
            ->where('groupname', $name)
            ->where('attribute', 'Simultaneous-Use')
            ->update(['value' => $request->simultaneous_use ?? 1]);

        // Update speed
        $radiusDb->table('radgroupreply')
            ->where('groupname', $name)
            ->where('attribute', 'Mikrotik-Rate-Limit')
            ->update(['value' => $request->speed]);

        // Update subscription link
        DB::table('tbl_subscriptions')
            ->where('radius_profile', $name)
            ->update(['radius_profile' => null, 'radius_speed' => null]);

        if ($request->subscription_id) {
            DB::table('tbl_subscriptions')
                ->where('id', $request->subscription_id)
                ->update([
                    'radius_profile' => $name,
                    'radius_speed' => $request->speed,
                ]);
        }

        return redirect()->route('admin.profiles.index')
            ->with('success', 'تم تحديث الباقة "' . $name . '" بنجاح');
    }

    public function destroy($name)
    {
        $radiusDb = DB::connection('radius');

        $radiusDb->table('radgroupcheck')
            ->where('groupname', $name)->delete();
        $radiusDb->table('radgroupreply')
            ->where('groupname', $name)->delete();

        // Unlink subscriptions
        DB::table('tbl_subscriptions')
            ->where('radius_profile', $name)
            ->update(['radius_profile' => null, 'radius_speed' => null]);

        return redirect()->route('admin.profiles.index')
            ->with('success', 'تم حذف الباقة "' . $name . '" بنجاح');
    }

    public function applyToUser(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'profile' => 'required|string',
        ]);

        $result = $this->profileService->applyProfile(
            $request->username,
            $request->profile
        );

        return response()->json([
            'success' => $result,
            'message' => $result ? 'تم تطبيق الباقة بنجاح' : 'فشل تطبيق الباقة',
        ]);
    }
}
