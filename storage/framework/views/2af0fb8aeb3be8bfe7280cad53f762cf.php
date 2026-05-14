<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['rows' => 3]));

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

foreach (array_filter((['rows' => 3]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<textarea rows="<?php echo e($rows); ?>"
          <?php echo e($attributes->class('w-full appearance-none rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-none transition-colors placeholder:text-slate-400 focus:border-primary-500 focus:outline-none focus:ring-0 focus:shadow-none')); ?>><?php echo e($slot); ?></textarea>
<?php /**PATH D:\xampp\htdocs\CLIENTES\ELECIND\resources\views/components/ui/textarea.blade.php ENDPATH**/ ?>