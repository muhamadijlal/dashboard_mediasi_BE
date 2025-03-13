<!-- Backdrop Layer -->
<div id="backdrop" class="hidden fixed inset-0 bg-black bg-opacity-40 z-[60]"></div>

<!-- Sidebar -->
<aside class="inset-0 md:left-0 -left-[300px] bg-white h-full p-8 fixed overflow-y-auto max-w-[300px] w-full z-[65] ease-out duration-300" id="sidebar">
  <div class="w-full pb-20 flex flex-col gap-3 mt-5">
    <!-- navigation menu -->
    <ul class="space-y-3">
        <li>
            <details {{ request()->routeIs('mediasi.*') ? "open" : ""}}>
                <summary class="hover:bg-yellow-400 p-1 pr-2 flex items-center justify-between text-base font-medium rounded-lg {{ request()->routeIs('mediasi.*') ? "bg-yellow-400" : "" }}">
                    <a href="#" class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-md bg-blue-950 flex justify-center items-center">
                        <i class="fa-solid fa-code-compare text-yellow-400"></i>
                        </div>
                        {{ __("Mediasi") }}
                    </a>
    
                    <i id="caret-menu" class="fa-solid fa-angle-up transition-all duration-100"></i>
                </summary>

                <ul id="submenu" class="ml-5 border-l-[1.5px] border-slate-200 mt-3 space-y-2">
                    <li>
                        <a href="{{ route("mediasi.transaction_detail.dashboard") }}" class="text-base font-normal pl-5 border-l-[1.5px] -ml-[1.5px]  hover:text-blue-950 hover:border-blue-950 {{ request()->routeIs('mediasi.transaction_detail.*') ? "text-blue-950 border-blue-950" : "text-gray-400" }}">{{ __("Transaksi Detail") }}</a>
                    </li>
                    <li>
                        <a href="{{ route("mediasi.recap_at4.dashboard") }}" class="text-base font-normal pl-5 border-l-[1.5px] -ml-[1.5px]  hover:text-blue-950 hover:border-blue-950 {{ request()->routeIs('mediasi.recap_at4.*') ? "text-blue-950 border-blue-950" : "text-gray-400" }}">{{ __("Rekap AT4") }}</a>
                    </li>
                    <li>
                        <a href="{{ route("mediasi.data_compare.dashboard") }}" class="text-base font-normal pl-5 border-l-[1.5px] -ml-[1.5px]  hover:text-blue-950 hover:border-blue-950 {{ request()->routeIs('mediasi.data_compare.*') ? "text-blue-950 border-blue-950" : "text-gray-400" }}">{{ __("Data Compare") }}</a>
                    </li>
                    <li>
                        <a href="{{ route("mediasi.lalin_gerbang_utama.dashboard") }}" class="text-base font-normal pl-5 border-l-[1.5px] -ml-[1.5px]  hover:text-blue-950 hover:border-blue-950 {{ request()->routeIs('mediasi.lalin_gerbang_utama.*') ? "text-blue-950 border-blue-950" : "text-gray-400" }}">{{ __("Lalin Gerbang Utama") }}</a>
                    </li>
                </ul>
            </details>
        </li>

        <!-- <li>
            <details {{ request()->routeIs('resi.*') ? "open" : ""}}>
                <summary class="hover:bg-yellow-400 p-1 pr-2 flex items-center justify-between text-base font-medium rounded-lg {{ request()->routeIs('resi.*') ? "bg-yellow-400" : "" }}">
                    <a href="#" class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-md bg-blue-950 flex justify-center items-center">
                            <i class="fa-solid fa-file-invoice text-yellow-400"></i>
                        </div>
                        {{ __("Resi Digital") }}
                    </a>
    
                    <i id="caret-menu" class="fa-solid fa-angle-up transition-all duration-100"></i>
                </summary>

                <ul id="submenu" class="ml-5 border-l-[1.5px] border-slate-200 mt-3 space-y-2">
                    <li>
                        <a href="{{ route("resi.transaction_detail.dashboard") }}" class="text-base font-normal pl-5 border-l-[1.5px] -ml-[1.5px]  hover:text-blue-950 hover:border-blue-950 {{ request()->routeIs('resi.transaction_detail.*') ? "text-blue-950 border-blue-950" : "text-gray-400" }}">{{ __("Transaction Detail") }}</a>
                    </li>
                    <li>
                        <a href="{{ route("resi.sync.transaction_detail.dashboard") }}" class="text-base font-normal pl-5 border-l-[1.5px] -ml-[1.5px]  hover:text-blue-950 hover:border-blue-950 {{ request()->routeIs('resi.sync.transaction_detail.*') ? "text-blue-950 border-blue-950" : "text-gray-400" }}">{{ __("Sync Transaction Detail") }}</a>
                    </li>
                    <li>  
                        <a href="{{ route("resi.data_compare.dashboard") }}" class="text-base font-normal pl-5 border-l-[1.5px] -ml-[1.5px]  hover:text-blue-950 hover:border-blue-950 {{ request()->routeIs('resi.data_compare.*') ? "text-blue-950 border-blue-950" : "text-gray-400" }}">{{ __("Data Compare") }}</a>
                    </li>
                </ul>
            </details>
        </li> -->
    </ul>
  </div>
</aside>
