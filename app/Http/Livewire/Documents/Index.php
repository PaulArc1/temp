<?php

namespace App\Http\Livewire\Documents;

use App\Models\Document;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\ComponentAttributeBag;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use WireUi\Traits\Actions;

class Index extends DataTableComponent
{
    use Actions;

    //protected $model = Document::class;

    public function builder(): Builder
    {
        return Document::query()->where('team_id', auth()->user()->currentTeam->id);
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

            Column::make('User ID', 'user_id')->hideIf(true)
                ->sortable(),

            Column::make('Created By', 'user_id')
                ->label(
                    fn ($row, Column $column) => $row->user->name //$row->user_id
                ),

            Column::make('Created at', 'created_at')
                ->sortable(),
            Column::make('Updated at', 'updated_at')
                ->sortable(),

            Column::make('Actions', 'uuid')
                ->format(function ($id) {
                    return view('livewire.documents.document-actions')
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
            'title' => 'Are you sure you would like to delete this document?',
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
        $document = Document::where('uuid', $data['id'])->first();
        $document->pageItems()->delete();
        $document->pages()->delete();
        $document->delete();

        Storage::deleteDirectory('documents/'.config('team.id').'/'.$data['id']);

        $this->notification()->error(
            $title = 'Document Deleted',
            $description = 'The document has been deleted and all associated files have been removed. THESE CAN NOT BE RECOVERED '
        );
    }
}
