<?php

namespace App\Http\Livewire\Equipment;

use Livewire\Component;
use WireUi\Traits\Actions;

class Show extends Component
{
    use Actions;

    public function mount($uuid)
    {
    }

    public function render()
    {
        return view('livewire.equipment.show');
    }
}
