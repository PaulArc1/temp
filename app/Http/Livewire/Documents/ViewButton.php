<?php

namespace App\Http\Livewire\Documents;

use Livewire\Component;

class ViewButton extends Component
{
    public $documentId;

    public $document;

    public function mount($documentId)
    {
        $this->documentId = $documentId;
    }

    public function render()
    {
        $this->document = \App\Models\Document::where('uuid', $this->documentId)->first();

        return view('livewire.documents.view-button');
    }
}
