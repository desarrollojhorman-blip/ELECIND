<div>
    <?php if (isset($component)) { $__componentOriginal91a231a9270579fa1ae9246bd51fb785 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91a231a9270579fa1ae9246bd51fb785 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.page-header','data' => ['title' => 'Familias de Material','subtitle' => 'Agrupa materiales que representan el mismo artículo aunque vengan de pedidos distintos.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Familias de Material','subtitle' => 'Agrupa materiales que representan el mismo artículo aunque vengan de pedidos distintos.']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.search-and-filter','data' => ['searchModel' => 'buscar','placeholder' => 'Buscar por nombre o descripción…','filtrosAplicados' => 0,'panelToggle' => '','panelOpen' => false,'resetKey' => $resetKey,'clearAllAction' => 'limpiarBuscador','clearSearchAction' => 'limpiarBuscador','hasContentToClear' => $this->tieneAlgoQueLimpiar]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.search-and-filter'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['search-model' => 'buscar','placeholder' => 'Buscar por nombre o descripción…','filtros-aplicados' => 0,'panel-toggle' => '','panel-open' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'reset-key' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($resetKey),'clear-all-action' => 'limpiarBuscador','clear-search-action' => 'limpiarBuscador','has-content-to-clear' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->tieneAlgoQueLimpiar)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>


             <?php $__env->slot('leftActions', null, []); ?> 
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\FamiliaMaterial::class)): ?>
                    <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['variant' => 'success','wire:click' => 'abrirCrear','icon' => 'heroicon-o-plus']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'success','wire:click' => 'abrirCrear','icon' => 'heroicon-o-plus']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        Nueva familia
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
             <?php $__env->endSlot(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.data-table','data' => ['colspan' => 4,'empty' => 'No hay familias que coincidan con la búsqueda.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.data-table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['colspan' => 4,'empty' => 'No hay familias que coincidan con la búsqueda.']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

         <?php $__env->slot('head', null, []); ?> 
            <tr>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.sortable-header','data' => ['column' => 'descripcion','currentColumn' => $ordenColumna,'currentDirection' => $ordenDireccion]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.sortable-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['column' => 'descripcion','current-column' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($ordenColumna),'current-direction' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($ordenDireccion)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    Descripción
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.sortable-header','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.sortable-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
Materiales <?php echo $__env->renderComponent(); ?>
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
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $familias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $familia): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <tr <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'fam-'.e($familia->id).''; ?>wire:key="fam-<?php echo e($familia->id); ?>" class="transition-colors hover:bg-slate-50">
                    <td class="px-4 py-3">
                        <div class="text-sm font-semibold text-slate-900"><?php echo e($familia->nombre); ?></div>
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-600">
                        <?php echo e($familia->descripcion ?? '—'); ?>

                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700">
                            <?php echo e($familia->materiales_count); ?>

                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($familia->trashed()): ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('restore', $familia)): ?>
                                    <?php if (isset($component)) { $__componentOriginal0b13408463faa13a13ad37dce6dd70f7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0b13408463faa13a13ad37dce6dd70f7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon-button','data' => ['wire:click' => 'restaurar('.e($familia->id).')','icon' => 'heroicon-o-arrow-uturn-left','variant' => 'success','tooltip' => 'Restaurar']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'restaurar('.e($familia->id).')','icon' => 'heroicon-o-arrow-uturn-left','variant' => 'success','tooltip' => 'Restaurar']); ?>
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
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view', $familia)): ?>
                                    <?php if (isset($component)) { $__componentOriginal0b13408463faa13a13ad37dce6dd70f7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0b13408463faa13a13ad37dce6dd70f7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon-button','data' => ['wire:click' => 'abrirVer('.e($familia->id).')','icon' => 'heroicon-o-eye','variant' => 'secondary','tooltip' => 'Ver']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'abrirVer('.e($familia->id).')','icon' => 'heroicon-o-eye','variant' => 'secondary','tooltip' => 'Ver']); ?>
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
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $familia)): ?>
                                    <?php if (isset($component)) { $__componentOriginal0b13408463faa13a13ad37dce6dd70f7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0b13408463faa13a13ad37dce6dd70f7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon-button','data' => ['wire:click' => 'abrirEditar('.e($familia->id).')','icon' => 'heroicon-o-pencil-square','variant' => 'info','tooltip' => 'Editar']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'abrirEditar('.e($familia->id).')','icon' => 'heroicon-o-pencil-square','variant' => 'info','tooltip' => 'Editar']); ?>
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
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $familia)): ?>
                                    <?php if (isset($component)) { $__componentOriginal0b13408463faa13a13ad37dce6dd70f7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0b13408463faa13a13ad37dce6dd70f7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon-button','data' => ['wire:click' => 'confirmarEliminar('.e($familia->id).')','icon' => 'heroicon-o-trash','variant' => 'danger','tooltip' => 'Eliminar']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'confirmarEliminar('.e($familia->id).')','icon' => 'heroicon-o-trash','variant' => 'danger','tooltip' => 'Eliminar']); ?>
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

    <div class="mt-3"><?php echo e($familias->links()); ?></div>

    
    <?php if (isset($component)) { $__componentOriginal7762953202be6518eecd1cfbd075bf2f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7762953202be6518eecd1cfbd075bf2f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.modal','data' => ['show' => $modalAbierto,'title' => $modoSoloLectura ? 'Ver familia' : ($form->id ? 'Editar familia' : 'Nueva familia'),'closeAction' => 'cerrarModal','size' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['show' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($modalAbierto),'title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($modoSoloLectura ? 'Ver familia' : ($form->id ? 'Editar familia' : 'Nueva familia')),'close-action' => 'cerrarModal','size' => 'lg']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>


        <form wire:submit="guardar" id="form-familia" class="space-y-4">
            <?php if (isset($component)) { $__componentOriginald816e58425c8bb369623a9433739178c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald816e58425c8bb369623a9433739178c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.field','data' => ['label' => 'Nombre','required' => true,'error' => $errors->first('form.nombre')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Nombre','required' => true,'error' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->first('form.nombre'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <?php if (isset($component)) { $__componentOriginal65bd7e7dbd93cec773ad6501ce127e46 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal65bd7e7dbd93cec773ad6501ce127e46 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.input','data' => ['wire:model' => 'form.nombre','autofocus' => true,'placeholder' => 'Ej. Cables H07V-K','disabled' => $modoSoloLectura]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'form.nombre','autofocus' => true,'placeholder' => 'Ej. Cables H07V-K','disabled' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($modoSoloLectura)]); ?>
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

            <?php if (isset($component)) { $__componentOriginald816e58425c8bb369623a9433739178c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald816e58425c8bb369623a9433739178c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.field','data' => ['label' => 'Descripción','error' => $errors->first('form.descripcion'),'hint' => 'Opcional. Aparece en el listado.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Descripción','error' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->first('form.descripcion')),'hint' => 'Opcional. Aparece en el listado.']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <?php if (isset($component)) { $__componentOriginal65bd7e7dbd93cec773ad6501ce127e46 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal65bd7e7dbd93cec773ad6501ce127e46 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.input','data' => ['wire:model' => 'form.descripcion','placeholder' => 'Ej. Cable flexible 750V para instalaciones interiores','disabled' => $modoSoloLectura]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'form.descripcion','placeholder' => 'Ej. Cable flexible 750V para instalaciones interiores','disabled' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($modoSoloLectura)]); ?>
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
        </form>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($form->id): ?>
            <div class="mt-5 border-t border-slate-200 pt-4">
                <div x-data="{ abierto: true }">
                    <div class="mb-2 flex items-center justify-between">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Materiales en esta familia
                            <span class="ml-1 inline-flex items-center rounded-full bg-slate-100 px-1.5 py-0.5 text-[10px] font-medium text-slate-700">
                                <?php echo e($this->materialesDeLaFamiliaActual->count()); ?>

                            </span>
                        </h3>
                        <div class="flex items-center gap-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $modoSoloLectura && auth()->user()?->can('materiales.familias.modificar')): ?>
                                <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['variant' => 'secondary','size' => 'sm','wire:click' => 'abrirModalAsignar','icon' => 'heroicon-o-plus']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'secondary','size' => 'sm','wire:click' => 'abrirModalAsignar','icon' => 'heroicon-o-plus']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                                    Asignar materiales
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
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <button type="button" x-on:click="abierto = !abierto"
                                    class="rounded-md p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-chevron-down'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-bind:class' => 'abierto ? \'rotate-180\' : \'\'','class' => 'size-4 transition-transform']); ?>
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
                            </button>
                        </div>
                    </div>

                    <div x-show="abierto" x-cloak x-transition>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->materialesDeLaFamiliaActual->isEmpty()): ?>
                            <p class="rounded-md border border-dashed border-slate-200 bg-slate-50 px-3 py-6 text-center text-sm text-slate-500">
                                Esta familia aún no tiene materiales asignados.
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $modoSoloLectura): ?>
                                    Usa <strong>Asignar materiales</strong> para añadir.
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </p>
                        <?php else: ?>
                            <ul class="space-y-1.5">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->materialesDeLaFamiliaActual; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <li <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'famm-'.e($mat->id).''; ?>wire:key="famm-<?php echo e($mat->id); ?>"
                                        class="flex items-center justify-between gap-3 rounded-md border border-slate-200 bg-slate-50 px-3 py-2">
                                        <div class="min-w-0 flex-1">
                                            <div class="text-sm font-medium text-slate-900 truncate">
                                                <?php echo e($mat->descripcion); ?>

                                            </div>
                                            <div class="mt-0.5 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mat->numeroPedido): ?>
                                                    <span class="inline-flex items-center rounded-full bg-primary-50 px-2 py-0.5 font-mono text-primary-700">
                                                        <?php echo e($mat->numeroPedido->numero); ?>

                                                    </span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <span>
                                                    Stock:
                                                    <span class="font-mono font-semibold text-slate-700">
                                                        <?php echo e(rtrim(rtrim(number_format((float) $mat->stock, 2, ',', ''), '0'), ',')); ?>

                                                    </span>
                                                    <?php echo e($mat->unidad_medida); ?>

                                                </span>
                                            </div>
                                        </div>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $modoSoloLectura): ?>
                                            <button type="button"
                                                    wire:click="quitarMaterialDeFamilia(<?php echo e($mat->id); ?>)"
                                                    class="rounded-md p-1.5 text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600"
                                                    title="Quitar de la familia">
                                                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-x-mark'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-4']); ?>
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
                                            </button>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </li>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </ul>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

         <?php $__env->slot('footer', null, []); ?> 
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($modoSoloLectura): ?>
                <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['variant' => 'ghost','wire:click' => 'cerrarModal']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'ghost','wire:click' => 'cerrarModal']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
