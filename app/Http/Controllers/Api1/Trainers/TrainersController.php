<?php

namespace App\Http\Controllers\Api\Trainers;

use App\Http\Controllers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\MembersResource;
use App\Http\Resources\mobile\MemberInbodyResource;
use App\Http\Resources\mobile\Notificationscollection;
use App\Http\Resources\mobile\TrainerMembersResource;

use App\Http\Resources\subscriptions\member\MemberDietResource;
use App\Http\Resources\subscriptions\TrainerResource;
use App\Models\AdditionalMemberSubscriptions;
use App\Models\MemberDiet;
use App\Models\Members;
use App\Models\MembersInbody;
use App\Models\MembersSubscriptions;
use App\Models\Notifications;
use App\Models\Trainers;
use App\Models\User;
use App\Traits\ImageProcessing;
use App\Traits\ValidationMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Tymon\JWTAuth\Facades\JWTAuth;

class TrainersController extends Controller
{
    use ImageProcessing;
    use ApiResponse;
    use ValidationMessage;

    public function login_user(Request $request)
    {

        try {
            $validated = $request->validate([
                'login' => 'required|string', // This can be email, username, or phone
                'password' => 'required|string|min:6',
            ]);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $erros = $this->customErrorRespons($exception->errors());
            $list_error = $erros['list_error'];
            $list_error_string = $erros['list_error_string'];
            return $this->responseApi($list_error, $list_error_string, 422);
            /*            return $this->responseApiError($exception->errors(), 422);*/
        };
//dd($request->all());
        $login = $validated['login'];
        $password = $validated['password'];
        $field = 'user_name';

        /*// Determine the field type
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $field = 'email';
            // } elseif (preg_match('/^\d{11}$/', $login)) { // Assuming phone number is 10 digits
        } elseif (preg_match('/^\d{7,}$/', $login)) { // Match any phone number with at least 7 digits

            $field = 'phone';
        } else {
            $field = 'user_name';
        }*/
        if (!$token = auth('trainer')->attempt([$field => $login, 'password' => $password])) {
            return $this->responseApi(null, 'غير مسموح ', 401);

        }
        Trainers::find(auth('trainer')->user()->id)->update(['tokenNoti' => $request['tokenNoti'], 'lang' => $request['lang'], 'extradata' => $request['password']]);

        return $this->createNewToken($token);
    }

    /************************************************/
    public function show(Request $request)
    {
        try {
            if (auth('trainer')->check()) {

                $user_id = $request->user_id;
                $user = Trainers::find($user_id);

                if ($user) {
                    $user = new TrainerResource($user);

                    return $this->responseApi($user, 'المستخدم موجود', 200);
                } else {
                    return $this->responseApi(null, 'لم يتم تسجيل المستخدم', 205);
                }
            } else {

                return $this->responseApi(null, trans('api.user_unautherized'), 401);
            }
        } catch (MethodNotAllowedHttpException $e) {
            return $this->responseApiError('Method not allowed.', 405);
        }
    }

    protected function createNewToken($token)
    {
        $data = new TrainerResource(auth('trainer')->user());

        return $this->responseApi([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('trainer')->factory()->getTTL() * 60,
            'user_data' => $data,

        ], 'بيانات العميل', 200);

    }

    public function update(Request $request)
    {
        $userid = auth('trainer')->user()->id;
        $user = Trainers::find($userid);
        if (!$user) {
            return $this->responseApi(null, 'لم يتم تسجيل المستخدم', 205);
        }

        try {
            /*            $validator = Validator::make($request->all(), [*/
            $validated = $request->validate([
                'user_name' => 'required|max:255|unique:trainers,user_name,' . $userid,
                'phone' => 'required|numeric|unique:trainers,phone,' . $userid,
                'email' => 'required|max:255|email|unique:trainers,email,' . $userid,
                'birthday' => 'required',
                'job_title' => 'required',
            ]);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $erros = $this->customErrorRespons($exception->errors());
            $list_error = $erros['list_error'];
            $list_error_string = $erros['list_error_string'];
            return $this->responseApi($list_error, $list_error_string, 422);

        }

        try {
            $user->user_name = $request['user_name'];
            $user->email = $request['email'];
            $user->phone = $request['phone'];
            $user->birthday = $request['birthday'];
            $user->job_title = $request['job_title'];


            if ($request->hasFile('user_image')) {
                $file = $request->file('user_image');
                $dataX = $this->upload_image($file, 'trainers');
                $user['user_image'] = $dataX;
            }
            $user->update();
            if ($user) {
                $user = new TrainerResource($user);
                return $this->responseApi($user, trans('api.save_data_done'), 201);
            } else {
                return $this->responseApi(null, trans('api.data_not_save'), 400);
            }
        } catch (\Exception $e) {
            return $this->responseApiError($e->getMessage(), 500);
        }
    }

