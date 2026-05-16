<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => null,
    'showHeader' => true,
    'showBack' => false,
    'backRoute' => null,
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
    'title' => null,
    'showHeader' => true,
    'showBack' => false,
    'backRoute' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="theme-color" content="<?php echo e(\App\Support\Branding::colorPrimario()); ?>">
    <title><?php echo e($title ? $title.' · '.\App\Support\Branding::nombre() : \App\Support\Branding::nombre()); ?></title>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! app()->runningUnitTests()): ?>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <div class="mx-auto flex min-h-screen max-w-[480px] flex-col bg-white shadow-sm sm:my-4 sm:min-h-[calc(100vh-2rem)] sm:rounded-lg">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showHeader): ?>
            <?php if (isset($component)) { $__componentOriginal304273b66860e5703fad07daf67109e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal304273b66860e5703fad07daf67109e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile.header','data' => ['title' => $title,'showBack' => $showBack,'backRoute' => $backRoute]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile.header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($title),'show-back' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($showBack),'back-route' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($backRoute)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal304273b66860e5703fad07daf67109e9)): ?>
<?php $attributes = $__attributesOriginal304273b66860e5703fad07daf67109e9; ?>
<?php unset($__attributesOriginal304273b66860e5703fad07daf67109e9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal304273b66860e5703fad07daf67109e9)): ?>
<?php $component = $__componentOriginal304273b66860e5703fad07daf67109e9; ?>
<?php unset($__componentOriginal304273b66860e5703fad07daf67109e9); ?>
<?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <main class="flex-1 overflow-y-auto">
            <?php if (isset($component)) { $__componentOriginalfc2c53cdc76e51152b8f2296be83e0da = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfc2c53cdc76e51152b8f2296be83e0da = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.flash','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.flash'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfc2c53cdc76e51152b8f2296be83e0da)): ?>
<?php $attributes = $__attributesOriginalfc2c53cdc76e51152b8f2296be83e0da; ?>
<?php unset($__attributesOriginalfc2c53cdc76e51152b8f2296be83e0da); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfc2c53cdc76e51152b8f2296be83e0da)): ?>
<?php $component = $__componentOriginalfc2c53cdc76e51152b8f2296be83e0da; ?>
<?php unset($__componentOriginalfc2c53cdc76e51152b8f2296be83e0da); ?>
<?php endif; ?>
            <?php echo e($slot); ?>

        </main>
    </div>

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

</body>
</html>
<?php /**PATH D:\xampp\htdocs\CLIENTES\ELECIND\resources\views/components/layouts/mobile.blade.php ENDPATH**/ ?>