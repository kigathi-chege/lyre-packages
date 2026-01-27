<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">

    @php
        $multiple = $getMultiple();
    @endphp

    <div x-data="{
        state: $wire.$entangle('{{ $getStatePath() }}'),
        selectedFiles: @js($getSelectedFiles()),
    }" x-init="function() {
        this.state = this.selectedFiles.map(file => file.id);
        $watch('selectedFiles', () => {
            this.state = this.selectedFiles.map(file => file.id);
        });
    }" x-restore="selectedFiles">
        <x-filament::button color="gray" size="md" class="w-full px-3 py-1/5" alignment="start"
            x-on:click="$dispatch('open-modal', { id: 'select-file' })">
            <div class="inset-0 flex flex-row items-center justify-start px-3 py-1/5 text-gray-950 font-normal">
                <template x-if="selectedFiles.length > 0">
                    <div class="flex flex-row items-center justify-center gap-2 flex-wrap">
                        {{-- TODO: Kigathi - July 19 2025 - Implement file previews as in file-gallery.blade --}}
                        <template x-for="file in selectedFiles">
                            <img :src="file.link" alt="i" class="h-16 w-16 rounded-md object-cover">
                        </template>
                    </div>
                </template>
                <template x-if="selectedFiles.length == 0">
                    <img src="{{ asset('lyre/file/placeholder.webp') }}" alt="i"
                        class="h-16 w-16 rounded-md object-cover">
                </template>
            </div>
        </x-filament::button>

        <x-filament::modal id="select-file" width="6xl">
            <x-slot name="heading">
                {{-- Select File --}}
            </x-slot>
            @livewire('file-gallery', ['selectedFiles' => $getSelectedFiles(), 'multiple' => $multiple])
        </x-filament::modal>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.directive('restore', (el, {
                expression
            }, {
                evaluate
            }) => {
                el.addEventListener('filesSelected', (event) => {
                    const files = event.detail.files;
                    evaluate(`${expression} = ${JSON.stringify(files)}`);
                });
            })
        });
    </script>
</x-dynamic-component>
