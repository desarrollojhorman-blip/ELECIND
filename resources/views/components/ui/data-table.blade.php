@props(['empty' => 'No hay registros que coincidan.', 'colspan' => 1])

<div {{ $attributes->class('overflow-hidden rounded-md border border-slate-200 bg-white shadow-sm') }}>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            @isset ($head)
                <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                    {{ $head }}
                </thead>
            @endisset
            <tbody class="divide-y divide-slate-100 bg-white text-slate-700">
                @if (isset($rows) && trim($rows) !== '')
                    {{ $rows }}
                @else
                    <tr>
                        <td colspan="{{ $colspan }}" class="px-4 py-12 text-center text-sm text-slate-500">
                            {{ $empty }}
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
