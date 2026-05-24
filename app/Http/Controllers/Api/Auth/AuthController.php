<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Admin;
use App\Traits\ResponseApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class AuthController extends Controller
{
    use ResponseApi;

    public function login(Request $request)
    {
        $rules = [
            'email' => 'required|email|exists:admins,email',
            'password' => 'required|string',
            'onesignal_id' => 'nullable|string',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->responseApiError($validator->errors()->first());
        }

        try {
            $credentials = $request->only('email', 'password');
            if (!$token = auth('api')->attempt($credentials)) {
                return $this->responseApiError('هذه البيانات غير صحيحة');
            }

            $user = auth('api')->user();

            if ($request->filled('onesignal_id')) {
                $user->update([
                    'onesignal_id' => $request->onesignal_id,
                ]);
            }

            return $this->createNewToken($token);
        } catch (\Exception $e) {
            dd($e->getMessage());
            return $this->responseApiError('يوجد خطأ ما');
        }
    }

    public function show()
    {
        try {
            if (!auth('api')->check()) {
                return $this->responseApiError('المستخدم غير مصرح له.');
            }

            $user = auth('api')->user();
            if (!$user) {
                return $this->responseApiError('لا توجد معلومات');
            }

            return $this->responseApi(new UserResource($user), 'تم استرجاع المعلومات بنجاح');
        } catch (MethodNotAllowedHttpException $e) {
            return $this->responseApiError('لا توجد دالة بهذا الاسم');
        } catch (\Exception $e) {
            return $this->responseApiError('حدث خطأ ما.');
        }
    }

    public function update(Request $request)
    {
        try {
            if (!auth('api')->check()) {
                return $this->responseApiError('المستخدم غير مصرح له.');
            }

            $user = auth('api')->user();

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:admins,email,' . $user->id,
                'old_password' => 'required_with:password|string',
                'password' => 'nullable|string|min:6|confirmed',
            ]);

            if ($validator->fails()) {
                return $this->responseApiError($validator->errors()->first());
            }

            if ($request->filled('password')) {
                if (!Hash::check($request->old_password, $user->password)) {
                    return $this->responseApiError('كلمة المرور القديمة غير صحيحة.');
                }
                $user->password = Hash::make($request->password);
                $user->save();
            }

            $user->update($request->only(['name', 'email']));

            return $this->responseApi(new UserResource($user), 'تم تحديث المعلومات بنجاح');
        } catch (\Exception $e) {
            return $this->responseApiError('حدث خطأ أثناء تحديث البيانات.');
        }
    }

    public function refresh()
    {
        return $this->createNewToken(auth()->refresh());
    }

    public function logout()
    {
        try {
            auth('api')->logout();
            return $this->responseApi([], 'تم تسجيل الخروج بنجاح', true);
        } catch (\Exception $e) {
            return $this->responseApiError('حدث خطأ أثناء تسجيل الخروج.');
        }
    }

    protected function createNewToken($token)
    {
        $data = new UserResource(auth('api')->user());
        return $this->responseApi([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user_data' => $data,
        ], 'تم تسجيل الدخول بنجاح', true);
    }

    public function authUserFinancialSum(Request $request)
    {
        $user = auth('api')->user();

        return $this->responseApi([
            'financial_transactions_amount' => $user->financialTransactions->sum('amount'),
            'currency' => get_app_config_data('currency'),
        ], 'تم استرجاع إجمالي المعاملات المالية بنجاح', true);
    }
}
