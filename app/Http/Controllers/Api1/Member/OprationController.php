<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Admin\subscriptions\DeviceExercises_C;
use App\Http\Controllers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\subscriptions\member\DeviceExercisesResource;
use App\Http\Resources\subscriptions\member\ScheduleResource;
use App\Http\Resources\subscriptions\member\TransportationResource;
use App\Models\MemberSubscriptionsFreezingDays;
use App\Models\schedule;
use App\Models\subscriptions\DeviceExercises_M;
use App\Models\subscriptions\Transportation_M;
use App\Traits\ImageProcessing;
use App\Traits\ValidationMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OprationController extends Controller
{
    use ImageProcessing;
    use ApiResponse;
    use ValidationMessage;

    function list_exercise(Request $request)
    {
        try {
            if (auth('api')->check()) {
                $device_code = $request->device_code;
                $list_data = DeviceExercises_M::where('device_code', $device_code)->get();
                if ($list_data->isNotEmpty()) {
                    return $this->ResponseApi(DeviceExercisesResource::collection($list_data), trans('api.list_type_unit'), 200);
//                   return $this->ResponseApi($list_data, trans('api.list_exercise'), 200);
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

    function one_exercise(Request $request)
    {
        try {
            if (auth('api')->check()) {
                $ExercisesNum = $request->ExercisesNum;
                $list_data = DeviceExercises_M::find($ExercisesNum);
                if ($list_data) {
                    return $this->ResponseApi(new DeviceExercisesResource($list_data), trans('api.list_type_unit'), 200);
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

    function list_transportation(Request $request)
    {
        try {
            if (auth('api')->check()) {
                $day = $request->day;
                $list_data = Transportation_M::where('moving_day', $day)->get();
                if ($list_data->isNotEmpty()) {
                    return $this->ResponseApi(TransportationResource::collection($list_data), trans('api.list_type_unit'), 200);
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

    function list_schedules(Request $request)
    {
        try {
            if (auth('api')->check()) {
                $member_id = auth('api')->user()->member->id;
                $list_data = Schedule::select(DB::raw('date as day, COUNT(id) as ExercisesNum'))
                    ->groupBy('date')
                    ->get();
                $freezing_days = MemberSubscriptionsFreezingDays::where('member_id', $member_id)->pluck('freezing_day');
                if ($list_data->isNotEmpty()) {
                    return $this->ResponseApi( [
                        'data' => $list_data,
                        'freezing_days' => $freezing_days,
                    ], trans('api.list_type_unit'), 200);
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
            if (auth('api')->check()) {
                $day = $request->day;
                $list_data = Schedule::where('date', $day)->get();
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


}
