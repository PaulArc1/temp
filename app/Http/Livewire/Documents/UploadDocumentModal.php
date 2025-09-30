<?php

namespace App\Http\Livewire\Documents;

use App\Jobs\ProcessDocument;
use App\Models\Document;
use App\Models\Page;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class UploadDocumentModal extends Component
{
    use WithFileUploads;

    public $name;

    public $file;

    public $showModal;

    protected $listeners = ['openModal' => 'openModal'];

    public function openModal()
    {
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.documents.upload-document-modal');
    }

    public function upload()
    {
        ini_set('max_execution_time', 180);

        $this->validate([
            'file' => 'required|mimetypes:application/pdf|max:20480', // 20MB Max
            'name' => 'required',
        ]);

        if (! Storage::exists(storage_path('app/uploads'))) {
            Storage::makeDirectory('uploads');
        }

        $filename = $this->file->store('uploads');

        $document = Document::create([
            'user_id' => \Auth::user()->id,
            'team_id' => \Auth::user()->currentTeam->id,
            'name' => $this->name,
            'file' => $filename,
        ]);

        if (! Storage::exists(storage_path('app/documents/'.auth()->user()->currentTeam->id.'/'.$document->id))) {
            Storage::makeDirectory('documents/'.auth()->user()->currentTeam->id.'/'.$document->id);
        }

        //ProcessDocument::dispatch($document);

        $uploadfile = storage_path('app/'.$filename);
        $directory = storage_path('app/documents/'.auth()->user()->currentTeam->id.'/'.$document->id);

        if (PHP_OS_FAMILY == 'Darwin') {
            $bin = 'vendor/bin/mutool-macos';
        } else {
            $bin = 'vendor/bin/mutool';
        }

        $pdf = new \Karkow\MuPdf\Pdf($uploadfile, base_path($bin));

        $numberOfPages = $pdf->numberOfPages();

        if ($numberOfPages === 0) {
            return [];
        }

        array_map(function ($pageNumber) use ($directory, $pdf, $document) {
            $pdf->setPage($pageNumber);

            $page = Page::create([
                'document_id' => $document->id,
                'page_number' => $pageNumber,
                'uuid' => Str::uuid(),
            ]);

            $destination = $directory.'/'.$page->uuid.'.jpg';
            $pdf->saveImage($destination);

            return $destination;
        }, range(1, $numberOfPages));

        ray('STorae delete: '.storage_path('app/'.$filename));

        Storage::delete($filename);

        return redirect()->route('dashboard')->with('message', 'Document Successfully Uploaded.');
    }
}
