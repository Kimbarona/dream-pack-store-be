<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            \App\Jobs\CheckCryptoInvoiceStatusJob::dispatchForPendingInvoices();
        })->everyTwoMinutes()
          ->description('Check status of pending crypto invoices')
          ->onSuccess(function () {
              Log::info('Pending crypto invoice status check completed successfully');
          })
          ->onFailure(function () {
              Log::error('Pending crypto invoice status check failed');
          });

        $schedule->call(function () {
            \App\Jobs\CheckCryptoInvoiceStatusJob::dispatchForExpiredInvoices();
        })->everyFiveMinutes()
          ->description('Check status of expired crypto invoices')
          ->onSuccess(function () {
              Log::info('Expired crypto invoice status check completed successfully');
          })
          ->onFailure(function () {
              Log::error('Expired crypto invoice status check failed');
          });

        $schedule->command('queue:retry all')
          ->hourly()
          ->description('Retry failed crypto monitoring jobs')
          ->onOneServer();

        $schedule->command('queue:prune-failed --hours=24')
          ->daily()
          ->description('Clean up old failed jobs');

        $schedule->command('cache:clear')
          ->daily()
          ->description('Daily cache cleanup');

        $schedule->command('banners:deactivate-expired')
          ->hourly()
          ->description('Deactivate banners whose end date has passed');

        $schedule->command('banners:activate-scheduled')
          ->hourly()
          ->description('Activate banners whose start date has arrived');
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}