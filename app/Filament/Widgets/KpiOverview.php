<?php

namespace App\Filament\Widgets;

use App\Models\Driver;
use App\Models\Trip;
use App\Models\Vehicle;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;

class KpiOverview extends BaseWidget
{
    protected static bool $isLazy = false; // Load immediately on dashboard

    protected function getStats(): array
    {
        // Cache KPIs for 1 minute to reduce DB load but keep data fresh
        return Cache::remember('dashboard.kpis', 60, function () {
            $now = Carbon::now();

            $availableDrivers = Trip::getAvailableDrivers($now, $now);
            $availableVehicles = Trip::getAvailableVehicles($now, $now);

            return [
                $this->getActiveTripsStats($now),
                $this->getAvailableResourcesStats($availableDrivers->count(), $availableVehicles->count()),
                $this->getCompletedTripsStats($now),
                $this->getTotalResourcesStats(), // إضافة stat جديد
            ];
        });
    }

    private function getActiveTripsStats(Carbon $now): Stat
    {
        $activeTripsCount = Trip::where('status', 'active')->count();

        // Get comparison with yesterday
        $yesterdayActive = Trip::where('status', 'active')
            ->whereBetween('start_time', [
                $now->copy()->subDay()->startOfDay(),
                $now->copy()->subDay()->endOfDay()
            ])
            ->count();

        $difference = $activeTripsCount - $yesterdayActive;
        $trend = $difference > 0 ? 'increase' : ($difference < 0 ? 'decrease' : 'same');

        return Stat::make('الرحلات النشطة الآن', $activeTripsCount)
            ->description(abs($difference) . ' ' . ($trend === 'increase' ? 'زيادة' : ($trend === 'decrease' ? 'نقصان' : 'نفس')) . ' عن أمس')
            ->descriptionIcon($trend === 'increase' ? 'heroicon-m-arrow-trending-up' : ($trend === 'decrease' ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-minus'))
            ->color($trend === 'increase' ? 'success' : ($trend === 'decrease' ? 'danger' : 'gray'))
            ->chart($this->getActiveTripsChart());
    }

    private function getAvailableResourcesStats(int $availableDrivers, int $availableVehicles): Stat
    {
        $totalDrivers = Driver::count();
        $totalVehicles = Vehicle::count();

        $driversPercentage = $totalDrivers > 0 ? round(($availableDrivers / $totalDrivers) * 100) : 0;
        $vehiclesPercentage = $totalVehicles > 0 ? round(($availableVehicles / $totalVehicles) * 100) : 0;

        $averageAvailability = round(($driversPercentage + $vehiclesPercentage) / 2);

        return Stat::make('المتاحون الآن', $availableDrivers . ' سائق / ' . $availableVehicles . ' مركبة')
            ->description($averageAvailability . '% متوسط التوفر')
            ->descriptionIcon('heroicon-m-user-group')
            ->color($averageAvailability >= 70 ? 'success' : ($averageAvailability >= 40 ? 'warning' : 'danger'));
    }

    private function getCompletedTripsStats(Carbon $now): Stat
    {
        $completedThisMonth = Trip::where('status', 'completed')
            ->whereBetween('end_time', [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth()
            ])
            ->count();

        $completedLastMonth = Trip::where('status', 'completed')
            ->whereBetween('end_time', [
                $now->copy()->subMonth()->startOfMonth(),
                $now->copy()->subMonth()->endOfMonth()
            ])
            ->count();

        $monthlyGrowth = $completedLastMonth > 0
            ? round((($completedThisMonth - $completedLastMonth) / $completedLastMonth) * 100)
            : 0;

        return Stat::make('رحلات مكتملة هذا الشهر', $completedThisMonth)
            ->description(abs($monthlyGrowth) . '% ' . ($monthlyGrowth >= 0 ? 'زيادة' : 'نقصان') . ' عن الشهر الماضي')
            ->descriptionIcon($monthlyGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            ->color($monthlyGrowth >= 0 ? 'success' : 'danger')
            ->chart($this->getMonthlyTripsChart($now));
    }

    private function getTotalResourcesStats(): Stat
    {
        $totalTrips = Trip::count();
        $totalDrivers = Driver::count();
        $totalVehicles = Vehicle::count();

        return Stat::make('إجمالي الموارد', $totalDrivers . ' سائق / ' . $totalVehicles . ' مركبة')
            ->description($totalTrips . ' إجمالي الرحلات')
            ->descriptionIcon('heroicon-m-building-office')
            ->color('info');
    }

    // Chart للرحلات النشطة في آخر 7 أيام
    private function getActiveTripsChart(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = Trip::where('status', 'active')
                ->whereDate('start_time', $date)
                ->count();
            $data[] = $count;
        }
        return $data;
    }

    // Chart للرحلات المكتملة في آخر 7 أشهر
    private function getMonthlyTripsChart(Carbon $now): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $count = Trip::where('status', 'completed')
                ->whereBetween('end_time', [
                    $month->startOfMonth(),
                    $month->endOfMonth()
                ])
                ->count();
            $data[] = $count;
        }
        return $data;
    }

    // Override getColumns to make it responsive
    protected function getColumns(): int
    {
        return 4; // 4 columns on desktop, will stack on mobile
    }
}
