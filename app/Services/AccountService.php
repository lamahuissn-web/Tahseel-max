<?php


namespace App\Services;


use App\Interfaces\BasicRepositoryInterface;
use App\Models\Admin;
use App\Models\Admin\Account;
use App\Traits\ImageProcessing;

class AccountService
{

    use ImageProcessing;
    protected $accountsRepository;
    protected $adminsRepository;
    public function __construct(BasicRepositoryInterface $basicRepository)
    {
        $this->accountsRepository   = createRepository($basicRepository, new Account());
        $this->adminsRepository   = createRepository($basicRepository, new Admin());

    }
    /************************************************/
    public function store($request)
    {
        $validated_data = $request->validated();

        if (!empty($validated_data['user_id'])) {
            $existingAdmin = Admin::where('account_id', '!=', null)
                                    ->where('id', $validated_data['user_id'])
                                    ->first();

            if ($existingAdmin) {
                toastr()->addError(trans('accounts.this_user_is_already_assigned_to_an_account.'));
                return redirect()->back();
            }
        }

        $validated_data['created_by'] = auth()->user()->id;

        $account = $this->accountsRepository->create($validated_data);

        if (!empty($validated_data['user_id'])) {
            $user = $this->adminsRepository->getById($validated_data['user_id']);
            if ($user) {
                $user->update(['account_id' => $account->id]);
            }
        }

        return $account;
    }

    /************************************************/
    public function update($request, $id)
    {
        $validated_data = $request->validated();
        if (!empty($validated_data['user_id'])) {
            $existingAdmin = Admin::where('id', $validated_data['user_id'])
                                    ->where('account_id', '!=', null)
                                    ->where('account_id', '!=', $id)
                                    ->first();

            if ($existingAdmin) {
                toastr()->addError(trans('accounts.this_user_is_already_assigned_to_an_account.'));
                return redirect()->back();
            }
        }

        $validated_data['updated_by'] = auth()->user()->id;
        $account = $this->accountsRepository->update($id, $validated_data);

        if (!empty($validated_data['user_id'])) {
            $user = $this->adminsRepository->getById($validated_data['user_id']);
            if ($user) {
                $user->update(['account_id' => $id]);
            }
        }
        //dd($validated_data);
        return $account;
    }
    /**************************************************/
}
