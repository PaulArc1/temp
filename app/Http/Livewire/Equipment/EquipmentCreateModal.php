<?php

namespace App\Http\Livewire\Equipment;

use App\Models\Document;
use App\Models\Equipment;
use App\Models\EquipmentType;
use Livewire\Component;

class EquipmentCreateModal extends Component
{
    public $name;

    public $equipmentId;

    public $equipmentType;

    public $showModal;

    protected $listeners = ['openModal' => 'openModal'];

    public function openModal($id = null)
    {
        if ($id) {
            $this->equipmentType = EquipmentType::findOrFail($id);
            $this->name = $this->equipmentType->name;
            $this->equipmentId = $this->equipmentType->equipment->pluck('name')->toArray();
        }
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        unset($this->equipmentType, $this->name, $this->equipmentId);
    }

    public function render()
    {
        return view('livewire.equipment.equipment-create-modal');
    }

    public function save()
    {
        //ini_set('max_execution_time', 180);

        $this->validate([
            //'file' => 'required|mimetypes:application/pdf|max:20480', // 20MB Max
            'name' => 'required',
            'equipmentId' => 'required',
        ]);

        if ($this->equipmentType) {
            $this->equipmentType->update([
                'name' => $this->name,
            ]);

            foreach ($this->equipmentType->equipment as $equipment) {
                if ($equipment->id && ! in_array($equipment, explode(',', $this->equipmentId))) {
                    $equipment->delete();
                }
            }

            foreach (explode(',', $this->equipmentId) as $equipmentId) {
                Equipment::updateOrCreate([
                    'equipment_type_id' => $this->equipmentType->id,
                    'name' => preg_replace('/\s+/', '', $equipmentId),
                ]);
            }
        } else {
            $equipment = EquipmentType::create([
                'team_id' => \Auth::user()->currentTeam->id,
                'name' => $this->name,
            ]);

            foreach (explode(',', $this->equipmentId) as $equipmentId) {
                Equipment::create([
                    'equipment_type_id' => $equipment->id,
                    'name' => preg_replace('/\s+/', '', $equipmentId),
                ]);
            }
        }

        /*$document = Document::create([
            'user_id' => \Auth::user()->id,
            'name' => $this->name,
            'file' => $filename,
        ]);
*/

        unset($this->equipmentType, $this->name, $this->equipmentId);

        return redirect()->route('equipment.index')->with('message', 'Equipment Created.');
    }

    public function addEquipmentId()
    {
        $this->equipmentId->push('');
    }
}
