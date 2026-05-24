<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\complaints;
use App\Traits\ImageProcessing;
use App\Traits\ValidationMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiComplaints extends Controller
{
    use ImageProcessing;
    use ApiResponse;
    use ValidationMessage;

    public function list_complains(Request $request)
    {

        try {
            $data_array = $request->all();
            $insert_data = complaints::create($data_array);

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

    public function Create_Complaints(Request $request)
    {

        try {

            $validated = $request->validate([
                'description' => 'required|string',
                'type' => 'required|string|in:suggestion,complaint',
                'Submitted_by' => 'required|string|in:member,trainer,employee',
            ]);


        } catch (\Illuminate\Validation\ValidationException $exception) {
            $erros = $this->customErrorRespons($exception->errors());
            $list_error = $erros['list_error'];
            $list_error_string = $erros['list_error_string'];
            return $this->responseApi($list_error, $list_error_string, 422);
        };
        try {
            $data_array = $request->all();
            $insert_data = complaints::create($data_array);

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


    /*************************************************************************** */

    public function Data()
    {
        try {

            $data = complaints::find(1);
            if ($data) {
                return $this->responseApi($data, 'تم الحفظ بنجاح', 201);
            } else {
                return $this->responseApi(null, 'لم يتم الحفظ', 400);
            }
        } catch
        (\Exception $e) {
            return $this->responseApiError($e->getMessage(), 500);

        }
    }


}
