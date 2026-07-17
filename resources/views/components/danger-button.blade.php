<button {{ $attributes->merge(['type' => 'submit', 'class' => 'dc-button-danger']) }}>
    {{ $slot }}
</button>
