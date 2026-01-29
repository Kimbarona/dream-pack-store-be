<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\TestFirstOrderSeeder;

class SeedFirstOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:seed-first-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the first test order with order items for testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Running TestFirstOrderSeeder via Artisan command...');
        
        $seeder = new TestFirstOrderSeeder();
        $seeder->setCommand($this);
        $seeder->run();
        
        $this->info('✅ Test order seeding completed!');
        
        return 0;
    }
}
