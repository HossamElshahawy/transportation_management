<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
//    public function run(): void
//    {
//        $this->call([
//            CompanySeeder::class,
//            DriverSeeder::class,
//            VehicleSeeder::class,
//        ]);
//    }

    public function run(): void
    {
        // Create 5 companies
        $companies = Company::factory(5)->create();

        $companies->each(function ($company) {
            // Each company gets 3-8 drivers
            $drivers = Driver::factory(rand(3, 8))->create([
                'company_id' => $company->id
            ]);

            // Each company gets 2-5 vehicles
            $vehicles = Vehicle::factory(rand(2, 5))->create([
                'company_id' => $company->id
            ]);

            // Create trips for the last 30 days and next 7 days
            $this->createTripsForCompany($company, $drivers, $vehicles);
        });
    }

    private function createTripsForCompany($company, $drivers, $vehicles)
    {
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now()->addDays(7);

        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            // Create 1-4 trips per day for each company
            $tripsPerDay = rand(1, 4);

            for ($i = 0; $i < $tripsPerDay; $i++) {
                $driver = $drivers->random();
                $vehicle = $vehicles->random();

                // Random start hour between 6 AM and 10 PM
                $startHour = rand(6, 22);
                $tripStart = $date->copy()->setHour($startHour)->setMinute(0);
                $tripEnd = $tripStart->copy()->addHours(rand(1, 6));

                // Skip if there's overlap (simple check)
                if ($this->hasOverlap($driver->id, $vehicle->id, $tripStart, $tripEnd)) {
                    continue;
                }

                // Set status based on date
                $status = $this->getTripStatus($tripStart);

                Trip::create([
                    'company_id' => $company->id,
                    'driver_id' => $driver->id,
                    'vehicle_id' => $vehicle->id,
                    'start_time' => $tripStart,
                    'end_time' => $tripEnd,
                    'status' => $status,
                ]);
            }
        }
    }

    private function hasOverlap($driverId, $vehicleId, $startTime, $endTime)
    {
        return Trip::where(function ($query) use ($driverId, $vehicleId) {
            $query->where('driver_id', $driverId)
                ->orWhere('vehicle_id', $vehicleId);
        })
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            })
            ->exists();
    }

    private function getTripStatus($startTime)
    {
        $now = Carbon::now();

        if ($startTime > $now) {
            return 'scheduled';
        } elseif ($startTime <= $now && $startTime->addHours(6) > $now) {
            return rand(0, 1) ? 'active' : 'completed';
        } else {
            return 'completed';
        }
    }
}
