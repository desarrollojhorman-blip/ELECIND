<div>
    <form wire:submit="guardar" id="form-albaran" class="px-4 pb-4 pt-3">
        
        <?php if (isset($component)) { $__componentOriginalc1b8b2767d6e169db5a1e4874658ca51 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc1b8b2767d6e169db5a1e4874658ca51 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile.section-title','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile.section-title'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
Datos generales <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc1b8b2767d6e169db5a1e4874658ca51)): ?>
<?php $attributes = $__attributesOriginalc1b8b2767d6e169db5a1e4874658ca51; ?>
<?php unset($__attributesOriginalc1b8b2767d6e169db5a1e4874658ca51); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc1b8b2767d6e169db5a1e4874658ca51)): ?>
<?php $component = $__componentOriginalc1b8b2767d6e169db5a1e4874658ca51; ?>
<?php unset($__componentOriginalc1b8b2767d6e169db5a1e4874658ca51); ?>
<?php endif; ?>

        <div class="space-y-3">
            <?php if (isset($component)) { $__componentOriginala07467ffca7c8ca031924663d9137271 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala07467ffca7c8ca031924663d9137271 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile.field','data' => ['label' => 'Proyecto','required' => true,'error' => $errors->first('form.proyecto_id')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile.field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Proyecto','required' => true,'error' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->first('form.proyecto_id'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <select wire:model.live="form.proyecto_id"
                        class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm bg-white">
                    <option value="">— Selecciona proyecto —</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->proyectosDisponibles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($p->id); ?>"><?php echo e($p->nombre); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala07467ffca7c8ca031924663d9137271)): ?>
<?php $attributes = $__attributesOriginala07467ffca7c8ca031924663d9137271; ?>
<?php unset($__attributesOriginala07467ffca7c8ca031924663d9137271); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala07467ffca7c8ca031924663d9137271)): ?>
<?php $component = $__componentOriginala07467ffca7c8ca031924663d9137271; ?>
<?php unset($__componentOriginala07467ffca7c8ca031924663d9137271); ?>
<?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($form->cliente_id): ?>
                <?php if (isset($component)) { $__componentOriginala07467ffca7c8ca031924663d9137271 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala07467ffca7c8ca031924663d9137271 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile.field','data' => ['label' => 'Cliente']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile.field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Cliente']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <p class="rounded-md bg-slate-100 px-3 py-2 text-sm text-slate-700">
                        <?php echo e(\App\Models\Cliente::find($form->cliente_id)?->nombre ?? '—'); ?>

                    </p>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala07467ffca7c8ca031924663d9137271)): ?>
