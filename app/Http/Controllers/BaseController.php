<?php

namespace App\Http\Controllers;

use App\Traits\ResponseHandler;

class BaseController extends Controller
{
    use ResponseHandler;

    public function __construct()
    {
        //
    }

    public function apiResponse($data = null, $message = null, $status_code = 200, $additional_data = null)
    {
        return response()->json([
            'status_code'       => is_array($status_code) ? $status_code[1] : $status_code,
            'message'           => $message ?: trans('messages.success'),
            'data'              => $data,
            'additional_data'   => $additional_data,
        ]);
    }

    public function apiErrorResponse($message = null, $status_code = 400)
    {
        return response()->json([
            'status_code'       => is_array($status_code) ? $status_code[1] : $status_code,
            'message'           => $message ?: trans('messages.bad_request'),
        ], is_array($status_code) ? $status_code[0] : $status_code);
    }
}
