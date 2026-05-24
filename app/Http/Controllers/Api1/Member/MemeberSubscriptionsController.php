<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\mobile\MainSubscriptionResource;
use App\Http\Resources\mobile\Notificationscollection;
use App\Http\Resources\subscriptions\member\MemberDietResource;
use App\Http\Resources\subscriptions\member\MemberSubscriptionsResource;
use App\Http\Resources\subscriptions\SpecialSubscriptionsResource;
use App\Http\Resources\subscriptions\TrainerResource;
use App\Models\AdditionalMemberSubscriptions;
use App\Models\MemberDiet;
use App\Models\MemberFreeDays;
use App\Models\Members;
use App\Models\MembersSubscriptions;
use App\Models\MemberSubscriptionsFreezingDays;
use App\Models\Notifications;
use App\Models\subscriptions\MainSubscription_M;
use App\Models\subscriptions\SpecialSubscription_M;
use App\Models\Trainers;
use App\Models\User;
use App\Traits\ImageProcessing;
use App\Traits\ValidationMessage;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MemeberSubscriptionsController extends Controller
{
    use ImageProcessing;
    use ApiResponse;
    use ValidationMessage;

    public function get_member_subscriptions(Request $request)
    {
        try {
            if (auth('api')->check()) {
                $member_id = $request->member_id;
                $last_subscription = MembersSubscriptions::where('member_id', $member_id)
                    ->orderBy('id', 'desc')
                    ->first();

                if ($last_subscription) {
                    // Return the single resource
                    return $this->ResponseApi(new MemberSubscriptionsResource($last_subscription), trans('api.list_type_unit'), 200);
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

    public function get_one_subscriptions(Request $request)
    {
        try {
            if (auth('api')->check()) {
                $id = $request->id;
                $last_subscription = MainSubscription_M::find($id);
//dd($last_subscription);
                if ($last_subscription) {
                    // Return the single resource
                    return $this->ResponseApi(new MainSubscriptionResource($last_subscription), trans('api.list_type_unit'), 200);
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

    public function get_trainers(Request $request)
    {
        try {

            $lang = $request->header('lang', 'en');
            \App::setLocale($lang);

            if (auth('api')->check()) {
                $list_data = Trainers::all();
                if ($list_data) {
                    return $this->ResponseApi(TrainerResource::collection($list_data), trans('api.list_type_unit'), 200);
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

    /****************************************************************/
    public function get_main_subscriptions(Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');
            \App::setLocale($lang);
            if (auth('api')->check()) {
                $category = $request->category;
                $subscription = MainSubscription_M::where('status', 'active')->where('category', $category)->get();
                //return $this->ResponseApi($subscription, trans('api.nodata'), 200);
                if ($subscription) {
                    return $this->ResponseApi(MainSubscriptionResource::collection($subscription), trans('api.list_type_unit'), 200);
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

    /*****************************************************************/
    public function get_special_subscriptions(Request $request)
    {
        try {

            $lang = $request->header('lang', 'en');
            \App::setLocale($lang);

            if (auth('api')->check()) {
                $subscription = SpecialSubscription_M::where('status', 'active')->get();
                if ($subscription) {
                    return $this->ResponseApi(SpecialSubscriptionsResource::collection($subscription), trans('api.list_type_unit'), 200);
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

    /*****************************************************************/
    public function store_member_subscription(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'main_subscription_id' => 'required',
            'pay_method' => 'required',
            // 'special_subscription_ids' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->responseApi(null, $validator->errors(), 422);

        }
        try {
            $lang = $request->header('lang', 'en');
            \App::setLocale($lang);

            if (auth('api')->check()) {

                $last_member_subscription = MembersSubscriptions::where('member_id', auth('api')->user()->member->id)
                    ->orderBy('id', 'desc')
                    ->first();

                if (!empty($last_member_subscription)) {
                    if ($last_member_subscription->end_date < date('Y-m-d')) {
                        return $this->ResponseApi(null, trans('api.sorry_you_dont_finish_last_subscription'), 400);
                    }
                }


                $main_subscription_id = $request->main_subscription_id;
                $main_subscription_resource = new MainSubscriptionResource(MainSubscription_M::find($main_subscription_id));
                $main_subscription = $main_subscription_resource->toArray(request());

                $lastProcessNum = MembersSubscriptions::max('process_num');
                $duration = $main_subscription['Duration'];

                $invite_code = $request->invite_code;


                $data['process_num'] = $lastProcessNum ? $lastProcessNum + 1 : 1;
                $data['type'] = 'main';
                $data['subscription_id'] = $main_subscription['id'];
                $data['member_id'] = auth('api')->user()->member->id;
                $data['start_date'] = date('Y-m-d');
                $startDate = new DateTime($data['start_date']);
                $startDate->modify("+$duration months");
                $data['end_date'] = $startDate->format('Y-m-d');

                $data['pay_method'] = $request->pay_method;
                $data['added_date'] = date('Y-m-d');
                $data['transport'] = $request->transport;
                $data['transport_value'] = $main_subscription['Duration'] * getMainData()->transport_value;
                $data['discount'] = $main_subscription['max_discount'];
                $data['transport_duration'] = $main_subscription['Duration'];
                $data['package_duration'] = $main_subscription['Duration'];
                $data['package_price'] = $main_subscription['total_main_cost'];
                $data['invite_code'] = $invite_code;

                // Use transaction
                DB::beginTransaction();

                try {
                    $member_subscription = MembersSubscriptions::create($data);

                    $special_subscription_ids = $request->special_subscription_ids;
                    if (is_string($special_subscription_ids)) {
                        $special_subscription_ids = json_decode($special_subscription_ids, true);
                    }

                    if (is_array($special_subscription_ids)) {
                        $total_cost = 0;
                        foreach ($special_subscription_ids as $subscription_data) {
                            $additional_id = $subscription_data['id'];
                            $trainer_id = $subscription_data['trainer_id'];

                            $special_subscription = SpecialSubscription_M::find($additional_id);
                            if ($special_subscription) {
                                $additional_data = [
                                    'member_subscription_id' => $member_subscription->id,
                                    'type' => 'special',
                                    'subscription_id' => $special_subscription->id,
                                    'start_date' => $member_subscription->start_date,
                                    'end_date' => $member_subscription->end_date ? $member_subscription->end_date : $data['end_date'],
                                    'added_date' => date('Y-m-d'),
                                    'trainer_id' => $trainer_id,
                                    'discount' => $special_subscription->max_discount,
                                    'duration' => $member_subscription->duration ? $member_subscription->duration : 0,
                                    'cost' => $special_subscription->price
                                ];

                                $discounted_cost = $special_subscription->price - ($special_subscription->price * ($special_subscription->max_discount / 100));
                                $total_cost += $discounted_cost;
                                AdditionalMemberSubscriptions::create($additional_data);
                            }
                        }
                    }
                    $discount=0;
                    if (!empty($invite_code)) {
                        $member = Members::where('invite_code', $invite_code)->first();
                        $invite_data['member_id'] = $member->id;
                        $invite_data['free_days'] = get_app_config_data('freedays');
                        $invite_data['member_subscription_id'] = $member_subscription->id;


                        $to_user_id=$member->id;
                        $member = Members::find($to_user_id);
                        $to_user_name = $member ? $member->member_name : null;
                        $from_user_id=auth('api')->user()->id;
                        $from_user_name=auth('api')->user()->name;
                        $lang=User::find($member->user_id)->lang;
                        $data_not = [0 => $lang];
                        $extra_data=[0=>$member->id];
                        $status = 'unread';
                        $type = 'invite';
                        $token=User::find($member->user_id)->tokenNoti;
                        $code = 3;
                        //dd($from_user_name);
                        send_notifications($to_user_id, $to_user_name, $from_user_id, $from_user_name, $data_not, $extra_data, $status, $type, $token, $code);


                        MemberFreeDays::create($invite_data);
                        $discount=($total_cost + $data['package_price'])*get_app_config_data('freedays_discount')/100;

                    }

                    $data_updated['total_cost'] = $total_cost + $data['package_price']-$discount;
                    $member_subscription->update($data_updated);
                    $last_subscription = MembersSubscriptions::where('member_id', auth('api')->user()->member->id)
                        ->orderBy('id', 'desc')
                        ->first();


                    DB::commit();

                    if ($last_subscription) {
                        return $this->ResponseApi(new MemberSubscriptionsResource($last_subscription), trans('api.list_type_unit'), 200);
                    } else {
                        return $this->ResponseApi(null, trans('api.nodata'), 401);
                    }
                } catch (\Exception $e) {
                    // Rollback transaction if there is an error
                    DB::rollBack();
                    return $this->ResponseApi($e, trans('api.nodata'), 200);
                }
            } else {
                return $this->responseApi(null, trans('api.user_unautherized'), 401);
            }
        } catch (\Exception $e) {
            return $this->responseApiError($e->getMessage(), 500);
        }
    }

    /*****************************************************************************/
    public function renew_subscriptions()
    {
        try {
            if (auth('api')->check()) {
                $user_id = auth('api')->user()->id;
                $member_id = Members::where('user_id', $user_id)->first()->id;
                $last_subscription = MembersSubscriptions::where('member_id', $member_id)
                    ->orderBy('id', 'desc')
                    ->first();

                if (empty($last_subscription)) {
                    return $this->ResponseApi(null, trans('api.this_member_is_not_subscribed'), 200);
                }


                $all_subscription = new MemberSubscriptionsResource($last_subscription);
                //  return $this->ResponseApi($all_subscription,trans('api.list_type_unit'), 200);
                $all_subscribtion_data = $all_subscription->toArray(request());
                if (!empty($all_subscribtion_data)) {
                    //return $this->ResponseApi($all_subscribtion_data['end_date'], trans('api.nodata'), 200);


                    if ($all_subscribtion_data['end_date'] < date('Y-m-d')) {

                        $lastProcessNum = MembersSubscriptions::max('process_num');
                        $duration = $all_subscribtion_data['package_duration'];
                        $data['process_num'] = $lastProcessNum ? $lastProcessNum + 1 : 1;
                        $data['type'] = 'main';
                        $data['subscription_id'] = $all_subscribtion_data['subscription']['id'];
                        $data['member_id'] = $member_id;
                        $data['start_date'] = date('Y-m-d');
                        $startDate = new DateTime($data['start_date']);
                        $startDate->modify("+$duration months");
                        $data['end_date'] = $startDate->format('Y-m-d');
                        $data['pay_method'] = $all_subscribtion_data['payMethod'];
                        $data['added_date'] = date('Y-m-d');
                        // $data['transport_value'] = $all_subscribtion_data->transport_value;
                        $data['discount'] = $all_subscribtion_data['discount'];
                        $data['transport_duration'] = $all_subscribtion_data['transport_duration'];
                        $data['package_duration'] = $all_subscribtion_data['package_duration'];
                        $data['package_price'] = $all_subscribtion_data['package_price'];
                        $data['total_cost'] = $all_subscribtion_data['total_cost'];
                        $data['notes'] = $all_subscribtion_data['notes'];

                        $member_subscription = MembersSubscriptions::create($data);
//                    $addtional_data = $all_subscribtion_data['additional_subscription']->toArray(request());
//
//                    return $this->ResponseApi($addtional_data[0]['end_date'] ,trans('api.list_type_unit'), 200);

                        if ($all_subscribtion_data['additional_subscription']) {
                            $addtional_data = $all_subscribtion_data['additional_subscription']->toArray(request());

                            foreach ($addtional_data as $subscription_data) {

                                $additional_data = [
                                    'member_subscription_id' => $member_subscription->id,
                                    'type' => $subscription_data['type'],
                                    'subscription_id' => $subscription_data['subscription']['id'],
                                    'start_date' => $subscription_data['start_date'],
                                    'end_date' => $subscription_data['end_date'],
                                    'added_date' => date('Y-m-d'),
                                    'trainer_id' => $subscription_data['trainer_id'],
                                    'discount' => $subscription_data['discount'],
                                    'duration' => $subscription_data['subscription']['Duration'] ? $subscription_data['subscription']['Duration'] : 0,
                                    'cost' => $subscription_data['cost']
                                ];
                                AdditionalMemberSubscriptions::create($additional_data);

                            }
                        }

                        $saved_data = MembersSubscriptions::where('member_id', $member_id)
                            ->orderBy('id', 'desc')
                            ->first();
                        if ($saved_data) {
                            // Return the single resource
                            return $this->ResponseApi(new MemberSubscriptionsResource($saved_data), trans('api.list_type_unit'), 200);
                        } else {
                            return $this->ResponseApi(null, trans('api.nodata'), 200);
                        }
                    } else {
                        return $this->ResponseApi($all_subscribtion_data['end_date'], trans('api.sorry_you_dont_finish_last_subscription'), 400);
                    }
                } else {
                    return $this->responseApi(null, trans('api.this_member_is_not_subscribed'), 401);
                }


            } else {
                return $this->responseApi(null, trans('api.user_unautherized'), 401);
            }

        } catch (\Exception $e) {
            return $this->responseApiError($e->getMessage(), 500);
        }
    }

    /******************************************************************************/
    public function get_diet(Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');
            \App::setLocale($lang);

            if (auth('api')->check()) {
                $date = $request->date;
                $member_id = auth('api')->user()->member->id;
                $list_data = MemberDiet::where('date', $date)->where('members_id', $member_id)->get();
                if ($list_data) {
                    return $this->ResponseApi(MemberDietResource::collection($list_data), trans('api.list_type_unit'), 200);
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


    /****************************************************/
    public function check_validation_invite_code(Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');
            \App::setLocale($lang);

            $invite_code = $request->invite_code;
            $member = Members::where('invite_code', $invite_code)->first();

            if ($member) {

                $data = [
                    'MemberId' => $member->id,
                    'MemberName' => $member->member_name,
                    'InviteCodeFreeDays' => get_app_config_data('freedays'),
                    'InviteCodeDiscountPercentage' => get_app_config_data('freedays_discount'),

                ];

                return $this->ResponseApi($data, trans('api.success'), 200);
            } else {
                return $this->ResponseApi(null, trans('api.nodata'), 404);
            }
        } catch (\Exception $e) {
            return $this->responseApiError($e->getMessage(), 500);
        }
    }

    /****************************************************/
    public function add_freezing_day(Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');
            \App::setLocale($lang);

            $date = $request->date;
            //return $this->ResponseApi(auth('api')->user()->id, trans('api.this_day_already_exists'), 200);
            $member_id = auth('api')->user()->member->id;
            $current_subscriptions = MembersSubscriptions::with('main_subscriptions', 'additional_subscriptions', 'freezing_days')
                ->where('member_id', $member_id)
                ->where('end_date', '>', Carbon::now())
                ->orderBy('end_date', 'desc')
                ->first();


            if ($current_subscriptions) {
                $max_freezing_days = $current_subscriptions->main_subscriptions->max_freezing_days;
                $member_subscriptions_freezing_days = count($current_subscriptions->freezing_days);
                $old_freezing_data = [
                    'max_freezing_days' => $max_freezing_days,
                    'added_freezing_days' => $member_subscriptions_freezing_days,
                ];
               // return $this->ResponseApi($current_subscriptions->id, trans('api.success'), 200);
                if ($max_freezing_days > $member_subscriptions_freezing_days) {
                    $dayExists = MemberSubscriptionsFreezingDays::where('member_subscription_id', $current_subscriptions->id)
                        ->where('member_id', $member_id)
                        ->where('freezing_day', $date)
                        ->exists();
                 //   return $this->ResponseApi($dayExists, trans('api.this_day_already_exists'), 200);
                    if ($dayExists)
                    {
                        return $this->ResponseApi($date, trans('api.this_day_already_exists'), 200);
                    }

                    $data['member_subscription_id'] = $current_subscriptions->id;
                    $data['member_id'] = $member_id;
                    $data['freezing_day'] = $date;
                  //  $data['created_by'] = auth()->user('api')->id;
                    $saved_data = MemberSubscriptionsFreezingDays::create($data);
                    if ($saved_data) {
                        $return_data = [
                            'max_freezing_days' => $max_freezing_days,
                            'added_freezing_days' =>MemberSubscriptionsFreezingDays::where('member_subscription_id',$current_subscriptions->id)->where('member_id',$member_id)->count(),
                        ];
                        return $this->ResponseApi($return_data, trans('api.success'), 200);
                    } else {
                        return $this->ResponseApi(null, trans('api.failed'), 500);
                    }

                } else {
                    return $this->ResponseApi($old_freezing_data, trans('api.freezing_days_exceeded'), 200);
                }

            } else {
                return $this->ResponseApi(null, trans('api.nodata'), 404);
            }
        } catch (\Exception $e) {
            return $this->responseApiError($e->getMessage(), 500);
        }
    }
    /****************************************************/
    public function cancel_freezing_day(Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');
            \App::setLocale($lang);

            $date = $request->day;
            $member_id = auth('api')->user()->member->id;
            $dayExists = MemberSubscriptionsFreezingDays::where('member_id', $member_id)->where('freezing_day', $date);

            if ($dayExists->exists()) {
                $dayExists->delete();
                $current_subscriptions = MembersSubscriptions::where('member_id', $member_id)
                    ->where('end_date', '>', Carbon::now())
                    ->orderBy('end_date', 'desc')
                    ->first();
                $dayExists = MemberSubscriptionsFreezingDays::where('member_subscription_id', $current_subscriptions->id)
                    ->where('member_id', $member_id)
                    ->pluck('freezing_day');

                return $this->ResponseApi($dayExists, trans('api.success'), 200);
            } else {
                return $this->ResponseApi(null, trans('api.failed'), 500);
            }
        } catch (\Exception $e) {
            return $this->responseApiError($e->getMessage(), 500);
        }
    }
    /*------------------------------------------------------------------------*/

    public function get_member_notifications(Request $request)
    {
        try {
            $to_user_id =$request['user_id'];
            $perPage = $request->input('per_page', 20);

            //   $data = Notifications::with("from_user")->where('to_user_id', $to_user_id)->get()->toArray();
            $data = Notifications::with(['from_user' => function($query) {
                $query->select('id', 'user_name', 'gender', 'user_image');
            }])
                ->where('to_user_id', $to_user_id)
                ->paginate($perPage);

            //  return $this->responseApi($data, "1كل الاشعارات", 200);

            if (!empty($data)){
                $user_data =new Notificationscollection($data);

                return $this->responseApi($user_data, "1كل الاشعارات", 200);
            } else {
                return $this->responseApi(null, "   لااشعارات ", 205);
            }

        } catch (\Exception $e) {
            return $this->responseApiError($e->getMessage(), 500);

        }

    }




}
