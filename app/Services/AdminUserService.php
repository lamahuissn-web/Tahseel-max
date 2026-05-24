<?php


namespace App\Services;


use App\Interfaces\BasicRepositoryInterface;
use App\Models\Admin;
use App\Models\Admin\Account;
use App\Models\Admin\AccountSettings;
use App\Models\Admin\Test;
use App\Traits\ImageProcessing;
use Illuminate\Support\Facades\Hash;

class AdminUserService
{

    use ImageProcessing;
    protected $AdminUserRepository;
    public function __construct(BasicRepositoryInterface $basicRepository)
    {
        $this->AdminUserRepository   = createRepository($basicRepository, new Admin());
    }
    /************************************************/
    public function store($request)
    {
        $validated_data = $request->validated();
        $validated_data['real_password'] = $validated_data['password'];
        $validated_data['password'] = Hash::make($validated_data['password']);
        $validated_data['created_by'] = auth()->user()->id;
        // dd($validated_data);
        if ($request->hasFile('image')) {
            $validated_data['image'] = $this->saveImage($request->file('image'), 'admins');
        }
        $role = $validated_data['role'] ?? null;
        unset($validated_data['role']);

        $admin = $this->AdminUserRepository->create($validated_data);

        if ($role) {
            $admin->assignRole($role);
        }

        $account = Account::create([
            'name' => $validated_data['name'],
            'parent_id' => null,
            'level' => 2,
            'created_by' => auth()->id(),
        ]);

        $admin->update(['account_id' => $account->id]);

        $accountSettings = AccountSettings::first();
        if ($accountSettings) {
            $account->update(['parent_id' => $accountSettings->employee_account_id]);
        }

        return $admin;
    }

    /************************************************/
    public function update($request, $id)
    {
        $validated_data = $request->validated();
        $validated_data['updated_by'] = auth()->user()->id;

        if (!empty($validated_data['password'])) {
            $validated_data['real_password'] = $validated_data['password'];
            $validated_data['password'] = Hash::make($validated_data['password']);
        } else {
            unset($validated_data['password']);
        }

        $role = $validated_data['role'] ?? null;
        unset($validated_data['role']);

        // dd($validated_data);
        $admin = $this->AdminUserRepository->getById($id);
        if ($request->hasFile('image')) {
            if ($admin->image) {
                $oldImagePath = public_path('images/' . $admin->image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            $validated_data['image'] = $this->saveImage($request->file('image'), 'admins');
        }

        $this->AdminUserRepository->update($id, $validated_data);

        if ($role) {
            $admin->assignRole($role);
        }

        return $admin;
    }
    /**************************************************/




}