Cerrar <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
            <?php else: ?>
                <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['variant' => 'ghost','wire:click' => 'cerrarModal']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'ghost','wire:click' => 'cerrarModal']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
Cancelar <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['variant' => 'success','type' => 'submit','form' => 'form-familia','wire:loading.attr' => 'disabled','icon' => 'heroicon-o-check']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'success','type' => 'submit','form' => 'form-familia','wire:loading.attr' => 'disabled','icon' => 'heroicon-o-check']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    Guardar
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
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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

    
    <?php if (isset($component)) { $__componentOriginal7762953202be6518eecd1cfbd075bf2f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7762953202be6518eecd1cfbd075bf2f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.modal','data' => ['show' => $modalAsignarAbierto,'title' => 'Asignar materiales','closeAction' => 'cerrarModalAsignar','size' => 'xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['show' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($modalAsignarAbierto),'title' => 'Asignar materiales','close-action' => 'cerrarModalAsignar','size' => 'xl']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>


        <div class="space-y-3">
            
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex-1">
                    <?php if (isset($component)) { $__componentOriginal65bd7e7dbd93cec773ad6501ce127e46 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal65bd7e7dbd93cec773ad6501ce127e46 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.input','data' => ['wire:model.live.debounce.300ms' => 'buscarAsignar','placeholder' => 'Buscar por descripción, unidad o nº pedido…']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live.debounce.300ms' => 'buscarAsignar','placeholder' => 'Buscar por descripción, unidad o nº pedido…']); ?>
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
                </div>
                <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" wire:model.live="mostrarTodosAsignar"
                           class="size-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                    <span>Mostrar también materiales con otra familia</span>
                </label>
            </div>

            
            <div class="max-h-[50vh] overflow-y-auto rounded-md border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="sticky top-0 z-10 bg-slate-50">
                        <tr>
                            <th class="w-10 px-3 py-2"></th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-600">Descripción</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-600">Pedido</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-600">Familia actual</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->materialesAsignables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'asig-'.e($mat->id).''; ?>wire:key="asig-<?php echo e($mat->id); ?>" class="hover:bg-slate-50">
                                <td class="px-3 py-2">
                                    <input type="checkbox"
                                           wire:model.live="materialesSeleccionados"
                                           value="<?php echo e($mat->id); ?>"
                                           class="size-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                </td>
                                <td class="px-3 py-2">
                                    <div class="text-slate-900"><?php echo e($mat->descripcion); ?></div>
                                    <div class="text-xs text-slate-500">
                                        Stock: <?php echo e(rtrim(rtrim(number_format((float) $mat->stock, 2, ',', ''), '0'), ',')); ?>

                                        <?php echo e($mat->unidad_medida); ?>

                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mat->numeroPedido): ?>
                                        <span class="font-mono text-xs text-primary-700">
                                            <?php echo e($mat->numeroPedido->numero); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400">—</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="px-3 py-2">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mat->familia): ?>
                                        <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-xs text-amber-700">
                                            <?php echo e($mat->familia->nombre); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400">Sin familia</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr>
                                <td colspan="4" class="px-3 py-8 text-center text-sm text-slate-500">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mostrarTodosAsignar): ?>
                                        No hay materiales que coincidan con la búsqueda.
                                    <?php else: ?>
                                        No hay materiales sin familia que coincidan. Activa el toggle para incluir materiales con otra familia.
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>

            <p class="text-xs text-slate-500">
                Mostrando hasta 100 resultados. Refina la búsqueda si no encuentras el material.
            </p>
        </div>

         <?php $__env->slot('footer', null, []); ?> 
            <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['variant' => 'ghost','wire:click' => 'cerrarModalAsignar']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'ghost','wire:click' => 'cerrarModalAsignar']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
