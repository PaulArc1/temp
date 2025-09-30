<?php

namespace App\Http\Livewire\Documents;

use App\Exports\InspectionReport;
use App\Models\Document;
use App\Models\Equipment;
use App\Models\EquipmentType;
use App\Models\PageItem;
use Illuminate\Database\QueryException;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use WireUi\Traits\Actions;

class Show extends Component
{
    use Actions;

    public $document;

    public $showDataTable = false;

    public $data = [];

    public $equipmentType;

    public $equipment;

    public $equipmentId;

    public $features;

    public $nextLabel;

    public $lastStyle = [];

    public $deletedUuids = [];

    public $meta = [];

    public $inspectedOn;

    public $date;

    protected $listeners = [
        'pageItemAdded',
        'updateData',
        'deleteData',
        'dragEnd',
        'syncJson',
        'setNextLabel',
        'setLastStyle',
    ];

    public function mount($uuid)
    {
        $this->document = Document::where('uuid', $uuid)->firstOrFail();

        foreach ($this->document->pages as $page) {
            foreach ($page->pageItems as $pageItem) {
                $this->data[$pageItem->uuid] = [
                    //'label' => $pageItem->label,
                    'uuid' => $pageItem->uuid,
                    'page_id' => $page->id,
                    'data' => $pageItem->data,
                    'values' => $pageItem->values ?? [],
                ];
            }
        }

        $this->data = collect($this->data)->sortBy('data.label');

        /* $equipment = nova_get_setting('equipment');
         $this->equipment = collect($equipment->map(function ($equipment) {
             return ['name' => $equipment->name, 'value' => $equipment->name];
         }));*/

        $this->equipmentType = EquipmentType::all()->where('team_id', auth()->user()->currentTeam->id)->map(function ($equipmentType) {
            return [
                'name' => $equipmentType->name,
                'value' => $equipmentType->id,
            ];
        });

        /*$equipmentId = nova_get_setting('equipment_id');
        $this->equipmentId = collect($equipmentId->map(function ($equipmentId) {
            return ['name' => $equipmentId->id, 'value' => $equipmentId->id];
        }));*/

        $equipmentModel = Equipment::all();
        $equipment = [];
        foreach ($equipmentModel as $equipmentItem) {
            $equipment[$equipmentItem->equipment_type_id][] = ['name' => $equipmentItem->name, 'value' => $equipmentItem->id];
        }
        $this->equipment = $equipment;

        $features = nova_get_setting('features');
        $this->features = collect($features->map(function ($feature) {
            return ['name' => $feature->name, 'value' => $feature->name];
        }));

        $this->nextLabel = $this->document->pageItems->count() + 1;

        $this->meta = $this->document->meta;

        $this->inspectedOn = $this->document->inspected_on;
    }

    public function render()
    {
        return view('livewire.documents.show');
    }

    public function save()
    {
        //foreach ($this->data as $pageId => $data) {
        foreach ($this->data as $itemId => $d) {
            //ray($itemId);
            try {
                PageItem::updateOrCreate([
                    'uuid' => $itemId,
                ], [
                    'uuid' => $itemId,
                    'page_id' => $d['page_id'] ?? null,
                    'data' => $d['data'] ?? null,
                    'values' => $d['values'] ?? null,
                ]);
            } catch (QueryException $e) {
                //ray($e->getMessage());
                //ray($d);
            }
        }

        foreach ($this->deletedUuids as $uuid) {
            PageItem::where('uuid', $uuid)->delete();
        }

        $this->document->update([
            'inspected_on' => $this->inspectedOn,
            'meta' => $this->meta,
        ]);

        //$this->showDataTable = false;

        $this->notification()->success(
            $title = 'Document Saved',
            $description = 'The document has been saved'
        );
    }

    public function pageItemAdded($data)
    {
        $this->data[$data['uuid']] = [
            'data' => $data['data'],
            'page_id' => $data['page_id'],
            'uuid' => $data['uuid'],
        ];

        $this->data = collect($this->data)->sortBy('data.label')->toArray();

        $this->emit('refreshData', ['data' => $this->data]);
    }

    public function updateData($data)
    {
        $this->data = collect($this->data);

        $updatedLabel = $data['data']['pageItem']['label'];

        //Check if label already exists
        $labelExists = $this->data->where('data.label', $updatedLabel)->count();

        if ($labelExists) {
            $checkLabel = $this->data->where('data.label', '>=', $updatedLabel)
                                      ->where('uuid', '!=', $data['data']['pageItem']['uuid']);

            if ($checkLabel->count() > 0) {
                $checkLabel->each(function ($item, $key) use ($data) {
                    ray('Loop');
                    ray('Was: '.$item['data']['label'])->green();
                    ray('Now: '.($item['data']['label'] + 1))->blue();
                    $newLabel = $item;

                    if ($data['data']['pageItem']['uuid'] != $item['uuid']) {
                        $newLabel['data']['label'] = $item['data']['label'] + 1;
                        $this->data[$item['uuid']] = $newLabel;
                    }
                });
            } else {
                ray('Bleh');
                $item = $this->data->where('uuid', $data['data']['pageItem']['uuid'])->first();

                $newLabel = [
                    ...$this->data[$data['data']['pageItem']['uuid']],
                    'data' => $data['data']['pageItem'],
                ];

                $this->data[$item['uuid']] = $newLabel;
            }
        }

        if ($labelExists) {
            $labelData = [];
            foreach ($this->data as $d) {
                $labelData[$d['uuid']] = $d['data']['label'];
            }
            $this->emit('updateLabels', ['data' => $labelData]);

            //Set Next Label
            $this->data = $this->data->sortBy('data.label');

            $this->emit('setNextLabel', ($this->data->last()['data']['label'] + 1));
        }
    }

    public function deleteData($data)
    {
        $this->deletedUuids[] = $data['uuid'];

        unset($this->data[$data['uuid']]);
    }

    public function dragEnd($data)
    {
        $update = $this->data[$data['uuid']]['data'];

        $update['x'] = $data['x'];
        $update['y'] = $data['y'];

        $this->data[$data['uuid']] = [
            'data' => $update,
            'page_id' => $this->data[$data['uuid']]['page_id'],
            'uuid' => $this->data[$data['uuid']]['uuid'],
            'values' => $this->data[$data['uuid']]['values'] ?? [],
        ];
    }

    public function syncJson($data)
    {
        //$this->json[$data['page']] = $data['json'];
    }

    public function toggleDataTable()
    {
        $this->data = collect($this->data)->sortBy('data.label');
        $this->showDataTable = ! $this->showDataTable;
    }

    public function closeModal()
    {
        $this->showDataTable = false;
    }

    public function downloadInspection($format)
    {
        $this->save();

        if ($format == 'xlsx') {
            return Excel::download(new InspectionReport($this->document), 'inspection-report.xlsx');
        } elseif ($format == 'pdf') {
            return Excel::download(new InspectionReport($this->document), 'inspection-report.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
        }
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
