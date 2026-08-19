<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when a user authenticates successfully via Avarewase SSO but no
 * local account matches them. Unlike a generic web app, AR-Eftkad accounts
 * require domain fields (membership_code, Father/Servant type) that the SSO
 * identity has no way to supply, so this app never auto-creates a user from
 * an SSO login — an admin must create the account first, with SSO then
 * linking to it (by email, backfilling avarewase_sub) on the next login.
 */
class AvarewaseAccountNotFoundException extends RuntimeException
{
}
