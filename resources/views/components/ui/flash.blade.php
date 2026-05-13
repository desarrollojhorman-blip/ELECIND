@if (session('status'))
    <div x-data="{ show: true }"
         x-show="show"
         x-init="setTimeout(() => show = false, 5000)"
         x-transition.duration.300ms
         class="mb-4 flex items-start gap-2 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
        <x-heroicon-o-check-circle class="mt-0.5 size-4 shrink-0" />
        <span class="flex-1">{{ session('status') }}</span>
        <button type="button" @click="show = false" class="rounded p-0.5 hover:bg-emerald-100">
            <x-heroicon-m-x-mark class="size-3.5" />
        </button>
    </div>
@endif

@if (session('error'))
    <div x-data="{ show: true }"
         x-show="show"
         x-init="setTimeout(() => show = false, 5000)"
         x-transition.duration.300ms
         class="mb-4 flex items-start gap-2 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <x-heroicon-o-exclamation-triangle class="mt-0.5 size-4 shrink-0" />
        <span class="flex-1">{{ session('error') }}</span>
        <button type="button" @click="show = false" class="rounded p-0.5 hover:bg-red-100">
            <x-heroicon-m-x-mark class="size-3.5" />
        </button>
    </div>
@endif
