<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Laravel\Sanctum\PersonalAccessToken;

class PruneStaleTokens extends Command
{
    protected $signature = 'tokens:prune-stale';

    protected $description = 'Weekly cleanup: revoke all tokens for users with more than 2 active tokens, forcing them to sign in again';

    public function handle(): int
    {
        $userIds = PersonalAccessToken::query()
            ->select('tokenable_id')
            ->where('tokenable_type', User::class)
            ->groupBy('tokenable_id')
            ->havingRaw('count(*) > 2')
            ->pluck('tokenable_id');

        $deleted = PersonalAccessToken::query()
            ->where('tokenable_type', User::class)
            ->whereIn('tokenable_id', $userIds)
            ->delete();

        $this->info("Revoked {$deleted} token(s) across {$userIds->count()} user(s).");

        return self::SUCCESS;
    }
}
