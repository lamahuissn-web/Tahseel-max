<?php


namespace App\Traits;


trait ValidationMessage
{

    function customErrorRespons($errors)
    {

        $list_error = [];
        $list_error_string = '';
        foreach ($errors as $field => $errorMessages) {
            foreach ($errorMessages as $errorMessage) {
                $list_error[] = "$field: $errorMessage";
                $list_error_string .= "$field: $errorMessage,";
            }
        }

        return array('list_error' => $list_error, 'list_error_string' => $list_error_string);
    }
    /*---------------------------------------------------*/
    function customErrorAjax($errors)
    {

        $list_error = [];
        $list_error_string = [];
        foreach ($errors as $field => $errorMessages) {
            foreach ($errorMessages as $errorMessage) {
                $list_error_string[$field] = $errorMessage;
//                $list_error[]= $field;
            }
        }

//        return array('list_error' => $list_error, 'list_error_string' => $list_error_string);
   return $list_error_string;
    }
}
