<?php


namespace App\Traits;


trait ResponseApi
{
    function responseApi($content, $massage = null, $status = true)
    {
        $array = [
            'result' => $status,
            'message' => $massage,
            'data' => (object)$content
        ];
        return response()->json($array, 200);
    }

    /*********************************************************/
    function responseApi_v2($content, $massage = null, $status = true,$total_value)
    {
        $array = [
            'result' => $status,
            'message' => $massage,
            'data' => (object)$content,
            'total'=>$total_value,
        ];
        return response()->json($array, 200);
    }
    /*********************************************************/

    function responseApiError($massage = null, $status = false)
    {
        $array = [
            'result' => $status,
            'message' => $massage,
            'data' => (object)[]
        ];
        return response($array, 200);
    }
}
