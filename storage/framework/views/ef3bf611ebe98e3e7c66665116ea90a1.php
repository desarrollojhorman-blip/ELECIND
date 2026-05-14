<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'tone' => 'neutral',
    'dot' => false,
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
    'tone' => 'neutral',
    'dot' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $tones = [
        'neutral' => 'bg-slate-100 text-slate-700',
        'success' => 'bg-emerald-100 text-emerald-700',
        'warning' => 'bg-amber-100 text-amber-800',
        'danger' => 'bg-red-100 text-red-700',
        'info' => 'bg-blue-100 text-blue-700',
        'primary' => 'bg-primary-100 text-primary-700',
        'pending' => 'bg-yellow-200 text-yellow-900',
    ];

    $dotColors = [
        'neutral' => 'bg-slate-400',
        'success' => 'bg-emerald-500',
        'warning' => 'bg-amber-500',
        'danger' => 'bg-red-500',
        'info' => 'bg-blue-500',
        'primary' => 'bg-primary-500',
        'pending' => 'bg-yellow-500',
    ];

    $classes = 'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium '.($tones[$tone] ?? $tones['neutral']);
?>

<span <?php echo e($attributes->class($classes)); ?>>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($dot): ?>
        <span class="inline-block size-1.5 rounded-full <?php echo e($dotColors[$tone] ?? $dotColors['neutral']); ?>"></span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php echo e($slot); ?>

</span>
<?php /**PATH C:\xampp\htdocs\CLIENTES\ELECIND\resources\views/components/ui/badge.blade.php ENDPATH**/ ?>