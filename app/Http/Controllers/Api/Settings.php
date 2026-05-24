<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\subscriptions\MainSubscriptionResource;
use App\Http\Resources\subscriptions\SubscriptionSettingsResource;
use App\Models\City;
use App\Models\District;
use App\Models\GroubTypes;
use App\Models\CarBrand;
use App\Models\subscriptions\MainSubscription_M;
use App\Models\subscriptions\SubscriptionSettings_M;
use App\Models\TypesExercises;
use App\Traits\ResponseApi;
use App\Traits\ValidationMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class Settings extends Controller
{
    use ResponseApi;
    use ValidationMessage;


    function get_Subscription(Request $request)
    {
        $search = $request->search;

        try {
            if ($search) {
                $data = MainSubscription_M::where('name->' . app()->getLocale(), 'like', "%{$search}%")->get();
            } else {
                $data = MainSubscription_M::all();;
            }

            if (!empty($data)) {
                return $this->ResponseApi(MainSubscriptionResource::collection($data), trans('api.list_TypesExercises'), 200);
            } else {
                return $this->ResponseApi(null, trans('api.nodata'), 204);

            }
        } catch (\Exception $e) {
            return $this->responseApiError($e->getMessage(), 500);

        }
    }

    /*-----------------------*********************** start setting ***********************--------------------------*/
    function get_exercise_type(Request $request)
    {
        $search = strtolower($request->search);

        try {
            if ($search) {
                $data = SubscriptionSettings_M::where('ttype','exercise_type')->where('name->' . app()->getLocale(), 'like', "%{$search}%")->get();
            } else {
                $data = SubscriptionSettings_M::where('ttype','exercise_type')->get();
            }

            if (!empty($data)) {
                return $this->ResponseApi(SubscriptionSettingsResource::collection($data), trans('api.list_type_unit'), 200);
            } else {
                return $this->ResponseApi(null, trans('api.nodata'), 204);

            }
        } catch (\Exception $e) {
            return $this->responseApiError($e->getMessage(), 500);

        }
    }
    function get_setting(Request $request)
    {
        $search = strtolower($request->search);
        $type = $request->type;
        // Define the allowed types
        $validTypes = ['exercise_type', 'exercise_level', 'car_type', 'goals', 'health_status'];

        // Validate the 'type' parameter
        if (!in_array($type, $validTypes)) {
            return $this->responseApi($validTypes,trans('api.Invalid_value'), 422); // Return a 400 Bad Request error
//            return $this->responseApi($list_error, $list_error_string, 422);

        }
        try {
            if ($search) {
                $data = SubscriptionSettings_M::where('ttype',$type)->where('name->' . app()->getLocale(), 'like', "%{$search}%")->get();
            } else {
                $data = SubscriptionSettings_M::where('ttype',$type)->get();
            }

            if (!empty($data)) {
                return $this->ResponseApi(SubscriptionSettingsResource::collection($data), trans('api.list_type_unit'), 200);
            } else {
                return $this->ResponseApi(null, trans('api.nodata'), 204);

            }
        } catch (\Exception $e) {
            return $this->responseApiError($e->getMessage(), 500);

        }
    }

    /*-----------------------*********************** start setting ***********************--------------------------*/
function list_category(){

    $sub_type_arr = [
        'monthly' => trans('sub.monthly'),
        'quarter' => trans('sub.quarter'),
        'semi' => trans('sub.semi'),
        'annual' => trans('sub.annual'),
    ];

    return $this->ResponseApi($sub_type_arr, trans('api.list_TypesExercises'), 200);

}

    function get_SubscriptionByCatogry(Request $request)
    {

        try {
            $validated = $request->validate([
                'category' => ['required', 'in:monthly,quarter,semi,annual'],
                'search' => 'sometimes',
            ]);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $erros = $this->customErrorAjax($exception->errors());

            return $this->responseApi($erros, null, 422);
        };
        $search = $request->search;
        $category = $request->category;

        try {
            if ($search) {
                $data = MainSubscription_M::where('category',$category)->where('name->' . app()->getLocale(), 'like', "%{$search}%")->get();
            } else {
                $data = MainSubscription_M::where('category',$category)->get();;
            }

            if (!empty($data)) {
                return $this->ResponseApi(\App\Http\Resources\mobile\MainSubscriptionResource::collection($data), trans('api.list_TypesExercises'), 200);
            } else {
                return $this->ResponseApi(null, trans('api.nodata'), 204);

            }
        } catch (\Exception $e) {
            return $this->responseApiError($e->getMessage(), 500);

        }
    }


}
