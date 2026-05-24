<?php


namespace App\Http\Controllers\Api;


trait ApiResponse
{
    function responseApi($content = null, $massage = null, $status = null)
    {
        $array = [
            'result' => $status,
            'message' => $massage,
            'data' => $content
        ];
        return response()->json($array, $status);
    }

    function responseApiError($massage = null, $status = null)
    {


        $array = [
            'result' => $status,
            'message' => $massage
        ];
        return response($array, $status);
    }



}
