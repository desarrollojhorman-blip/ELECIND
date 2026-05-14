{{--
  Barra fija inferior con espacio para uno o varios botones de acción.
  Usa safe-area en iOS para no quedar pegada al borde inferior.
--}}
<div {{ $attributes->class('sticky bottom-0 z-10 mt-auto border-t border-slate-200 bg-white px-4 py-3 shadow-[0_-4px_12px_rgba(0,0,0,0.04)]') }}
     style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom));">
    <div class="flex items-center gap-2">
        {{ $slot }}
    </div>
</div>
