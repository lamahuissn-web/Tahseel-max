<?php


namespace App\Traits;


use App\Interfaces\BasicRepositoryInterface;
use App\Models\real_estate\RealEstateOffice;
use App\Models\settings\City;
use App\Models\settings\Quarter;
use App\Models\settings\State;
use Illuminate\Http\Request;

trait MainFunction
{
    public function __construct(BasicRepositoryInterface $basicRepository)
    {
        $this->basicRepository = $basicRepository;
    }

    function getMainData()
    {
        $this->basicRepository->set_model(new City());
        $mdata = $this->basicRepository->getByquerylatest()->first();

        return optional($mdata);
    }

    function get_city(Request $request)
    {
        $this->basicRepository->set_model(new City());
        $data = $this->basicRepository->getBywhere(['emara_id_fk' => $request->emara_id, 'is_deleted' => 0]);
        return $data;

    }

    function get_quarter(Request $request)
    {
        $this->basicRepository->set_model(new Quarter());
        $data = $this->basicRepository->getBywhere(['city_id_fk' => $request->city_id_fk, 'is_deleted' => 0]);
        return $data;
    }

    function get_emara(Request $request)
    {
        $this->basicRepository->set_model(new State());
        $data = $this->basicRepository->getBywhere(['country_id_fk' => $request->country_id, 'is_deleted' => 0]);
        return $data;

    }

    function prepare_data($data)
    {

        $json = json_encode($data);
        return json_decode($json);
    }
}
