<?php

namespace App\Auth;

use App\Enums\UserRole;
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
 * so Avarewase stays the source of truth for identity fields. Users an admin
 * added beforehand (POST /users: no avarewase_sub yet) are linked here by
 * membership_code on their first login; their type and phone are kept. Deleted
 * (soft-deleted) users are refused. If no match
 * exists, creates one from the SSO identity (Avarewase now returns
 * membership_code).
 *
 * `type` (Father/Servant) has no SSO equivalent, so newly-created users
 * are left with type = null until an admin assigns it. New users default
 * to the "user" authorization role; an admin/superadmin can promote them.
 *
 * @throws AvarewaseAccountNotFoundException if no user matches and the
 *         SSO identity carries no membership_code to create one with.
 */
class AvarewaseUserProvisioner implements ProvisionsAvarewaseUsers
{
    public function resolve(AvarewaseUserInfo $userInfo): Authenticatable
    {
        $user = User::withTrashed()->where('avarewase_sub', $userInfo->sub)->first();

        if (! $user && $userInfo->membershipCode) {
            $user = User::withTrashed()->where('membership_code', $userInfo->membershipCode)->first();
        }

        // Email is only a safe link to an existing account when the SSO vouches for it;
        // otherwise anyone could claim a victim's address and inherit their role.
        if (! $user && $userInfo->email && $userInfo->emailVerified) {
            $user = User::withTrashed()->where('email', $userInfo->email)->first();
        }

        // Lookups include deleted users so a deleted person is refused here, rather than missed
        // and re-created with their old membership code (still reserved by the unique index).
        if ($user && $user->trashed()) {
            throw new AvarewaseAccountNotFoundException(
                "Local user {$user->getKey()} has been deleted.",
                AvarewaseAccountNotFoundException::DELETED,
            );
        }

        // A user already linked to a different SSO identity must never be re-linked by a
        // membership_code/email match, or whoever claims that code would inherit the account.
        if ($user && $user->avarewase_sub && $user->avarewase_sub !== $userInfo->sub) {
            throw new AvarewaseAccountNotFoundException(
                "Local user {$user->getKey()} is already linked to a different Avarewase identity than {$userInfo->sub}.",
                AvarewaseAccountNotFoundException::LINKED_ELSEWHERE,
            );
        }

        if (! $user) {
            if (! $userInfo->membershipCode) {
                throw new AvarewaseAccountNotFoundException(
                    "No AR-Eftkad account matches Avarewase identity {$userInfo->sub}, and it carries no membership_code to create one with.",
                    AvarewaseAccountNotFoundException::NO_MEMBERSHIP_CODE,
                );
            }

            $user = new User([
                'name' => $userInfo->name ?: $userInfo->membershipCode,
                'password' => bcrypt(Str::random(40)),
            ]);
        }

        // Never take over an identifier that already belongs to a different local user.
        $membershipCode = $userInfo->membershipCode;
        if ($membershipCode && User::withTrashed()->where('membership_code', $membershipCode)->whereKeyNot($user->getKey() ?? 0)->exists()) {
            $membershipCode = null;
        }

        $email = $userInfo->email;
        if ($email && User::withTrashed()->where('email', $email)->whereKeyNot($user->getKey() ?? 0)->exists()) {
            $email = null;
        }

        $user->forceFill(array_filter([
            'avarewase_sub' => $userInfo->sub,
            'avarewase_avatar' => $userInfo->picture,
            'membership_code' => $membershipCode,
            'name' => $userInfo->name,
            'email' => $email,
            'date_of_birth' => $userInfo->dateOfBirth,
        ], fn ($value) => ! is_null($value)))->save();

        if ($userInfo->emailVerified && ! $user->email_verified_at) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        if ($user->roles->isEmpty()) {
            $user->assignRole(UserRole::USER);
        }

        return $user;
    }
}
