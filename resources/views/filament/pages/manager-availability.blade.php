<x-filament-panels::page>
    {{-- Form Section --}}
    <div class="mb-6">
        <x-filament::section>
            <x-slot name="heading">
                البحث عن المتاحين
            </x-slot>

            <form wire:submit.prevent="$refresh">
                {{ $this->form }}

                <div class="mt-4">
                    <x-filament::button type="submit" size="sm">
                        تحديث النتائج
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>
    </div>

    {{-- Stats Cards --}}
    @php
        $stats = $this->getAvailabilityStats();
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <x-filament::section class="p-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-success-600">{{ $stats['available_drivers'] }}</div>
                <div class="text-sm text-gray-600">سائق متاح</div>
                <div class="text-xs text-gray-500">من أصل {{ $stats['total_drivers'] }} ({{ $stats['drivers_percentage'] }}%)</div>
            </div>
        </x-filament::section>

        <x-filament::section class="p-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-info-600">{{ $stats['available_vehicles'] }}</div>
                <div class="text-sm text-gray-600">مركبة متاحة</div>
                <div class="text-xs text-gray-500">من أصل {{ $stats['total_vehicles'] }} ({{ $stats['vehicles_percentage'] }}%)</div>
            </div>
        </x-filament::section>

        <x-filament::section class="p-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-warning-600">{{ $stats['total_drivers'] - $stats['available_drivers'] }}</div>
                <div class="text-sm text-gray-600">سائق مشغول</div>
            </div>
        </x-filament::section>

        <x-filament::section class="p-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-danger-600">{{ $stats['total_vehicles'] - $stats['available_vehicles'] }}</div>
                <div class="text-sm text-gray-600">مركبة مشغولة</div>
            </div>
        </x-filament::section>
    </div>

    {{-- Tabs --}}
    <div class="mb-4">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button
                    wire:click="setActiveTab('drivers')"
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'drivers' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    السائقون المتاحون
                </button>
                <button
                    wire:click="setActiveTab('vehicles')"
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'vehicles' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    المركبات المتاحة
                </button>
            </nav>
        </div>
    </div>

    {{-- Table Section --}}
    <div>
        {{ $this->table }}
    </div>

    {{-- Time Range Display --}}
    <div class="mt-4 text-sm text-gray-600 text-center">
        @if($start_time && $end_time)
            <p>البحث في الفترة من: <strong>{{ \Carbon\Carbon::parse($start_time)->format('Y-m-d H:i') }}</strong>
                إلى: <strong>{{ \Carbon\Carbon::parse($end_time)->format('Y-m-d H:i') }}</strong></p>
        @endif
    </div>
</x-filament-panels::page>