<?php $attributes = $__attributesOriginala07467ffca7c8ca031924663d9137271; ?>
<?php unset($__attributesOriginala07467ffca7c8ca031924663d9137271); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala07467ffca7c8ca031924663d9137271)): ?>
<?php $component = $__componentOriginala07467ffca7c8ca031924663d9137271; ?>
<?php unset($__componentOriginala07467ffca7c8ca031924663d9137271); ?>
<?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if (isset($component)) { $__componentOriginala07467ffca7c8ca031924663d9137271 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala07467ffca7c8ca031924663d9137271 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile.field','data' => ['label' => 'Concepto','error' => $errors->first('form.concepto_id')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile.field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Concepto','error' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->first('form.concepto_id'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <select wire:model="form.concepto_id"
                        class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm bg-white">
                    <option value="">— Sin concepto —</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->conceptosDisponibles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($c->id); ?>"><?php echo e($c->nombre); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala07467ffca7c8ca031924663d9137271)): ?>
<?php $attributes = $__attributesOriginala07467ffca7c8ca031924663d9137271; ?>
<?php unset($__attributesOriginala07467ffca7c8ca031924663d9137271); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala07467ffca7c8ca031924663d9137271)): ?>
<?php $component = $__componentOriginala07467ffca7c8ca031924663d9137271; ?>
<?php unset($__componentOriginala07467ffca7c8ca031924663d9137271); ?>
<?php endif; ?>

            <?php if (isset($component)) { $__componentOriginala07467ffca7c8ca031924663d9137271 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala07467ffca7c8ca031924663d9137271 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile.field','data' => ['label' => 'Responsable del proyecto','error' => $errors->first('form.responsable_id')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile.field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Responsable del proyecto','error' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->first('form.responsable_id'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <div <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'resp-'.e($selectKey).''; ?>wire:key="resp-<?php echo e($selectKey); ?>">
                    <?php if (isset($component)) { $__componentOriginala8477b4ecee8eec802e9913415383e3a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8477b4ecee8eec802e9913415383e3a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.searchable-select','data' => ['wireModel' => 'form.responsable_id','options' => $this->responsablesDisponibles->map(fn($u) => ['value' => $u->id, 'label' => trim($u->nombre.' '.$u->apellidos)]),'placeholder' => '— Sin asignar —']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.searchable-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire-model' => 'form.responsable_id','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->responsablesDisponibles->map(fn($u) => ['value' => $u->id, 'label' => trim($u->nombre.' '.$u->apellidos)])),'placeholder' => '— Sin asignar —']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8477b4ecee8eec802e9913415383e3a)): ?>
<?php $attributes = $__attributesOriginala8477b4ecee8eec802e9913415383e3a; ?>
<?php unset($__attributesOriginala8477b4ecee8eec802e9913415383e3a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8477b4ecee8eec802e9913415383e3a)): ?>
<?php $component = $__componentOriginala8477b4ecee8eec802e9913415383e3a; ?>
<?php unset($__componentOriginala8477b4ecee8eec802e9913415383e3a); ?>
<?php endif; ?>
                </div>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala07467ffca7c8ca031924663d9137271)): ?>
