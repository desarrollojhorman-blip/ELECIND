<div>
    <?php if (isset($component)) { $__componentOriginal91a231a9270579fa1ae9246bd51fb785 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91a231a9270579fa1ae9246bd51fb785 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.page-header','data' => ['title' => 'Clientes','subtitle' => 'Gestión de clientes y sus datos fiscales.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Clientes','subtitle' => 'Gestión de clientes y sus datos fiscales.']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal91a231a9270579fa1ae9246bd51fb785)): ?>
<?php $attributes = $__attributesOriginal91a231a9270579fa1ae9246bd51fb785; ?>
<?php unset($__attributesOriginal91a231a9270579fa1ae9246bd51fb785); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal91a231a9270579fa1ae9246bd51fb785)): ?>
<?php $component = $__componentOriginal91a231a9270579fa1ae9246bd51fb785; ?>
<?php unset($__componentOriginal91a231a9270579fa1ae9246bd51fb785); ?>
<?php endif; ?>

    
    <div class="mb-3">
        <?php if (isset($component)) { $__componentOriginalfcc7a9c111a705a082e127d86b6c3c4d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfcc7a9c111a705a082e127d86b6c3c4d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.search-and-filter','data' => ['searchModel' => 'buscar','placeholder' => 'Buscar por nombre, CIF, email o población…','filtrosAplicados' => $this->filtrosAplicados,'panelToggle' => 'togglePanelFiltros','panelOpen' => $panelFiltrosAbierto,'resetKey' => $resetKey,'clearAllAction' => 'limpiarFiltros','clearSearchAction' => 'limpiarBuscador','hasContentToClear' => $this->tieneAlgoQueLimpiar]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.search-and-filter'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['search-model' => 'buscar','placeholder' => 'Buscar por nombre, CIF, email o población…','filtros-aplicados' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->filtrosAplicados),'panel-toggle' => 'togglePanelFiltros','panel-open' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($panelFiltrosAbierto),'reset-key' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($resetKey),'clear-all-action' => 'limpiarFiltros','clear-search-action' => 'limpiarBuscador','has-content-to-clear' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->tieneAlgoQueLimpiar)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>


             <?php $__env->slot('leftActions', null, []); ?> 
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Cliente::class)): ?>
                    <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['as' => 'a','href' => ''.e(route('clientes.crear')).'','wire:navigate' => true,'variant' => 'success','icon' => 'heroicon-o-plus']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['as' => 'a','href' => ''.e(route('clientes.crear')).'','wire:navigate' => true,'variant' => 'success','icon' => 'heroicon-o-plus']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        Nuevo
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
                <?php endif; ?>

                <?php if (isset($component)) { $__componentOriginalb1d0a319bf00660c6a23fca91d79f257 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb1d0a319bf00660c6a23fca91d79f257 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.actions-menu','data' => ['label' => 'Acciones','icon' => 'heroicon-o-bars-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.actions-menu'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Acciones','icon' => 'heroicon-o-bars-3']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <?php if (isset($component)) { $__componentOriginal4e17f4a935181514e44dbcacced63a96 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4e17f4a935181514e44dbcacced63a96 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.actions-menu-item','data' => ['icon' => 'heroicon-o-arrow-up-tray','disabled' => true,'badge' => 'Pronto']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.actions-menu-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-arrow-up-tray','disabled' => true,'badge' => 'Pronto']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        Importar desde Excel/CSV
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4e17f4a935181514e44dbcacced63a96)): ?>
<?php $attributes = $__attributesOriginal4e17f4a935181514e44dbcacced63a96; ?>
<?php unset($__attributesOriginal4e17f4a935181514e44dbcacced63a96); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4e17f4a935181514e44dbcacced63a96)): ?>
<?php $component = $__componentOriginal4e17f4a935181514e44dbcacced63a96; ?>
<?php unset($__componentOriginal4e17f4a935181514e44dbcacced63a96); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal8011f4ffd0c95629746b3bdcb3d71cdd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8011f4ffd0c95629746b3bdcb3d71cdd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.actions-menu-divider','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.actions-menu-divider'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8011f4ffd0c95629746b3bdcb3d71cdd)): ?>
<?php $attributes = $__attributesOriginal8011f4ffd0c95629746b3bdcb3d71cdd; ?>
<?php unset($__attributesOriginal8011f4ffd0c95629746b3bdcb3d71cdd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8011f4ffd0c95629746b3bdcb3d71cdd)): ?>
<?php $component = $__componentOriginal8011f4ffd0c95629746b3bdcb3d71cdd; ?>
<?php unset($__componentOriginal8011f4ffd0c95629746b3bdcb3d71cdd); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal4e17f4a935181514e44dbcacced63a96 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4e17f4a935181514e44dbcacced63a96 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.actions-menu-item','data' => ['icon' => 'heroicon-o-arrow-down-tray','disabled' => true,'badge' => 'Pronto']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.actions-menu-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-arrow-down-tray','disabled' => true,'badge' => 'Pronto']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        Exportar a Excel
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4e17f4a935181514e44dbcacced63a96)): ?>
<?php $attributes = $__attributesOriginal4e17f4a935181514e44dbcacced63a96; ?>
<?php unset($__attributesOriginal4e17f4a935181514e44dbcacced63a96); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4e17f4a935181514e44dbcacced63a96)): ?>
<?php $component = $__componentOriginal4e17f4a935181514e44dbcacced63a96; ?>
<?php unset($__componentOriginal4e17f4a935181514e44dbcacced63a96); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal4e17f4a935181514e44dbcacced63a96 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4e17f4a935181514e44dbcacced63a96 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.actions-menu-item','data' => ['icon' => 'heroicon-o-document-arrow-down','disabled' => true,'badge' => 'Pronto']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.actions-menu-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-document-arrow-down','disabled' => true,'badge' => 'Pronto']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        Exportar a PDF
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4e17f4a935181514e44dbcacced63a96)): ?>
<?php $attributes = $__attributesOriginal4e17f4a935181514e44dbcacced63a96; ?>
<?php unset($__attributesOriginal4e17f4a935181514e44dbcacced63a96); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4e17f4a935181514e44dbcacced63a96)): ?>
<?php $component = $__componentOriginal4e17f4a935181514e44dbcacced63a96; ?>
<?php unset($__componentOriginal4e17f4a935181514e44dbcacced63a96); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal8011f4ffd0c95629746b3bdcb3d71cdd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8011f4ffd0c95629746b3bdcb3d71cdd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.actions-menu-divider','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.actions-menu-divider'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8011f4ffd0c95629746b3bdcb3d71cdd)): ?>
<?php $attributes = $__attributesOriginal8011f4ffd0c95629746b3bdcb3d71cdd; ?>
<?php unset($__attributesOriginal8011f4ffd0c95629746b3bdcb3d71cdd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8011f4ffd0c95629746b3bdcb3d71cdd)): ?>
<?php $component = $__componentOriginal8011f4ffd0c95629746b3bdcb3d71cdd; ?>
<?php unset($__componentOriginal8011f4ffd0c95629746b3bdcb3d71cdd); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal4e17f4a935181514e44dbcacced63a96 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4e17f4a935181514e44dbcacced63a96 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.actions-menu-item','data' => ['icon' => 'heroicon-o-printer','disabled' => true,'badge' => 'Pronto']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.actions-menu-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-printer','disabled' => true,'badge' => 'Pronto']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        Imprimir lista
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4e17f4a935181514e44dbcacced63a96)): ?>
<?php $attributes = $__attributesOriginal4e17f4a935181514e44dbcacced63a96; ?>
<?php unset($__attributesOriginal4e17f4a935181514e44dbcacced63a96); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4e17f4a935181514e44dbcacced63a96)): ?>
<?php $component = $__componentOriginal4e17f4a935181514e44dbcacced63a96; ?>
<?php unset($__componentOriginal4e17f4a935181514e44dbcacced63a96); ?>
<?php endif; ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb1d0a319bf00660c6a23fca91d79f257)): ?>
<?php $attributes = $__attributesOriginalb1d0a319bf00660c6a23fca91d79f257; ?>
<?php unset($__attributesOriginalb1d0a319bf00660c6a23fca91d79f257); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb1d0a319bf00660c6a23fca91d79f257)): ?>
<?php $component = $__componentOriginalb1d0a319bf00660c6a23fca91d79f257; ?>
<?php unset($__componentOriginalb1d0a319bf00660c6a23fca91d79f257); ?>
<?php endif; ?>
             <?php $__env->endSlot(); ?>

            
            <div class="grid gap-3 md:grid-cols-2">
                <?php if (isset($component)) { $__componentOriginald816e58425c8bb369623a9433739178c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald816e58425c8bb369623a9433739178c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.field','data' => ['label' => 'Estado']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Estado']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <?php if (isset($component)) { $__componentOriginal231e2c645bf8af0c5c05a5dc5a94c862 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal231e2c645bf8af0c5c05a5dc5a94c862 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.select','data' => ['wire:key' => 'estado-'.e($resetKey).'','wire:model.live' => 'filtroEstado']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:key' => 'estado-'.e($resetKey).'','wire:model.live' => 'filtroEstado']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        <option value="">Todos los estados</option>
                        <option value="activas">Activas</option>
                        <option value="inactivas">Inactivas</option>
                        <option value="papelera">En papelera</option>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal231e2c645bf8af0c5c05a5dc5a94c862)): ?>
