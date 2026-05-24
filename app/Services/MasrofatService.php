<?php


namespace App\Services;


use App\Interfaces\BasicRepositoryInterface;
use App\Models\Admin\Masrofat;
use App\Traits\ImageProcessing;

class MasrofatService
{

    use ImageProcessing;
    protected $MasrofatRepository;
    public function __construct(BasicRepositoryInterface $basicRepository)
    {
        $this->MasrofatRepository   = createRepository($basicRepository, new Masrofat());
    }
    /************************************************/
    public function store($request)
    {
        $validated_data=$request->validated();
        $validated_data['emp_id'] = $request->emp_id;
        $validated_data['band_id'] = $request->band_id;
        $validated_data['value'] = $request->value;
        $validated_data['notes'] = $request->notes;
        $validated_data['created_by']= auth()->user()->id;

        return $this->MasrofatRepository->create($validated_data);
    }

    /************************************************/
    public function update($request,$id)
    {
        $validated_data=$request->validated();
        $validated_data['emp_id'] = $request->emp_id;
        $validated_data['band_id'] = $request->band_id;
        $validated_data['value'] = $request->value;
        $validated_data['notes'] = $request->notes;
        $validated_data['updated_by']= auth()->user()->id;

        //dd($validated_data);
        return $this->MasrofatRepository->update($id, $validated_data);
    }
    /**************************************************/




}
