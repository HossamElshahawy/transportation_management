<?php

use App\Models\Company;
use App\Models\Driver;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Driver CRUD Operations', function () {

    test('can create a driver', function () {
        $company = Company::factory()->create();

        $driver = Driver::create([
            'company_id' => $company->id,
            'name' => 'Hossam Elshahawy',
            'phone' => '01234567890'
        ]);

        expect($driver)
            ->toBeInstanceOf(Driver::class)
            ->name->toBe('Hossam Elshahawy')
            ->phone->toBe('01234567890')
            ->company_id->toBe($company->id)
            ->id->toBeInt();

        $this->assertDatabaseHas('drivers', [
            'name' => 'Hossam Elshahawy',
            'phone' => '01234567890',
            'company_id' => $company->id
        ]);
    });

    test('can read a driver', function () {
        $company = Company::factory()->create();
        $driver = Driver::factory()->create([
            'company_id' => $company->id,
            'name' => 'Mohamed Hassan',
            'phone' => '01111111111'
        ]);

        $foundDriver = Driver::find($driver->id);

        expect($foundDriver)
            ->not->toBeNull()
            ->name->toBe('Mohamed Hassan')
            ->phone->toBe('01111111111')
            ->company_id->toBe($company->id)
            ->id->toBe($driver->id);
    });

    test('can update a driver', function () {
        $company = Company::factory()->create();
        $driver = Driver::factory()->create([
            'company_id' => $company->id,
            'name' => 'Original Name',
            'phone' => '01000000000'
        ]);

        $driver->update([
            'name' => 'Updated Name',
            'phone' => '01999999999'
        ]);

        expect($driver->refresh())
            ->name->toBe('Updated Name')
            ->phone->toBe('01999999999');

        $this->assertDatabaseHas('drivers', [
            'id' => $driver->id,
            'name' => 'Updated Name',
            'phone' => '01999999999'
        ]);
    });

    test('can delete a driver', function () {
        $driver = Driver::factory()->create();
        $driverId = $driver->id;

        $driver->delete();

        expect(Driver::find($driverId))->toBeNull();

        $this->assertDatabaseMissing('drivers', [
            'id' => $driverId
        ]);
    });

    test('can get all drivers', function () {
        $company = Company::factory()->create();
        Driver::factory()->count(3)->create(['company_id' => $company->id]);

        $drivers = Driver::all();

        expect($drivers)
            ->toHaveCount(3)
            ->each->toBeInstanceOf(Driver::class);
    });

    test('driver belongs to a company', function () {
        $company = Company::factory()->create();
        $driver = Driver::factory()->create(['company_id' => $company->id]);

        expect($driver->company)
            ->toBeInstanceOf(Company::class)
            ->id->toBe($company->id);
    });

    test('company has many drivers', function () {
        $company = Company::factory()->create();
        Driver::factory()->count(3)->create(['company_id' => $company->id]);

        expect($company->drivers)
            ->toHaveCount(3)
            ->each->toBeInstanceOf(Driver::class);
    });

    test('required fields validation', function () {
        // Test missing company_id
        expect(fn() => Driver::create([
            'name' => 'Test Driver',
            'phone' => '01234567890'
        ]))->toThrow(QueryException::class);

        // Test missing name
        $company = Company::factory()->create();
        expect(fn() => Driver::create([
            'company_id' => $company->id,
            'phone' => '01234567890'
        ]))->toThrow(QueryException::class);

        // Test missing phone
        expect(fn() => Driver::create([
            'company_id' => $company->id,
            'name' => 'Test Driver'
        ]))->toThrow(QueryException::class);
    });

    test('can search drivers by name', function () {
        $company = Company::factory()->create();
        Driver::factory()->create(['company_id' => $company->id, 'name' => 'Hossam Mohamed']);
        Driver::factory()->create(['company_id' => $company->id, 'name' => 'Ali Hassan']);
        Driver::factory()->create(['company_id' => $company->id, 'name' => 'Hossam Ali']);

        $results = Driver::where('name', 'like', '%Hossam%')->get();

        expect($results->pluck('name'))->each(fn($result) => expect($result->value)->toContain('Hossam'));
    });

    test('can get drivers by company', function () {
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();

        Driver::factory()->count(2)->create(['company_id' => $company1->id]);
        Driver::factory()->count(3)->create(['company_id' => $company2->id]);

        $company1Drivers = Driver::where('company_id', $company1->id)->get();
        $company2Drivers = Driver::where('company_id', $company2->id)->get();

        expect($company1Drivers)->toHaveCount(2);
        expect($company2Drivers)->toHaveCount(3);
    });

    test('timestamps are automatically set', function () {
        $driver = Driver::factory()->create();

        expect($driver)
            ->created_at->not->toBeNull()
            ->updated_at->not->toBeNull();
    });
});
