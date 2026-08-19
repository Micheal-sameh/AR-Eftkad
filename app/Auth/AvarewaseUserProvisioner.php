<?php

namespace App\Auth;

use App\Exceptions\AvarewaseAccountNotFoundException;
use App\Models\User;
use Avarewase\SsoClient\Contracts\ProvisionsAvarewaseUsers;
use Avarewase\SsoClient\DataObjects\AvarewaseUserInfo;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;

/**
 * Finds an AR-Eftkad user matching the SSO identity — by avarewase_sub,
 * then membership_code, then email — and, on every login (not just the
 * first), refreshes that user's profile (name, email, membership_code,
 * avatar, date of birth, email-verified status) from the latest SSO data,
 * so Avarewase stays the source of truth for identity fields. If no match
 * exists, creates one from the SSO identity (Avarewase now returns
 * membership_code).
 *
 * `type` (Father/Servant) has no SSO equivalent, so newly-created users
 * are left with type = null until an admin assigns it.
 *
 * @throws AvarewaseAccountNotFoundException if no user matches and the
 *         SSO identity carries no membership_code to create one with.
 */
class AvarewaseUserProvisioner implements ProvisionsAvarewaseUsers
{
    public function resolve(AvarewaseUserInfo $userInfo): Authenticatable
    {
        $user = User::query()->where('avarewase_sub', $userInfo->sub)->first();

        if (! $user && $userInfo->membershipCode) {
            $user = User::query()->where('membership_code', $userInfo->membershipCode)->first();
        }

        if (! $user && $userInfo->email) {
            $user = User::query()->where('email', $userInfo->email)->first();
        }

        if (! $user) {
            if (! $userInfo->membershipCode) {
                throw new AvarewaseAccountNotFoundException(
                    "No AR-Eftkad account matches Avarewase identity {$userInfo->sub}, and it carries no membership_code to create one with.",
                );
            }

            $user = new User([
                'name' => $userInfo->name ?: $userInfo->membershipCode,
                'password' => bcrypt(Str::random(40)),
            ]);
        }

        $user->forceFill(array_filter([
            'avarewase_sub' => $userInfo->sub,
            'avarewase_avatar' => $userInfo->picture,
            'membership_code' => $userInfo->membershipCode,
            'name' => $userInfo->name,
            'email' => $userInfo->email,
            'date_of_birth' => $userInfo->dateOfBirth,
        ], fn ($value) => ! is_null($value)))->save();

        if ($userInfo->emailVerified && ! $user->email_verified_at) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        return $user;
    }
}