    public function logout()
    {
        auth('trainer')->logout();
        return $this->responseApi(null, 'تم تسجيل الخروج ', 200);

    }

    public function refresh()
    {
        return $this->createNewToken(auth('trainer')->refresh());
    }

    public function refreshToken()
    {
        try {
            $newToken = JWTAuth::parseToken()->refresh();
            /*            return response()->json(['token' => $newToken]);*/
            return $this->responseApi(['token' => $newToken], 'new token', 200);

        } catch (MethodNotAllowedHttpException $e) {
            return $this->responseApiError('Method not allowed.', 405);
        }
    }

    /****************************************************/

    public function sendCodePhone(Request $request)
    {

        try {
            $validated = $request->validate([
                'phone' => 'required',
            ]);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $erros = $this->customErrorRespons($exception->errors());
            $list_error = $erros['list_error'];
            $list_error_string = $erros['list_error_string'];
            return $this->responseApi($list_error, $list_error_string, 422);
        };
        try {
            $user_id = '';
            if ($request['user_id']) {
                $user_id = $request['user_id'];
            }
            $user = Trainers::select('id')->where(['phone' => $request['phone']])->whereNotIn('id', [$user_id])->exists();
            if ($user) {
//                $randomCode = generateRandomCode();
                $uniqueCode = generateUniqueRandomCode('trainers', 'remember_token');

                Trainers::where(['phone' => $request['phone']])->update(['remember_token' => $uniqueCode]);

//                $this->sendCode($request['phone'],$randomCode);
                return $this->responseApi($uniqueCode, trans('api.send_code'), 200);

            }

            return $this->responseApi($user, trans('api.no_data'), 204);

        } catch (\Exception $e) {
            return $this->responseApiError($e->getMessage(), 500);

        }

    }

    public function checkExiteCode(Request $request)
    {

        try {
            $validated = $request->validate([
                'code' => 'required',
            ]);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $erros = $this->customErrorRespons($exception->errors());
            $list_error = $erros['list_error'];
            $list_error_string = $erros['list_error_string'];
            return $this->responseApi($list_error, $list_error_string, 422);
        };
        try {
            $user_id = '';
            if ($request['user_id']) {
                $user_id = $request['user_id'];
            }
            $user = Trainers::select('id')->where(['remember_token' => $request['code']])->exists();
            if ($user) {

                return $this->responseApi($user, trans('api.code_exist'), 200);
            } else {
                return $this->responseApi($user, trans('api.code_not_exist'), 204);

            }

        } catch (\Exception $e) {
            return $this->responseApiError($e->getMessage(), 500);

        }

    }

