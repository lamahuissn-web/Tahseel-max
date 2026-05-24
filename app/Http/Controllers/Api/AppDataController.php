<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Site\AboutResource;
use App\Http\Resources\Site\DataAppResource;
use App\Http\Resources\Site\TermsResource;
use App\Http\Resources\subscriptions\MainSubscriptionResource;
use App\Models\Site\SiteAbout;
use App\Models\Site\SiteContact;
use App\Models\Site\SiteData;
use App\Models\Site\SiteTerms;
use App\Models\subscriptions\MainSubscription_M;
use App\Traits\ImageProcessing;
use App\Traits\ResponseApi;
use App\Traits\ValidationMessage;
use Illuminate\Http\Request;

class AppDataController extends Controller
{
    use ImageProcessing;
    use ResponseApi;
    use ValidationMessage;

    public function contact_message(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required',
                'title' => 'sometimes',
                'phone' => 'sometimes',
                'email' => 'sometimes|email',
                'subject' => 'required',
            ]);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $erros = $this->customErrorAjax($exception->errors());

            return $this->responseApi($erros, null, 422);
        };
        try {
//dd($request->all());
            $data_array = $request->all();
            /*$data_array = [
                'name'         => $request['name'],
                'title'         => $request['title'],
                'phone'        => $request['phone'],
                'email'        => $request['email'],
                'subject'      => $request['subject']
            ];*/

            $insert_data = SiteContact::create($data_array);

            if ($insert_data) {

                return $this->responseApi($insert_data, 'تم الحفظ بنجاح', 201);
            } else {
                return $this->responseApi(null, 'لم يتم الحفظ', 400);
            }
        } catch
        (\Exception $e) {
            return $this->responseApiError($e->getMessage(), 500);

        }
    }

    function homePage()
    {
        try {
            $App_data = SiteData::find(1);
            $programs = SiteAbout::all();
            $terms = SiteTerms::all();
            $Subscription = MainSubscription_M::all();;
            $data['app_data'] = new DataAppResource($App_data);
            $data['terms'] = TermsResource::collection($terms);
            $data['programs'] = AboutResource::collection($programs);
            $data['Subscription'] = MainSubscriptionResource::collection($Subscription);
            if (!empty($data)) {
                return $this->ResponseApi($data, trans('api.list_data'), 200);
            } else {
                return $this->ResponseApi(null, trans('api.nodata'), 204);

            }
        } catch
        (\Exception $e) {
            return $this->responseApiError($e->getMessage(), 500);

        }
    }

    /*-------------------------------------------------------------*/
    public function data_app()
    {
        try {

            $data = SiteData::find(1);
            if ($data) {
                return $this->responseApi(new DataAppResource($data), 'تم الحفظ بنجاح', 201);
            } else {
                return $this->responseApi(null, 'لم يتم الحفظ', 400);
            }
        } catch
        (\Exception $e) {
            return $this->responseApiError($e->getMessage(), 500);

        }
    }

    function terms(Request $request)
    {
        try {
            $data = SiteTerms::all();
            if (!empty($data)) {
                return $this->ResponseApi(TermsResource::collection($data), trans('api.list_sponser'), 200);
            } else {
                return $this->ResponseApi(null, trans('api.nodata'), 204);

            }
        } catch (\Exception $e) {
            return $this->responseApiError($e->getMessage(), 500);

        }
    }

    function programs(Request $request)
    {
        try {
            $data = SiteAbout::all();
            if (!empty($data)) {
                return $this->ResponseApi(AboutResource::collection($data), trans('api.list_programs'), 200);
            } else {
                return $this->ResponseApi(null, trans('api.nodata'), 204);

            }
        } catch (\Exception $e) {
            return $this->responseApiError($e->getMessage(), 500);

        }
    }

    public function data_app_local()
    {
        try {

            $data = SiteData::find(1);
            if ($data) {
                return $this->responseApi(new \App\Http\Resources\mobile\DataAppResource($data), 'تم الحفظ بنجاح', 201);
            } else {
                return $this->responseApi(null, 'لم يتم الحفظ', 400);
            }
        } catch
        (\Exception $e) {
            return $this->responseApiError($e->getMessage(), 500);

        }
    }

    function terms_local(Request $request)
    {
        try {
            $data = SiteTerms::all();
            if (!empty($data)) {
                return $this->ResponseApi(\App\Http\Resources\mobile\TermsResource::collection($data), trans('api.list_sponser'), 200);
            } else {
                return $this->ResponseApi(null, trans('api.nodata'), 204);

            }
        } catch (\Exception $e) {
            return $this->responseApiError($e->getMessage(), 500);

        }
    }



}
