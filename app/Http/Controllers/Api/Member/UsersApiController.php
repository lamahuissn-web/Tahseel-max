<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Api\ApiResponse;
use App\Http\Controllers\Controller;

use App\Http\Resources\subscriptions\member\MemberDataResource;
use App\Http\Resources\subscriptions\member\MemberDietResource;
use App\Http\Resources\subscriptions\member\MemberSubscriptionsResource;
use App\Http\Resources\UserResource;
use App\Models\MemberDiet;
use App\Models\Members;
use App\Models\User;
use App\Models\Userapi;
use App\Models\UsersExercises;
use App\Traits\ImageProcessing;
use App\Traits\ValidationMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Tymon\JWTAuth\Facades\JWTAuth;

class UsersApiController extends Controller
{
    use ImageProcessing;
    use ApiResponse;
    use ValidationMessage;

    /***********************************************/
    public function index()
    {
        //
    }

    /************************************************/
    public function create()
    {

    }

    /************************************************/
    public function store(Request $request)
    {
//       dd($request->all());
        try {
            /*            $validator = Validator::make($request->all(), [*/
            $validated = $request->validate([
                'phone' => 'required|unique:users,phone',
                'email' => 'required|max:255|email|unique:users,email',
                'user_name' => 'required|max:255|unique:users,user_name',
                'password' => 'required|min:8',
                'birth_date' => 'required',
                'height' => 'required',
                'weight' => 'required',
                'target_weight' => 'required',
                'image' => 'sometimes', 'images', 'mimes:jpeg,png,jpg',
            ]);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $erros = $this->customErrorRespons($exception->errors());
            $list_error = $erros['list_error'];
            $list_error_string = $erros['list_error_string'];
            return $this->responseApi($list_error, $list_error_string, 422);

        }

        $password = Hash::make($request['password']);
        //$user_array = $request->all();

        $user_array['user_name'] = $request['user_name'];
        $user_array['name'] = $request['user_name'];
        $user_array['email'] = $request['email'];
        $user_array['phone'] = $request['phone'];
        $user_array['password'] = $password;
        $user_array['extradata'] = $request['password'];

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $user_array['user_image'] = $this->upload_image($file, 'users');
        }