<?php $attributes = $__attributesOriginal231e2c645bf8af0c5c05a5dc5a94c862; ?>
<?php unset($__attributesOriginal231e2c645bf8af0c5c05a5dc5a94c862); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal231e2c645bf8af0c5c05a5dc5a94c862)): ?>
<?php $component = $__componentOriginal231e2c645bf8af0c5c05a5dc5a94c862; ?>
<?php unset($__componentOriginal231e2c645bf8af0c5c05a5dc5a94c862); ?>
<?php endif; ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald816e58425c8bb369623a9433739178c)): ?>
<?php $attributes = $__attributesOriginald816e58425c8bb369623a9433739178c; ?>
<?php unset($__attributesOriginald816e58425c8bb369623a9433739178c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald816e58425c8bb369623a9433739178c)): ?>
<?php $component = $__componentOriginald816e58425c8bb369623a9433739178c; ?>
<?php unset($__componentOriginald816e58425c8bb369623a9433739178c); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginald816e58425c8bb369623a9433739178c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald816e58425c8bb369623a9433739178c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.field','data' => ['label' => 'Provincia']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Provincia']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <?php if (isset($component)) { $__componentOriginal65bd7e7dbd93cec773ad6501ce127e46 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal65bd7e7dbd93cec773ad6501ce127e46 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.input','data' => ['wire:key' => 'provincia-'.e($resetKey).'','wire:model.live.debounce.300ms' => 'filtroProvincia','placeholder' => 'Escribe provincia...']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:key' => 'provincia-'.e($resetKey).'','wire:model.live.debounce.300ms' => 'filtroProvincia','placeholder' => 'Escribe provincia...']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal65bd7e7dbd93cec773ad6501ce127e46)): ?>
<?php $attributes = $__attributesOriginal65bd7e7dbd93cec773ad6501ce127e46; ?>
<?php unset($__attributesOriginal65bd7e7dbd93cec773ad6501ce127e46); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal65bd7e7dbd93cec773ad6501ce127e46)): ?>
<?php $component = $__componentOriginal65bd7e7dbd93cec773ad6501ce127e46; ?>
<?php unset($__componentOriginal65bd7e7dbd93cec773ad6501ce127e46); ?>
<?php endif; ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald816e58425c8bb369623a9433739178c)): ?>
<?php $attributes = $__attributesOriginald816e58425c8bb369623a9433739178c; ?>
<?php unset($__attributesOriginald816e58425c8bb369623a9433739178c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald816e58425c8bb369623a9433739178c)): ?>
<?php $component = $__componentOriginald816e58425c8bb369623a9433739178c; ?>
<?php unset($__componentOriginald816e58425c8bb369623a9433739178c); ?>
<?php endif; ?>
            </div>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->filtrosAplicados > 0): ?>
                 <?php $__env->slot('chips', null, []); ?> 
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs text-slate-500">Filtros aplicados:</span>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($filtroEstado !== ''): ?>
                            <?php if (isset($component)) { $__componentOriginalc0177670b291fb3bce5b8c760c5c613c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc0177670b291fb3bce5b8c760c5c613c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.filter-chip','data' => ['label' => 'Estado','value' => ucfirst($filtroEstado),'removeAction' => 'quitarFiltroEstado']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.filter-chip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Estado','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(ucfirst($filtroEstado)),'remove-action' => 'quitarFiltroEstado']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc0177670b291fb3bce5b8c760c5c613c)): ?>
<?php $attributes = $__attributesOriginalc0177670b291fb3bce5b8c760c5c613c; ?>
<?php unset($__attributesOriginalc0177670b291fb3bce5b8c760c5c613c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc0177670b291fb3bce5b8c760c5c613c)): ?>
<?php $component = $__componentOriginalc0177670b291fb3bce5b8c760c5c613c; ?>
<?php unset($__componentOriginalc0177670b291fb3bce5b8c760c5c613c); ?>
<?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($filtroProvincia !== ''): ?>
                            <?php if (isset($component)) { $__componentOriginalc0177670b291fb3bce5b8c760c5c613c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc0177670b291fb3bce5b8c760c5c613c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.filter-chip','data' => ['label' => 'Provincia','value' => $filtroProvincia,'removeAction' => 'quitarFiltroProvincia']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.filter-chip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Provincia','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($filtroProvincia),'remove-action' => 'quitarFiltroProvincia']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc0177670b291fb3bce5b8c760c5c613c)): ?>
<?php $attributes = $__attributesOriginalc0177670b291fb3bce5b8c760c5c613c; ?>
<?php unset($__attributesOriginalc0177670b291fb3bce5b8c760c5c613c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc0177670b291fb3bce5b8c760c5c613c)): ?>
<?php $component = $__componentOriginalc0177670b291fb3bce5b8c760c5c613c; ?>
<?php unset($__componentOriginalc0177670b291fb3bce5b8c760c5c613c); ?>
<?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <button type="button"
                                wire:click="limpiarFiltros"
                                class="text-xs text-slate-500 underline hover:text-slate-700">
                            Limpiar todos
                        </button>
                    </div>
                 <?php $__env->endSlot(); ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfcc7a9c111a705a082e127d86b6c3c4d)): ?>
