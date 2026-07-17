@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'min-h-11 rounded-2xl border-brand-border bg-white shadow-sm transition focus:border-brand-primary focus:ring-brand-primary']) }}>
