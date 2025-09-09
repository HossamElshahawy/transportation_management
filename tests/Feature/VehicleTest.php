<?php

use App\Models\Company;
use App\Models\Vehicle;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

describe('Vehicle CRUD Operations', function () {

    test('can create a vehicle', function () {
        $company = Company::factory()->create();

        $vehicle = Vehicle::create([
            'company_id' => $company->id,
            'plate_number' => 'ABC-1234',
            'type' => 'car'
        ]);

        expect($vehicle)
            ->toBeInstanceOf(Vehicle::class)
            ->plate_number->toBe('ABC-1234')
            ->type->toBe('car')
            ->company_id->toBe($company->id)
            ->id->toBeInt();

        $this->assertDatabaseHas('vehicles', [
            'plate_number' => 'ABC-1234',
            'type' => 'car',
            'company_id' => $company->id
        ]);
    });

    test('can read a vehicle', function () {
        $company = Company::factory()->create();
        $vehicle = Vehicle::factory()->create([
            'company_id' => $company->id,
            'plate_number' => 'XYZ-5678',
            'type' => 'truck'
        ]);

        $foundVehicle = Vehicle::findOrFail($vehicle->id);

        expect($foundVehicle)
            ->not->toBeNull()
            ->plate_number->toBe('XYZ-5678')
            ->type->toBe('truck')
            ->company_id->toBe($company->id)
            ->id->toBe($vehicle->id);
    });

    test('can update a vehicle', function () {
        $company = Company::factory()->create();
        $vehicle = Vehicle::factory()->create([
            'company_id' => $company->id,
            'plate_number' => 'OLD-1111',
            'type' => 'car'
        ]);

        $vehicle->update([
            'plate_number' => 'NEW-2222',
            'type' => 'van'
        ]);

        expect($vehicle->refresh())
            ->plate_number->toBe('NEW-2222')
            ->type->toBe('van');

        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'plate_number' => 'NEW-2222',
            'type' => 'van'
        ]);
    });

    test('can delete a vehicle', function () {
        $vehicle = Vehicle::factory()->create();
        $vehicleId = $vehicle->id;

        $vehicle->delete();

        expect(Vehicle::find($vehicleId))->toBeNull();

        $this->assertDatabaseMissing('vehicles', [
            'id' => $vehicleId
        ]);
    });

    test('can get all vehicles', function () {
        $company = Company::factory()->create();
        Vehicle::factory()->count(4)->create(['company_id' => $company->id]);

        $vehicles = Vehicle::all();

        expect($vehicles)
            ->toHaveCount(4)
            ->each->toBeInstanceOf(Vehicle::class);
    });

    test('vehicle belongs to a company', function () {
        $company = Company::factory()->create();
        $vehicle = Vehicle::factory()->create(['company_id' => $company->id]);

        expect($vehicle->company)
            ->toBeInstanceOf(Company::class)
            ->id->toBe($company->id);
    });

    test('company has many vehicles', function () {
        $company = Company::factory()->create();
        Vehicle::factory()->count(3)->create(['company_id' => $company->id]);

        expect($company->vehicles)
            ->toHaveCount(3)
            ->each->toBeInstanceOf(Vehicle::class);
    });

    test('required fields validation', function () {
        // Test missing company_id
        expect(fn() => Vehicle::create([
            'plate_number' => 'ABC-1234',
            'type' => 'car'
        ]))->toThrow(Exception::class);

        // Test missing plate_number
        $company = Company::factory()->create();
        expect(fn() => Vehicle::create([
            'company_id' => $company->id,
            'type' => 'car'
        ]))->toThrow(Exception::class);

        // Test missing type
        expect(fn() => Vehicle::create([
            'company_id' => $company->id,
            'plate_number' => 'ABC-1234'
        ]))->toThrow(Exception::class);
    });

    test('plate number must be unique', function () {
        $company = Company::factory()->create();

        Vehicle::factory()->create([
            'company_id' => $company->id,
            'plate_number' => 'UNIQUE-123'
        ]);

        expect(fn() => Vehicle::create([
            'company_id' => $company->id,
            'plate_number' => 'UNIQUE-123',
            'type' => 'car'
        ]))->toThrow(Exception::class);
    });

    test('can search vehicles by plate number', function () {
        $company = Company::factory()->create();
        Vehicle::factory()->create(['company_id' => $company->id, 'plate_number' => 'ABC-1111']);
        Vehicle::factory()->create(['company_id' => $company->id, 'plate_number' => 'XYZ-2222']);
        Vehicle::factory()->create(['company_id' => $company->id, 'plate_number' => 'ABC-3333']);

        $results = Vehicle::where('plate_number', 'like', '%ABC%')->get();

        expect($results->pluck('plate_number'))->each(fn ($plate_number) => expect($plate_number->value)->toContain('ABC'));

    });

    test('can filter vehicles by type', function () {
        $company = Company::factory()->create();
        Vehicle::factory()->count(2)->create(['company_id' => $company->id, 'type' => 'car']);
        Vehicle::factory()->count(3)->create(['company_id' => $company->id, 'type' => 'truck']);

        $cars = Vehicle::where('type', 'car')->get();
        $trucks = Vehicle::where('type', 'truck')->get();

        expect($cars)->toHaveCount(2);
        expect($trucks)->toHaveCount(3);
    });

    test('can get vehicles by company', function () {
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();

        Vehicle::factory()->count(2)->create(['company_id' => $company1->id]);
        Vehicle::factory()->count(3)->create(['company_id' => $company2->id]);

        $company1Vehicles = Vehicle::where('company_id', $company1->id)->get();
        $company2Vehicles = Vehicle::where('company_id', $company2->id)->get();

        expect($company1Vehicles)->toHaveCount(2);
        expect($company2Vehicles)->toHaveCount(3);
    });

    test('timestamps are automatically set', function () {
        $vehicle = Vehicle::factory()->create();

        expect($vehicle)
            ->created_at->not->toBeNull()
            ->updated_at->not->toBeNull();
    });
});
