<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait ResponseHandler
{
    /**
     * @var int
     */
    protected $statusCode = 200;

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * @param  string  $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondNotFound($message = null)
    {
        $message = $message ?? trans('messages.not_found');

        return $this->setStatusCode(404)->respondWithError($message);
    }

    /**
     * @param  string  $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondInternalError($message = null)
    {
        $message = $message ?? trans('messages.internal_server_error');

        return $this->setStatusCode(500)->respondWithError($message);
    }

    /**
     * @param  array  $data
     * @param  array  $headers
     * @return \Illuminate\Http\JsonResponse
     */
    public function respond($data = [], $headers = [])
    {
        $data['status'] = $this->isSuccess();

        if (isset($data['meta']) && isset($data['meta']['message'])) {
            $data['meta']['message'] = $this->formattedMessage($data['meta']['message']);
        }

        return response()->json($data, $this->getStatusCode(), $headers);
    }

    /**
     * @param  array  $metaData
     * @return mixed
     */
    public function respondResource($resource, $metaData = [], $status = 200, $message = null, $additional_data = [])
    {
        $message ?? trans('messages.success');

        DB::commit();

        return optional($resource)->additional([
            'status_code'     => $this->statusCode,
            'message'         => $message,
            'additional_data' => array_merge($resource->additional, $additional_data),
        ] + (empty($metaData) ? [] : ['meta' => $metaData]))
            ?: response([
                'status_code'     => $this->statusCode,
                'message'         => $message,
                'data'            => [],
                'success'         => $this->isSuccess(),
                'meta'            => array_merge($metaData, [
                    'message' => isset($metaData['message']) ? $this->formattedMessage($metaData['message']) : null,
                ], $status),
                'additional_data' => $additional_data,
            ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondWithError($message, $code = null)
    {

        return $this->respond([
            'error' => [
                'message'     => $this->formattedMessage($message),
                'status_code' => $this->getStatusCode(),
                'code'        => $code,
            ],
        ]);
    }

    /**
     * @param $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondWithSuccess($message)
    {
        return $this->respond([
            'meta' => [
                'message' => $message,
            ],
        ]);
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->getStatusCode() < 300;
    }

    protected function formattedMessage($message)
    {
        if (is_array($message)) {
            foreach ($message['replace'] as $key => $value) {
                if (str_contains($value, 'app.')) {
                    $message['replace'][$key] = __($value);
                }
            }

            return __($message['text'], $message['replace']);
        }

        return __($message);
    }
}