<?php $attributes = $__attributesOriginala07467ffca7c8ca031924663d9137271; ?>
<?php unset($__attributesOriginala07467ffca7c8ca031924663d9137271); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala07467ffca7c8ca031924663d9137271)): ?>
<?php $component = $__componentOriginala07467ffca7c8ca031924663d9137271; ?>
<?php unset($__componentOriginala07467ffca7c8ca031924663d9137271); ?>
<?php endif; ?>

            <div class="grid grid-cols-2 gap-3">
                <?php if (isset($component)) { $__componentOriginala07467ffca7c8ca031924663d9137271 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala07467ffca7c8ca031924663d9137271 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile.field','data' => ['label' => 'Fecha','required' => true,'error' => $errors->first('form.fecha')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile.field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Fecha','required' => true,'error' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->first('form.fecha'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <?php if (isset($component)) { $__componentOriginal65bd7e7dbd93cec773ad6501ce127e46 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal65bd7e7dbd93cec773ad6501ce127e46 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.input','data' => ['type' => 'date','wire:model' => 'form.fecha']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'date','wire:model' => 'form.fecha']); ?>
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
<?php if (isset($__attributesOriginala07467ffca7c8ca031924663d9137271)): ?>
<?php $attributes = $__attributesOriginala07467ffca7c8ca031924663d9137271; ?>
<?php unset($__attributesOriginala07467ffca7c8ca031924663d9137271); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala07467ffca7c8ca031924663d9137271)): ?>
<?php $component = $__componentOriginala07467ffca7c8ca031924663d9137271; ?>
<?php unset($__componentOriginala07467ffca7c8ca031924663d9137271); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginala07467ffca7c8ca031924663d9137271 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala07467ffca7c8ca031924663d9137271 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile.field','data' => ['label' => 'Tipo de hora','required' => true,'error' => $errors->first('form.tipo_hora')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile.field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Tipo de hora','required' => true,'error' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->first('form.tipo_hora'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <?php if (isset($component)) { $__componentOriginal231e2c645bf8af0c5c05a5dc5a94c862 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal231e2c645bf8af0c5c05a5dc5a94c862 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.select','data' => ['wire:model' => 'form.tipo_hora']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'form.tipo_hora']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $tiposHora; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tipo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($tipo->value); ?>"><?php echo e($tipo->etiqueta()); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
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
<?php if (isset($__attributesOriginala07467ffca7c8ca031924663d9137271)): ?>
<?php $attributes = $__attributesOriginala07467ffca7c8ca031924663d9137271; ?>
<?php unset($__attributesOriginala07467ffca7c8ca031924663d9137271); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala07467ffca7c8ca031924663d9137271)): ?>
<?php $component = $__componentOriginala07467ffca7c8ca031924663d9137271; ?>
<?php unset($__componentOriginala07467ffca7c8ca031924663d9137271); ?>
<?php endif; ?>
            </div>

            <?php if (isset($component)) { $__componentOriginala07467ffca7c8ca031924663d9137271 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala07467ffca7c8ca031924663d9137271 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile.field','data' => ['label' => 'Observaciones','error' => $errors->first('form.observaciones')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile.field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Observaciones','error' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->first('form.observaciones'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <?php if (isset($component)) { $__componentOriginal62d1193389a71cd99ff302a00abbf991 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal62d1193389a71cd99ff302a00abbf991 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.textarea','data' => ['wire:model' => 'form.observaciones','rows' => '2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'form.observaciones','rows' => '2']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal62d1193389a71cd99ff302a00abbf991)): ?>
<?php $attributes = $__attributesOriginal62d1193389a71cd99ff302a00abbf991; ?>
<?php unset($__attributesOriginal62d1193389a71cd99ff302a00abbf991); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal62d1193389a71cd99ff302a00abbf991)): ?>
<?php $component = $__componentOriginal62d1193389a71cd99ff302a00abbf991; ?>
<?php unset($__componentOriginal62d1193389a71cd99ff302a00abbf991); ?>
<?php endif; ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala07467ffca7c8ca031924663d9137271)): ?>
<?php $attributes = $__attributesOriginala07467ffca7c8ca031924663d9137271; ?>
<?php unset($__attributesOriginala07467ffca7c8ca031924663d9137271); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala07467ffca7c8ca031924663d9137271)): ?>
<?php $component = $__componentOriginala07467ffca7c8ca031924663d9137271; ?>
<?php unset($__componentOriginala07467ffca7c8ca031924663d9137271); ?>
<?php endif; ?>
        </div>

        
        <?php if (isset($component)) { $__componentOriginalc1b8b2767d6e169db5a1e4874658ca51 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc1b8b2767d6e169db5a1e4874658ca51 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile.section-title','data' => ['hint' => 'Tu línea como creador']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile.section-title'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['hint' => 'Tu línea como creador']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
Mis horas <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc1b8b2767d6e169db5a1e4874658ca51)): ?>
<?php $attributes = $__attributesOriginalc1b8b2767d6e169db5a1e4874658ca51; ?>
<?php unset($__attributesOriginalc1b8b2767d6e169db5a1e4874658ca51); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc1b8b2767d6e169db5a1e4874658ca51)): ?>
<?php $component = $__componentOriginalc1b8b2767d6e169db5a1e4874658ca51; ?>
<?php unset($__componentOriginalc1b8b2767d6e169db5a1e4874658ca51); ?>
<?php endif; ?>

        <?php if (isset($component)) { $__componentOriginal972550af0641ce089c12059f484d131b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal972550af0641ce089c12059f484d131b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile.line-card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile.line-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <div class="space-y-3">
                <div class="rounded-md bg-primary-50 px-3 py-2 text-sm text-primary-800">
                    <strong><?php echo e(trim(auth()->user()->nombre.' '.auth()->user()->apellidos)); ?></strong>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <?php if (isset($component)) { $__componentOriginala07467ffca7c8ca031924663d9137271 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala07467ffca7c8ca031924663d9137271 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile.field','data' => ['label' => 'Horas','required' => true,'error' => $errors->first('form.mi_horas')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile.field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Horas','required' => true,'error' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->first('form.mi_horas'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        <?php if (isset($component)) { $__componentOriginal65bd7e7dbd93cec773ad6501ce127e46 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal65bd7e7dbd93cec773ad6501ce127e46 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.input','data' => ['type' => 'number','step' => '0.25','min' => '0','max' => '24','wire:model' => 'form.mi_horas']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'number','step' => '0.25','min' => '0','max' => '24','wire:model' => 'form.mi_horas']); ?>
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
<?php if (isset($__attributesOriginala07467ffca7c8ca031924663d9137271)): ?>
<?php $attributes = $__attributesOriginala07467ffca7c8ca031924663d9137271; ?>
<?php unset($__attributesOriginala07467ffca7c8ca031924663d9137271); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala07467ffca7c8ca031924663d9137271)): ?>
<?php $component = $__componentOriginala07467ffca7c8ca031924663d9137271; ?>
<?php unset($__componentOriginala07467ffca7c8ca031924663d9137271); ?>
<?php endif; ?>

                    <?php if (isset($component)) { $__componentOriginala07467ffca7c8ca031924663d9137271 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala07467ffca7c8ca031924663d9137271 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile.field','data' => ['label' => 'Horas extra','error' => $errors->first('form.mi_horas_extra')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile.field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Horas extra','error' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->first('form.mi_horas_extra'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        <?php if (isset($component)) { $__componentOriginal65bd7e7dbd93cec773ad6501ce127e46 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal65bd7e7dbd93cec773ad6501ce127e46 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.input','data' => ['type' => 'number','step' => '0.25','min' => '0','max' => '24','wire:model' => 'form.mi_horas_extra']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'number','step' => '0.25','min' => '0','max' => '24','wire:model' => 'form.mi_horas_extra']); ?>
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
<?php if (isset($__attributesOriginala07467ffca7c8ca031924663d9137271)): ?>
<?php $attributes = $__attributesOriginala07467ffca7c8ca031924663d9137271; ?>
<?php unset($__attributesOriginala07467ffca7c8ca031924663d9137271); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala07467ffca7c8ca031924663d9137271)): ?>
<?php $component = $__componentOriginala07467ffca7c8ca031924663d9137271; ?>
<?php unset($__componentOriginala07467ffca7c8ca031924663d9137271); ?>
<?php endif; ?>
                </div>
            </div>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal972550af0641ce089c12059f484d131b)): ?>
<?php $attributes = $__attributesOriginal972550af0641ce089c12059f484d131b; ?>
<?php unset($__attributesOriginal972550af0641ce089c12059f484d131b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal972550af0641ce089c12059f484d131b)): ?>
<?php $component = $__componentOriginal972550af0641ce089c12059f484d131b; ?>
<?php unset($__componentOriginal972550af0641ce089c12059f484d131b); ?>
<?php endif; ?>

        
        <?php if (isset($component)) { $__componentOriginalc1b8b2767d6e169db5a1e4874658ca51 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc1b8b2767d6e169db5a1e4874658ca51 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile.section-title','data' => ['hint' => count($form->companeros).' añadidos']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile.section-title'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['hint' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(count($form->companeros).' añadidos')]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
Compañeros <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc1b8b2767d6e169db5a1e4874658ca51)): ?>
<?php $attributes = $__attributesOriginalc1b8b2767d6e169db5a1e4874658ca51; ?>
<?php unset($__attributesOriginalc1b8b2767d6e169db5a1e4874658ca51); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc1b8b2767d6e169db5a1e4874658ca51)): ?>
<?php $component = $__componentOriginalc1b8b2767d6e169db5a1e4874658ca51; ?>
<?php unset($__componentOriginalc1b8b2767d6e169db5a1e4874658ca51); ?>
<?php endif; ?>

        <div class="space-y-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $form->companeros; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $companero): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php if (isset($component)) { $__componentOriginal972550af0641ce089c12059f484d131b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal972550af0641ce089c12059f484d131b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile.line-card','data' => ['title' => 'Compañero #'.($index + 1),'removeAction' => 'removeCompanero('.$index.')','wire:key' => 'comp-'.e($index).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile.line-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Compañero #'.($index + 1)),'remove-action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('removeCompanero('.$index.')'),'wire:key' => 'comp-'.e($index).'']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <div class="space-y-3">
                        <?php if (isset($component)) { $__componentOriginala07467ffca7c8ca031924663d9137271 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala07467ffca7c8ca031924663d9137271 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile.field','data' => ['label' => 'Trabajador','required' => true,'error' => $errors->first('form.companeros.'.$index.'.trabajador_id')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile.field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Trabajador','required' => true,'error' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->first('form.companeros.'.$index.'.trabajador_id'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                            <div <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'comp-sel-'.e($selectKey).'-'.e($index).''; ?>wire:key="comp-sel-<?php echo e($selectKey); ?>-<?php echo e($index); ?>">
                                <?php if (isset($component)) { $__componentOriginala8477b4ecee8eec802e9913415383e3a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8477b4ecee8eec802e9913415383e3a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.searchable-select','data' => ['wireModel' => 'form.companeros.'.e($index).'.trabajador_id','options' => $this->companerosDisponibles->map(fn($u) => ['value' => $u->id, 'label' => trim($u->nombre.' '.$u->apellidos)]),'placeholder' => '— Selecciona —']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.searchable-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire-model' => 'form.companeros.'.e($index).'.trabajador_id','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->companerosDisponibles->map(fn($u) => ['value' => $u->id, 'label' => trim($u->nombre.' '.$u->apellidos)])),'placeholder' => '— Selecciona —']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8477b4ecee8eec802e9913415383e3a)): ?>
<?php $attributes = $__attributesOriginala8477b4ecee8eec802e9913415383e3a; ?>
<?php unset($__attributesOriginala8477b4ecee8eec802e9913415383e3a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8477b4ecee8eec802e9913415383e3a)): ?>
<?php $component = $__componentOriginala8477b4ecee8eec802e9913415383e3a; ?>
<?php unset($__componentOriginala8477b4ecee8eec802e9913415383e3a); ?>
<?php endif; ?>
                            </div>
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala07467ffca7c8ca031924663d9137271)): ?>
<?php $attributes = $__attributesOriginala07467ffca7c8ca031924663d9137271; ?>
<?php unset($__attributesOriginala07467ffca7c8ca031924663d9137271); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala07467ffca7c8ca031924663d9137271)): ?>
<?php $component = $__componentOriginala07467ffca7c8ca031924663d9137271; ?>
<?php unset($__componentOriginala07467ffca7c8ca031924663d9137271); ?>
<?php endif; ?>

                        <div class="grid grid-cols-2 gap-3">
                            <?php if (isset($component)) { $__componentOriginala07467ffca7c8ca031924663d9137271 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala07467ffca7c8ca031924663d9137271 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile.field','data' => ['label' => 'Horas','required' => true,'error' => $errors->first('form.companeros.'.$index.'.horas')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile.field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Horas','required' => true,'error' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->first('form.companeros.'.$index.'.horas'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                                <?php if (isset($component)) { $__componentOriginal65bd7e7dbd93cec773ad6501ce127e46 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal65bd7e7dbd93cec773ad6501ce127e46 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.input','data' => ['type' => 'number','step' => '0.25','min' => '0','max' => '24','wire:model' => 'form.companeros.'.e($index).'.horas']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'number','step' => '0.25','min' => '0','max' => '24','wire:model' => 'form.companeros.'.e($index).'.horas']); ?>
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
<?php if (isset($__attributesOriginala07467ffca7c8ca031924663d9137271)): ?>
<?php $attributes = $__attributesOriginala07467ffca7c8ca031924663d9137271; ?>
<?php unset($__attributesOriginala07467ffca7c8ca031924663d9137271); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala07467ffca7c8ca031924663d9137271)): ?>
<?php $component = $__componentOriginala07467ffca7c8ca031924663d9137271; ?>
<?php unset($__componentOriginala07467ffca7c8ca031924663d9137271); ?>
<?php endif; ?>

                            <?php if (isset($component)) { $__componentOriginala07467ffca7c8ca031924663d9137271 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala07467ffca7c8ca031924663d9137271 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile.field','data' => ['label' => 'Horas extra','error' => $errors->first('form.companeros.'.$index.'.horas_extra')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile.field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Horas extra','error' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->first('form.companeros.'.$index.'.horas_extra'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                                <?php if (isset($component)) { $__componentOriginal65bd7e7dbd93cec773ad6501ce127e46 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal65bd7e7dbd93cec773ad6501ce127e46 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.input','data' => ['type' => 'number','step' => '0.25','min' => '0','max' => '24','wire:model' => 'form.companeros.'.e($index).'.horas_extra']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'number','step' => '0.25','min' => '0','max' => '24','wire:model' => 'form.companeros.'.e($index).'.horas_extra']); ?>
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
<?php if (isset($__attributesOriginala07467ffca7c8ca031924663d9137271)): ?>
<?php $attributes = $__attributesOriginala07467ffca7c8ca031924663d9137271; ?>
<?php unset($__attributesOriginala07467ffca7c8ca031924663d9137271); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala07467ffca7c8ca031924663d9137271)): ?>
<?php $component = $__componentOriginala07467ffca7c8ca031924663d9137271; ?>
<?php unset($__componentOriginala07467ffca7c8ca031924663d9137271); ?>
<?php endif; ?>
                        </div>
                    </div>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal972550af0641ce089c12059f484d131b)): ?>
