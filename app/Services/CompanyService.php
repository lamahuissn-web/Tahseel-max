<?php


namespace App\Services;


use App\Interfaces\BasicRepositoryInterface;
use App\Models\Admin\AreaSetting;
use App\Models\Clients;
use App\Models\ClientsCompanies;
use App\Traits\ImageProcessing;

class CompanyService
{

    use ImageProcessing;
    protected $CompanyRepository;
    public function __construct(BasicRepositoryInterface $basicRepository)
    {
        $this->CompanyRepository   = createRepository($basicRepository, new ClientsCompanies());
    }
    /************************************************/
    public function store($request)
    {
        $validated_data=$request->validated();
        $validated_data['client_id'] = $request->client_id;
        $validated_data['company_code'] = $request->company_code;
        $validated_data['created_by']= auth()->user()->id;
        /* if ($request->hasFile('image')) {
             $file = $request->file('image');
             $dataX = $this->saveImage($file, 'clients');
             $validated_data['image'] = $dataX;
         }*/
        //  dd($validated_data);
        return $this->CompanyRepository->create($validated_data);
    }

    /************************************************/
    public function get_company($id)
    {
        return $this->CompanyRepository->getById($id);
    }
    /************************************************/
    public function update($request,$id)
    {
        $validated_data=$request->validated();
        $validated_data['client_id'] = $request->client_id;
        $validated_data['company_code'] = $request->company_code;
        $validated_data['updated_by']= auth()->user()->id;
        /*if ($request->hasFile('image')) {
            $file = $request->file('image');
            $dataX = $this->saveImage($file, 'clients');
            $validated_data['image'] = $dataX;
        }*/
        // dd($validated_data);
        return $this->CompanyRepository->update($id,$validated_data);
    }
    /**************************************************/




}
