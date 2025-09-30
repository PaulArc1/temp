<?php

namespace App\Http\Livewire\Documents;

use Livewire\Component;

class Page extends Component
{
    public $page;

    public $data;

    public $label;

    public $nextLabel;

    public $printView;

    public $lastStyle = [
        'shape' => 'none',
        'fill' => '',
        'scale' => '1',
    ];

    protected $listeners = [
        'setNextLabel',
        'setLastStyle',
        'refreshData',
        //'updateLabels',

    ];

    public function mount(\App\Models\Page $page, $data, $nextLabel = null, $printView = null)
    {
        $this->page = $page;
        $this->data = $data;
        $this->nextLabel = $nextLabel;
        $this->printView = $printView;
        // $this->page = \App\Models\Page::where('uuid', $page)->first();
    }

    public function render()
    {
        return view('livewire.documents.page');
    }

    public function updateData($data)
    {
        $this->emit('updateData', $data);
        $this->label = null;
    }

    public function refreshData($data)
    {
        $this->data = collect($data['data'])->where('page_id', $this->page->id);
        /*if (isset($data['nextLabel'])) {
            $this->nextLabel = $data['nextLabel'];
        }*/
        //ray($this->data);
        //$this->emit('updateData', $data);
    }

    public function deleteData($data)
    {
        $this->emit('deleteData', $data);
    }

    public function dragEnd($data)
    {
        $this->emit('dragEnd', $data);
    }

    public function stop($data)
    {
        $this->emit('updateData', $data);
    }

    public function updateLastStyle($data)
    {
        $this->emit('setLastStyle', $data);
    }

    public function increaseNextLabel()
    {
        $this->emit('setNextLabel', ($this->nextLabel + 1));
        //$this->nextLabel++;
    }

    public function setNextLabel($nextLabel)
    {
        $this->nextLabel = $nextLabel;
    }

    public function setLastStyle($lastStyle)
    {
        $this->lastStyle = $lastStyle;
    }
}