<?php $attributes = $__attributesOriginalfcc7a9c111a705a082e127d86b6c3c4d; ?>
<?php unset($__attributesOriginalfcc7a9c111a705a082e127d86b6c3c4d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfcc7a9c111a705a082e127d86b6c3c4d)): ?>
<?php $component = $__componentOriginalfcc7a9c111a705a082e127d86b6c3c4d; ?>
<?php unset($__componentOriginalfcc7a9c111a705a082e127d86b6c3c4d); ?>
<?php endif; ?>
    </div>

    
    <?php if (isset($component)) { $__componentOriginal411237e6c56bc20b5d281130292e3852 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal411237e6c56bc20b5d281130292e3852 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.data-table','data' => ['colspan' => 8,'empty' => 'No hay clientes que coincidan con los filtros aplicados.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.data-table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['colspan' => 8,'empty' => 'No hay clientes que coincidan con los filtros aplicados.']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

         <?php $__env->slot('head', null, []); ?> 
            <tr>
                <?php if (isset($component)) { $__componentOriginal9c82416d97bef2ffca845759bdd7c679 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9c82416d97bef2ffca845759bdd7c679 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.sortable-header','data' => ['column' => 'numero_cliente','currentColumn' => $ordenColumna,'currentDirection' => $ordenDireccion]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.sortable-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['column' => 'numero_cliente','current-column' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($ordenColumna),'current-direction' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($ordenDireccion)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    Nº cliente
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9c82416d97bef2ffca845759bdd7c679)): ?>
<?php $attributes = $__attributesOriginal9c82416d97bef2ffca845759bdd7c679; ?>
<?php unset($__attributesOriginal9c82416d97bef2ffca845759bdd7c679); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9c82416d97bef2ffca845759bdd7c679)): ?>
<?php $component = $__componentOriginal9c82416d97bef2ffca845759bdd7c679; ?>
<?php unset($__componentOriginal9c82416d97bef2ffca845759bdd7c679); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginal9c82416d97bef2ffca845759bdd7c679 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9c82416d97bef2ffca845759bdd7c679 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.sortable-header','data' => ['column' => 'nombre','currentColumn' => $ordenColumna,'currentDirection' => $ordenDireccion]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.sortable-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['column' => 'nombre','current-column' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($ordenColumna),'current-direction' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($ordenDireccion)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    Nombre
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9c82416d97bef2ffca845759bdd7c679)): ?>
<?php $attributes = $__attributesOriginal9c82416d97bef2ffca845759bdd7c679; ?>
<?php unset($__attributesOriginal9c82416d97bef2ffca845759bdd7c679); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9c82416d97bef2ffca845759bdd7c679)): ?>
<?php $component = $__componentOriginal9c82416d97bef2ffca845759bdd7c679; ?>
<?php unset($__componentOriginal9c82416d97bef2ffca845759bdd7c679); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginal9c82416d97bef2ffca845759bdd7c679 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9c82416d97bef2ffca845759bdd7c679 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.sortable-header','data' => ['column' => 'cif','currentColumn' => $ordenColumna,'currentDirection' => $ordenDireccion]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.sortable-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['column' => 'cif','current-column' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($ordenColumna),'current-direction' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($ordenDireccion)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    CIF
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9c82416d97bef2ffca845759bdd7c679)): ?>
<?php $attributes = $__attributesOriginal9c82416d97bef2ffca845759bdd7c679; ?>
<?php unset($__attributesOriginal9c82416d97bef2ffca845759bdd7c679); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9c82416d97bef2ffca845759bdd7c679)): ?>
<?php $component = $__componentOriginal9c82416d97bef2ffca845759bdd7c679; ?>
<?php unset($__componentOriginal9c82416d97bef2ffca845759bdd7c679); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginal9c82416d97bef2ffca845759bdd7c679 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9c82416d97bef2ffca845759bdd7c679 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.sortable-header','data' => ['column' => 'poblacion','currentColumn' => $ordenColumna,'currentDirection' => $ordenDireccion]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.sortable-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['column' => 'poblacion','current-column' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($ordenColumna),'current-direction' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($ordenDireccion)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    Población
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9c82416d97bef2ffca845759bdd7c679)): ?>
<?php $attributes = $__attributesOriginal9c82416d97bef2ffca845759bdd7c679; ?>
<?php unset($__attributesOriginal9c82416d97bef2ffca845759bdd7c679); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9c82416d97bef2ffca845759bdd7c679)): ?>
<?php $component = $__componentOriginal9c82416d97bef2ffca845759bdd7c679; ?>
<?php unset($__componentOriginal9c82416d97bef2ffca845759bdd7c679); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginal9c82416d97bef2ffca845759bdd7c679 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9c82416d97bef2ffca845759bdd7c679 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.sortable-header','data' => ['column' => 'email','currentColumn' => $ordenColumna,'currentDirection' => $ordenDireccion]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.sortable-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['column' => 'email','current-column' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($ordenColumna),'current-direction' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($ordenDireccion)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    Email
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9c82416d97bef2ffca845759bdd7c679)): ?>
<?php $attributes = $__attributesOriginal9c82416d97bef2ffca845759bdd7c679; ?>
<?php unset($__attributesOriginal9c82416d97bef2ffca845759bdd7c679); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9c82416d97bef2ffca845759bdd7c679)): ?>
<?php $component = $__componentOriginal9c82416d97bef2ffca845759bdd7c679; ?>
<?php unset($__componentOriginal9c82416d97bef2ffca845759bdd7c679); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginal9c82416d97bef2ffca845759bdd7c679 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9c82416d97bef2ffca845759bdd7c679 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.sortable-header','data' => ['column' => 'telefono','currentColumn' => $ordenColumna,'currentDirection' => $ordenDireccion]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.sortable-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['column' => 'telefono','current-column' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($ordenColumna),'current-direction' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($ordenDireccion)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    Teléfono
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9c82416d97bef2ffca845759bdd7c679)): ?>
<?php $attributes = $__attributesOriginal9c82416d97bef2ffca845759bdd7c679; ?>
<?php unset($__attributesOriginal9c82416d97bef2ffca845759bdd7c679); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9c82416d97bef2ffca845759bdd7c679)): ?>
<?php $component = $__componentOriginal9c82416d97bef2ffca845759bdd7c679; ?>
<?php unset($__componentOriginal9c82416d97bef2ffca845759bdd7c679); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginal9c82416d97bef2ffca845759bdd7c679 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9c82416d97bef2ffca845759bdd7c679 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.sortable-header','data' => ['column' => 'activo','currentColumn' => $ordenColumna,'currentDirection' => $ordenDireccion]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.sortable-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['column' => 'activo','current-column' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($ordenColumna),'current-direction' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($ordenDireccion)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    Estado
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9c82416d97bef2ffca845759bdd7c679)): ?>
<?php $attributes = $__attributesOriginal9c82416d97bef2ffca845759bdd7c679; ?>
<?php unset($__attributesOriginal9c82416d97bef2ffca845759bdd7c679); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9c82416d97bef2ffca845759bdd7c679)): ?>
<?php $component = $__componentOriginal9c82416d97bef2ffca845759bdd7c679; ?>
<?php unset($__componentOriginal9c82416d97bef2ffca845759bdd7c679); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginal9c82416d97bef2ffca845759bdd7c679 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9c82416d97bef2ffca845759bdd7c679 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.sortable-header','data' => ['align' => 'right']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.sortable-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['align' => 'right']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
Acciones <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9c82416d97bef2ffca845759bdd7c679)): ?>
<?php $attributes = $__attributesOriginal9c82416d97bef2ffca845759bdd7c679; ?>
<?php unset($__attributesOriginal9c82416d97bef2ffca845759bdd7c679); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9c82416d97bef2ffca845759bdd7c679)): ?>
<?php $component = $__componentOriginal9c82416d97bef2ffca845759bdd7c679; ?>
<?php unset($__componentOriginal9c82416d97bef2ffca845759bdd7c679); ?>
<?php endif; ?>
            </tr>
         <?php $__env->endSlot(); ?>

         <?php $__env->slot('rows', null, []); ?> 
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $clientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cliente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <tr <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'cliente-'.e($cliente->id).''; ?>wire:key="cliente-<?php echo e($cliente->id); ?>" class="transition-colors hover:bg-slate-50">
                    <td class="px-4 py-3 text-slate-700"><?php echo e($cliente->numero_cliente); ?></td>
                    <td class="px-4 py-3">
                        <div class="font-medium text-slate-900"><?php echo e($cliente->nombre); ?></div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($cliente->nombre_comercial): ?>
                            <div class="text-xs text-slate-500"><?php echo e($cliente->nombre_comercial); ?></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-slate-600"><?php echo e($cliente->cif ?? '—'); ?></td>
                    <td class="px-4 py-3 text-slate-600">
                        <div><?php echo e($cliente->poblacion ?? '—'); ?></div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($cliente->provincia): ?>
                            <div class="text-xs text-slate-400"><?php echo e($cliente->provincia); ?></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-slate-600"><?php echo e($cliente->email ?? '—'); ?></td>
                    <td class="px-4 py-3 text-slate-600"><?php echo e($cliente->telefono ?? '—'); ?></td>
                    <td class="px-4 py-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($cliente->trashed()): ?>
                            <?php if (isset($component)) { $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.badge','data' => ['tone' => 'danger','dot' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['tone' => 'danger','dot' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
Eliminada <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $attributes = $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $component = $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
                        <?php elseif($cliente->activo): ?>
                            <?php if (isset($component)) { $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.badge','data' => ['tone' => 'success','dot' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['tone' => 'success','dot' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
Activa <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $attributes = $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $component = $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
                        <?php else: ?>
                            <?php if (isset($component)) { $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.badge','data' => ['tone' => 'neutral','dot' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['tone' => 'neutral','dot' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
Inactiva <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $attributes = $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $component = $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($cliente->trashed()): ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('restore', $cliente)): ?>
                                    <?php if (isset($component)) { $__componentOriginal0b13408463faa13a13ad37dce6dd70f7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0b13408463faa13a13ad37dce6dd70f7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon-button','data' => ['wire:click' => 'restaurar('.e($cliente->id).')','icon' => 'heroicon-o-arrow-uturn-left','variant' => 'success','tooltip' => 'Restaurar']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'restaurar('.e($cliente->id).')','icon' => 'heroicon-o-arrow-uturn-left','variant' => 'success','tooltip' => 'Restaurar']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0b13408463faa13a13ad37dce6dd70f7)): ?>
<?php $attributes = $__attributesOriginal0b13408463faa13a13ad37dce6dd70f7; ?>
<?php unset($__attributesOriginal0b13408463faa13a13ad37dce6dd70f7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0b13408463faa13a13ad37dce6dd70f7)): ?>
<?php $component = $__componentOriginal0b13408463faa13a13ad37dce6dd70f7; ?>
<?php unset($__componentOriginal0b13408463faa13a13ad37dce6dd70f7); ?>
<?php endif; ?>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view', $cliente)): ?>
                                    <?php if (isset($component)) { $__componentOriginal0b13408463faa13a13ad37dce6dd70f7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0b13408463faa13a13ad37dce6dd70f7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon-button','data' => ['as' => 'a','href' => ''.e(route('clientes.ver', $cliente)).'','wire:navigate' => true,'icon' => 'heroicon-o-eye','variant' => 'neutral','tooltip' => 'Ver detalle']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['as' => 'a','href' => ''.e(route('clientes.ver', $cliente)).'','wire:navigate' => true,'icon' => 'heroicon-o-eye','variant' => 'neutral','tooltip' => 'Ver detalle']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0b13408463faa13a13ad37dce6dd70f7)): ?>
<?php $attributes = $__attributesOriginal0b13408463faa13a13ad37dce6dd70f7; ?>
<?php unset($__attributesOriginal0b13408463faa13a13ad37dce6dd70f7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0b13408463faa13a13ad37dce6dd70f7)): ?>
<?php $component = $__componentOriginal0b13408463faa13a13ad37dce6dd70f7; ?>
<?php unset($__componentOriginal0b13408463faa13a13ad37dce6dd70f7); ?>
<?php endif; ?>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $cliente)): ?>
                                    <?php if (isset($component)) { $__componentOriginal0b13408463faa13a13ad37dce6dd70f7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0b13408463faa13a13ad37dce6dd70f7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon-button','data' => ['as' => 'a','href' => ''.e(route('clientes.editar', $cliente)).'','wire:navigate.fresh' => true,'icon' => 'heroicon-o-pencil-square','variant' => 'info','tooltip' => 'Editar']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['as' => 'a','href' => ''.e(route('clientes.editar', $cliente)).'','wire:navigate.fresh' => true,'icon' => 'heroicon-o-pencil-square','variant' => 'info','tooltip' => 'Editar']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0b13408463faa13a13ad37dce6dd70f7)): ?>
<?php $attributes = $__attributesOriginal0b13408463faa13a13ad37dce6dd70f7; ?>
<?php unset($__attributesOriginal0b13408463faa13a13ad37dce6dd70f7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0b13408463faa13a13ad37dce6dd70f7)): ?>
<?php $component = $__componentOriginal0b13408463faa13a13ad37dce6dd70f7; ?>
<?php unset($__componentOriginal0b13408463faa13a13ad37dce6dd70f7); ?>
<?php endif; ?>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $cliente)): ?>
                                    <?php if (isset($component)) { $__componentOriginal0b13408463faa13a13ad37dce6dd70f7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0b13408463faa13a13ad37dce6dd70f7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon-button','data' => ['wire:click' => 'confirmarEliminar('.e($cliente->id).')','icon' => 'heroicon-o-trash','variant' => 'danger','tooltip' => 'Eliminar']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'confirmarEliminar('.e($cliente->id).')','icon' => 'heroicon-o-trash','variant' => 'danger','tooltip' => 'Eliminar']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0b13408463faa13a13ad37dce6dd70f7)): ?>
<?php $attributes = $__attributesOriginal0b13408463faa13a13ad37dce6dd70f7; ?>
<?php unset($__attributesOriginal0b13408463faa13a13ad37dce6dd70f7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0b13408463faa13a13ad37dce6dd70f7)): ?>
<?php $component = $__componentOriginal0b13408463faa13a13ad37dce6dd70f7; ?>
<?php unset($__componentOriginal0b13408463faa13a13ad37dce6dd70f7); ?>
<?php endif; ?>
                                <?php endif; ?>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
         <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal411237e6c56bc20b5d281130292e3852)): ?>
