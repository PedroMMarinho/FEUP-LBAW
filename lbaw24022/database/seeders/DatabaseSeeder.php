<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Artisan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $path = base_path('database/okshon-seed.sql');
        $sql = file_get_contents($path);
        DB::unprepared($sql);
        $this->command->info('Database seeded!');

        // Reset all migrations (this ensures that everything is rolled back)
        Artisan::call('migrate:reset', ['--force' => true]); // Use --force in production

        // First, run the migrations to ensure the database schema is up to date.
        Artisan::call('migrate', ['--force' => true]); // --force is needed when running in production
    }
}
