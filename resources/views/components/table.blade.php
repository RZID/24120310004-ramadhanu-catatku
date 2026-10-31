@props(['head' => []])

<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200']) }}>
    <table class="w-full text-left text-sm">
        <thead class="bg-slate-50 max-sm:hidden">
            <tr class="border-b border-slate-200">
                @foreach ($head as $label)
                    <th scope="col" class="px-4 py-3 text-xs font-semibold tracking-wide text-slate-500 uppercase">
                        {{ $label }}
                    </th>
                @endforeach
            </tr>
        </thead>

        <tbody class="divide-y divide-slate-200 max-sm:block">
            {{ $slot }}
        </tbody>
    </table>
</div>
