<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    public function definition(): array
    {
        $companyNames = [
            'Fast Transport Co.',
            'City Express',
            'Cairo Logistics',
            'Delta Transport',
            'Nile Shipping Co.',
            'Alexandria Express',
            'Red Sea Transport',
            'Upper Egypt Logistics'
        ];

        return [
            'name' => fake()->randomElement($companyNames) . ' ' . fake()->numberBetween(1, 999),
        ];
    }
}
