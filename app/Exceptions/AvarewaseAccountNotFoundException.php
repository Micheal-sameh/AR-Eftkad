<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown by AvarewaseUserProvisioner when an SSO sign-in succeeded but there is no usable
 * AR-Eftkad account for it. `reason` says why, so the user gets the right message and the
 * log shows the real cause:
 *  - NO_MEMBERSHIP_CODE: nothing matched (by avarewase_sub, membership_code or verified email)
 *    and the SSO identity carries no membership_code to create an account with.
 *  - LINKED_ELSEWHERE: the matching account is already linked to a different SSO identity.
 *  - DELETED: the matching account was deleted by an admin.
 */
class AvarewaseAccountNotFoundException extends RuntimeException
{
    public const NO_MEMBERSHIP_CODE = 'no_membership_code';

    public const LINKED_ELSEWHERE = 'linked_elsewhere';

    public const DELETED = 'deleted';

    public function __construct(string $message, public readonly string $reason = self::NO_MEMBERSHIP_CODE)
    {
        parent::__construct($message);
    }

    /** Translation key for the message shown to the user. */
    public function messageKey(): string
    {
        return match ($this->reason) {
            self::LINKED_ELSEWHERE => 'messages.avarewase account linked elsewhere',
            self::DELETED => 'messages.avarewase account deleted',
            default => 'messages.avarewase no matching account',
        };
    }
}
