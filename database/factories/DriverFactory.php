<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class DriverFactory extends Factory
{
    public function definition(): array
    {
        $arabicNames = [
            'أحمد محمد', 'محمد أحمد', 'علي حسن', 'حسن علي', 'محمود سيد',
            'سيد محمود', 'عبد الله أحمد', 'يوسف محمد', 'إبراهيم علي', 'عمر حسن',
            'خالد محمد', 'طارق أحمد', 'وائل سيد', 'هشام علي', 'كريم محمود'
        ];

        return [
            'company_id' => Company::factory(),
            'name' => fake()->randomElement($arabicNames),
            'phone' => '01' . fake()->randomElement([0, 1, 2, 5]) . fake()->numerify('########'),
        ];
    }
}
