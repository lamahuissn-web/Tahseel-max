<?php

namespace App\Http\Controllers\Api\Trainers;

use App\Http\Controllers\Admin\subscriptions\DeviceExercises_C;
use App\Http\Controllers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\subscriptions\member\DeviceExercisesResource;
use App\Http\Resources\subscriptions\member\ScheduleResource;
use App\Http\Resources\subscriptions\member\TransportationResource;
use App\Models\schedule;
use App\Models\subscriptions\DeviceExercises_M;
use App\Models\subscriptions\Transportation_M;
use App\Traits\ImageProcessing;
use App\Traits\ValidationMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ScheduleController extends Controller
{
    use ImageProcessing;
    use ApiResponse;
    use ValidationMessage;

    function list_schedules(Request $request)
    {
        try {
            if (auth('trainer')->check()) {
               /* $list_data = Schedule::select('id','date as day',DB::raw(' COUNT(id) as ExercisesNum'))
                    ->groupBy('date')->where('trainer_id',auth('trainer')->user()->id)
                    ->get();*/
                $list_data = Schedule::select('date', DB::raw('COUNT(date) as ExercisesNum'))
                    ->where('trainer_id', auth('trainer')->user()->id)
                    ->groupBy('date')
                    ->get();
                if ($list_data->isNotEmpty()) {
                    return $this->ResponseApi($list_data, trans('api.list_type_unit'), 200);
//                    return $this->ResponseApi(TransportationResource::collection($list_data), trans('api.list_type_unit'), 200);
                } else {
                    return $this->ResponseApi(null, trans('api.nodata'), 200);
                }
            } else {
                return $this->responseApi(null, trans('api.user_unautherized'), 401);
            }
        } catch (\Exception $e) {
            return $this->responseApiError($e->getMessage(), 500);

        }
    }

    function list_schedules_day(Request $request)
    {
        try {
            if (auth('trainer')->check()) {
                $day = $request->day;
                $list_data = Schedule::where('date', $day)->where('trainer_id',auth('trainer')->user()->id)->get();
                if ($list_data->isNotEmpty()) {
//                    return $this->ResponseApi($list_data, trans('api.list_type_unit'), 200);
                    return $this->ResponseApi(ScheduleResource::collection($list_data), trans('api.list_type_unit'), 200);
                } else {
                    return $this->ResponseApi(null, trans('api.nodata'), 200);
                }
            } else {
                return $this->responseApi(null, trans('api.user_unautherized'), 401);
            }
        } catch (\Exception $e) {
            return $this->responseApiError($e->getMessage(), 500);

        }
    }


    function one_schedules(Request $request)
    {
        try {
            if (auth('trainer')->check()) {
                $ScheduleNum = $request->ScheduleNum;
                $list_data = Schedule::find($ScheduleNum);
                if ($list_data) {
                    return $this->ResponseApi(new ScheduleResource($list_data), trans('api.list_type_unit'), 200);
                } else {
                    return $this->ResponseApi(null, trans('api.nodata'), 200);

                }
            } else {
                return $this->responseApi(null, trans('api.user_unautherized'), 401);
            }

        } catch (\Exception $e) {
            return $this->responseApiError($e->getMessage(), 500);

        }
    }
    function cancel_schedules(Request $request)
    {
        try {
            if (auth('trainer')->check()) {
                $ScheduleNum = $request->ScheduleNum;
                $list_data = Schedule::find($ScheduleNum)->delete();
                if ($list_data) {
                    return $this->ResponseApi(null, trans('api.delete_done'), 205);
                } else {
                    return $this->ResponseApi(null, trans('api.nodata'), 200);
                }
            } else {
                return $this->responseApi(null, trans('api.user_unautherized'), 401);
            }
        } catch (\Exception $e) {
            return $this->responseApiError($e->getMessage(), 500);
        }
    }

    public function update_schedules(Request $request)
    {
        if (auth('trainer')->check()) {

            try {
                $validated = $request->validate( [
                    'class_id' => 'required',
                    'time' => 'required',
                    'date' => 'required',
                    'duration' => 'required',
                ]);
            } catch (\Illuminate\Validation\ValidationException $exception) {
                $erros = $this->customErrorRespons($exception->errors());
                $list_error = $erros['list_error'];
                $list_error_string = $erros['list_error_string'];
                return $this->responseApi($list_error, $list_error_string, 422);
            };



            try {

                $userid = auth('trainer')->user()->id;
                $ScheduleNum = $request->ScheduleNum;

                $list_data = Schedule::find($ScheduleNum);

                $input = $request->all();
                $list_data->update($input);
                $list_data = new ScheduleResource($list_data);
                if ($list_data) {
                    return $this->responseApi($list_data, trans('api.save_data_done'), 201);
                } else {
                    return $this->responseApi(null, trans('api.data_not_save'), 400);
                }
            } catch (\Exception $e) {
                return $this->responseApiError($e->getMessage(), 500);

            }
        } else {

            return $this->responseApi(null, trans('api.user_unautherized'), 401);
        }


    }

}
