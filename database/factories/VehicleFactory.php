<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleFactory extends Factory
{
    public function definition(): array
    {
        $types = ['car', 'truck', 'van', 'bus'];
        $letters = ['أ', 'ب', 'ج', 'د', 'ه', 'و', 'ز'];

        // Egyptian plate format: ABC-1234
        $plateNumber = fake()->randomElement($letters) .
            fake()->randomElement($letters) .
            fake()->randomElement($letters) .
            '-' .
            fake()->numerify('####');

        return [
            'company_id' => Company::factory(),
            'plate_number' => $plateNumber,
            'type' => fake()->randomElement($types),
        ];
    }

    public function car()
    {
        return $this->state(['type' => 'car']);
    }

    public function truck()
    {
        return $this->state(['type' => 'truck']);
    }

    public function van()
    {
        return $this->state(['type' => 'van']);
    }

    public function bus()
    {
        return $this->state(['type' => 'bus']);
    }
}