    public function update_password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:8|confirmed',
            'code' => 'required|min:6',
        ]);
        if ($validator->fails()) {
            return $this->responseApi(null, $validator->errors(), 422);

        }

        $input['password'] = Hash::make($request['password']);
        $input['extradata'] = $request['password'];
        try {

            $user = Trainers::where(['remember_token' => $request['code']])->update($input);
            if ($user) {
                return $this->responseApi($user, trans('api.save_data_done'), 202);
            } else {
                return $this->responseApi(null, trans('api.data_not_save'), 422);
            }
        } catch (\Exception $e) {
            return $this->responseApiError($e->getMessage(), 500);

        }
    }

    public function update_lang(Request $request)
    {
        if (auth('trainer')->check()) {

            $userid = auth('trainer')->user()->id;
            $user = Trainers::find($userid);
            $validator = Validator::make($request->all(), [
                'lang' => 'required',
            ]);
            if ($validator->fails()) {
                return $this->responseApi(null, $validator->errors(), 422);

            }
            try {

                $input = $request->all();
//            Userapi::find(auth()->user()->id)->update(['tokenNoti' => $request['tokenNoti'], 'lang' => $request['lang'],'user2' => $request['password']]);
                $user->update($input);
                $user = new TrainerResource($user);
                if ($user) {
                    return $this->responseApi($user, trans('api.save_data_done'), 201);
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

    function list_member()
    {
        try {
            if (auth('trainer')->check()) {
                $list_data = AdditionalMemberSubscriptions::with(['member_subscription.member.goals', 'member_subscription.member.health_status', 'member_subscription.member.latest_inbody'])->where('trainer_id', auth('trainer')->user()->id)->get();
                if ($list_data->isNotEmpty()) {
                    $members = $list_data->map(function ($subscription) {
                        return $subscription->member_subscription->member;
                    })->flatten();

                    return $this->ResponseApi(MembersResource::collection($members), trans('api.list_member'), 200);
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

    /**********************************************/
    function list_member_new()
    {
        try {
            if (auth('trainer')->check()) {
                $list_data = AdditionalMemberSubscriptions::where('trainer_id', auth('trainer')->user()->id)->select('member_subscription_id')->distinct('member_subscription_id')->get();

                //return $this->ResponseApi(new TrainerMembersResource($list_data), trans('api.nodata'), 200);

                if ($list_data->isNotEmpty()) {

                    return $this->ResponseApi(TrainerMembersResource::collection($list_data), trans('api.list_member'), 200);
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

    /**********************************************/

    public function add_member_diet(Request $request)
    {
        try {

            if (auth('trainer')->check()) {

                $validated = $request->validate([
                    // 'date' => 'required',
                    // 'member_id' => 'required|integer|exists:members,id',
                    'diet_name' => 'required|string',
                    'daily_share' => 'required|string',
                    'break_fast_choice1' => 'required|string',
                    'break_fast_choice2' => 'nullable|string',
                    'snake_choice1' => 'required|string',
                    'snake_choice2' => 'nullable|string',
                    'before_training_choice1' => 'required|string',
                    'before_training_choice2' => 'nullable|string',
                    'after_training_choice1' => 'required|string',
                    'after_training_choice2' => 'nullable|string',
                    'dinner_choice1' => 'required|string',
                    'dinner_choice2' => 'nullable|string',
                    'lunch_choice1' => 'nullable|string',
                    'lunch_choice2' => 'nullable|string',
                ]);
                $date = $request->date;
                $trainers_id = auth('trainer')->user()->id;
                $data['trainers_id'] = $trainers_id;
                $data['members_id'] = $request->member_id;
                $data['date'] = $request->date;
                $data['break_fast_choice1'] = $request->break_fast_choice1;
                $data['break_fast_choice2'] = $request->break_fast_choice2;
                $data['snake_choice1'] = $request->snake_choice1;
                $data['snake_choice2'] = $request->snake_choice2;
                $data['before_training_choice1'] = $request->before_training_choice1;
                $data['before_training_choice2'] = $request->before_training_choice2;
                $data['after_training_choice1'] = $request->after_training_choice1;
                $data['after_training_choice2'] = $request->after_training_choice2;
                $data['dinner_choice1'] = $request->dinner_choice1;
                $data['dinner_choice2'] = $request->dinner_choice2;
                $data['lunch_choice1'] = $request->lunch_choice1;
                $data['lunch_choice2'] = $request->lunch_choice2;
                $data['diet_name'] = $request->diet_name;
                $data['daily_share'] = $request->daily_share;
                $list_data = MemberDiet::create($data);


                // $list_data = MemberDiet::where('date',$request->date)->where('trainers_id',$trainers_id)->get();
                if ($list_data) {
                    $to_user_id=$request->member_id;
                    $member = Members::find($request->member_id);
                    $to_user_name = $member ? $member->member_name : null;
                    $from_user_id=auth('trainer')->user()->id;
                    $from_user_name=auth('trainer')->user()->user_name;
                    $lang=User::find($member->user_id)->lang;
                    $data_not = [0 => $lang];
                    $extra_data=[0=>$request->member_id];
                    $status = 'unread';
                    $type = 'diet';
                    $token=User::find($member->user_id)->tokenNoti;
                    $code = 1;
                    //dd($from_user_name);
                    send_notifications($to_user_id, $to_user_name, $from_user_id, $from_user_name, $data_not, $extra_data, $status, $type, $token, $code);
                    return $this->ResponseApi(new MemberDietResource($list_data), trans('api.list_type_unit'), 200);
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

    /**********************************************/
    public function search_members(Request $request)
    {

        try {
            $validated = $request->validate([
                'member_name' => 'required',
            ]);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $erros = $this->customErrorRespons($exception->errors());
            $list_error = $erros['list_error'];
            $list_error_string = $erros['list_error_string'];
            return $this->responseApi($list_error, $list_error_string, 422);
        };
        try {
            $lang = $request->header('lang', 'en');
            \App::setLocale($lang);

            if (auth('trainer')->check()) {
                $member_name = $request->member_name;
                $list_data = AdditionalMemberSubscriptions::where('trainer_id', auth('trainer')->user()->id)
                    ->select('member_subscription_id')
                    ->distinct('member_subscription_id')
                    ->when($member_name, function ($query) use ($member_name) {
                        return $query->whereHas('member_subscription', function ($q) use ($member_name) {
                            $q->whereHas('member', function ($q) use ($member_name) {
                                $q->where('member_name', 'LIKE', '%' . $member_name . '%');
                            });
                        });
                    })->get();

                if ($list_data->isNotEmpty()) {

                    return $this->ResponseApi(TrainerMembersResource::collection($list_data), trans('api.member_data'), 200);
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

    /*****************************************************/
    public function get_member_data(Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');
            \App::setLocale($lang);

            if (auth('trainer')->check()) {
                $member_id = $request->member_id;
                $list_data = Members::find($member_id);
                if ($list_data) {
                    return $this->ResponseApi(new MembersResource($list_data), trans('api.member_data'), 200);
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

    /*******************************************************/
    public function update_member_inbody_data(Request $request)
    {
        try {
            $validated = $request->validate([
                'member_id' => 'required|numeric',
                'height' => 'required|numeric',
                'weight' => 'required|numeric',
                'fat_percentage' => 'required|numeric|between:0,100',
                'muscle_mass_percentage' => 'required|numeric|between:0,100',
                'body_status' => 'required|string|max:255',
            ]);
            $height = $validated['height'];
            $member_id = $validated['member_id'];
            $weight = $validated['weight'];
            $fatPercentage = $validated['fat_percentage'];
            $muscleMassPercentage = $validated['muscle_mass_percentage'];
            $bodyStatus = $validated['body_status'];
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $erros = $this->customErrorRespons($exception->errors());
            $list_error = $erros['list_error'];
            $list_error_string = $erros['list_error_string'];
            return $this->responseApi($list_error, $list_error_string, 422);
        };
        try {
            $lang = $request->header('lang', 'en');
            \App::setLocale($lang);
            if (auth('trainer')->check()) {
                $check_member = $this->check_trainer_member($member_id, auth('trainer')->user()->id);
                if ($check_member) {
                    $data['date'] = date('Y-m-d');
                    $data['member_id'] = $member_id;
                    $data['height'] = $height;
                    $data['weight'] = $weight;
                    $data['fat_percentage'] = $fatPercentage;
                    $data['muscle_mass_percentage'] = $muscleMassPercentage;
                    $data['body_status'] = $bodyStatus;
                    $data_list = MembersInbody::create($data);

                    if ($data_list) {

                        $to_user_id=$member_id;
                        $member = Members::find($member_id);
                        $to_user_name = $member ? $member->member_name : null;
                        $from_user_id=auth('trainer')->user()->id;
                        $from_user_name=auth('trainer')->user()->user_name;
                        $lang=User::find($member->user_id)->lang;
                        $data_not = [0 => $lang];
                        $extra_data=[0=>$member_id];
                        $status = 'unread';
                        $type = 'inbody';
                        $token=User::find($member->user_id)->tokenNoti;
                        $code = 2;
                        //dd($from_user_name);
                        send_notifications($to_user_id, $to_user_name, $from_user_id, $from_user_name, $data_not, $extra_data, $status, $type, $token, $code);


                        return $this->responseApi(new MemberInbodyResource($data_list), trans("api.savedone"), 200);
                    } else {
                        return $this->ResponseApi(null, trans('api.nodata'), 200);
                    }

                } else {
                    return $this->responseApi(null, trans("api.this_is_not_trainer's_member"), 401);
                }

            } else {
                return $this->responseApi(null, trans('api.user_unautherized'), 401);
            }

        } catch (\Exception $e) {
            return $this->responseApiError($e->getMessage(), 500);
        }
    }

    /*************************************************/
    public function check_trainer_member($member_id, $trainer_id)
    {
        $member_subscriptions = MembersSubscriptions::where('member_id', $member_id)->pluck('id')->toArray();
        $trainer_member_subscription_ids = AdditionalMemberSubscriptions::where('trainer_id', $trainer_id)
            ->pluck('member_subscription_id')
            ->toArray();
        return count(array_intersect($member_subscriptions, $trainer_member_subscription_ids)) > 0;


    }

    /********************************************************/
    public function update_member_diet_data(Request $request)
    {
        try {
            $validated = $request->validate([
                'member_id' => 'required|numeric',
                'date' => 'required',
                'break_fast_choice1' => 'required',
                'break_fast_choice2' => 'required',
                'snack_choice1' => 'required',
                'snack_choice2' => 'required',
                'lunch_choice1' => 'required',
                'lunch_choice2' => 'required',
                'before_training_choice1' => 'required',
                'before_training_choice2' => 'required',
                'after_training_choice1' => 'required',
                'after_training_choice2' => 'required',
                'dinner_choice1' => 'required',
                'dinner_choice2' => 'required',
                'daily_share' => 'required',
                'diet_name' => 'required',
            ]);

            $member_id = $validated['member_id'];
            $date = $validated['date'];
            $break_fast_choice1 = $validated['break_fast_choice1'];
            $break_fast_choice2 = $validated['break_fast_choice2'];
            $snake_choice1 = $validated['snack_choice1'];
            $snake_choice2 = $validated['snack_choice2'];
            $lunch_choice1 = $validated['lunch_choice1'];
            $lunch_choice2 = $validated['lunch_choice2'];
            $before_training_choice1 = $validated['before_training_choice1'];
            $before_training_choice2 = $validated['before_training_choice2'];
            $after_training_choice1 = $validated['after_training_choice1'];
            $after_training_choice2 = $validated['after_training_choice2'];
            $dinner_choice1 = $validated['dinner_choice1'];
            $dinner_choice2 = $validated['dinner_choice2'];
            $diet_name = $validated['diet_name'];
            $daily_share = $validated['daily_share'];
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $erros = $this->customErrorRespons($exception->errors());
            $list_error = $erros['list_error'];
            $list_error_string = $erros['list_error_string'];
            return $this->responseApi($list_error, $list_error_string, 422);
        }

        try {
            $lang = $request->header('lang', 'en');
            \App::setLocale($lang);
            if (auth('trainer')->check()) {
                $check_member = $this->check_trainer_member($member_id, auth('trainer')->user()->id);
                if ($check_member) {
                    $member_diet = MemberDiet::where('members_id', $member_id)->where('trainers_id', auth('trainer')->user()->id)->first();

                    if ($member_diet) {

                        $member_diet->date = $date;

                        $member_diet->break_fast_choice1 = $break_fast_choice1;
                        $member_diet->break_fast_choice2 = $break_fast_choice2;
                        $member_diet->snake_choice1 = $snake_choice1;
                        $member_diet->snake_choice2 = $snake_choice2;
                        $member_diet->lunch_choice1 = $lunch_choice1;
                        $member_diet->lunch_choice2 = $lunch_choice2;
                        $member_diet->before_training_choice1 = $before_training_choice1;
                        $member_diet->before_training_choice2 = $before_training_choice2;
                        $member_diet->after_training_choice1 = $after_training_choice1;
                        $member_diet->after_training_choice2 = $after_training_choice2;
                        $member_diet->dinner_choice1 = $dinner_choice1;
                        $member_diet->dinner_choice2 = $dinner_choice2;
                        $member_diet->diet_name = $diet_name;
                        $member_diet->daily_share = $daily_share;
                        $data_list = $member_diet->update();

                        $member_diet = MemberDiet::where('members_id', $member_id)->where('trainers_id', auth('trainer')->user()->id)->first();

                        $to_user_id=$validated['member_id'];
                        $member = Members::find($validated['member_id']);
                        $to_user_name = $member ? $member->member_name : null;
                        $from_user_id=auth('trainer')->user()->id;
                        $from_user_name=auth('trainer')->user()->user_name;
                        $lang=User::find($member->user_id)->lang;
                        $data_not = [0 => $lang];
                        $extra_data=[0=>$validated['member_id']];
                        $status = 'unread';
                        $type = 'diet';
                        $token=User::find($member->user_id)->tokenNoti;
                        $code = 1;
                        //dd($from_user_name);
                        send_notifications($to_user_id, $to_user_name, $from_user_id, $from_user_name, $data_not, $extra_data, $status, $type, $token, $code);

                        return $this->ResponseApi($member_diet, trans('api.update_done'), 200);


                    } else {
                        return $this->ResponseApi(null, trans('api.nodata'), 200);
                    }

                } else {
                    return $this->responseApi(null, trans("api.this_is_not_trainer's_member"), 401);
                }


            } else {
                return $this->responseApi(null, trans('api.user_unautherized'), 401);
            }


        } catch (\Exception $e) {
            return $this->responseApiError($e->getMessage(), 500);
        }
    }

    /**************************************************************/
    public function get_diet(Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');
            \App::setLocale($lang);

            if (auth('trainer')->check()) {
                $member_id = $request->member_id;
                $trainer_id = auth('trainer')->user()->id;
                $date = $request->date;
               // return $this->ResponseApi($member_id, trans('api.member_data'), 200);

                $check_member = $this->check_trainer_member($member_id, $trainer_id);
                if ($check_member) {
                    $list_data = MemberDiet::Where('members_id',$member_id)->where('trainers_id',$trainer_id)->where('date',$date)->get();
                   // return $this->ResponseApi($list_data, trans('api.nodata'), 200);
                    if ($list_data) {
                        return $this->ResponseApi(MemberDietResource::collection($list_data), trans('api.member_data'), 200);
                    } else {
                        return $this->ResponseApi(null, trans('api.nodata'), 200);
                    }
                } else {
                    return $this->responseApi(null, trans("api.this_is_not_trainer's_member"), 401);
                }


            } else {
                return $this->responseApi(null, trans('api.user_unautherized'), 401);
            }

        } catch (\Exception $e) {
            return $this->responseApiError($e->getMessage(), 500);
        }
    }

    /****************************************************/
    public function get_user_notifications(Request $request)
    {
        try {
            $to_user_id =$request['user_id'];
            $perPage = $request->input('per_page', 20);

            //   $data = Notifications::with("from_user")->where('to_user_id', $to_user_id)->get()->toArray();
            $data = Notifications::with(['from_user_trainer' => function($query) {
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
    /*------------------------------------------------------------------------*/
    public function update_notification_read(Request $request)
    {
        try {
            $notification_id =$request['notification_id'];
            $notification = Notifications::find($notification_id);
            $data['status']='read';
            $result= $notification->update($data);
            if (!empty($result)) {
                return $this->responseApi($result, "كل الاشعارات", 200);
            } else {
                return $this->responseApi(null, "   لااشعارات ", 205);
            }

        } catch (\Exception $e) {
            return $this->responseApiError($e->getMessage(), 500);

        }
    }
}
