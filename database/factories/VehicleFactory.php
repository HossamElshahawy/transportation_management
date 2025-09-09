<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleFactory extends Factory
{
    public function definition(): array
    {
        $types = ['car', 'truck', 'van', 'bus'];

        return [
            'company_id' => Company::factory(),
            'plate_number' => strtoupper(fake()->bothify('???-####')),
            'type' => fake()->randomElement($types),
        ];
    }
}
