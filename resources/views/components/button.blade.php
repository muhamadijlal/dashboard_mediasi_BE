<button disabled id="btnFilter" {{ $attributes->merge(['type' => 'submit', 'class' => 'disabled:opacity-50  px-4 py-2 bg-blue-950 rounded-md text-white uppercase font-bold tracking-widest text-xs focus:outline-none cursor-pointer focus:ring-2 disabled:hover:cursor-not-allowed hover:bg-opacity-90 disabled:hover:bg-opacity-100 focus:ring-yellow-400']) }}>
    {{ $slot }}
</button>