<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'driver_id',
        'vehicle_id',
        'start_time',
        'end_time',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    // Check if driver has overlapping trips
    public static function hasDriverOverlap($driverId, $startTime, $endTime, $excludeTripId = null)
    {
        $query = self::where('driver_id', $driverId)
            ->whereIn('status', ['scheduled', 'active']) // Only check active/scheduled trips
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($q2) use ($startTime, $endTime) {
                        $q2->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            });

        if ($excludeTripId) {
            $query->where('id', '!=', $excludeTripId);
        }

        return $query->exists();
    }

    // Check if vehicle has overlapping trips
    public static function hasVehicleOverlap($vehicleId, $startTime, $endTime, $excludeTripId = null)
    {
        $query = self::where('vehicle_id', $vehicleId)
            ->whereIn('status', ['scheduled', 'active']) // Only check active/scheduled trips
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($q2) use ($startTime, $endTime) {
                        $q2->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            });

        if ($excludeTripId) {
            $query->where('id', '!=', $excludeTripId);
        }

        return $query->exists();
    }

    // Get available drivers for a time period
    public static function getAvailableDrivers($startTime, $endTime, $companyId = null)
    {
        $busyDriverIds = self::whereIn('status', ['scheduled', 'active'])
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($q2) use ($startTime, $endTime) {
                        $q2->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            })
            ->pluck('driver_id');

        $query = Driver::whereNotIn('id', $busyDriverIds);

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        return $query->get();
    }

    // Get available vehicles for a time period
    public static function getAvailableVehicles($startTime, $endTime, $companyId = null)
    {
        $busyVehicleIds = self::whereIn('status', ['scheduled', 'active'])
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($q2) use ($startTime, $endTime) {
                        $q2->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            })
            ->pluck('vehicle_id');

        $query = Vehicle::whereNotIn('id', $busyVehicleIds);

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        return $query->get();
    }
}
