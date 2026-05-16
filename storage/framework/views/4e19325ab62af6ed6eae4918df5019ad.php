
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'wireModel',
    'options'     => [],
    'placeholder' => '— Selecciona —',
    'disabled'    => false,
    'entangle'    => null,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'wireModel',
    'options'     => [],
    'placeholder' => '— Selecciona —',
    'disabled'    => false,
    'entangle'    => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    use Illuminate\Support\Js;
    $optionsJson = Js::from(
        collect($options)->map(fn ($o) => [
            'value' => is_array($o) ? $o['value'] : $o->value,
            'label' => is_array($o) ? $o['label'] : $o->label,
        ])->values()
    );
    $disabledJs = $disabled ? 'true' : 'false';
?>

<div
    <?php echo e($attributes->only('wire:key')); ?>

    x-data="{
        open:     false,
        search:   '',
        selected: null,
        disabled: <?php echo e($disabledJs); ?>,
        options: <?php echo e($optionsJson); ?>,
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
            const v = this.$refs.hiddenInput?.value;
            if (v) {
                this.selected = this.options.find(o => String(o.value) === String(v)) ?? null;
            }
            this.$watch('open', val => {
                if (val) this.$nextTick(() => this.$refs.searchInput?.focus());
            });
        }
    }"
    x-on:click.outside="open = false"
    class="relative"
>
    <input type="hidden" wire:model.live="<?php echo e($wireModel); ?>" x-ref="hiddenInput" />

    
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
            x-text="selected ? selected.label : '<?php echo e(addslashes($placeholder)); ?>'"
            :class="{ 'text-slate-400': !selected }"
            class="min-w-0 flex-1 truncate"
        ></span>
        <span class="ml-2 flex shrink-0 items-center gap-1">
            <span
                x-show="selected && !disabled"
                x-on:click.stop="clear()"
                class="flex size-4 cursor-pointer items-center justify-center rounded text-slate-400 hover:text-slate-700"
                title="Limpiar selección"
            ><?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-x-mark'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-3']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?></span>
            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-chevron-down'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-4 text-slate-400 transition-transform duration-150','x-bind:class' => '{ \'rotate-180\': open }']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
        </span>
    </button>

    
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
        
        <div class="border-b border-slate-100 px-2 py-2">
            <div class="flex items-center gap-2 rounded border border-slate-200 bg-slate-50 px-2 py-1">
                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-magnifying-glass'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-4 shrink-0 text-slate-400']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
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
<?php /**PATH D:\xampp\htdocs\CLIENTES\ELECIND\resources\views/components/ui/searchable-select.blade.php ENDPATH**/ ?>