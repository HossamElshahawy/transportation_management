<?php

use App\Models\Company;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Company CRUD Operations', function () {

    test('can create a company', function () {
        $company = Company::create([
            'name' => 'Test Company'
        ]);

        expect($company)
            ->toBeInstanceOf(Company::class)
            ->name->toBe('Test Company')
            ->id->toBeInt();

        $this->assertDatabaseHas('companies', [
            'name' => 'Test Company'
        ]);
    });

    test('can read a company', function () {
        $company = Company::factory()->create([
            'name' => 'Sample Company'
        ]);

        $foundCompany = Company::find($company->id);

        expect($foundCompany)
            ->not->toBeNull()
            ->name->toBe('Sample Company')
            ->id->toBe($company->id);
    });

    test('can update a company', function () {
        $company = Company::factory()->create([
            'name' => 'Original Name'
        ]);

        $company->update([
            'name' => 'Updated Name'
        ]);

        expect($company->refresh())
            ->name->toBe('Updated Name');

        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'name' => 'Updated Name'
        ]);
    });

    test('can delete a company', function () {
        $company = Company::factory()->create();
        $companyId = $company->id;

        $company->delete();

        expect(Company::find($companyId))->toBeNull();

        $this->assertDatabaseMissing('companies', [
            'id' => $companyId
        ]);
    });

    test('can get all companies', function () {
        Company::factory()->count(3)->create();

        $companies = Company::all();

        expect($companies)
            ->toHaveCount(3)
            ->each->toBeInstanceOf(Company::class);
    });

    test('company name is required', function () {
        expect(fn() => Company::create([]))
            ->toThrow(QueryException::class);
    });

    test('can search companies by name', function () {
        Company::factory()->create(['name' => 'ABC Transport']);
        Company::factory()->create(['name' => 'XYZ Logistics']);
        Company::factory()->create(['name' => 'ABC Shipping']);

        $results = Company::where('name', 'like', '%ABC%')->get();

        expect($results->pluck('name'))->each(fn ($name) => expect($name->value)->toContain('ABC'));

    });

    test('timestamps are automatically set', function () {
        $company = Company::factory()->create();

        expect($company)
            ->created_at->not->toBeNull()
            ->updated_at->not->toBeNull();
    });
});
