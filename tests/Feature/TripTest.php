<?php

use App\Models\Company;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Trip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

uses(RefreshDatabase::class);

describe('Trip CRUD Operations', function () {

    test('can create a trip', function () {
        $company = Company::factory()->create();
        $driver = Driver::factory()->create(['company_id' => $company->id]);
        $vehicle = Vehicle::factory()->create(['company_id' => $company->id]);

        $startTime = now();
        $endTime = now()->addHours(2);

        $trip = Trip::create([
            'company_id' => $company->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'scheduled'
        ]);

        expect($trip)
            ->toBeInstanceOf(Trip::class)
            ->company_id->toBe($company->id)
            ->driver_id->toBe($driver->id)
            ->vehicle_id->toBe($vehicle->id)
            ->status->toBe('scheduled')
            ->id->toBeInt();

        $this->assertDatabaseHas('trips', [
            'company_id' => $company->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'status' => 'scheduled'
        ]);
    });

    test('can read a trip', function () {
        $trip = Trip::factory()->create();

        $foundTrip = Trip::find($trip->id);

        expect($foundTrip)
            ->not->toBeNull()
            ->id->toBe($trip->id)
            ->company_id->toBe($trip->company_id)
            ->driver_id->toBe($trip->driver_id)
            ->vehicle_id->toBe($trip->vehicle_id);
    });

    test('can update a trip', function () {
        $trip = Trip::factory()->create(['status' => 'scheduled']);

        $trip->update([
            'status' => 'active'
        ]);

        expect($trip->refresh())
            ->status->toBe('active');

        $this->assertDatabaseHas('trips', [
            'id' => $trip->id,
            'status' => 'active'
        ]);
    });

    test('can delete a trip', function () {
        $trip = Trip::factory()->create();
        $tripId = $trip->id;

        $trip->delete();

        expect(Trip::find($tripId))->toBeNull();

        $this->assertDatabaseMissing('trips', [
            'id' => $tripId
        ]);
    });

    test('can get all trips', function () {
        Trip::factory()->count(3)->create();

        $trips = Trip::all();

        expect($trips)
            ->toHaveCount(3)
            ->each->toBeInstanceOf(Trip::class);
    });
});

describe('Trip Relationships', function () {

    test('trip belongs to company', function () {
        $company = Company::factory()->create();
        $trip = Trip::factory()->create(['company_id' => $company->id]);

        expect($trip->company)
            ->toBeInstanceOf(Company::class)
            ->id->toBe($company->id);
    });

    test('trip belongs to driver', function () {
        $driver = Driver::factory()->create();
        $trip = Trip::factory()->create(['driver_id' => $driver->id]);

        expect($trip->driver)
            ->toBeInstanceOf(Driver::class)
            ->id->toBe($driver->id);
    });

    test('trip belongs to vehicle', function () {
        $vehicle = Vehicle::factory()->create();
        $trip = Trip::factory()->create(['vehicle_id' => $vehicle->id]);

        expect($trip->vehicle)
            ->toBeInstanceOf(Vehicle::class)
            ->id->toBe($vehicle->id);
    });

    test('company has many trips', function () {
        $company = Company::factory()->create();
        Trip::factory()->count(3)->create(['company_id' => $company->id]);

        expect($company->trips)
            ->toHaveCount(3)
            ->each->toBeInstanceOf(Trip::class);
    });

    test('driver has many trips', function () {
        $driver = Driver::factory()->create();
        Trip::factory()->count(2)->create(['driver_id' => $driver->id]);

        expect($driver->trips)
            ->toHaveCount(2)
            ->each->toBeInstanceOf(Trip::class);
    });

    test('vehicle has many trips', function () {
        $vehicle = Vehicle::factory()->create();
        Trip::factory()->count(2)->create(['vehicle_id' => $vehicle->id]);

        expect($vehicle->trips)
            ->toHaveCount(2)
            ->each->toBeInstanceOf(Trip::class);
    });
});

