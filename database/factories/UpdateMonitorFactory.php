<?php

namespace Database\Factories;

use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Model>
 */
class UpdateMonitorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'AP3000D Firmware',
            'url'  => 'https://www.cudy.com/en-us/pages/download-center/ap3000d-1-0',
            'last_version' => '2.3.13', // current known version
        ];
    }
}
