<a {{ $attributes->merge([
    'class' => 'inline-block px-5 py-2 rounded bg-black text-white hover:bg-gray-800 transition'
]) }}>
    {{ $slot }}
</a>
