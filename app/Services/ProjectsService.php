<?php


namespace App\Services;


use App\Interfaces\BasicRepositoryInterface;
use App\Models\Admin\AreaSetting;
use App\Models\Clients;
use App\Models\ClientsCompanies;
use App\Models\ClientsProjects;
use App\Traits\ImageProcessing;

class ProjectsService
{

    use ImageProcessing;
    protected $ProjectsRepository;
    public function __construct(BasicRepositoryInterface $basicRepository)
    {
        $this->ProjectsRepository   = createRepository($basicRepository, new ClientsProjects());
    }
    /************************************************/
    public function store($request)
    {
        $validated_data=$request->validated();
        $validated_data['client_id'] = $request->client_id;
        $validated_data['company_id'] = $request->company_id;
        $validated_data['project_code'] = $request->project_code;
        $validated_data['created_by']= auth()->user()->id;
        /* if ($request->hasFile('image')) {
             $file = $request->file('image');
             $dataX = $this->saveImage($file, 'clients');
             $validated_data['image'] = $dataX;
         }*/
        // dd($validated_data);
        return $this->ProjectsRepository->create($validated_data);
    }

    /************************************************/
    public function get_company($id)
    {
        return $this->ProjectsRepository->getById($id);
    }
    /************************************************/
    public function update($request,$id)
    {
        $validated_data=$request->validated();
        $validated_data['client_id'] = $request->client_id;
        $validated_data['company_id'] = $request->company_id;
        $validated_data['project_code'] = $request->project_code;
        $validated_data['updated_by']= auth()->user()->id;
        /*if ($request->hasFile('image')) {
            $file = $request->file('image');
            $dataX = $this->saveImage($file, 'clients');
            $validated_data['image'] = $dataX;
        }*/
        // dd($validated_data);
        return $this->ProjectsRepository->update($id,$validated_data);
    }
    /**************************************************/




}
