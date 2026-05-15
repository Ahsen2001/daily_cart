<button {{ $attributes->merge(['type' => 'submit', 'class' => 'dc-button']) }}>
    {{ $slot }}
</button>
