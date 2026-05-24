<?php


namespace App\Repositories;


use App\Interfaces\BasicRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class BasicRepository implements BasicRepositoryInterface
{
//    protected $model;
//
//    public function __construct(Model $model)
//    {
//        $this->model = $model;
//    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function getSoftDelete()
    {
        // TODO: Implement getSoftDelete() method.
    }

    public function getById($id)
    {
        return $this->model->find($id);

    }

    public function create(array $data)
    {
        return $this->model->create($data);

    }

    public function update($id, array $data)
    {
        $row = $this->getById($id);
        return $row->update($data);

    }
    public function updateWhere(array $conditions, array $data)
    {
        $row = $this->getBywhere($conditions)->first();

        return $row->update($data);

    }

    public function delete($id)
    {
        $row = $this->getById($id);
        return $row->delete();
    }

    public function final_delete($id)
    {
        // TODO: Implement final_delete() method.
    }

    public function restore($id)
    {
        // TODO: Implement restore() method.
    }


    public function getPaginate($per_page)
    {
        return $this->model->paginate($per_page);

    }

    public function getBywhere(array $conditions)
    {
        $query = $this->model->query();
        foreach ($conditions as $field => $value) {
            $query->where($field, $value);
        }
        return $query->get();
    }

    public function getBywhereDesc(array $conditions)
    {
        $query = $this->model->query();
        foreach ($conditions as $field => $value) {
            $query->where($field, $value);
        }
        return $query->orderByDesc('id')->get();
    }

    public function getByquerylatest()
    {
        $query = $this->model->query()->latest();

        return $query;
    }

    public function set_model($model)
    {
        $this->model = $model;
    }

    public function get_model()
    {
        return $this->model;
    }

    /*-----------------------------------------------*/
    public function getWithRelations($relations = [])
    {
        return $this->model->with($relations)->get();
    }

    // public function getLastFieldValue($field)
    // {
    //     $query = $this->model;

    //     if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($this->model))) {
    //         $query = $query->withTrashed();
    //     }

    //     $lastValue = $query->orderByDesc($field)->value($field);

    //     return is_null($lastValue) ? 1 : $lastValue + 1;
    // }

    public function getLastFieldValue($field)
    {
        $query = $this->model->newQuery();

        if (method_exists($this->model, 'trashed')) {
            $query->withTrashed();
        }

        $lastRecord = $query->latest('id')->first();

        if (!$lastRecord) {
            return 1;
        }

        $newValue = $lastRecord->{$field} + 1;

        while ($query->where($field, $newValue)->exists()) {
            $newValue++;
        }

        return $newValue;
    }
}