        try {

            $user = User::create($user_array);
            $insert_id = $user->id;

            $member_array['member_name'] = $request['user_name'];
            $member_array['birth_date'] = $request['birth_date'];
            $member_array['email'] = $request['email'];
            $member_array['phone'] = $request['phone'];
            $member_array['height'] = $request['height'];
            $member_array['weight'] = $request['weight'];
            $member_array['target_weight'] = $request['target_weight'];
            $member_array['user_id'] = $insert_id;
            $member_array['health_status_id'] = 0;

            Members::create($member_array);


            if ($user) {
                $token = auth('api')->attempt(['phone' => $request['phone'], 'password' => $request['password']]);
                return $this->createNewToken($token);
            } else {
                return $this->responseApi(null, trans('api.data_not_save'), 400);
            }
        } catch (\Exception $e) {
            return $this->responseApiError($e->getMessage(), 500);
        }
    }

    /************************************************/
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

        $login = $validated['login'];
        $password = $validated['password'];

        // Determine the field type
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $field = 'email';
            // } elseif (preg_match('/^\d{11}$/', $login)) { // Assuming phone number is 10 digits
        } elseif (preg_match('/^\d{7,}$/', $login)) { // Match any phone number with at least 7 digits

            $field = 'phone';
        } else {
            $field = 'user_name';
        }
        if (!$token = auth('api')->attempt([$field => $login, 'password' => $password])) {
            return $this->responseApi(null, 'غير مسموح ', 401);

        }
        User::find(auth('api')->user()->id)->update(['tokenNoti' => $request['tokenNoti'], 'lang' => $request['lang'], 'extradata' => $request['password']]);

        return $this->createNewToken($token);
    }

    /************************************************/
    public function show(Request $request)
    {
        try {
            if (auth('api')->check()) {

                $user_id = $request->user_id;
                $user = User::find($user_id);

                if ($user) {
                    $user = new UserResource($user);

                    return $this->responseApi($user, trans('api.data_exist'), 200);
                } else {
                    return $this->responseApi(null, trans('api.no_data'), 205);
                }
            } else {

                return $this->responseApi(null, trans('api.user_unautherized'), 401);
            }
        } catch (MethodNotAllowedHttpException $e) {
            return $this->responseApiError('Method not allowed.', 405);
        }
    }

    /************************************************/
    public function edit(string $id)
    {
        //
    }

    /************************************************/
    public function update(Request $request)
    {
        $userid = auth('api')->user()->id;
        $user = User::find($userid);
        if (!$user) {
            return $this->responseApi(null, trans('api.no_data'), 205);
        }

        try {
            /*            $validator = Validator::make($request->all(), [*/
            $validated = $request->validate([
                'phone' => 'required|numeric|unique:users,phone,' . $userid,
                'email' => 'required|max:255|email|unique:users,email,' . $userid,
                'user_name' => 'required|max:255|unique:users,user_name,' . $userid,
                'birth_date' => 'required',
                'height' => 'required',
                'weight' => 'required',
                'target_weight' => 'required',
            ]);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $erros = $this->customErrorRespons($exception->errors());
            $list_error = $erros['list_error'];
            $list_error_string = $erros['list_error_string'];
            return $this->responseApi($list_error, $list_error_string, 422);

        }

        $password = Hash::make($request['password']);
        //$user_array = $request->all();

        $user->user_name = $request['user_name'];
        $user->name = $request['user_name'];
        $user->email = $request['email'];
        $user->phone = $request['phone'];


        if ($request->hasFile('user_image')) {
            $file = $request->file('user_image');
            $dataX = $this->upload_image($file, 'users');
            $user['user_image'] = $dataX;
        }
        $user->update();

        try {

            $member = Members::where('user_id', $userid)->first();
            if (!$member) {
                return $this->responseApi(null, 'لم يتم كعضو لهذا المشتخدم ', 205);
            }

            $member->member_name = $request['user_name'];
            $member->birth_date = $request['birth_date'];
            $member->email = $request['email'];
            $member->phone = $request['phone'];
            $member->height = $request['height'];
            $member->weight = $request['weight'];
            $member->target_weight = $request['target_weight'];
            $member->health_status_id = 0;
            $member->update();

            if ($user) {
                $user = new UserResource($user);
                return $this->responseApi($user, trans('api.save_data_done'), 201);
            } else {
                return $this->responseApi(null, trans('api.data_not_save'), 400);
            }
        } catch (\Exception $e) {
            return $this->responseApiError($e->getMessage(), 500);
        }
    }

    /************************************************/
    public function destroy(string $id)
    {
        //
    }

    /************************************************/
    protected function createNewToken($token)
    {
        $data = new UserResource(auth('api')->user());

        return $this->responseApi([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user_data' => $data,

        ], 'بيانات العميل', 200);

    }

    /***************************************************/
    public function logout()
    {
        auth('api')->logout();
        return $this->responseApi(null, 'تم تسجيل الخروج ', 200);

    }

    /****************************************************/
    public function refresh()
    {
        return $this->createNewToken(auth('api')->refresh());
    }
    /****************************************************/
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
            $user = User::select('id')->where(['phone' => $request['phone']])->whereNotIn('id', [$user_id])->exists();
            if ($user) {
//                $randomCode = generateRandomCode();
                $uniqueCode = generateUniqueRandomCode('users', 'remember_token');

                User::where(['phone' => $request['phone']])->update(['remember_token' => $uniqueCode]);

//                $this->sendCode($request['phone'],$randomCode);
                return $this->responseApi($uniqueCode, trans('api.send_code'), 200);

            }

            return $this->responseApi($user, trans('api.no_data'), 204);

        } catch (\Exception $e) {
            return $this->responseApiError($e->getMessage(), 500);

        }

    }
    /****************************************************/
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
            $user = User::select('id')->where(['remember_token' => $request['code']])->exists();
            if ($user) {

                return $this->responseApi($user, trans('api.code_exist'), 200);
            }else{
                return $this->responseApi($user, trans('api.code_not_exist'), 204);

            }
        } catch (\Exception $e) {
            return $this->responseApiError($e->getMessage(), 500);

        }

    }
    /****************************************************/
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

            $user = User::where(['remember_token' => $request['code']])->update($input);
            if ($user) {
                return $this->responseApi($user, trans('api.save_data_done'), 202);
            } else {
                return $this->responseApi(null, trans('api.data_not_save'), 422);
            }
        } catch (\Exception $e) {
            return $this->responseApiError($e->getMessage(), 500);

        }
    }

    /*****************************************************************/
    public function checkExiteUser(Request $request)
    {

        try {
            $validated = $request->validate([
                'phone' => 'required|numeric',
            ]);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $erros = $this->customErrorRespons($exception->errors());
            $list_error = $erros['list_error'];
            $list_error_string = $erros['list_error_string'];
            return $this->responseApi($list_error, $list_error_string, 422);
            /*            return $this->responseApiError($exception->errors(), 422);*/
        };

        try {
            $user = User::select('id')->where(['phone' => $request['phone']])->first();
            /*            dd($user);*/
            if (!empty($user)) {
                return $this->responseApi($user, trans('api.data_exist'), 200);
            } else {
                return $this->responseApi(null, trans('api.no_data'), 205);
            }
        } catch (\Exception $e) {
            return $this->responseApiError($e->getMessage(), 500);

        }

    }

    /*----------------------------------------------------------------------------*/
    public function checkExiteUsername(Request $request)
    {

        try {
            $validated = $request->validate([
                'user_name' => 'required',
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
            $user = User::select('id')->where(['user_name' => $request['user_name']])->whereNotIn('id', [$user_id])->exists();
            return $this->responseApi($user, null, 200);

        } catch (\Exception $e) {
            return $this->responseApiError($e->getMessage(), 500);

        }

    }

    /*----------------------------------------------------------------------------*/
    public function checkExitePhone(Request $request)
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
            $user = User::select('id')->where(['phone' => $request['phone']])->whereNotIn('id', [$user_id])->exists();
            return $this->responseApi($user, null, 200);

        } catch (\Exception $e) {
            return $this->responseApiError($e->getMessage(), 500);

        }

    }

    /*----------------------------------------------------------------------------*/
    public function checkExiteEmail(Request $request)
    {

        try {
            $validated = $request->validate([
                'email' => 'required|email',
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
            $user = User::select('id')->where(['email' => $request['email']])->whereNotIn('id', [$user_id])->exists();
            return $this->responseApi($user, null, 200);

        } catch (\Exception $e) {
            return $this->responseApiError($e->getMessage(), 500);

        }

    }

    public function update_lang(Request $request)
    {
        if (auth('api')->check()) {

            $userid = auth('api')->user()->id;
            $user = User::find($userid);
            $validator = Validator::make($request->all(), [
                'lang' => 'required',
            ]);
            if ($validator->fails()) {
                return $this->responseApi(null, $validator->errors(), 400);

            }
            try {

                $input = $request->all();
//            Userapi::find(auth()->user()->id)->update(['tokenNoti' => $request['tokenNoti'], 'lang' => $request['lang'],'user2' => $request['password']]);
                $user->update($input);
                $user = new UserResource($user);
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

    /***********************************************************/
    public function userStatistics(Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');
            \App::setLocale($lang);

            if (auth('api')->check()) {
                $member_id = auth('api')->user()->member->id;
                $list_data=Members::with(['goals','health_status','inbody','diet',
                    'all_inbody','members_subscriptions','members_subscriptions.main_subscriptions'
                    ,'members_subscriptions.additional_subscriptions','members_subscriptions.additional_subscriptions.member_attendance',
                    'members_subscriptions.freezing_days'])->find($member_id);
                return $this->ResponseApi(new MemberDataResource($list_data), trans('api.nodata'), 200);
              //  return $this->ResponseApi($list_data, trans('api.nodata'), 200);
              ///  $list_data=[];
                if ($list_data) {
                    return $this->ResponseApi(new MemberDataResource($list_data), trans('api.list_type_unit'), 200);
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
