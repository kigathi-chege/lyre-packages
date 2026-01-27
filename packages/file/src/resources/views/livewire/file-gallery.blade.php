<div x-data="{
    tempState: [],
    state: @js($state),
    tempSelectedFiles: @js($selectedFiles),
    selectedFiles: @js($selectedFiles),
    multiple: @js($multiple),
    previousPage: @entangle('previousPage'),
    nxtPage: @entangle('nxtPage'),
    toggleSelection(fileId, fileName, fileMime, fileLink) {
        if (this.tempState == null) {
            this.tempState = []
        }
        if (this.tempState?.includes(fileId)) {
            this.tempState = this.tempState.filter(id => id !== fileId);
            this.tempSelectedFiles = this.tempSelectedFiles.filter(file => file.id !== fileId);
        } else {
            if (!this.multiple) {
                this.tempState = []
                this.tempSelectedFiles = []
            }
            this.tempState.push(fileId);
            this.tempSelectedFiles.push({ id: fileId, name: fileName, mimetype: fileMime, link: fileLink });
        }
    },
    selectFiles() {
        this.state = this.tempState;
        this.selectedFiles = this.tempSelectedFiles;
        $dispatch('filesSelected', { files: this.selectedFiles });
    },
    refreshState(uploadedFile = null) {
        if (uploadedFile) {
            this.selectedFiles.push(uploadedFile);
            this.tempSelectedFiles.push(uploadedFile);
        }
        this.state = this.selectedFiles.map(file => file.id);
        this.tempState = this.state;
    }
}" x-init="refreshState()"
    @refresh-gallery-state.window="refreshState($event.detail.uploadedFile)">
    <div class="flex flex-col space-y-4">
        <div>
            <x-filament::tabs class="ring-0" label="Content tabs">
                <x-filament::tabs.item :active="!$addNew" icon="heroicon-m-bell" wire:click="$set('addNew', false)">
                    View All
                </x-filament::tabs.item>

                <x-filament::tabs.item :active="$addNew" icon="heroicon-m-bell" wire:click="$set('addNew', true)">
                    Add New
                </x-filament::tabs.item>
            </x-filament::tabs>
        </div>

        @if (!$addNew)
            <div class="grid grid-cols-4 gap-4 justify-center items-center">
                @foreach ($files as $file)
                    <div class="rounded-md h-64 relative cursor-pointer border-2"
                        :class="tempState?.includes({{ $file['id'] }}) ? 'border-green-500' : 'border-transparent'"
                        @click="toggleSelection(@js($file['id']), @js($file['name']), @js($file['mimetype'] ?? null), @js($file['link']))">
                        <template x-if="tempState?.includes({{ $file['id'] }})">
                            <div
                                class="absolute top-0 right-0 w-0 h-0 border-t-[40px] border-t-green-500 border-l-[40px] border-l-transparent">
                            </div>
                        </template>
                        <div class="absolute inset-0 hover:bg-black hover:opacity-25 rounded-md"
                            :class="tempState?.includes({{ $file['id'] }}) ? 'bg-green-600 opacity-25' : 'bg-transparent'">
                        </div>

                        @php
                            $ext = strtolower($file['extension'] ?? '');
                        @endphp

                        @if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp']))
                            <img src="{{ $file['link'] }}" alt="{{ $file['name'] }}"
                                class="w-full h-full object-cover rounded-md">
                        @elseif ($ext === 'pdf')
                            <iframe src="{{ $file['link'] }}" class="w-full h-full rounded-md" frameborder="0"></iframe>
                        @elseif (in_array($ext, ['mp4', 'webm']))
                            <video controls class="w-full h-full object-cover rounded-md">
                                <source src="{{ $file['link'] }}" type="video/{{ $ext }}">
                                Your browser does not support the video tag.
                            </video>
                        @elseif ($ext === 'xlsx')
                            <div class="flex flex-col items-center justify-center h-full p-4 text-center">
                                <img src="{{ asset('xlsx.png') }}" alt="Excel" class="w-16 h-16 mb-2">
                                <p class="text-sm">{{ $file['name'] }}</p>
                                <p class="text-xs text-gray-500">Excel file - no preview</p>
                            </div>
                        @elseif ($ext === 'md')
                            <div class="flex flex-col items-center justify-center h-full p-4 text-center overflow-auto">
                                <img src="{{ asset('md.png') }}" alt="Markdown" class="w-16 h-16 mb-2">
                                <p class="text-sm">{{ $file['name'] }}</p>
                                <p class="text-xs text-gray-500">Markdown preview coming soon</p>
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center gap-4 h-full w-full">
                                <img src="{{ asset("{$ext}.png") }}" alt="{{ $ext }}" class="h-20 w-20">
                                <p class="text-sm">{{ $file['name'] }}</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <form wire:submit.prevent="create">
                {{ $this->form }}
            </form>
        @endif

        <div class="mt-4 flex justify-between" id="sth">
            <div class="flex gap-4">
                {{-- TODO: Kigathi - July 19 2025 - Implement actual pagination with page numbers --}}
                <x-filament::button wire:click="prevPage" color="gray"
                    x-bind:disabled="!previousPage">Previous</x-filament::button>
                <x-filament::button wire:click="nextPage" color="gray"
                    x-bind:disabled="!nxtPage">Next</x-filament::button>
            </div>
            <div class="flex gap-4">
                @if (!$addNew)
                    <x-filament::button @click="selectFiles; $dispatch('close-modal', { id: 'select-file' })">
                        Select Files
                    </x-filament::button>
                @else
                    <x-filament::button color="info" wire:click="create">
                        Submit
                    </x-filament::button>
                @endif
                <x-filament::button @click="tempstate = []; state = []; $dispatch('close-modal', { id: 'select-file' })"
                    color="gray" x-on:click="tempState=null">
                    Cancel
                </x-filament::button>
            </div>
        </div>
    </div>
</div>