<?php $attributes = $__attributesOriginal972550af0641ce089c12059f484d131b; ?>
<?php unset($__attributesOriginal972550af0641ce089c12059f484d131b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal972550af0641ce089c12059f484d131b)): ?>
<?php $component = $__componentOriginal972550af0641ce089c12059f484d131b; ?>
<?php unset($__componentOriginal972550af0641ce089c12059f484d131b); ?>
<?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>

        <button type="button"
                wire:click="addCompanero"
                class="mt-3 flex w-full items-center justify-center gap-2 rounded-lg border border-dashed border-slate-300 bg-white px-4 py-3 text-sm font-medium text-slate-600 transition-colors hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700">
            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-plus'); ?>
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
            Añadir compañero
        </button>

        
        <?php if (isset($component)) { $__componentOriginalc1b8b2767d6e169db5a1e4874658ca51 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc1b8b2767d6e169db5a1e4874658ca51 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile.section-title','data' => ['hint' => count($form->materiales).' añadidos']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile.section-title'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['hint' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(count($form->materiales).' añadidos')]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
Materiales <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc1b8b2767d6e169db5a1e4874658ca51)): ?>
<?php $attributes = $__attributesOriginalc1b8b2767d6e169db5a1e4874658ca51; ?>
<?php unset($__attributesOriginalc1b8b2767d6e169db5a1e4874658ca51); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc1b8b2767d6e169db5a1e4874658ca51)): ?>
<?php $component = $__componentOriginalc1b8b2767d6e169db5a1e4874658ca51; ?>
<?php unset($__componentOriginalc1b8b2767d6e169db5a1e4874658ca51); ?>
<?php endif; ?>

        <div class="space-y-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $form->materiales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $material): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php
                    $matSeleccionado = $this->materialesProyecto->firstWhere('id', $material['material_id'] ?? null);
                ?>
                <?php if (isset($component)) { $__componentOriginal972550af0641ce089c12059f484d131b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal972550af0641ce089c12059f484d131b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile.line-card','data' => ['title' => 'Material #'.($index + 1),'removeAction' => 'removeMaterial('.$index.')','wire:key' => 'mat-'.e($index).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile.line-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Material #'.($index + 1)),'remove-action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('removeMaterial('.$index.')'),'wire:key' => 'mat-'.e($index).'']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <div class="space-y-3">
                        <?php if (isset($component)) { $__componentOriginala07467ffca7c8ca031924663d9137271 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala07467ffca7c8ca031924663d9137271 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile.field','data' => ['label' => 'Material','required' => true,'error' => $errors->first('form.materiales.'.$index.'.material_id')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile.field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Material','required' => true,'error' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->first('form.materiales.'.$index.'.material_id'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                            <div <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'mat-sel-'.e($selectKey).'-'.e($index).''; ?>wire:key="mat-sel-<?php echo e($selectKey); ?>-<?php echo e($index); ?>">
                                <?php if (isset($component)) { $__componentOriginala8477b4ecee8eec802e9913415383e3a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8477b4ecee8eec802e9913415383e3a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.searchable-select','data' => ['wireModel' => 'form.materiales.'.e($index).'.material_id','options' => $this->materialesProyecto->map(fn($m) => ['value' => $m->id, 'label' => $m->descripcion.' | '.rtrim(rtrim(number_format((float)$m->stock,2,',',''),'0'),',').' '.$m->unidad_medida]),'placeholder' => '— Selecciona —']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.searchable-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire-model' => 'form.materiales.'.e($index).'.material_id','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->materialesProyecto->map(fn($m) => ['value' => $m->id, 'label' => $m->descripcion.' | '.rtrim(rtrim(number_format((float)$m->stock,2,',',''),'0'),',').' '.$m->unidad_medida])),'placeholder' => '— Selecciona —']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8477b4ecee8eec802e9913415383e3a)): ?>
<?php $attributes = $__attributesOriginala8477b4ecee8eec802e9913415383e3a; ?>
<?php unset($__attributesOriginala8477b4ecee8eec802e9913415383e3a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8477b4ecee8eec802e9913415383e3a)): ?>
<?php $component = $__componentOriginala8477b4ecee8eec802e9913415383e3a; ?>
<?php unset($__componentOriginala8477b4ecee8eec802e9913415383e3a); ?>
<?php endif; ?>
                            </div>
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala07467ffca7c8ca031924663d9137271)): ?>
<?php $attributes = $__attributesOriginala07467ffca7c8ca031924663d9137271; ?>
<?php unset($__attributesOriginala07467ffca7c8ca031924663d9137271); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala07467ffca7c8ca031924663d9137271)): ?>
<?php $component = $__componentOriginala07467ffca7c8ca031924663d9137271; ?>
<?php unset($__componentOriginala07467ffca7c8ca031924663d9137271); ?>
<?php endif; ?>

                        <?php if (isset($component)) { $__componentOriginala07467ffca7c8ca031924663d9137271 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala07467ffca7c8ca031924663d9137271 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile.field','data' => ['label' => 'Cantidad','required' => true,'error' => $errors->first('form.materiales.'.$index.'.cantidad')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile.field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Cantidad','required' => true,'error' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->first('form.materiales.'.$index.'.cantidad'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                            <div class="flex items-stretch gap-2">
                                <div class="min-w-0 flex-1">
                                    <?php if (isset($component)) { $__componentOriginal65bd7e7dbd93cec773ad6501ce127e46 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal65bd7e7dbd93cec773ad6501ce127e46 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.input','data' => ['type' => 'number','step' => '0.01','min' => '0.01','wire:model' => 'form.materiales.'.e($index).'.cantidad']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'number','step' => '0.01','min' => '0.01','wire:model' => 'form.materiales.'.e($index).'.cantidad']); ?>
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
                                <span class="inline-flex shrink-0 items-center rounded-md bg-slate-100 px-3 text-sm font-medium text-slate-600">
                                    <?php echo e($matSeleccionado?->unidad_medida ?? '—'); ?>

                                </span>
                            </div>
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala07467ffca7c8ca031924663d9137271)): ?>
<?php $attributes = $__attributesOriginala07467ffca7c8ca031924663d9137271; ?>
<?php unset($__attributesOriginala07467ffca7c8ca031924663d9137271); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala07467ffca7c8ca031924663d9137271)): ?>
<?php $component = $__componentOriginala07467ffca7c8ca031924663d9137271; ?>
<?php unset($__componentOriginala07467ffca7c8ca031924663d9137271); ?>
<?php endif; ?>
                    </div>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal972550af0641ce089c12059f484d131b)): ?>