describe('Trip Overlap Prevention', function () {

    test('driver cannot have overlapping trips', function () {
        $driver = Driver::factory()->create();

        // First trip: 10:00 - 12:00
        Trip::factory()->create([
            'driver_id' => $driver->id,
            'start_time' => Carbon::today()->setHour(10),
            'end_time' => Carbon::today()->setHour(12),
            'status' => 'scheduled'
        ]);

        // Try to create overlapping trip: 11:00 - 13:00
        expect(Trip::hasDriverOverlap(
            $driver->id,
            Carbon::today()->setHour(11),
            Carbon::today()->setHour(13)
        ))->toBeTrue();
    });

    test('vehicle cannot have overlapping trips', function () {
        $vehicle = Vehicle::factory()->create();

        // First trip: 14:00 - 16:00
        Trip::factory()->create([
            'vehicle_id' => $vehicle->id,
            'start_time' => Carbon::today()->setHour(14),
            'end_time' => Carbon::today()->setHour(16),
            'status' => 'scheduled'
        ]);

        // Try to create overlapping trip: 15:00 - 17:00
        expect(Trip::hasVehicleOverlap(
            $vehicle->id,
            Carbon::today()->setHour(15),
            Carbon::today()->setHour(17)
        ))->toBeTrue();
    });

    test('can create non-overlapping trips for same driver', function () {
        $driver = Driver::factory()->create();

        // First trip: 10:00 - 12:00
        Trip::factory()->create([
            'driver_id' => $driver->id,
            'start_time' => Carbon::today()->setHour(10),
            'end_time' => Carbon::today()->setHour(12),
        ]);

        // Non-overlapping trip: 13:00 - 15:00
        expect(Trip::hasDriverOverlap(
            $driver->id,
            Carbon::today()->setHour(13),
            Carbon::today()->setHour(15)
        ))->toBeFalse();
    });

    test('can create non-overlapping trips for same vehicle', function () {
        $vehicle = Vehicle::factory()->create();

        // First trip: 10:00 - 12:00
        Trip::factory()->create([
            'vehicle_id' => $vehicle->id,
            'start_time' => Carbon::today()->setHour(10),
            'end_time' => Carbon::today()->setHour(12),
        ]);

        // Non-overlapping trip: 13:00 - 15:00
        expect(Trip::hasVehicleOverlap(
            $vehicle->id,
            Carbon::today()->setHour(13),
            Carbon::today()->setHour(15)
        ))->toBeFalse();
    });

    test('completed trips do not block new trips', function () {
        $driver = Driver::factory()->create();

        // Completed trip: 10:00 - 12:00
        Trip::factory()->create([
            'driver_id' => $driver->id,
            'start_time' => Carbon::today()->setHour(10),
            'end_time' => Carbon::today()->setHour(12),
            'status' => 'completed'
        ]);

        // Overlapping time but previous trip is completed
        expect(Trip::hasDriverOverlap(
            $driver->id,
            Carbon::today()->setHour(11),
            Carbon::today()->setHour(13)
        ))->toBeFalse();
    });
});

describe('Trip Status and Filtering', function () {

    test('can filter trips by status', function () {
        Trip::factory()->count(2)->create(['status' => 'scheduled']);
        Trip::factory()->count(3)->create(['status' => 'active']);
        Trip::factory()->count(1)->create(['status' => 'completed']);

        expect(Trip::where('status', 'scheduled')->count())->toBe(2);
        expect(Trip::where('status', 'active')->count())->toBe(3);
        expect(Trip::where('status', 'completed')->count())->toBe(1);
    });

    test('can get active trips', function () {
        Trip::factory()->count(2)->create(['status' => 'active']);
        Trip::factory()->count(3)->create(['status' => 'scheduled']);

        $activeTrips = Trip::where('status', 'active')->get();
        expect($activeTrips->pluck('status'))->each(fn ($status) => expect($status->value)->toContain('active'));

    });

    test('can get trips by date range', function () {
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        Trip::factory()->create([
            'start_time' => $today->setHour(10),
            'end_time' => $today->setHour(12)
        ]);

        Trip::factory()->create([
            'start_time' => $tomorrow->setHour(10),
            'end_time' => $tomorrow->setHour(12)
        ]);

        $todayTrips = Trip::whereDate('start_time', $today)->get();

        expect($todayTrips)->toHaveCount(1);
    });

    test('required fields validation', function () {
        expect(fn() => Trip::create([]))->toThrow(Exception::class);
    });

    test('timestamps are automatically set', function () {
        $trip = Trip::factory()->create();

        expect($trip)
            ->created_at->not->toBeNull()
            ->updated_at->not->toBeNull();
    });
});
