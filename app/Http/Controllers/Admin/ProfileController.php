<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Services\Radius\ProfileService;
use App\Services\Radius\RadiusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    protected ProfileService $profileService;

    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    /**
     * Show all profiles (tbl_profiles) with aggregated stats
     */
    public function index()
    {
        $stats = $this->profileService->getProfileStats();
        $allProfiles = Profile::withCount('subscriptions')->get();
        $linkedSubs = DB::table('tbl_subscriptions')
            ->whereNotNull('profile_id')
            ->get()
            ->keyBy('profile_id');

        $totalUsers = 0;
        $totalProfiles = count($stats);
        foreach ($stats as $s) {
            $totalUsers += $s->clients_count ?? 0;
        }
        $totalLinkedSubs = $allProfiles->sum('subscriptions_count');

        return view('dashbord.profiles.index', compact(
            'stats', 'allProfiles', 'linkedSubs', 'totalUsers', 'totalProfiles', 'totalLinkedSubs'
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
            'name'              => 'required|string|max:100|unique:tbl_profiles,name',
            'speed'             => 'required|string|max:20',
            'simultaneous_use'  => 'nullable|integer|min:1|max:10',
            'subscription_id'   => 'nullable|exists:tbl_subscriptions,id',
        ]);

        DB::beginTransaction();
        try {
            $profile = Profile::create([
                'name'             => $request->name,
                'speed'            => $request->speed,
                'simultaneous_use' => $request->simultaneous_use ?? 1,
            ]);

            $radiusDb = DB::connection('radius');

            $radiusDb->table('radgroupcheck')->insert([
                'groupname' => $profile->name,
                'attribute' => 'Simultaneous-Use',
                'op'        => ':=',
                'value'     => $request->simultaneous_use ?? 1,
            ]);

            $radiusDb->table('radgroupreply')->insert([
                'groupname' => $profile->name,
                'attribute' => 'Mikrotik-Rate-Limit',
                'op'        => ':=',
                'value'     => $request->speed,
            ]);

            if ($request->subscription_id) {
                DB::table('tbl_subscriptions')->where('id', $request->subscription_id)->update([
                    'profile_id'     => $profile->id,
                    'radius_profile' => $profile->name,
                    'radius_speed'   => $request->speed,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.profiles.index')
                ->with('success', 'تم إنشاء الباقة "' . $profile->name . '" بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create profile: ' . $e->getMessage());
            return redirect()->back()->withInput()->withErrors(['error' => 'فشل إنشاء الباقة: ' . $e->getMessage()]);
        }
    }

    public function edit(Profile $profile)
    {
        $subscription = DB::table('tbl_subscriptions')
            ->where('profile_id', $profile->id)
            ->orWhere('radius_profile', $profile->name)
            ->first();

        $subscriptions = DB::table('tbl_subscriptions')
            ->select('id', 'name', 'price')
            ->get();

        return view('dashbord.profiles.form', compact('profile', 'subscription', 'subscriptions'));
    }

    public function update(Request $request, Profile $profile)
    {
        $request->validate([
            'speed'             => 'required|string|max:20',
            'simultaneous_use'  => 'nullable|integer|min:1|max:10',
            'subscription_id'   => 'nullable|exists:tbl_subscriptions,id',
        ]);

        DB::beginTransaction();
        try {
            $oldSpeed = $profile->speed;
            $radiusDb = DB::connection('radius');

            $radiusDb->table('radgroupcheck')
                ->where('groupname', $profile->name)
                ->where('attribute', 'Simultaneous-Use')
                ->update(['value' => $request->simultaneous_use ?? 1]);

            $radiusDb->table('radgroupreply')
                ->where('groupname', $profile->name)
                ->where('attribute', 'Mikrotik-Rate-Limit')
                ->update(['value' => $request->speed]);

            $profile->update([
                'speed'            => $request->speed,
                'simultaneous_use' => $request->simultaneous_use ?? 1,
            ]);

            // Unlink old subscription
            DB::table('tbl_subscriptions')->where('profile_id', $profile->id)->update([
                'profile_id'     => null,
                'radius_profile' => null,
                'radius_speed'   => null,
            ]);

            if ($request->subscription_id) {
                DB::table('tbl_subscriptions')->where('id', $request->subscription_id)->update([
                    'profile_id'     => $profile->id,
                    'radius_profile' => $profile->name,
                    'radius_speed'   => $request->speed,
                ]);
            }

            DB::commit();

            $result = ['updated_count' => 0, 'coa_success' => 0, 'coa_failed' => 0];
            if ($oldSpeed !== $request->speed) {
                $result = $this->profileService->bulkUpdateSpeed($profile->id, $request->speed);
            }

            $message = 'تم تحديث الباقة "' . $profile->name . '" بنجاح';
            if ($result['updated_count'] > 0) {
                $message .= ' — تم تحديث ' . $result['updated_count'] . ' زبون';
                if ($result['coa_failed'] > 0) {
                    $message .= ' (' . $result['coa_failed'] . ' فشل CoA)';
                    return redirect()->route('admin.profiles.index')->with('warning', $message);
                }
            }

            return redirect()->route('admin.profiles.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update profile: ' . $e->getMessage());
            return redirect()->back()->withInput()->withErrors(['error' => 'فشل تحديث الباقة: ' . $e->getMessage()]);
        }
    }

    public function destroy(Profile $profile)
    {
        $radiusDb = DB::connection('radius');

        try {
            DB::beginTransaction();

            DB::table('tbl_clients')
                ->where('profile_id', $profile->id)
                ->orWhere('radius_profile', $profile->name)
                ->update(['profile_id' => null, 'radius_profile' => null]);

            DB::table('tbl_subscriptions')
                ->where('profile_id', $profile->id)
                ->orWhere('radius_profile', $profile->name)
                ->update(['profile_id' => null, 'radius_profile' => null, 'radius_speed' => null]);

            $radiusDb->table('radgroupcheck')->where('groupname', $profile->name)->delete();
            $radiusDb->table('radgroupreply')->where('groupname', $profile->name)->delete();

            $profile->delete();

            DB::commit();

            return redirect()->route('admin.profiles.index')
                ->with('success', 'تم حذف الباقة "' . $profile->name . '" بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete profile: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'فشل حذف الباقة: ' . $e->getMessage()]);
        }
    }

    public function applyToUser(Request $request)
    {
        $request->validate([
            'username'   => 'required|string',
            'profile_id' => 'required|exists:tbl_profiles,id',
        ]);

        $result = $this->profileService->applyProfileById(
            $request->username,
            $request->profile_id
        );

        return response()->json([
            'success' => $result,
            'message' => $result ? 'تم تطبيق الباقة بنجاح' : 'فشل تطبيق الباقة',
        ]);
    }
}
