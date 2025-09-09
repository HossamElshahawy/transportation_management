<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TripFactory extends Factory
{
    public function definition(): array
    {
        $company = Company::factory()->create();
        $startTime = fake()->dateTimeBetween('now', '+1 week');
        $endTime = Carbon::parse($startTime)->addHours(fake()->numberBetween(1, 8));

        return [
            'company_id' => $company->id,
            'driver_id' => Driver::factory()->create(['company_id' => $company->id])->id,
            'vehicle_id' => Vehicle::factory()->create(['company_id' => $company->id])->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => fake()->randomElement(['scheduled', 'active', 'completed']),
        ];
    }

    public function scheduled()
    {
        return $this->state(['status' => 'scheduled']);
    }

    public function active()
    {
        return $this->state(['status' => 'active']);
    }

    public function completed()
    {
        return $this->state(['status' => 'completed']);
    }
}
