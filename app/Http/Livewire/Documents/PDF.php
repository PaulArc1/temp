<?php

namespace App\Http\Livewire\Documents;

use App\Models\Document;
use Livewire\Component;

class PDF extends Component
{
    public $document;

    public $data;

    public function mount(Document $document)
    {
        $this->document = $document;

        foreach ($this->document->pages as $page) {
            foreach ($page->pageItems as $pageItem) {
                $this->data[$page->id][$pageItem->uuid] = [
                    'uuid' => $pageItem->uuid,
                    'data' => $pageItem->data,
                    'values' => $pageItem->values ?? [],
                ];
            }
        }
    }

    public function render()
    {
        return view('livewire.documents.pdf');
    }
}