<?php $attributes = $__attributesOriginal972550af0641ce089c12059f484d131b; ?>
<?php unset($__attributesOriginal972550af0641ce089c12059f484d131b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal972550af0641ce089c12059f484d131b)): ?>
<?php $component = $__componentOriginal972550af0641ce089c12059f484d131b; ?>
<?php unset($__componentOriginal972550af0641ce089c12059f484d131b); ?>
<?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>

        <button type="button"
                wire:click="addMaterial"
                class="mt-3 flex w-full items-center justify-center gap-2 rounded-lg border border-dashed border-slate-300 bg-white px-4 py-3 text-sm font-medium text-slate-600 transition-colors hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700">
            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-plus'); ?>
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
            Añadir material
        </button>
    </form>

    
    <?php if (isset($component)) { $__componentOriginalbe7b1827fd2a006c86aadb0511f0c0c3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbe7b1827fd2a006c86aadb0511f0c0c3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile.bottom-bar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile.bottom-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <button type="submit"
                form="form-albaran"
                wire:loading.attr="disabled"
                class="w-full rounded-md bg-emerald-600 px-3 py-3 text-base font-semibold text-white shadow-sm transition-colors hover:bg-emerald-700 active:scale-[0.99] active:transition-transform disabled:opacity-50">
            <span wire:loading.remove>Guardar</span>
            <span wire:loading>Guardando…</span>
        </button>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbe7b1827fd2a006c86aadb0511f0c0c3)): ?>
