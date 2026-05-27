{{--
    Searchable select para listas largas. Integra con Livewire 3 vía $wire.set().

    Uso estático (opciones PHP, no reactivas):
    <x-ui.searchable-select
        wire-model="campo"
        :options="$collection->map(fn($m) => ['value' => $m->id, 'label' => $m->nombre])"
        placeholder="— Selecciona —"
    />

    Uso reactivo (watch a una propiedad pública de Livewire):
    <x-ui.searchable-select
        wire-model="campo"
        entangle="nombrePropiedadLivewire"
        placeholder="— Selecciona —"
    />

    Cada opción: array con claves 'value' y 'label'.
--}}
@props([
    'wireModel',
    'options'     => [],
    'placeholder' => '— Selecciona —',
    'disabled'    => false,
    'entangle'    => null,
    'value'       => null,
])

@php
    use Illuminate\Support\Js;
    $optionsJson = Js::from(
        collect($options)->map(fn ($o) => [
            'value' => is_array($o) ? $o['value'] : $o->value,
            'label' => is_array($o) ? $o['label'] : $o->label,
        ])->values()
    );
    $disabledJs      = $disabled ? 'true' : 'false';
    $initialValueJs  = $value !== null ? Js::from((string) $value) : 'null';
@endphp

<div
    {{ $attributes->only('wire:key') }}
    x-data="{
        open:         false,
        search:       '',
        selected:     (function() { var v = {{ $initialValueJs }}; var opts = {{ $optionsJson }}; if (v !== null) { return opts.find(function(o) { return String(o.value) === String(v); }) || null; } return null; })(),
        disabled:     {{ $disabledJs }},
        initialValue: {{ $initialValueJs }},
        options:      {{ $optionsJson }},
        get filtered() {
            if (!this.search.trim()) return this.options;
            const q = this.search.toLowerCase();
            return this.options.filter(o => o.label.toLowerCase().includes(q));
        },
        select(opt) {
            if (this.disabled) return;
            this.selected = opt;
            this.open     = false;
            this.search   = '';
            this.$refs.hiddenInput.value = opt.value;
            this.$refs.hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
        },
        clear() {
            this.selected = null;
            this.search   = '';
            this.$refs.hiddenInput.value = '';
            this.$refs.hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
        },
        init() {
            const v = this.initialValue !== null
                ? String(this.initialValue)
                : (this.$refs.hiddenInput?.value ?? '');
            if (v) {
                this.selected = this.options.find(o => String(o.value) === v) ?? null;
            }
            this.$watch('open', val => {
                if (val) this.$nextTick(() => this.$refs.searchInput?.focus());
            });
        }
    }"
    x-on:click.outside="open = false"
    class="relative"
>
    <input type="hidden" wire:model.live="{{ $wireModel }}" x-ref="hiddenInput" />

    {{-- Trigger --}}
    <button
        type="button"
        x-on:click="if (!disabled) open = !open"
        :class="{
            'border-primary-500 ring-1 ring-primary-200': open,
            'cursor-not-allowed bg-slate-50 text-slate-500 pointer-events-none': disabled,
            'cursor-pointer hover:border-slate-400': !disabled
        }"
        class="flex w-full items-center justify-between rounded-md border border-slate-300 bg-white px-3 py-2 text-left text-sm transition-colors"
    >
        <span
            x-text="selected ? selected.label : '{{ addslashes($placeholder) }}'"
            :class="{ 'text-slate-400': !selected }"
            class="min-w-0 flex-1 truncate"
        ></span>
        <span class="ml-2 flex shrink-0 items-center gap-1">
            <span
                x-show="selected && !disabled"
                x-on:click.stop="clear()"
                class="flex size-4 cursor-pointer items-center justify-center rounded text-slate-400 hover:text-slate-700"
                title="Limpiar selección"
            ><x-heroicon-o-x-mark class="size-3" /></span>
            <x-heroicon-o-chevron-down
                class="size-4 text-slate-400 transition-transform duration-150"
                x-bind:class="{ 'rotate-180': open }"
            />
        </span>
    </button>

    {{-- Dropdown --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 mt-1 w-full origin-top overflow-hidden rounded-md border border-slate-200 bg-white shadow-lg"
    >
        {{-- Campo búsqueda --}}
        <div class="border-b border-slate-100 px-2 py-2">
            <div class="flex items-center gap-2 rounded border border-slate-200 bg-slate-50 px-2 py-1">
                <x-heroicon-o-magnifying-glass class="size-4 shrink-0 text-slate-400" />
                <input
                    type="text"
                    x-model="search"
                    x-ref="searchInput"
                    x-on:keydown.escape="open = false"
                    x-on:keydown.enter.prevent="if (filtered.length === 1) select(filtered[0])"
                    placeholder="Buscar…"
                    class="w-full bg-transparent text-sm text-slate-700 outline-none placeholder:text-slate-400"
                    autocomplete="off"
                />
            </div>
        </div>

        {{-- Lista de opciones --}}
        <ul class="max-h-52 overflow-y-auto py-1" role="listbox">
            <template x-for="opt in filtered" :key="opt.value">
                <li
                    x-on:click="select(opt)"
                    :class="{
                        'bg-primary-50 text-primary-700 font-medium': selected && selected.value == opt.value,
                        'hover:bg-slate-50': !(selected && selected.value == opt.value)
                    }"
                    class="cursor-pointer px-3 py-2 text-sm text-slate-700"
                    role="option"
                ><span x-text="opt.label"></span></li>
            </template>

            <li x-show="filtered.length === 0" class="px-3 py-2 text-sm italic text-slate-400">
                Sin resultados para "<span x-text="search"></span>"
            </li>
        </ul>
    </div>
</div>
