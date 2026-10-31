@props(['name', 'label', 'type' => 'text'])

@php
    $hasError = $errors->has($name);
@endphp

<div class="space-y-1.5">
    <label for="{{ $name }}" class="block text-sm font-medium text-slate-700">{{ $label }}</label>

    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @if ($hasError) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
        {{ $attributes->merge([
            'class' => 'block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset transition placeholder:text-slate-400 focus:ring-2 focus:ring-inset sm:text-sm '.($hasError ? 'ring-rose-400 focus:ring-rose-500' : 'ring-slate-300 focus:ring-accent-600'),
        ]) }}
    >

    @error($name)
        <p id="{{ $name }}-error" class="text-sm text-rose-600">{{ $message }}</p>
    @enderror
</div>