Cancelar <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['variant' => 'success','wire:click' => 'asignarSeleccionados','disabled' => empty($materialesSeleccionados),'icon' => 'heroicon-o-check']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'success','wire:click' => 'asignarSeleccionados','disabled' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(empty($materialesSeleccionados)),'icon' => 'heroicon-o-check']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                Asignar <?php echo e(count($materialesSeleccionados) > 0 ? count($materialesSeleccionados).' material'.(count($materialesSeleccionados) === 1 ? '' : 'es') : ''); ?>

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

    
    <?php if (isset($component)) { $__componentOriginal7762953202be6518eecd1cfbd075bf2f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7762953202be6518eecd1cfbd075bf2f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.modal','data' => ['show' => $confirmarEliminarId !== null,'title' => 'Eliminar familia','closeAction' => 'cancelarEliminar','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['show' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($confirmarEliminarId !== null),'title' => 'Eliminar familia','close-action' => 'cancelarEliminar','size' => 'sm']); ?>
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
                    Esta acción enviará la familia a la <strong>papelera</strong> (eliminación lógica).
                </p>
                <p class="mt-1 text-sm text-slate-500">
                    Los materiales asignados quedarán <strong>sin familia</strong>, pero no se borran.
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
Cancelar <?php echo $__env->renderComponent(); ?>
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
<?php /**PATH D:\xampp\htdocs\CLIENTES\ELECIND\resources\views/livewire/materiales/familias/index.blade.php ENDPATH**/ ?>