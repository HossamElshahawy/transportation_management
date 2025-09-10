<?php

namespace App\Filament\Pages;

use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Trip;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Schemas\Schema;

class ManagerAvailability extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedUserGroup;
    protected static ?string $navigationLabel = 'توافر السائقين والمركبات';

    protected string $view = 'filament.pages.manager-availability';

    public ?string $start_time = null;
    public ?string $end_time = null;
    public string $activeTab = 'drivers'; // drivers or vehicles

    public function mount(): void
    {
        // Set default values
        $this->start_time = now()->format('Y-m-d H:i:s');
        $this->end_time = now()->addHours(2)->format('Y-m-d H:i:s');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('فترة البحث')
                    ->description('اختر الفترة الزمنية للبحث عن المتاحين')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('start_time')
                                    ->label('وقت البداية')
                                    ->native(false)
                                    ->seconds(false)
                                    ->live()
                                    ->required()
                                    ->default(now())
                                    ->closeOnDateSelection(),

                                DateTimePicker::make('end_time')
                                    ->label('وقت النهاية')
                                    ->native(false)
                                    ->seconds(false)
                                    ->live()
                                    ->required()
                                    ->default(now()->addHours(2))
                                    ->closeOnDateSelection()
                                    ->after('start_time'), // Validation: end must be after start
                            ])
                    ])
            ]);
    }

    public function table(Table $table): Table
    {
        // استخدام الوقت الافتراضي إذا لم يتم تحديد شيء
        $startTime = $this->start_time ? Carbon::parse($this->start_time) : now();
        $endTime = $this->end_time ? Carbon::parse($this->end_time) : now()->addHours(2);

        if ($this->activeTab === 'vehicles') {
            return $this->getVehiclesTable($table, $startTime, $endTime);
        }

        return $this->getDriversTable($table, $startTime, $endTime);
    }

    private function getDriversTable(Table $table, Carbon $startTime, Carbon $endTime): Table
    {
        // استخدام المتود الموجودة في Trip model
        $availableDrivers = Trip::getAvailableDrivers($startTime, $endTime);

        return $table
            ->query(Driver::whereIn('id', $availableDrivers->pluck('id')))
            ->columns([
                TextColumn::make('name')
                    ->label('السائق')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('التليفون')
                    ->toggleable(),

                TextColumn::make('company.name')
                    ->label('الشركة')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('trips_count')
                    ->label('عدد الرحلات الكلي')
                    ->counts('trips')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->heading('السائقون المتاحون (' . $availableDrivers->count() . ')')
            ->paginated([10, 25, 50])
            ->striped()
            ->defaultSort('name');
    }

    private function getVehiclesTable(Table $table, Carbon $startTime, Carbon $endTime): Table
    {
        // استخدام المتود الموجودة في Trip model
        $availableVehicles = Trip::getAvailableVehicles($startTime, $endTime);

        return $table
            ->query(Vehicle::whereIn('id', $availableVehicles->pluck('id')))
            ->columns([
                TextColumn::make('plate_number')
                    ->label('رقم اللوحة')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'car' => 'success',
                        'truck' => 'warning',
                        'van' => 'info',
                        'bus' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('company.name')
                    ->label('الشركة')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('trips_count')
                    ->label('عدد الرحلات الكلي')
                    ->counts('trips')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->heading('المركبات المتاحة (' . $availableVehicles->count() . ')')
            ->paginated([10, 25, 50])
            ->striped()
            ->defaultSort('plate_number');
    }

    // Method to switch between tabs
    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetTable(); // Reset table state when switching tabs
    }

    // Get stats for the view
    public function getAvailabilityStats(): array
    {
        $startTime = $this->start_time ? Carbon::parse($this->start_time) : now();
        $endTime = $this->end_time ? Carbon::parse($this->end_time) : now()->addHours(2);

        $availableDrivers = Trip::getAvailableDrivers($startTime, $endTime);
        $availableVehicles = Trip::getAvailableVehicles($startTime, $endTime);

        $totalDrivers = Driver::count();
        $totalVehicles = Vehicle::count();

        return [
            'available_drivers' => $availableDrivers->count(),
            'total_drivers' => $totalDrivers,
            'available_vehicles' => $availableVehicles->count(),
            'total_vehicles' => $totalVehicles,
            'drivers_percentage' => $totalDrivers > 0 ? round(($availableDrivers->count() / $totalDrivers) * 100) : 0,
            'vehicles_percentage' => $totalVehicles > 0 ? round(($availableVehicles->count() / $totalVehicles) * 100) : 0,
        ];
    }
}
