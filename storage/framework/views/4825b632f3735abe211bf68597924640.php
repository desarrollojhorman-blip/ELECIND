<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'icon' => null,
    'variant' => 'ghost',
    'tooltip' => null,
    'type' => 'button',
    'size' => 'md',
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
    'icon' => null,
    'variant' => 'ghost',
    'tooltip' => null,
    'type' => 'button',
    'size' => 'md',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $sizes = [
        'sm' => 'size-7 [&_svg]:size-3.5',
        'md' => 'size-8 [&_svg]:size-4',
        'lg' => 'size-9 [&_svg]:size-5',
    ];

    $variants = [
        'ghost' => 'text-slate-500 hover:bg-slate-100 hover:text-slate-900',
        'primary' => 'text-primary-600 hover:bg-primary-50',
        'danger' => 'text-red-600 hover:bg-red-50',
        'success' => 'text-emerald-600 hover:bg-emerald-50',
        'info' => 'text-blue-600 hover:bg-blue-50',
        'warning' => 'text-amber-600 hover:bg-amber-50',
        'soft-danger' => 'bg-red-50 text-red-700 hover:bg-red-100',
        'soft-primary' => 'bg-primary-50 text-primary-700 hover:bg-primary-100',
        'soft-info' => 'bg-blue-50 text-blue-700 hover:bg-blue-100',
        'soft-success' => 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100',
    ];

    $classes = trim('inline-flex items-center justify-center rounded-md transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-1 focus-visible:ring-primary-500 disabled:opacity-50 disabled:pointer-events-none '.($sizes[$size] ?? $sizes['md']).' '.($variants[$variant] ?? $variants['ghost']));
?>

<button type="<?php echo e($type); ?>"
        <?php if($tooltip): ?> title="<?php echo e($tooltip); ?>" aria-label="<?php echo e($tooltip); ?>" <?php endif; ?>
        <?php echo e($attributes->class($classes)); ?>>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($icon): ?>
        <?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => $icon] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\DynamicComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $attributes = $__attributesOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $component = $__componentOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__componentOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
    <?php else: ?>
        <?php echo e($slot); ?>

    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</button>
<?php /**PATH C:\xampp\htdocs\CLIENTES\ELECIND\resources\views/components/ui/icon-button.blade.php ENDPATH**/ ?>