<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array<int, string>|string|null
     */
    // Only loopback and private ranges (the host's reverse proxy / Docker network) are trusted;
    // override with a comma-separated TRUSTED_PROXIES list of IPs/CIDRs, or '*' to trust all.
    protected $proxies = [
        '127.0.0.0/8',
        '10.0.0.0/8',
        '172.16.0.0/12',
        '192.168.0.0/16',
    ];

    public function __construct()
    {
        $configured = config('app.trusted_proxies');
        if (is_string($configured) && trim($configured) !== '') {
            $this->proxies = trim($configured) === '*'
                ? '*'
                : array_map('trim', explode(',', $configured));
        }
    }

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
