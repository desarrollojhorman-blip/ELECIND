<?php if (isset($component)) { $__componentOriginal0074217baee23f3172c031133efa0b34 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0074217baee23f3172c031133efa0b34 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.mobile','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.mobile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <div class="space-y-3 px-4 py-5">
        <?php if (isset($component)) { $__componentOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile.menu-action','data' => ['href' => ''.e(route('mobile.albaranes.nuevo')).'','variant' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile.menu-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('mobile.albaranes.nuevo')).'','variant' => 'primary']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            Parte de Trabajo
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616)): ?>
<?php $attributes = $__attributesOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616; ?>
<?php unset($__attributesOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616)): ?>
<?php $component = $__componentOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616; ?>
<?php unset($__componentOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616); ?>
<?php endif; ?>

        <?php if (isset($component)) { $__componentOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile.menu-action','data' => ['href' => ''.e(route('mobile.albaranes.personalizado')).'','variant' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile.menu-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('mobile.albaranes.personalizado')).'','variant' => 'primary']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            Parte personalizado
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616)): ?>
<?php $attributes = $__attributesOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616; ?>
<?php unset($__attributesOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616)): ?>
<?php $component = $__componentOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616; ?>
<?php unset($__componentOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616); ?>
<?php endif; ?>

        <?php if (isset($component)) { $__componentOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile.menu-action','data' => ['href' => ''.e(route('mobile.albaranes.index')).'','variant' => 'outline','icon' => 'heroicon-o-document-text']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile.menu-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('mobile.albaranes.index')).'','variant' => 'outline','icon' => 'heroicon-o-document-text']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            Gestión de Albaranes
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616)): ?>
<?php $attributes = $__attributesOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616; ?>
<?php unset($__attributesOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616)): ?>
<?php $component = $__componentOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616; ?>
<?php unset($__componentOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616); ?>
<?php endif; ?>

        <?php if (isset($component)) { $__componentOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile.menu-action','data' => ['href' => ''.e(route('mobile.ausencias.index')).'','variant' => 'outline','icon' => 'heroicon-o-calendar-days']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile.menu-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('mobile.ausencias.index')).'','variant' => 'outline','icon' => 'heroicon-o-calendar-days']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            Faltas de Asistencia
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616)): ?>
<?php $attributes = $__attributesOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616; ?>
<?php unset($__attributesOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616)): ?>
<?php $component = $__componentOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616; ?>
<?php unset($__componentOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616); ?>
<?php endif; ?>

        <?php if (isset($component)) { $__componentOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile.menu-action','data' => ['href' => ''.e(route('mobile.resumen.index')).'','variant' => 'outline','icon' => 'heroicon-o-chart-bar']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile.menu-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('mobile.resumen.index')).'','variant' => 'outline','icon' => 'heroicon-o-chart-bar']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            Resumen mensual
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616)): ?>
<?php $attributes = $__attributesOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616; ?>
<?php unset($__attributesOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616)): ?>
<?php $component = $__componentOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616; ?>
<?php unset($__componentOriginal0b2af72e6cdf4a1a6ebecb0b9ad44616); ?>
<?php endif; ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0074217baee23f3172c031133efa0b34)): ?>
<?php $attributes = $__attributesOriginal0074217baee23f3172c031133efa0b34; ?>
<?php unset($__attributesOriginal0074217baee23f3172c031133efa0b34); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0074217baee23f3172c031133efa0b34)): ?>
<?php $component = $__componentOriginal0074217baee23f3172c031133efa0b34; ?>
<?php unset($__componentOriginal0074217baee23f3172c031133efa0b34); ?>
<?php endif; ?>
<?php /**PATH D:\xampp\htdocs\CLIENTES\ELECIND\resources\views/mobile/dashboard.blade.php ENDPATH**/ ?>