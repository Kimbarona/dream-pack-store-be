<?php

namespace App\Console\Commands;

use App\Models\Banner;
use Illuminate\Console\Command;

class DeactivateExpiredBanners extends Command
{
    protected $signature = 'banners:deactivate-expired';

    protected $description = 'Set is_active = false for banners where ends_at has passed';

    public function handle(): int
    {
        $count = Banner::where('is_active', true)
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', now())
            ->update(['is_active' => false]);

        $this->info("Deactivated {$count} expired banner(s).");

        return Command::SUCCESS;
    }
}
