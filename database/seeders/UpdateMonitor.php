<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UpdateMonitor extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UpdateMonitor::factory()->create();
    }
}
