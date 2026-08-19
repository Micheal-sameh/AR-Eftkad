<?php

namespace App\Auth;

use App\Exceptions\AvarewaseAccountNotFoundException;
use App\Models\User;
use Avarewase\SsoClient\Contracts\ProvisionsAvarewaseUsers;
use Avarewase\SsoClient\DataObjects\AvarewaseUserInfo;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Finds an existing AR-Eftkad user matching the SSO identity (by
 * avarewase_sub, then by email), linking/backfilling avarewase_sub on
 * match. Never creates a new user: membership_code and Father/Servant
 * type are required domain fields with no SSO equivalent, so an admin
 * must create the account through the normal flow first.
 *
 * @throws AvarewaseAccountNotFoundException
 */
class AvarewaseUserProvisioner implements ProvisionsAvarewaseUsers
{
    public function resolve(AvarewaseUserInfo $userInfo): Authenticatable
    {
        $user = User::query()->where('avarewase_sub', $userInfo->sub)->first();

        if (! $user && $userInfo->email) {
            $user = User::query()->where('email', $userInfo->email)->first();
        }

        if (! $user) {
            throw new AvarewaseAccountNotFoundException(
                "No AR-Eftkad account matches Avarewase identity {$userInfo->sub} (email: {$userInfo->email}).",
            );
        }

        $user->forceFill(array_filter([
            'avarewase_sub' => $userInfo->sub,
            'avarewase_avatar' => $userInfo->picture,
        ], fn ($value) => ! is_null($value)))->save();

        return $user;
    }
}
