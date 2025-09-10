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
        $startTime = fake()->dateTimeBetween('-30 days', '+7 days');
        $endTime = Carbon::parse($startTime)->addHours(fake()->numberBetween(1, 8));

        // Status based on time
        $now = Carbon::now();
        if ($startTime > $now) {
            $status = 'scheduled';
        } elseif ($startTime <= $now && $endTime > $now) {
            $status = fake()->randomElement(['active', 'completed']);
        } else {
            $status = 'completed';
        }

        return [
            'company_id' => Company::factory(),
            'driver_id' => function (array $attributes) {
                return Driver::factory()->create(['company_id' => $attributes['company_id']])->id;
            },
            'vehicle_id' => function (array $attributes) {
                return Vehicle::factory()->create(['company_id' => $attributes['company_id']])->id;
            },
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => $status,
        ];
    }

    public function scheduled()
    {
        return $this->state(function (array $attributes) {
            $futureTime = fake()->dateTimeBetween('now', '+7 days');
            return [
                'start_time' => $futureTime,
                'end_time' => Carbon::parse($futureTime)->addHours(fake()->numberBetween(1, 8)),
                'status' => 'scheduled'
            ];
        });
    }

    public function active()
    {
        return $this->state(function (array $attributes) {
            $currentTime = Carbon::now()->subHours(fake()->numberBetween(1, 3));
            return [
                'start_time' => $currentTime,
                'end_time' => $currentTime->copy()->addHours(fake()->numberBetween(2, 6)),
                'status' => 'active'
            ];
        });
    }

    public function completed()
    {
        return $this->state(function (array $attributes) {
            $pastTime = fake()->dateTimeBetween('-30 days', '-1 day');
            return [
                'start_time' => $pastTime,
                'end_time' => Carbon::parse($pastTime)->addHours(fake()->numberBetween(1, 8)),
                'status' => 'completed'
            ];
        });
    }
}
