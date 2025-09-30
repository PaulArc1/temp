<?php

namespace App\Http\Livewire\Equipment;

use App\Models\Equipment;
use App\Models\EquipmentType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\ComponentAttributeBag;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use WireUi\Traits\Actions;

class Index extends DataTableComponent
{
    use Actions;

    // protected $model = EquipmentType::class;

    public function builder(): Builder
    {
        return EquipmentType::query()->where('team_id', auth()->user()->currentTeam->id);
    }

    public function configure(): void
    {
        $this->setPrimaryKey('id');
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id')
                ->sortable(),
            //Column::make('Client id', 'client_id')
            //  ->sortable(),
            Column::make('Name', 'name')
                ->sortable(),

            Column::make('Equipment', 'id')
                ->format(function ($id) {
                    $equipment = Equipment::where('equipment_type_id', $id)->pluck('name')->toArray();

                    return implode(', ', $equipment);
                }),

            Column::make('Actions', 'id')
                ->format(function ($id) {
                    return view('livewire.equipment.equipment-actions')
                        ->with('attributes', new ComponentAttributeBag([
                            'dismissible' => true,
                        ]))
                        ->with('id', $id);
                }),

        ];
    }

    public function delete($id): void
    {
        // use a simple syntax
        $this->dialog()->confirm([
            'title' => 'Are you sure you would like to delete this equipment?',
            'description' => 'Save the information?',
            'acceptLabel' => 'Yes, delete it',
            'method' => 'doDelete',
            'params' => ['id' => $id],
            'icon' => 'trash',
            'color' => 'brand-700',
        ]);
    }

    public function doDelete($data)
    {
        $equipmentType = EquipmentType::where('id', $data['id'])->first();
        $equipmentType->equipment()->delete();
        $equipmentType->delete();

        $this->notification()->error(
            $title = 'Equipment Deleted',
            $description = 'The Equipment and IDs have been deleted. THESE CAN NOT BE RECOVERED '
        );
    }
}