<?php $attributes = $__attributesOriginalbe7b1827fd2a006c86aadb0511f0c0c3; ?>
<?php unset($__attributesOriginalbe7b1827fd2a006c86aadb0511f0c0c3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbe7b1827fd2a006c86aadb0511f0c0c3)): ?>
<?php $component = $__componentOriginalbe7b1827fd2a006c86aadb0511f0c0c3; ?>
<?php unset($__componentOriginalbe7b1827fd2a006c86aadb0511f0c0c3); ?>
<?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($albaranCreadoId !== null): ?>
        <div class="fixed inset-0 z-50 flex items-end justify-center bg-black/50 px-0 pb-0 sm:items-center sm:px-4 sm:pb-4"
             x-data x-init="$el.scrollIntoView({ behavior: 'smooth' })">
            <div class="w-full max-w-sm overflow-hidden rounded-t-2xl bg-white shadow-xl sm:rounded-2xl">

                
                <div class="flex flex-col items-center gap-2 bg-slate-50 px-6 pt-6 pb-4 text-center">
                    <div class="flex size-12 items-center justify-center rounded-full bg-green-100 text-green-600">
                        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-document-check'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-6']); ?>
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
                    <h2 class="text-base font-semibold text-slate-900">Parte creado</h2>
                    <p class="text-sm text-slate-500">
                        ¿Quieres firma el parte ahora mismo o prefieres hacerlo después?
                    </p>
                </div>

                
                <div class="space-y-3 px-6 py-5">
                    
                    <button
                        type="button"
                        wire:click="irAFirmar"
                        wire:loading.attr="disabled"
                        class="flex w-full items-center gap-4 rounded-xl border-2 border-primary-500 bg-primary-50 px-4 py-3.5 text-left transition hover:bg-primary-100 active:scale-[0.99]"
                    >
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary-600 text-white">
                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-pencil'); ?>
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
                        </span>
                        <span>
                            <span class="block text-sm font-semibold text-primary-800">Firmar ahora</span>
                            <span class="block text-xs text-primary-600">El responsable puede firmar también en este momento.</span>
                        </span>
                    </button>

                    
                    <button
                        type="button"
                        wire:click="irAlDashboard"
                        wire:loading.attr="disabled"
                        class="flex w-full items-center gap-4 rounded-xl border border-slate-200 bg-white px-4 py-3.5 text-left transition hover:bg-slate-50 active:scale-[0.99]"
                    >
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-clock'); ?>
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
                        </span>
                        <span>
                            <span class="block text-sm font-semibold text-slate-700">Firmar más tarde</span>
                            <span class="block text-xs text-slate-500">Vuelve al inicio. El parte queda guardado como borrador.</span>
                        </span>
                    </button>
                </div>

            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

</div>
<?php /**PATH D:\xampp\htdocs\CLIENTES\ELECIND\resources\views/livewire/mobile/albaranes/crear.blade.php ENDPATH**/ ?>