<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when a user authenticates successfully via Avarewase SSO, no local
 * account matches them (by avarewase_sub, membership_code, or email), and
 * the SSO identity carries no membership_code — the one domain field
 * required to create an AR-Eftkad account — so none can be provisioned.
 */
class AvarewaseAccountNotFoundException extends RuntimeException
{
}
