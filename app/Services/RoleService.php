<?php


namespace App\Services;


use App\Interfaces\BasicRepositoryInterface;
use App\Models\Admin;
use App\Models\Admin\Test;
use App\Traits\ImageProcessing;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use App\Models\Role;

class RoleService
{

    use ImageProcessing;
    protected $rolesRepository;
    protected $permissionsRepository;
    public function __construct(BasicRepositoryInterface $basicRepository)
    {
        $this->rolesRepository   = createRepository($basicRepository, new Role());
        $this->permissionsRepository = createRepository($basicRepository, new Permission());
    }
    /************************************************/
    public function store($request)
    {
        $validatedData = $request->validated();
        // dd($validatedData);
        $validatedData['name'] = $validatedData['title']['en'];

        $permissions = $validatedData['permissions'] ?? [];
        unset($validatedData['permissions']);
        // dd($validatedData);
        $role = $this->rolesRepository->create($validatedData);

        if (!empty($permissions)) {
            $role->syncPermissions($permissions);
        }

        return $role;
    }

    /************************************************/
    public function update($request, $id)
    {
        $validatedData = $request->validated();
        $validatedData['name'] = $validatedData['title']['en'];
        
        $permissions = $validatedData['permissions'] ?? [];
        unset($validatedData['permissions']);

        $role = $this->rolesRepository->getById($id);

        if (!$role) {
            return null;
        }

        $this->rolesRepository->update($id, $validatedData);

        if (!empty($permissions)) {
            $role->syncPermissions($permissions);
        }

        return $role;
    }
    /**************************************************/
    public function delete($id)
    {
        $role = $this->rolesRepository->getById($id);

        if (!$role) {
            return false;
        }

        $role->syncPermissions([]);

        return $this->rolesRepository->delete($id);
    }


}
