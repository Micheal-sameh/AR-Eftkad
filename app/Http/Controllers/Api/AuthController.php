<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;

class AuthController extends BaseController
{
    public function login(LoginRequest $request)
    {
        // preg_match('/E1C1F(\d+)NR/', $request->membership_code, $matches);
        // $E1C1F = $matches[1];
        // preg_match('/NR(\d+)/', $request->membership_code, $matchesNR);
        // $NR = $matchesNR[1];

        // $credentials = $request->only('password', 'membership_code');
        // $credentials['E1C1F'] = $E1C1F;
        // $credentials['NR'] = $NR;

        if (! auth()->attempt($request->only('password', 'membership_code'))) {
            return $this->apiErrorResponse(message: trans('messages.wrong credentials'), status_code: 401);
        }
        $user = auth()->user();
        $token = $this->generateToken($user);

        return $this->apiResponse([
            'token' => $token,
            'user' => new UserResource($user),
        ], trans('messages.login successfuly'));
    }

    public function logout()
    {
        $user = auth()->user();
        $token = $user->currentAccessToken();
        $token->delete();
        auth()->guard('web')->logout();

        return $this->apiResponse(message: 'logout successfuly');
    }

    private function generateToken($user)
    {
        $lastTokens = $user->tokens;
        $lastTokens->each(function ($token) {
            $token->delete();
        });
        $token = $user->createToken(config('app.name'))->plainTextToken;

        return $token;
    }
}
