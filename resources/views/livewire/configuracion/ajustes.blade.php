<div class="mb-4 space-y-4">
        <h2 class="text-xl font-semibold text-slate-900">Ajustes</h2>
        <p class="text-sm text-slate-500">Identidad visual, numeración y configuración general.</p>

    {{-- CONTENEDOR GLOBAL: guardado controlado por Livewire (sin submit HTML nativo) --}}
    <div class="space-y-6">
        <div x-data="{ tab: 'identidad' }">
            {{-- Tabs (solo navegación con Alpine) --}}
            <div class="flex items-end border-b border-slate-200 px-2 pt-1.5">
                @foreach ([
                    ['key' => 'identidad',   'label' => 'Identidad visual'],
                    ['key' => 'numeracion',  'label' => 'Numeración'],
                    ['key' => 'firma',       'label' => 'Firma y archivos'],
                ] as $t)
                    <button type="button"
                            @click="tab = '{{ $t['key'] }}'"
                            :class="tab === '{{ $t['key'] }}'
                                ? '-mb-px border border-slate-200 border-b-white bg-white rounded-t-lg text-primary-700 font-semibold'
                                : 'text-slate-500 hover:text-slate-700'"
                            class="flex items-center gap-1.5 whitespace-nowrap px-5 py-3 text-sm transition-colors">
                        {{ $t['label'] }}
                    </button>
                @endforeach
                @role('superadmin')
                    <button type="button"
                            @click="tab = 'correo'"
                            :class="tab === 'correo'
                                ? '-mb-px border border-slate-200 border-b-white bg-white rounded-t-lg text-primary-700 font-semibold'
                                : 'text-slate-500 hover:text-slate-700'"
                            class="flex items-center gap-1.5 whitespace-nowrap px-5 py-3 text-sm transition-colors">
                        Correo
                    </button>
                    <button type="button"
                            @click="tab = 'modulos'"
                            :class="tab === 'modulos'
                                ? '-mb-px border border-slate-200 border-b-white bg-white rounded-t-lg text-primary-700 font-semibold'
                                : 'text-slate-500 hover:text-slate-700'"
                            class="flex items-center gap-1.5 whitespace-nowrap px-5 py-3 text-sm transition-colors">
                        Módulos
                    </button>
                @endrole
            </div>

            {{-- ═══ Tab: Identidad visual ═══ --}}
            <div x-show="tab === 'identidad'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white p-6 shadow-sm">

                {{-- Logo de la aplicación --}}
                <div class="mb-6">
                    <h3 class="mb-1 text-sm font-semibold text-slate-800">Logo de la aplicación</h3>
                    <p class="mb-4 text-xs text-slate-500">
                        Aparece en login, barra lateral y cabecera móvil. Tiene <strong>prioridad absoluta</strong> sobre el logo de empresa.
                    </p>

                    @php
                        $logoAppUrlActual = $nuevoLogoApp
                            ? $nuevoLogoApp->temporaryUrl()
                            : (! $eliminarLogoApp && $logo_app_path
                                ? \Illuminate\Support\Facades\Storage::disk('public')->url($logo_app_path)
                                : null);
                        $zoomOpciones = [80, 90, 100, 110, 120, 130];
                    @endphp

                    <div class="grid gap-5 md:grid-cols-[auto_1fr_auto]">
                        <div class="flex size-28 shrink-0 items-center justify-center overflow-hidden rounded-md border border-slate-200 bg-slate-50">
                            @if ($logoAppUrlActual)
                                <img src="{{ $logoAppUrlActual }}" alt="Previsualización" class="max-h-full max-w-full object-contain">
                            @else
                                <x-heroicon-o-photo class="size-8 text-slate-300" />
                            @endif
                        </div>

                        <div class="space-y-2">
                            @if (! $eliminarLogoApp)
                                    <input id="nuevo_logo_app"
                                        name="nuevo_logo_app"
                                        type="file"
                                       wire:model="nuevoLogoApp"
                                       accept="image/png,image/jpeg,image/svg+xml,image/webp"
                                       class="block w-full text-sm text-slate-700 file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-200">
                                <p class="text-xs text-slate-500">PNG/JPG/SVG/WebP, máx. 2 MB.</p>
                                <div wire:loading wire:target="nuevoLogoApp" class="text-xs text-slate-500">Subiendo…</div>
                                @error('nuevoLogoApp')
                                    <p class="text-xs text-red-600">{{ $message }}</p>
                                @enderror
                                @if ($nuevoLogoApp)
                                    <x-ui.button variant="neutral" size="sm" type="button" wire:click="descartarNuevoLogoApp">
                                        Descartar imagen seleccionada
                                    </x-ui.button>
                                @elseif ($logo_app_path)
                                    <x-ui.button variant="danger" size="sm" type="button" wire:click="quitarLogoApp">
                                        Quitar logo actual
                                    </x-ui.button>
                                @endif
                            @else
                                <div class="rounded-md border border-amber-200 bg-amber-50 p-3">
                                    <p class="text-sm text-amber-800">El logo se eliminará al guardar.</p>
                                    <x-ui.button variant="neutral" size="sm" type="button" wire:click="cancelarQuitarLogoApp" class="mt-2">
                                        Cancelar
                                    </x-ui.button>
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-col items-center gap-2">
                            <p class="text-xs text-slate-500">Zoom</p>
                            <x-ui.select wire:model="logo_app_zoom" class="w-20 text-center">
                                @foreach ($zoomOpciones as $z)
                                    <option value="{{ $z }}">{{ $z }}%</option>
                                @endforeach
                            </x-ui.select>
                        </div>
                    </div>
                </div>

                {{-- Colores --}}
                <div class="border-t border-slate-100 pt-6">
                    <h3 class="mb-1 text-sm font-semibold text-slate-800">Colores de la aplicación</h3>
                    <p class="mb-4 text-xs text-slate-500">Afectan a botones, encabezados de tablas y elementos de marca.</p>

                    @php
                        $colorPrimarioConfig = \App\Support\AjustesFields::getField('color_primario');
                        $colorSecundarioConfig = \App\Support\AjustesFields::getField('color_secundario');
                        $colorTextoEncabezadoConfig = \App\Support\AjustesFields::getField('color_texto_encabezado');
                    @endphp

                    <div class="grid gap-4 md:grid-cols-2">
                        <x-ui.field label="Color primario" for="color_primario" required :error="$errors->first('color_primario')">
                            <div class="flex items-center gap-2">
                                <input type="color"
                                       wire:model.live="color_primario"
                                       class="h-10 w-14 cursor-pointer rounded border border-slate-300">
                                <x-ui.input id="color_primario"
                                            name="color_primario"
                                            wire:model.live.debounce.500ms="color_primario"
                                            placeholder="#334155" class="font-mono" maxlength="{{ $colorPrimarioConfig['maxLength'] ?? 7 }}" />
                                <button type="button"
                                        wire:click="$set('color_primario', '#334155')"
                                        title="Restablecer"
                                        class="shrink-0 rounded-md p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                                    <x-heroicon-o-arrow-path class="size-4" />
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">{{ $colorPrimarioConfig['help'] ?? '' }}</p>
                        </x-ui.field>

                        <x-ui.field label="Color secundario" for="color_secundario" required :error="$errors->first('color_secundario')">
                            <div class="flex items-center gap-2">
                                <input type="color"
                                       wire:model.live="color_secundario"
                                       class="h-10 w-14 cursor-pointer rounded border border-slate-300">
                                <x-ui.input id="color_secundario"
                                            name="color_secundario"
                                            wire:model.live.debounce.500ms="color_secundario"
                                            placeholder="#f1f5f9" class="font-mono" maxlength="{{ $colorSecundarioConfig['maxLength'] ?? 7 }}" />
                                <button type="button"
                                        wire:click="$set('color_secundario', '#f1f5f9')"
                                        title="Restablecer"
                                        class="shrink-0 rounded-md p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                                    <x-heroicon-o-arrow-path class="size-4" />
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">{{ $colorSecundarioConfig['help'] ?? '' }}</p>
                        </x-ui.field>

                        <x-ui.field label="Color texto encabezado tabla" for="color_texto_encabezado" required :error="$errors->first('color_texto_encabezado')">
                            <div class="flex items-center gap-2">
                                <input type="color"
                                       wire:model.live="color_texto_encabezado"
                                       class="h-10 w-14 cursor-pointer rounded border border-slate-300">
                                <x-ui.input id="color_texto_encabezado"
                                            name="color_texto_encabezado"
                                            wire:model.live.debounce.500ms="color_texto_encabezado"
                                            placeholder="#ffffff" class="font-mono" maxlength="{{ $colorTextoEncabezadoConfig['maxLength'] ?? 7 }}" />
                                <button type="button"
                                        wire:click="$set('color_texto_encabezado', '#ffffff')"
                                        title="Restablecer"
                                        class="shrink-0 rounded-md p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                                    <x-heroicon-o-arrow-path class="size-4" />
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">{{ $colorTextoEncabezadoConfig['help'] ?? '' }}</p>
                        </x-ui.field>

                        <div class="flex items-center justify-center rounded-md border border-slate-200 p-4"
                             style="background-color: {{ $color_secundario }};">
                            <div>
                                <p class="text-xs uppercase tracking-wide" style="color: {{ $color_primario }};">Previsualización</p>
                                <p class="mt-1 text-sm font-semibold" style="color: {{ $color_primario }};">
                                    {{ \App\Support\Branding::nombre() }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══ Tab: Numeración ═══ --}}
            <div x-show="tab === 'numeracion'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="mb-1 text-sm font-semibold text-slate-900">Plantillas de numeración</h3>
                <ul class="mb-4 space-y-1 text-xs text-slate-500">
                    <li><code class="rounded bg-slate-100 px-1 font-mono">{YYYY}</code> → año completo <span class="text-slate-400">(ej. 2025)</span></li>
                    <li><code class="rounded bg-slate-100 px-1 font-mono">{YY}</code> → año 2 dígitos <span class="text-slate-400">(ej. 25)</span></li>
                    <li><code class="rounded bg-slate-100 px-1 font-mono">{MM}</code> → mes <span class="text-slate-400">(ej. 05)</span></li>
                    <li><code class="rounded bg-slate-100 px-1 font-mono">{NNNN}</code> → secuencial 4 dígitos <span class="text-slate-400">(ej. 0042)</span></li>
                    <li><code class="rounded bg-slate-100 px-1 font-mono">{NNN}</code> → secuencial 3 dígitos <span class="text-slate-400">(ej. 042)</span></li>
                    <li><code class="rounded bg-slate-100 px-1 font-mono">{NN}</code> → secuencial 2 dígitos <span class="text-slate-400">(ej. 42)</span></li>
                    <li><code class="rounded bg-slate-100 px-1 font-mono">{N}</code> → secuencial sin ceros <span class="text-slate-400">(ej. 42)</span></li>
                </ul>
                <p class="mb-4 text-xs text-amber-700">💡 Puedes poner lo que quieras. Si es incorrecto, lo verás al crear el primer registro.</p>

                   @php
                       $plantillaAlbaranConfig = \App\Support\AjustesFields::getField('plantilla_numeracion_albaran');
                       $prefijoProyectoConfig  = \App\Support\AjustesFields::getField('prefijo_proyecto');
                   @endphp

                <div class="grid gap-4 md:grid-cols-2">
                    <x-ui.field label="Número de albarán" for="plantilla_numeracion_albaran" :error="$errors->first('plantilla_numeracion_albaran')">
                        <x-ui.input id="plantilla_numeracion_albaran" name="plantilla_numeracion_albaran" wire:model.live.debounce.500ms="plantilla_numeracion_albaran" class="font-mono" maxlength="{{ $plantillaAlbaranConfig['maxLength'] ?? 60 }}" />
                    </x-ui.field>

                    <x-ui.field label="Prefijo código proyecto" for="prefijo_proyecto" :error="$errors->first('prefijo_proyecto')">
                        <x-ui.input id="prefijo_proyecto" name="prefijo_proyecto" wire:model.live.debounce.500ms="prefijo_proyecto" class="font-mono uppercase" maxlength="10"
                                    placeholder="PR" />
                        <p class="mt-1 text-xs text-slate-500">
                            {{ $prefijoProyectoConfig['help'] ?? '' }}
                            Sugerencia generada: <code class="rounded bg-slate-100 px-1 font-mono">{{ now()->format('y') }}{{ strtoupper(trim($prefijo_proyecto)) ?: 'PR' }}-1-</code>
                        </p>
                    </x-ui.field>
                </div>
            </div>

            {{-- ═══ Tab: Firma y archivos ═══ --}}
            <div x-show="tab === 'firma'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white p-6 shadow-sm">

                <div class="grid gap-8 md:grid-cols-2">
                    {{-- Firma digital --}}
                    <div>
                        <h3 class="mb-1 text-sm font-semibold text-slate-900">Firma digital</h3>
                        <p class="mb-4 text-xs text-slate-500">Configuración del enlace de firma que se envía por email.</p>

                        <x-ui.field label="Caducidad del token de firma (días)"
                                for="token_caducidad_dias"
                                    required
                                    :error="$errors->first('token_caducidad_dias')">
                            <x-ui.input id="token_caducidad_dias" name="token_caducidad_dias" type="number" min="1" max="90" wire:model="token_caducidad_dias" />
                        </x-ui.field>
                    </div>

                    {{-- Archivos adjuntos --}}
                    <div>
                        <h3 class="mb-1 text-sm font-semibold text-slate-900">Archivos adjuntos en albaranes</h3>
                        <p class="mb-4 text-xs text-slate-500">
                            Límites aplicados al subir documentos desde el tab «Archivos».
                            Límite del servidor PHP: <strong>{{ ini_get('upload_max_filesize') }}</strong>.
                        </p>

                        <div class="space-y-4">
                                <x-ui.field label="Tamaño máximo por archivo"
                                    for="archivo_tamano_max_mb"
                                        required
                                        :error="$errors->first('archivo_tamano_max_mb')">
                                <x-ui.select id="archivo_tamano_max_mb" name="archivo_tamano_max_mb" wire:model="archivo_tamano_max_mb">
                                    @foreach ([2, 5, 10, 20, 50] as $mb)
                                        <option value="{{ $mb }}">{{ $mb }} MB</option>
                                    @endforeach
                                </x-ui.select>
                            </x-ui.field>

                                <x-ui.field label="Cantidad máxima de archivos por albarán"
                                    for="archivo_cantidad_max"
                                        required
                                        :error="$errors->first('archivo_cantidad_max')">
                                <x-ui.input id="archivo_cantidad_max" name="archivo_cantidad_max" type="number" min="1" max="100" wire:model="archivo_cantidad_max" />
                                <p class="mt-1 text-xs text-slate-500">Entre 1 y 100 archivos.</p>
                            </x-ui.field>
                        </div>
                    </div>
                </div>
            </div>


            {{-- ═══ Tab: Correo (solo superadmin) ═══ --}}
            @role('superadmin')
            <div x-show="tab === 'correo'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="mb-1 text-sm font-semibold text-slate-900">Servidor de correo saliente (SMTP)</h3>
                <p class="mb-6 text-xs text-slate-500">
                    Credenciales usadas para enviar correos de solicitud de firma. Funciona con Gmail, servidores propios y cualquier proveedor SMTP.
                </p>

                @if (session('correo_status'))
                    <div class="mb-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                        {{ session('correo_status') }}
                    </div>
                @endif

                <div class="grid gap-4 md:grid-cols-2">
                    <x-ui.field label="Host SMTP" required :error="$errors->first('mail_host')">
                        <x-ui.input wire:model="mail_host" placeholder="smtp.gmail.com" />
                        <p class="mt-1 text-xs text-slate-500">Gmail: smtp.gmail.com · Outlook: smtp.office365.com</p>
                    </x-ui.field>

                    <div class="grid grid-cols-2 gap-3">
                        <x-ui.field label="Puerto" required :error="$errors->first('mail_port')">
                            <x-ui.select wire:model="mail_port">
                                <option value="587">587 (recomendado)</option>
                                <option value="465">465</option>
                                <option value="25">25</option>
                                <option value="2525">2525</option>
                            </x-ui.select>
                        </x-ui.field>

                        <x-ui.field label="Cifrado" required :error="$errors->first('mail_encryption')">
                            <x-ui.select wire:model="mail_encryption">
                                <option value="tls">TLS</option>
                                <option value="ssl">SSL</option>
                                <option value="starttls">STARTTLS</option>
                                <option value="none">Ninguno</option>
                            </x-ui.select>
                        </x-ui.field>
                    </div>

                    <x-ui.field label="Usuario" required :error="$errors->first('mail_username')">
                        <x-ui.input wire:model="mail_username" placeholder="correo@tuempresa.com" autocomplete="off" />
                    </x-ui.field>

                    <x-ui.field label="Contraseña" :error="$errors->first('mail_password')"
                                x-data="{ mostrar: false }">
                        <div class="relative">
                            <x-ui.input wire:model="mail_password"
                                        x-bind:type="mostrar ? 'text' : 'password'"
                                        placeholder="••••••••"
                                        autocomplete="new-password" />
                            <button type="button"
                                    x-on:click="mostrar = !mostrar"
                                    class="absolute inset-y-0 right-2 flex items-center text-slate-400 hover:text-slate-600">
                                <x-heroicon-o-eye x-show="!mostrar" class="size-4" />
                                <x-heroicon-o-eye-slash x-show="mostrar" class="size-4" />
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">Gmail: usa una "contraseña de aplicación", no la de tu cuenta.</p>
                    </x-ui.field>

                    <x-ui.field label="Correo remitente (From)" required :error="$errors->first('mail_from_address')">
                        <x-ui.input wire:model="mail_from_address" type="email" placeholder="noreply@tuempresa.com" />
                    </x-ui.field>

                    <x-ui.field label="Nombre remitente" required :error="$errors->first('mail_from_name')">
                        <x-ui.input wire:model="mail_from_name" placeholder="ENIA Firma" />
                    </x-ui.field>
                </div>

                <div class="mt-6 flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                    <x-ui.button variant="neutral" type="button"
                                 wire:click="probarConexionCorreo"
                                 wire:loading.attr="disabled"
                                 wire:target="probarConexionCorreo">
                        <x-heroicon-o-paper-airplane wire:loading.remove wire:target="probarConexionCorreo" class="size-4" />
                        <svg wire:loading wire:target="probarConexionCorreo" class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                        </svg>
                        <span wire:loading.remove wire:target="probarConexionCorreo">Probar conexión</span>
                        <span wire:loading wire:target="probarConexionCorreo">Enviando…</span>
                    </x-ui.button>

                    <x-ui.button variant="info" type="button"
                                 wire:click="guardarCorreo"
                                 wire:loading.attr="disabled"
                                 wire:target="guardarCorreo">
                        <x-heroicon-o-arrow-down-tray wire:loading.remove wire:target="guardarCorreo" class="size-4" />
                        <svg wire:loading wire:target="guardarCorreo" class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                        </svg>
                        <span wire:loading.remove wire:target="guardarCorreo">Guardar</span>
                        <span wire:loading wire:target="guardarCorreo">Guardando…</span>
                    </x-ui.button>
                </div>
            </div>
            @endrole

            {{-- ═══ Tab: Módulos (solo superadmin) ═══ --}}
            @role('superadmin')
            <div x-show="tab === 'modulos'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="mb-1 text-sm font-semibold text-slate-900">Módulos opcionales</h3>
                <p class="mb-6 text-xs text-slate-500">
                    Los módulos permiten activar o desactivar funcionalidades completas de la aplicación.
                    Los datos nunca se eliminan al desactivar un módulo.
                </p>

                <div class="max-w-lg space-y-4">
                    <div class="flex items-start gap-4 rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-slate-800">Módulo de materiales</p>
                            <p class="mt-1 text-xs text-slate-500">
                                Cuando está <strong>activo</strong>: pedidos, familias, stock, precios y líneas de material en albaranes.<br>
                                Cuando está <strong>inactivo</strong>: todo el bloque de materiales se oculta; los albaranes solo registran horas de trabajadores.
                            </p>
                        </div>
                        <label class="relative inline-flex shrink-0 cursor-pointer items-center">
                            <input type="checkbox"
                                   wire:model="modulo_materiales_avanzado"
                                   class="peer sr-only">
                            <div class="peer h-6 w-11 rounded-full bg-slate-300 transition-colors peer-checked:bg-primary-600
                                        after:absolute after:left-[2px] after:top-[2px] after:size-5 after:rounded-full after:bg-white
                                        after:shadow after:transition-transform after:content-[''] peer-checked:after:translate-x-full"></div>
                        </label>
                    </div>

                    <div class="flex justify-end">
                        <x-ui.button variant="info" type="button"
                                     wire:click="toggleModuloMateriales"
                                     wire:loading.attr="disabled"
                                     wire:target="toggleModuloMateriales">
                            <span wire:loading.remove wire:target="toggleModuloMateriales">Aplicar</span>
                            <span wire:loading wire:target="toggleModuloMateriales">Guardando…</span>
                        </x-ui.button>
                    </div>
                </div>
            </div>
            @endrole

            {{-- ═══ AVISO GENERAL DE VALIDACIÓN (sin duplicar errores por campo) ═══ --}}
            @if ($errors->any())
                <div class="mt-4 rounded-md border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
                    Revisa los campos marcados en rojo para continuar.
                </div>
            @endif



            {{-- ═══ BOTONES GLOBALES (ocultos en tabs con guardado propio) ═══ --}}
            <div x-show="tab !== 'modulos' && tab !== 'correo'" class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                <x-ui.button variant="neutral" type="button" wire:click="deshacer" wire:loading.attr="disabled" wire:target="deshacer">
                    <x-heroicon-o-arrow-uturn-left wire:loading.remove wire:target="deshacer" class="size-4" />
                    <svg wire:loading wire:target="deshacer" class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                    </svg>
                    <span wire:loading.remove wire:target="deshacer">Deshacer</span>
                    <span wire:loading wire:target="deshacer">Deshaciendo…</span>
                </x-ui.button>
                <x-ui.button variant="info" type="button" wire:click="guardar" wire:target="guardar" wire:loading.attr="disabled">
                    <x-heroicon-o-arrow-down-tray wire:loading.remove wire:target="guardar" class="size-4" />
                    <svg wire:loading wire:target="guardar" class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                    </svg>
                    <span wire:loading.remove wire:target="guardar">Guardar</span>
                    <span wire:loading wire:target="guardar">Guardando…</span>
                </x-ui.button>
            </div>

            {{-- Bloqueo global durante guardado --}}
            <div wire:loading.flex wire:target="guardar,guardarCorreo" class="fixed inset-0 z-[9999] items-center justify-center bg-black/50">
                <div class="rounded-lg bg-white px-8 py-8 shadow-2xl">
                    <div class="mb-4 flex justify-center">
                        <div class="size-12 animate-spin rounded-full border-4 border-slate-300 border-t-blue-600"></div>
                    </div>
                    <p class="text-lg font-semibold text-slate-900">Guardando ajustes...</p>
                    <p class="mt-2 text-sm text-slate-600">Por favor, espera a que se complete.</p>
                </div>
            </div>
        </div>
    </div>
</div>