<?php $attributes = $__attributesOriginal411237e6c56bc20b5d281130292e3852; ?>
<?php unset($__attributesOriginal411237e6c56bc20b5d281130292e3852); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal411237e6c56bc20b5d281130292e3852)): ?>
<?php $component = $__componentOriginal411237e6c56bc20b5d281130292e3852; ?>
<?php unset($__componentOriginal411237e6c56bc20b5d281130292e3852); ?>
<?php endif; ?>

    <div class="mt-3">
        <?php echo e($clientes->links()); ?>

    </div>

    
    <?php if (isset($component)) { $__componentOriginal7762953202be6518eecd1cfbd075bf2f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7762953202be6518eecd1cfbd075bf2f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.modal','data' => ['show' => $confirmarEliminarId !== null,'title' => 'Eliminar cliente','closeAction' => 'cancelarEliminar','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['show' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($confirmarEliminarId !== null),'title' => 'Eliminar cliente','close-action' => 'cancelarEliminar','size' => 'sm']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>


        <div class="flex gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-exclamation-triangle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-5']); ?>
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
            </div>
            <div>
                <p class="text-sm text-slate-700">
                    Esta acción enviará el cliente a la <strong>papelera</strong> (eliminación lógica).
                </p>
                <p class="mt-1 text-sm text-slate-500">
                    Podrás restaurarla más tarde desde el filtro <em>«En papelera»</em>.
                </p>
            </div>
        </div>

         <?php $__env->slot('footer', null, []); ?> 
            <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['variant' => 'ghost','wire:click' => 'cancelarEliminar']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'ghost','wire:click' => 'cancelarEliminar']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                Cancelar
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['variant' => 'danger','wire:click' => 'eliminar('.e($confirmarEliminarId ?? 0).')','icon' => 'heroicon-o-trash']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'danger','wire:click' => 'eliminar('.e($confirmarEliminarId ?? 0).')','icon' => 'heroicon-o-trash']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                Eliminar
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
         <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7762953202be6518eecd1cfbd075bf2f)): ?>
<?php $attributes = $__attributesOriginal7762953202be6518eecd1cfbd075bf2f; ?>
<?php unset($__attributesOriginal7762953202be6518eecd1cfbd075bf2f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7762953202be6518eecd1cfbd075bf2f)): ?>
<?php $component = $__componentOriginal7762953202be6518eecd1cfbd075bf2f; ?>
<?php unset($__componentOriginal7762953202be6518eecd1cfbd075bf2f); ?>
<?php endif; ?>
</div>
<?php /**PATH D:\xampp\htdocs\CLIENTES\ELECIND\resources\views/livewire/clientes/index.blade.php ENDPATH**/ ?>