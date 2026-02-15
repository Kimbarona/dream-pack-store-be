<?php

namespace App\Console\Commands;

use App\Models\Banner;
use Illuminate\Console\Command;

class ActivateScheduledBanners extends Command
{
    protected $signature = 'banners:activate-scheduled';

    protected $description = 'Set is_active = true for banners where starts_at has passed and were previously active';

    public function handle(): int
    {
        $count = Banner::where('is_active', false)
            ->whereNotNull('starts_at')
            ->where('starts_at', '<', now())
            ->update(['is_active' => true]);

        $this->info("Activated {$count} scheduled banner(s).");

        return Command::SUCCESS;
    }
}
