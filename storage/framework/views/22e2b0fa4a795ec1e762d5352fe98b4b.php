<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['active' => null]));

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

foreach (array_filter((['active' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $items = [
        [
            'label' => 'Dashboard',
            'icon' => 'heroicon-o-home',
            'route' => 'web.dashboard',
            'key' => 'dashboard',
            'permission' => null,
        ],
        [
            'label' => 'Control de Horas',
            'icon' => 'heroicon-o-clock',
            'route' => null,
            'key' => 'horas',
            'permission' => null,
        ],
        [
            'label' => 'Proyectos',
            'icon' => 'heroicon-o-folder',
            'route' => null,
            'key' => 'proyectos',
            'permission' => null,
            'children' => [
                [
                    'label' => 'Grupo proyectos',
                    'route' => 'proyectos.grupos',
                    'key' => 'proyectos_grupos',
                    'permission' => 'grupos_proyecto.ver',
                ],
                [
                    'label' => 'Proyectos',
                    'route' => 'proyectos.index',
                    'key' => 'proyectos_lista',
                    'permission' => 'proyectos.ver',
                ],
            ],
        ],
        [
            'label' => 'Albaranes',
            'icon' => 'heroicon-o-document-text',
            'route' => 'albaranes.index',
            'key' => 'albaranes',
            'permission' => 'albaranes.ver_todos',
        ],
        [
            'label' => 'Materiales',
            'icon' => 'heroicon-o-cube',
            'route' => null,
            'key' => 'materiales',
            'permission' => null,
            'children' => [
                [
                    'label' => 'Nº Pedido',
                    'route' => 'materiales.pedidos',
                    'key' => 'pedidos',
                    'permission' => 'pedidos.ver',
                ],
                [
                    'label' => 'Familias',
                    'route' => 'materiales.familias',
                    'key' => 'familias',
                    'permission' => 'materiales.familias.ver',
                ],
                [
                    'label' => 'Materiales',
                    'route' => 'materiales.index',
                    'key' => 'materiales_lista',
                    'permission' => 'materiales.ver',
                ],
            ],
        ],
        [
            'label' => 'Clientes',
            'icon' => 'heroicon-o-building-office-2',
            'route' => 'clientes.index',
            'key' => 'clientes',
            'permission' => 'clientes.ver',
        ],
        [
            'label' => 'Conceptos',
            'icon' => 'heroicon-o-tag',
            'route' => 'conceptos.index',
            'key' => 'conceptos',
            'permission' => 'conceptos.ver',
        ],
        [
            'label' => 'Usuarios',
            'icon' => 'heroicon-o-users',
            'route' => 'usuarios.index',
            'key' => 'usuarios',
            'permission' => 'usuarios.ver_todos',
        ],
        [
            'label' => 'Ausencias',
            'icon' => 'heroicon-o-calendar-days',
            'route' => null,
            'key' => 'ausencias',
            'permission' => 'ausencias.ver_todas',
        ],
        [
            'label' => 'Incidencias',
            'icon' => 'heroicon-o-exclamation-circle',
            'route' => null,
            'key' => 'incidencias',
            'permission' => 'incidencias.ver_todas',
        ],
        [
            'label' => 'Configuración',
            'icon' => 'heroicon-o-cog-6-tooth',
            'route' => null,
            'key' => 'configuracion',
            'permission' => null,
            'children' => [
                [
                    'label' => 'Empresa',
                    'route' => 'configuracion.empresa',
                    'key' => 'empresa',
                    'permission' => 'configuracion.ver',
                ],
                [
                    'label' => 'Ajustes',
                    'route' => 'configuracion.ajustes',
                    'key' => 'ajustes',
                    'permission' => 'configuracion.ver',
                ],
                [
                    'label' => 'Roles y permisos',
                    'route' => 'configuracion.roles',
                    'key' => 'roles',
                    'permission' => 'roles.gestionar',
                ],
            ],
        ],
    ];
?>


<aside x-data="{
        open: $persist(true).as('sidebar-open'),
        drawerOpen: false,
        menuOpen: false,
        expanded: $persist([]).as('sidebar-expanded'),
        isExpanded(key) { return this.expanded.includes(key); },
        toggleExpand(key) {
            this.expanded = this.isExpanded(key)
                ? []
                : [key];
        }
       }"
       @drawer:open.window="drawerOpen = true"
       @drawer:close.window="drawerOpen = false"
       @keydown.escape.window="menuOpen = false; $dispatch('drawer:close')"
       :class="[
           drawerOpen ? 'translate-x-0' : '-translate-x-full',
           open ? 'md:w-60 md:translate-x-0' : 'md:w-16 md:translate-x-0',
       ]"
       class="fixed inset-y-0 left-0 z-40 flex w-64 shrink-0 flex-col border-r border-slate-200 bg-white transition-all duration-200 md:relative md:inset-auto md:z-auto">

    <?php
        $logoUrl = \App\Support\Branding::logoUrl();
        $marca = \App\Support\Branding::nombre();
        $abreviatura = \App\Support\Branding::abreviatura();
        $logoZoom = \App\Support\Branding::logoZoom();
        $logoEsCuadrado = \App\Support\Branding::logoEsCuadrado();
    ?>

    <div class="flex h-16 items-center justify-between gap-2 border-b border-slate-200 px-3">
        <a href="<?php echo e(route('web.dashboard')); ?>"
           x-data="{ logoRoto: false }"
           class="flex min-w-0 flex-1 items-center justify-center overflow-hidden">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($logoUrl): ?>
                <img x-show="(open || drawerOpen) && ! logoRoto" x-transition.opacity src="<?php echo e($logoUrl); ?>"
                     alt="<?php echo e($marca); ?>"
                     style="max-height: calc(2.25rem * <?php echo e($logoZoom / 100); ?>);"
                     class="w-auto" x-on:error="logoRoto = true">

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($logoEsCuadrado): ?>
                    <img x-show="! (open || drawerOpen) && ! logoRoto" x-transition.opacity src="<?php echo e($logoUrl); ?>"
                         alt="<?php echo e($marca); ?>" class="size-8 object-contain" x-on:error="logoRoto = true">
                <?php else: ?>
                    <span x-show="! (open || drawerOpen)" x-transition.opacity
                          class="text-lg font-bold text-primary-700">
                        <?php echo e($abreviatura); ?>

                    </span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <span x-show="(open || drawerOpen) && logoRoto" x-cloak x-transition.opacity
                      class="text-xs font-medium text-slate-500">
                    Imagen no disponible
                </span>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($logoEsCuadrado): ?>
                    <span x-show="! (open || drawerOpen) && logoRoto" x-cloak x-transition.opacity
                          class="text-xs font-bold text-slate-400">
                        <?php echo e($abreviatura); ?>

                    </span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php else: ?>
                <span x-show="open || drawerOpen" x-transition.opacity
                      class="text-lg font-bold tracking-wide text-primary-700">
                    <?php echo e($marca); ?>

                </span>
                <span x-show="! (open || drawerOpen)" x-transition.opacity
                      class="text-lg font-bold text-primary-700">
                    <?php echo e($abreviatura); ?>

                </span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </a>

        
        <button type="button"
                @click="$dispatch('drawer:close')"
                class="shrink-0 rounded-md p-1.5 text-slate-500 hover:bg-slate-100 md:hidden">
            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-x-mark'); ?>
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
        </button>

        
        <button type="button"
                @click="open = ! open"
                class="hidden shrink-0 rounded-md p-1.5 text-slate-500 hover:bg-slate-100 md:block"
                :title="open ? 'Colapsar menú' : 'Expandir menú'">
            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-bars-3'); ?>
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
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto px-2 py-3">
        <ul class="space-y-0.5">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item['permission'] && ! auth()->user()?->can($item['permission'])): ?>
                    <?php continue; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php
                    $childrenVisibles = collect($item['children'] ?? [])
                        ->filter(fn ($c) => empty($c['permission']) || auth()->user()?->can($c['permission']))
                        ->values()
                        ->all();

                    if (! empty($item['children']) && $item['route'] === null && count($childrenVisibles) === 0) {
                        continue;
                    }

                    $hasChildren = count($childrenVisibles) > 0;
                    $isActive = $active === $item['key'] || collect($childrenVisibles)->pluck('key')->contains($active);
                    $disabled = $item['route'] === null && ! $hasChildren;
                    $href = $item['route'] ? route($item['route']) : '#';
                    $isToggle = $hasChildren && $item['route'] === null;
                    $itemClasses = [
                        'group flex w-full items-center gap-3 rounded-md px-2.5 py-2 text-sm font-medium transition-colors',
                        'bg-accent-100 text-primary-700' => $isActive,
                        'text-slate-700 hover:bg-slate-50 hover:text-slate-900' => ! $isActive && ! $disabled,
                        'text-slate-400 cursor-not-allowed' => $disabled,
                    ];
                ?>

                <li>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isToggle): ?>
                        <button type="button"
                                @click="(open || drawerOpen) ? toggleExpand('<?php echo e($item['key']); ?>') : (open = true, expanded = isExpanded('<?php echo e($item['key']); ?>') ? expanded : [...expanded, '<?php echo e($item['key']); ?>'])"
                                class="<?php echo \Illuminate\Support\Arr::toCssClasses($itemClasses); ?>">
                            <?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => $item['icon']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\DynamicComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-5 shrink-0']); ?>
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
                            <span x-show="open || drawerOpen" x-transition.opacity class="flex-1 truncate text-left">
                                <?php echo e($item['label']); ?>

                            </span>
                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-m-chevron-right'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-show' => 'open || drawerOpen','x-bind:class' => '(isExpanded(\''.e($item['key']).'\') || '.e($isActive ? 'true' : 'false').') ? \'rotate-90\' : \'\'','class' => 'size-4 shrink-0 text-slate-400 transition-transform']); ?>
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
                    <?php else: ?>
                        <a href="<?php echo e($href); ?>"
                           <?php if($disabled): ?> aria-disabled="true" <?php endif; ?>
                           class="<?php echo \Illuminate\Support\Arr::toCssClasses($itemClasses); ?>">
                            <?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => $item['icon']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\DynamicComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-5 shrink-0']); ?>
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
                            <span x-show="open || drawerOpen" x-transition.opacity class="flex-1 truncate text-left">
                                <?php echo e($item['label']); ?>

                            </span>
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasChildren): ?>
                        <ul x-show="(open || drawerOpen) && (isExpanded('<?php echo e($item['key']); ?>') || <?php echo e($isActive ? 'true' : 'false'); ?>)"
                            x-cloak
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            class="mt-0.5 ml-7 space-y-0.5 border-l border-slate-200 pl-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $childrenVisibles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php
                                    $childActive = $active === $child['key'];
                                    $childHref = $child['route'] ? route($child['route']) : '#';
                                ?>
                                <li>
                                    <a href="<?php echo e($childHref); ?>"
                                       class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                           'block rounded-md px-2.5 py-1.5 text-sm transition-colors',
                                           'bg-primary-50 text-primary-700 font-medium' => $childActive,
                                           'text-slate-600 hover:bg-slate-50 hover:text-slate-900' => ! $childActive,
                                       ]); ?>">
                                        <?php echo e($child['label']); ?>

                                    </a>
                                </li>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </ul>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </li>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </ul>
    </nav>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
        <?php
            $user = auth()->user();
            $nombreCompleto = trim($user->nombre.' '.$user->apellidos);
            $inicial = mb_strtoupper(mb_substr($nombreCompleto, 0, 1));
            $rolPrincipal = $user->getRoleNames()->first() ?? 'Sin rol';
        ?>
        <div class="border-t border-slate-200">

            <div x-show="menuOpen"
                 x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-1"
                 class="border-b border-slate-100">

                <div class="px-2 py-1">
                    <a href="<?php echo e(route('perfil.mi-perfil')); ?>"
                       class="flex w-full items-center gap-2.5 rounded-md px-2.5 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50">
                        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-user-circle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-4 shrink-0']); ?>
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
                        <span x-show="open || drawerOpen" x-transition.opacity class="flex-1 text-left">Mi perfil</span>
                    </a>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->tieneAccesoMovil()): ?>
                        <a href="<?php echo e(route('mobile.dashboard')); ?>"
                           class="flex w-full items-center gap-2.5 rounded-md px-2.5 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50">
                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-device-phone-mobile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-4 shrink-0']); ?>
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
                            <span x-show="open || drawerOpen" x-transition.opacity class="flex-1 text-left">Versión móvil</span>
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit"
                                class="flex w-full items-center gap-2.5 rounded-md px-2.5 py-2 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50">
                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-arrow-right-on-rectangle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-4 shrink-0']); ?>
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
                            <span x-show="open || drawerOpen" x-transition.opacity class="flex-1 text-left">Cerrar sesión</span>
                        </button>
                    </form>
                </div>
            </div>

            <div class="p-2">
                <button type="button"
                        @click="menuOpen = ! menuOpen"
                        :class="menuOpen ? 'bg-slate-100' : ''"
                        class="flex w-full items-center gap-2.5 rounded-md px-1.5 py-1.5 transition-colors hover:bg-slate-100">
                    <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary-100 text-sm font-semibold text-primary-700">
                        <?php echo e($inicial); ?>

                    </div>
                    <div x-show="open || drawerOpen" x-transition.opacity class="min-w-0 flex-1 text-left">
                        <p class="truncate text-sm font-medium text-slate-800"><?php echo e($nombreCompleto); ?></p>
                        <p class="truncate text-xs capitalize text-slate-500"><?php echo e($rolPrincipal); ?></p>
                    </div>
                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-m-chevron-up'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-show' => 'open || drawerOpen','x-bind:class' => 'menuOpen ? \'\' : \'rotate-180\'','class' => 'size-4 shrink-0 text-slate-400 transition-transform']); ?>
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
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</aside>
<?php /**PATH D:\xampp\htdocs\CLIENTES\ELECIND\resources\views/components/ui/sidebar.blade.php ENDPATH**/ ?>