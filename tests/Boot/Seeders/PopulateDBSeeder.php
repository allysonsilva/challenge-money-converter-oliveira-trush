<?php

namespace Tests\Boot\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PopulateDBSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @example php artisan db:seed --class="\\Tests\\Boot\\Seeders\\PopulateDBSeeder"
     *
     * @return void
     */
    public function run()
    {
        $file = 'dump_2022_07_25.sql';

        DB::unprepared(file_get_contents(realpath(__DIR__ . "/../DBs/{$file}")));

        $this->command->newLine();
        $this->command->info("Dump [{$file}] executado com sucesso!");
        $this->command->newLine();
    }
}
