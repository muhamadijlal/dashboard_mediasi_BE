<button {{ $attributes->merge(['type' => 'submit', 'class' => 'px-4 py-2 bg-blue-950 rounded-md text-white uppercase font-bold tracking-widest text-xs focus:outline-none focus:ring-2 hover:bg-opacity-90 focus:ring-yellow-400']) }}>
    {{ $slot }}
</button>