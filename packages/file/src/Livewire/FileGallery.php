<?php

namespace Lyre\File\Livewire;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;
use Lyre\File\Repositories\Contracts\FileRepositoryInterface;

class FileGallery extends Component implements HasForms
{
    use InteractsWithForms;

    public bool $addNew = false;

    public ?array $data = [];

    public int $page = 1, $perPage = 8, $lastPage = 1;
    public array $files = [], $selectedFiles = [];
    public bool $multiple = false, $previousPage = false, $nxtPage = false;
    public $state;

    public function mount()
    {
        $this->loadFiles();
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('file')
                    ->label('')
                    ->imagePreviewHeight('250')
                    ->loadingIndicatorPosition('left')
                    ->panelAspectRatio('3:1')
                    ->panelLayout('integrated')
                    ->removeUploadedFileButtonPosition('right')
                    ->uploadButtonPosition('left')
                    ->uploadProgressIndicatorPosition('left')
                    ->storeFileNamesIn('attachment_file_names')
                    ->previewable(true)
                    ->multiple(false)
                    ->imageEditor()
                    ->columnSpanFull()
                    ->rules(['mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,jpg,jpeg,png,gif,svg,webp,mp4,mp3,txt'])
                    ->acceptedFileTypes([
                        'application/pdf',
                        'image/*',
                        'video/*',
                        'audio/*',
                        'text/*',

                        // Microsoft Office documents
                        'application/msword',                    // .doc
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
                        'application/vnd.ms-excel',              // .xls
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
                        'application/vnd.ms-powerpoint',         // .ppt
                        'application/vnd.openxmlformats-officedocument.presentationml.presentation', // .pptx

                        // text
                        'text/csv',
                        'text/plain',

                        // Archives
                        'application/zip',
                        'application/x-zip-compressed',
                        'multipart/x-zip',
                    ])
                    ->imageEditorAspectRatios([
                        '16:9',
                        '4:3',
                        '1:1',
                    ]),

            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $record = \Lyre\File\Actions\CreateFile::make($this->form->getState());
        $this->addNew = false;
        $this->page = $this->lastPage;
        $this->loadFiles();
        $this->dispatch('refresh-gallery-state', uploadedFile: $record);
        $this->form->fill([]);
    }

    public function loadFiles()
    {
        $repo = app(FileRepositoryInterface::class);
        $data = $repo->paginate($this->perPage, $this->page)->all();
        $this->page = $data['meta']['current_page'];
        $this->perPage = $data['meta']['per_page'];
        $this->lastPage = $data['meta']['last_page'];
        $this->previousPage = $this->page > 1;
        $this->nxtPage = $this->page < $this->lastPage;
        $this->files = $data['data']->resolve();
    }

    public function nextPage()
    {
        $this->page++;
        $this->loadFiles();
    }

    public function prevPage()
    {
        if ($this->page > 1) {
            $this->page--;
            $this->loadFiles();
        }
    }

    public function render()
    {
        return view('lyre.file::livewire.file-gallery');
    }
}
